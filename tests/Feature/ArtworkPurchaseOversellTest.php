<?php

namespace Tests\Feature;

use App\Http\Controllers\ArtworkPurchaseController;
use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\ArtworkOrder;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Test de non-régression pour la correction de la survente sur œuvre à stock
 * unique (audit BIZ-01) : deux commandes payées concurremment sur la même pièce
 * ne doivent jamais aboutir toutes les deux à un statut "paid" silencieux.
 */
class ArtworkPurchaseOversellTest extends TestCase
{
    use RefreshDatabase;

    private function completePurchase(ArtworkOrder $order, string $token): void
    {
        $controller = app(ArtworkPurchaseController::class);
        $method = new ReflectionMethod($controller, 'completePurchase');
        $method->setAccessible(true);
        $method->invoke($controller, $order, $token);
    }

    public function test_second_concurrent_payment_on_single_stock_artwork_is_flagged_oversold(): void
    {
        $providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'art-creations-test', 'name_fr' => 'Art', 'name_en' => 'Art', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $providerUser->id,
            'category_id' => $category->id,
            'name' => 'Atelier Test',
            'slug' => 'atelier-test-'.uniqid(),
            'status' => 'active',
        ]);

        $artCategory = ArtworkCategory::create(['slug' => 'peinture-test', 'name_fr' => 'Peinture', 'name_en' => 'Painting', 'is_active' => true]);
        $artwork = Artwork::create([
            'provider_id' => $provider->id,
            'category_id' => $artCategory->id,
            'title' => 'Pièce Unique',
            'slug' => 'piece-unique-'.uniqid(),
            'price_xof' => 50000,
            'stock_quantity' => 1,
            'images' => ['/storage/test.jpg'],
            'status' => Artwork::STATUS_PUBLISHED,
        ]);

        $buyer1 = User::factory()->create();
        $buyer2 = User::factory()->create();

        $orderData = [
            'artwork_id' => $artwork->id,
            'provider_id' => $provider->id,
            'unit_price_xof' => 50000,
            'amount_total_xof' => 50000,
            'commission_percent' => 15,
            'commission_amount_xof' => 7500,
            'artist_net_amount_xof' => 42500,
            'currency' => 'XOF',
            'status' => ArtworkOrder::STATUS_PENDING_PAYMENT,
            'gateway' => 'cinetpay',
        ];

        $order1 = ArtworkOrder::create([...$orderData, 'buyer_user_id' => $buyer1->id, 'gateway_txn_id' => 'TXN-1', 'buyer_name' => 'B1', 'buyer_email' => $buyer1->email]);
        $order2 = ArtworkOrder::create([...$orderData, 'buyer_user_id' => $buyer2->id, 'gateway_txn_id' => 'TXN-2', 'buyer_name' => 'B2', 'buyer_email' => $buyer2->email]);

        // Les deux paiements CinetPay ont réellement abouti (simulé ici en appelant
        // directement la méthode de complétion, comme le ferait le webhook/retour
        // navigateur après confirmation CinetPay) — quasi simultanément.
        $this->completePurchase($order1, 'TXN-1');
        $this->completePurchase($order2, 'TXN-2');

        $order1->refresh();
        $order2->refresh();
        $artwork->refresh();

        $this->assertSame(ArtworkOrder::STATUS_PAID, $order1->status, 'Le premier acheteur doit obtenir la pièce.');
        $this->assertSame(ArtworkOrder::STATUS_OVERSOLD, $order2->status, 'Le second acheteur ne doit jamais être marqué "paid" silencieusement.');
        $this->assertSame(0, $artwork->stock_quantity, 'Le stock ne doit jamais devenir négatif.');
        $this->assertSame(Artwork::STATUS_SOLD, $artwork->status);

        // L'argent du second acheteur a bien été crédité normalement (money already
        // collected by CinetPay) — prêt pour le remboursement manuel admin existant.
        $artistWallet = Wallet::forProvider($provider);
        $this->assertSame(85000, $artistWallet->balance_pending_xof, 'Les deux ventes doivent créditer le wallet artiste (remboursement géré séparément).');

        // L'acheteur lésé et les admins doivent être notifiés.
        $this->assertSame(1, $buyer2->notifications()->count());
    }

    public function test_normal_single_buyer_purchase_is_not_affected(): void
    {
        $providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'art-creations-test2', 'name_fr' => 'Art', 'name_en' => 'Art', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $providerUser->id,
            'category_id' => $category->id,
            'name' => 'Atelier Test 2',
            'slug' => 'atelier-test2-'.uniqid(),
            'status' => 'active',
        ]);

        $artCategory = ArtworkCategory::create(['slug' => 'sculpture-test', 'name_fr' => 'Sculpture', 'name_en' => 'Sculpture', 'is_active' => true]);
        $artwork = Artwork::create([
            'provider_id' => $provider->id,
            'category_id' => $artCategory->id,
            'title' => 'Œuvre Normale',
            'slug' => 'oeuvre-normale-'.uniqid(),
            'price_xof' => 20000,
            'stock_quantity' => 5,
            'images' => ['/storage/test2.jpg'],
            'status' => Artwork::STATUS_PUBLISHED,
        ]);

        $buyer = User::factory()->create();
        $order = ArtworkOrder::create([
            'artwork_id' => $artwork->id,
            'provider_id' => $provider->id,
            'buyer_user_id' => $buyer->id,
            'unit_price_xof' => 20000,
            'amount_total_xof' => 20000,
            'commission_percent' => 15,
            'commission_amount_xof' => 3000,
            'artist_net_amount_xof' => 17000,
            'currency' => 'XOF',
            'status' => ArtworkOrder::STATUS_PENDING_PAYMENT,
            'gateway' => 'cinetpay',
            'gateway_txn_id' => 'TXN-NORMAL',
            'buyer_name' => 'Buyer',
            'buyer_email' => $buyer->email,
        ]);

        $this->completePurchase($order, 'TXN-NORMAL');
        $order->refresh();
        $artwork->refresh();

        $this->assertSame(ArtworkOrder::STATUS_PAID, $order->status);
        $this->assertSame(4, $artwork->stock_quantity);
        $this->assertSame(Artwork::STATUS_PUBLISHED, $artwork->status, 'Le stock restant doit garder l\'œuvre publiée, pas "sold".');
    }
}
