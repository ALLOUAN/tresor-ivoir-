<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre l'idempotence du crédit wallet à la confirmation d'une réservation :
 * un double appel (webhook CinetPay + retour navigateur concurrents, scénario
 * documenté dans WalletService) ne doit jamais créditer deux fois.
 */
class ReservationWalletCreditingTest extends TestCase
{
    use RefreshDatabase;

    private function makeReservation(): Reservation
    {
        $providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels-test-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $providerUser->id,
            'category_id' => $category->id,
            'name' => 'Hôtel Test',
            'slug' => 'hotel-test-'.uniqid(),
            'status' => 'active',
        ]);

        return Reservation::create([
            'provider_id' => $provider->id,
            'accommodation_name' => 'Hôtel Test',
            'room_name' => 'Chambre Standard',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(7)->toDateString(),
            'nights' => 2,
            'rooms_count' => 1,
            'guests_count' => 2,
            'room_price_xof' => 30000,
            'total_xof' => 60000,
            'deposit_amount_xof' => 20000,
            'commission_rate_percent' => 15,
            'commission_amount_xof' => 3000,
            'full_name' => 'Client Test',
            'email' => 'client-test@example.ci',
            'status' => 'new',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_deposit_credit_is_idempotent_against_duplicate_calls(): void
    {
        $reservation = $this->makeReservation();
        $walletService = app(WalletService::class);

        // Simule l'appel concurrent webhook + retour navigateur : la même réservation
        // confirmée deux fois ne doit jamais créditer deux fois le wallet plateforme.
        $walletService->creditDepositForReservation($reservation);
        $walletService->creditDepositForReservation($reservation);

        $depositRows = WalletTransaction::where('reservation_id', $reservation->id)
            ->where('type', WalletTransaction::TYPE_DEPOSIT_COLLECTED)
            ->count();

        $this->assertSame(1, $depositRows, 'Un seul crédit de dépôt ne doit être enregistré, même après deux appels.');

        $providerWallet = Wallet::forProvider($reservation->provider);
        $this->assertSame(17000, $providerWallet->balance_pending_xof, 'Part prestataire nette (20000 - 3000 commission) créditée une seule fois.');

        $platformWallet = Wallet::platform();
        // Le solde plateforme correspond exactement à la commission conservée
        // (dépôt encaissé puis part prestataire transférée), pas au dépôt brut cumulé.
        $this->assertSame(3000, $platformWallet->balance_available_xof);
    }
}
