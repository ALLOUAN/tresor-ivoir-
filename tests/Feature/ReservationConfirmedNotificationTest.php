<?php

namespace Tests\Feature;

use App\Mail\ReservationDepositConfirmedMail;
use App\Mail\ReservationReceivedProviderMail;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\VisitorSystemNotification;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Couvre la notification automatique (client + prestataire) déclenchée à la
 * confirmation d'une réservation (acompte payé) — cf. cahier des charges
 * « Notification automatique des réservations ».
 */
class ReservationConfirmedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeReservation(): Reservation
    {
        $providerUser = User::factory()->provider()->create(['email' => 'hotelier-test@example.ci']);
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

    public function test_wallet_payment_notifies_both_client_and_provider_exactly_once(): void
    {
        Mail::fake();
        Notification::fake();

        $reservation = $this->makeReservation();
        $client = User::factory()->create();
        Wallet::forUser($client)->update(['balance_available_xof' => 50000]);

        $walletService = app(WalletService::class);
        $walletService->payReservationFromWallet($reservation, $client);

        Mail::assertQueued(ReservationDepositConfirmedMail::class, fn ($mail) => $mail->reservation->id === $reservation->id);
        Mail::assertQueued(ReservationReceivedProviderMail::class, fn ($mail) => $mail->reservation->id === $reservation->id);
        Mail::assertQueued(ReservationDepositConfirmedMail::class, 1);
        Mail::assertQueued(ReservationReceivedProviderMail::class, 1);

        Notification::assertSentTo($reservation->provider->user, VisitorSystemNotification::class);

        // Rejouer le même paiement (double clic, appel concurrent) ne doit renvoyer
        // aucune notification supplémentaire — protégé par la garde d'idempotence
        // existante de payReservationFromWallet() (payment_status déjà deposit_paid).
        $walletService->payReservationFromWallet($reservation->fresh(), $client);

        Mail::assertQueued(ReservationDepositConfirmedMail::class, 1);
        Mail::assertQueued(ReservationReceivedProviderMail::class, 1);
    }

    public function test_provider_email_falls_back_to_business_contact_email_when_set(): void
    {
        Mail::fake();

        $reservation = $this->makeReservation();
        $reservation->provider->update(['email' => 'contact-etablissement@example.ci']);

        $client = User::factory()->create();
        Wallet::forUser($client)->update(['balance_available_xof' => 50000]);

        app(WalletService::class)->payReservationFromWallet($reservation, $client);

        Mail::assertQueued(
            ReservationReceivedProviderMail::class,
            fn ($mail) => $mail->hasTo('contact-etablissement@example.ci')
        );
    }
}
