<?php

namespace Tests\Feature;

use App\Models\ArtCommission;
use App\Models\ArtistFollow;
use App\Models\ArtistProfile;
use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\ArtworkOffer;
use App\Models\ArtworkOrder;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ArtSalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Art & Créations « marché » : page artiste et abonnés, livraison et assurance, offres, paiement en 3 fois,
 * dimensions, retour sous 7 jours (artiste payé après), éditions limitées numérotées, sur mesure.
 */
class ArtMarketFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $artistUser;

    private Provider $artist;

    private Artwork $artwork;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('public');
        $this->artistUser = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'art-creations'], ['name_fr' => 'Art & Créations', 'name_en' => 'Art', 'is_active' => true]);
        $this->artist = Provider::create(['user_id' => $this->artistUser->id, 'category_id' => $cat->id, 'name' => 'Atelier Koffi', 'slug' => 'atelier-koffi', 'status' => 'active']);
        ArtistProfile::forProvider($this->artist)->update(['slug' => 'koffi', 'abidjan_delivery_xof' => 5000, 'country_delivery_xof' => 15000, 'large_supplement_xof' => 10000, 'insurance_percent' => 2, 'pickup_address' => 'Angré']);
        $ac = ArtworkCategory::firstOrCreate(['slug' => 'peinture'], ['name_fr' => 'Peinture', 'name_en' => 'Painting', 'is_active' => true]);
        $this->artwork = Artwork::create(['provider_id' => $this->artist->id, 'category_id' => $ac->id, 'title' => 'Lagune au matin', 'slug' => 'lagune-au-matin',
            'price_xof' => 300000, 'stock_quantity' => 1, 'images' => ['/storage/a.jpg'], 'status' => Artwork::STATUS_PUBLISHED,
            'width_cm' => 120, 'height_cm' => 80, 'accepts_offers' => true, 'min_offer_xof' => 240000, 'layaway_allowed' => true]);
        $this->buyer = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($this->buyer)->update(['balance_available_xof' => 2000000]);
    }

    private function checkout(array $in, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->buyer)->post(route('art.checkout', $this->artwork), $in + ['delivery_mode' => 'pickup', 'payment_method' => 'wallet', 'phone' => '0707']);
    }

    private function wallet(): int
    {
        return Wallet::forUser($this->buyer)->fresh()->balance_available_xof;
    }

    public function test_artist_page_follow_and_new_artwork_alert(): void
    {
        $this->get(route('artist.show', 'koffi'))->assertOk()->assertSee('Atelier Koffi')->assertSee('Lagune au matin')->assertSee('Commande sur mesure');
        $this->actingAs($this->buyer)->post(route('artist.follow', 'koffi'))->assertSessionHas('success');
        $this->assertSame(1, ArtistFollow::count());
        $new = Artwork::create(['provider_id' => $this->artist->id, 'category_id' => $this->artwork->category_id, 'title' => 'Masque bleu', 'slug' => 'masque-bleu', 'price_xof' => 90000, 'stock_quantity' => 1, 'images' => ['/storage/b.jpg'], 'status' => Artwork::STATUS_PENDING_REVIEW]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->patch(route('admin.artworks.approve', $new))->assertSessionHas('success');
        Notification::assertSentTo($this->buyer, \App\Notifications\VisitorSystemNotification::class);
        $this->get(route('art.show', 'lagune-au-matin'))->assertSee(route('artist.show', 'koffi'), false)->assertSee('Voir sur mon mur');
    }

    public function test_delivery_large_format_and_insurance_then_receipt_and_release_after_return_window(): void
    {
        // 300 000 + Abidjan 5 000 + grand format 10 000 + assurance 2 % (6 000)
        $this->checkout(['delivery_mode' => 'abidjan', 'delivery_address' => 'Riviera 3', 'insurance' => 1])->assertRedirect();
        $o = ArtworkOrder::sole();
        $this->assertSame(321000, $o->amount_total_xof);
        $this->assertSame(ArtworkOrder::STATUS_PAID, $o->status);
        $this->assertSame(2000000 - 321000, $this->wallet());
        $this->assertSame(Artwork::STATUS_SOLD, $this->artwork->fresh()->status);
        // Commission (5 %) sur le prix de l'œuvre seulement ; la livraison et l'assurance reviennent à l'artiste.
        $this->assertSame(321000 - 15000, $o->artist_net_amount_xof);
        $this->assertSame(306000, Wallet::forProvider($this->artist)->fresh()->balance_pending_xof);

        $this->actingAs($this->artistUser)->post(route('provider.art-orders.ship', $o), ['carrier' => 'Coursier Yango', 'tracking_number' => 'YG-77'])->assertSessionHas('success');
        $this->actingAs($this->buyer)->get(route('visitor.art-orders.index'))->assertSee('YG-77');
        $this->actingAs($this->buyer)->post(route('visitor.art-orders.receipt', $o))->assertSessionHas('success');
        $this->assertTrue($o->fresh()->canRequestReturn());

        app(ArtSalesService::class)->processDaily();
        $this->assertSame(0, Wallet::forProvider($this->artist)->fresh()->balance_available_xof, 'Pas avant la fin du délai de retour.');
        $this->travelTo(now()->addDays(8));
        app(ArtSalesService::class)->processDaily();
        $this->assertSame(306000, Wallet::forProvider($this->artist)->fresh()->balance_available_xof);
    }

    public function test_return_within_7_days_refunds_the_buyer_and_restocks(): void
    {
        $this->checkout([])->assertRedirect();
        $o = ArtworkOrder::sole();
        $this->actingAs($this->buyer)->post(route('visitor.art-orders.receipt', $o));
        $this->actingAs($this->buyer)->post(route('visitor.art-orders.return', $o), ['reason' => 'Les couleurs sont très différentes des photos.'])->assertSessionHas('success');
        $this->actingAs($this->artistUser)->post(route('provider.art-orders.return', $o), ['action' => 'accept'])->assertSessionHas('success');
        $this->actingAs($this->artistUser)->post(route('provider.art-orders.return', $o), ['action' => 'received'])->assertSessionHas('success');
        $o->refresh();
        $this->assertSame('refunded', $o->return_status);
        $this->assertSame(2000000, $this->wallet());
        $this->assertSame(0, Wallet::forProvider($this->artist)->fresh()->balance_pending_xof);
        $this->assertTrue($this->artwork->fresh()->isAvailable(), 'L\'œuvre est remise en vente.');
        $this->travelTo(now()->addDays(10));
        app(ArtSalesService::class)->processDaily();
        $this->assertSame(0, Wallet::forProvider($this->artist)->fresh()->balance_available_xof);
    }

    public function test_offer_floor_accept_and_pay_at_agreed_price(): void
    {
        $this->actingAs($this->buyer)->post(route('art.offer', $this->artwork), ['amount_xof' => 200000])->assertSessionHas('error'); // sous le plancher
        $this->assertSame('declined', ArtworkOffer::sole()->status);
        ArtworkOffer::query()->delete();
        $this->actingAs($this->buyer)->post(route('art.offer', $this->artwork), ['amount_xof' => 250000, 'message' => 'Coup de cœur'])->assertSessionHas('success');
        $offer = ArtworkOffer::sole();
        $this->actingAs($this->artistUser)->post(route('provider.art-offers.respond', $offer), ['action' => 'counter', 'counter_xof' => 270000])->assertSessionHas('success');
        $this->actingAs($this->buyer)->post(route('visitor.art-offers.accept', $offer))->assertRedirect();
        $this->assertSame(270000, $offer->fresh()->payablePrice());
        $this->checkout(['offer_id' => $offer->id])->assertRedirect();
        $o = ArtworkOrder::sole();
        $this->assertSame(270000, $o->amount_total_xof);
        $this->assertSame('paid', $offer->fresh()->status);
        // Une autre personne ne peut pas utiliser cette offre.
        $this->assertSame(Artwork::STATUS_SOLD, $this->artwork->fresh()->status);
    }

    public function test_layaway_reserves_then_completes_and_default_cancels(): void
    {
        $this->checkout(['plan' => 'layaway'])->assertRedirect();
        $o = ArtworkOrder::sole();
        $this->assertSame(ArtworkOrder::STATUS_RESERVED, $o->status);
        $this->assertSame([90000, 105000, 105000], collect($o->installments)->pluck('amount_xof')->all());
        $this->assertSame(2000000 - 90000, $this->wallet());
        $this->assertFalse($this->artwork->fresh()->isAvailable());
        $this->assertTrue($this->artwork->fresh()->isReserved());
        $this->assertSame(0, Wallet::forProvider($this->artist)->fresh()->balance_pending_xof, 'L\'artiste n\'est crédité qu\'une fois soldé.');

        $this->actingAs($this->buyer)->post(route('visitor.art-orders.installment', $o), ['payment_method' => 'wallet']);
        $this->actingAs($this->buyer)->post(route('visitor.art-orders.installment', $o), ['payment_method' => 'wallet']);
        $o->refresh();
        $this->assertSame(ArtworkOrder::STATUS_PAID, $o->status);
        $this->assertSame(2000000 - 300000, $this->wallet());
        $this->assertSame(285000, Wallet::forProvider($this->artist)->fresh()->balance_pending_xof);
        $this->assertSame(15000, Wallet::platform()->fresh()->balance_available_xof);
    }

    public function test_unpaid_layaway_is_cancelled_deposit_goes_to_the_artist(): void
    {
        $this->checkout(['plan' => 'layaway']);
        $o = ArtworkOrder::sole();
        $this->actingAs($this->buyer)->post(route('visitor.art-orders.installment', $o), ['payment_method' => 'wallet']);
        $this->travelTo(now()->addDays(70));
        app(ArtSalesService::class)->processDaily();
        $o->refresh();
        $this->assertSame(ArtworkOrder::STATUS_CANCELLED, $o->status);
        $this->assertSame(2000000 - 90000, $this->wallet(), 'Échéance 2 remboursée, acompte conservé.');
        $this->assertSame(90000 - 4500, Wallet::forProvider($this->artist)->fresh()->balance_available_xof);
        $this->assertTrue($this->artwork->fresh()->isAvailable());
    }

    public function test_limited_editions_are_numbered_and_sell_out(): void
    {
        $this->artwork->update(['stock_quantity' => 0, 'status' => Artwork::STATUS_PUBLISHED]);
        $ed = $this->artwork->editions()->create(['label' => 'Tirage d\'art A3', 'width_cm' => 30, 'height_cm' => 42, 'price_xof' => 45000, 'edition_size' => 2]);
        $this->actingAs($this->buyer)->get(route('art.show', 'lagune-au-matin'))->assertSee('Tirage d&#039;art A3', false)->assertSee('édition de 2');
        $this->checkout(['edition_id' => $ed->id])->assertRedirect();
        $other = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($other)->update(['balance_available_xof' => 100000]);
        $this->checkout(['edition_id' => $ed->id], $other)->assertRedirect();
        $this->assertSame([1, 2], ArtworkOrder::orderBy('id')->pluck('edition_number')->all());
        $this->assertSame(0, $ed->fresh()->remaining());
        $this->checkout(['edition_id' => $ed->id])->assertSessionHasErrors('checkout', null, 'art');
        $o = ArtworkOrder::first();
        $this->get(route('art.certificate.verify', [$o->id, $o->certificateCode()]))->assertSee('exemplaire 1/2');
    }

    public function test_custom_commission_quote_deposit_sketch_revision_balance_delivery(): void
    {
        $this->actingAs($this->buyer)->post(route('artist.commission.store', 'koffi'), [
            'subject' => 'Portrait de famille', 'brief' => 'Un portrait de nos trois enfants sur la plage de Bassam.', 'format' => '80 × 60 cm',
            'budget_xof' => 200000, 'references' => [UploadedFile::fake()->image('ref.jpg')],
        ])->assertRedirect(route('visitor.art-commissions.index'));
        $c = ArtCommission::sole();
        $this->actingAs($this->artistUser)->post(route('provider.art-commissions.action', $c), ['action' => 'quote', 'quote_xof' => 220000, 'quote_days' => 21])->assertSessionHas('success');
        $c->refresh();
        $this->assertSame(110000, $c->deposit_xof);
        $this->actingAs($this->buyer)->post(route('visitor.art-commissions.pay', $c), ['payment_method' => 'wallet'])->assertSessionHas('success');
        $this->assertSame('deposit_paid', $c->fresh()->status);
        $this->assertSame(110000 - 5500, Wallet::forProvider($this->artist)->fresh()->balance_available_xof);

        $this->actingAs($this->artistUser)->post(route('provider.art-commissions.action', $c), ['action' => 'sketch', 'sketches' => [UploadedFile::fake()->image('s.jpg')]])->assertSessionHas('success');
        $this->actingAs($this->buyer)->post(route('visitor.art-commissions.sketch', $c), ['decision' => 'revise', 'note' => 'Plus de bleu dans le ciel'])->assertSessionHas('success');
        $this->actingAs($this->artistUser)->post(route('provider.art-commissions.action', $c), ['action' => 'sketch', 'sketches' => [UploadedFile::fake()->image('s2.jpg')]]);
        $this->actingAs($this->buyer)->post(route('visitor.art-commissions.sketch', $c), ['decision' => 'approve'])->assertSessionHas('success');
        $this->actingAs($this->buyer)->post(route('visitor.art-commissions.pay', $c), ['payment_method' => 'wallet'])->assertSessionHas('success');
        $c->refresh();
        $this->assertSame('balance_paid', $c->status);
        $this->assertSame(220000, $c->amount_paid_xof);
        $this->assertSame(2000000 - 220000, $this->wallet());
        $this->actingAs($this->artistUser)->post(route('provider.art-commissions.action', $c), ['action' => 'deliver'])->assertSessionHas('success');
        $this->assertSame('delivered', $c->fresh()->status);
    }

    public function test_artist_cancelling_a_commission_refunds_everything(): void
    {
        $this->actingAs($this->buyer)->post(route('artist.commission.store', 'koffi'), ['subject' => 'Fresque', 'brief' => 'Une fresque pour notre restaurant à Cocody.']);
        $c = ArtCommission::sole();
        $this->actingAs($this->artistUser)->post(route('provider.art-commissions.action', $c), ['action' => 'quote', 'quote_xof' => 100000, 'quote_days' => 10]);
        $this->actingAs($this->buyer)->post(route('visitor.art-commissions.pay', $c), ['payment_method' => 'wallet']);
        $this->actingAs($this->artistUser)->post(route('provider.art-commissions.action', $c), ['action' => 'cancel'])->assertSessionHas('success');
        $this->assertSame('cancelled', $c->fresh()->status);
        $this->assertSame(2000000, $this->wallet());
        $this->assertSame(0, Wallet::forProvider($this->artist)->fresh()->balance_available_xof);
    }

    public function test_artist_manages_profile_editions_and_dimensions(): void
    {
        \App\Models\PaymentSetting::updateOrCreate(['key' => 'provider_free_access'], ['value' => '1']); // espace œuvres sans abonnement pour le test
        $this->actingAs($this->artistUser)->put(route('provider.artist-profile.update'), [
            'display_name' => 'Koffi Yao', 'tagline' => 'Peintre de la lagune', 'exhibitions' => "2025 — Abidjan Art Week\n2024 — Rotonde des Arts",
            'commission_deposit_percent' => 40, 'insurance_percent' => 3, 'abidjan_delivery_xof' => 4000, 'accepts_commissions' => 1, 'returns_accepted' => 1,
        ])->assertSessionHas('success');
        $this->get(route('artist.show', 'koffi'))->assertSee('Koffi Yao')->assertSee('Rotonde des Arts');

        $this->actingAs($this->artistUser)->put(route('provider.artworks.update', $this->artwork), [
            'title' => 'Lagune au matin', 'category_id' => $this->artwork->category_id, 'price_xof' => 300000, 'stock_quantity' => 1,
            'width_cm' => 100, 'height_cm' => 70, 'accepts_offers' => 1, 'layaway_allowed' => 0,
            'editions' => [['label' => 'Giclée A2', 'width_cm' => 42, 'height_cm' => 59, 'price_xof' => 60000, 'edition_size' => 30], ['label' => '']],
        ])->assertRedirect();
        $a = $this->artwork->fresh();
        $this->assertSame(100.0, $a->width_cm);
        $this->assertFalse($a->layaway_allowed);
        $this->assertSame(['Giclée A2'], $a->editions()->pluck('label')->all());
        foreach (['provider.artist-profile.edit', 'provider.art-offers.index', 'provider.art-commissions.index'] as $r) {
            $this->actingAs($this->artistUser)->get(route($r))->assertOk();
        }
    }
}
