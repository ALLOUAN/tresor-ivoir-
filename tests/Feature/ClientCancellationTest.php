<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\VisitorSystemNotification;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Annulation par le client selon la politique de l'hébergement : remboursement total dans
 * le délai d'annulation gratuite, acompte acquis au prestataire au-delà.
 */
class ClientCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private Provider $provider;

    private Accommodation $accommodation;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->client = User::factory()->create();
        $providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $this->provider = Provider::create([
            'user_id' => $providerUser->id, 'category_id' => $category->id,
            'name' => 'Hôtel Annulation', 'slug' => 'hotel-annul-'.uniqid(), 'status' => 'active',
        ]);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->accommodation = Accommodation::create([
            'provider_id' => $this->provider->id, 'city_id' => $city->id, 'name' => 'Hôtel Annulation',
            'slug' => 'hotel-annulation-'.uniqid(), 'type' => 'hotel', 'is_active' => true,
            'free_cancellation_days' => 5, 'room_types' => [],
        ]);
    }

    private function paidReservation(int $daysBeforeArrival): Reservation
    {
        return Reservation::create([
            'user_id' => $this->client->id, 'provider_id' => $this->provider->id,
            'accommodation_id' => $this->accommodation->id, 'accommodation_name' => 'Hôtel Annulation',
            'room_name' => 'Standard',
            'check_in' => now()->addDays($daysBeforeArrival)->toDateString(),
            'check_out' => now()->addDays($daysBeforeArrival + 2)->toDateString(),
            'nights' => 2, 'rooms_count' => 1, 'guests_count' => 2,
            'room_price_xof' => 30000, 'total_xof' => 60000,
            'deposit_amount_xof' => 20000, 'commission_rate_percent' => 15, 'commission_amount_xof' => 3000,
            'full_name' => 'Client', 'email' => $this->client->email,
            'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);
    }

    public function test_cancelling_within_the_free_period_owes_the_full_deposit_and_alerts_admins(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $reservation = $this->paidReservation(10);

        $this->actingAs($this->client)->post(route('visitor.reservations.cancel', $reservation))
            ->assertRedirect(route('visitor.reservations.show', $reservation));

        $reservation->refresh();
        $this->assertSame(Reservation::STATUS_CANCELLED, $reservation->status);
        $this->assertSame(Reservation::CANCELLED_BY_CLIENT, $reservation->cancelled_by);
        $this->assertSame(20000, $reservation->refund_due_xof);
        Notification::assertSentTo($admin, VisitorSystemNotification::class);
        Notification::assertSentTo($this->provider->user, VisitorSystemNotification::class);
    }

    public function test_late_cancellation_is_not_refunded_and_the_provider_keeps_its_share(): void
    {
        $wallets = app(WalletService::class);
        $reservation = $this->paidReservation(2);
        $wallets->creditDepositForReservation($reservation);

        $this->actingAs($this->client)->post(route('visitor.reservations.cancel', $reservation))->assertRedirect();

        $reservation->refresh();
        $this->assertSame(Reservation::STATUS_CANCELLED, $reservation->status);
        $this->assertSame(0, $reservation->refund_due_xof);

        $this->travel(5)->days();
        $wallets->releasePendingCredits();
        $this->assertSame(17000, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof);
    }

    public function test_free_cancellation_keeps_the_provider_share_frozen(): void
    {
        $wallets = app(WalletService::class);
        $reservation = $this->paidReservation(10);
        $wallets->creditDepositForReservation($reservation);

        $this->actingAs($this->client)->post(route('visitor.reservations.cancel', $reservation))->assertRedirect();

        $this->travel(15)->days();
        $wallets->releasePendingCredits();
        $this->assertSame(0, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof);
    }

    public function test_a_client_cannot_cancel_someone_elses_or_a_started_stay(): void
    {
        $reservation = $this->paidReservation(10);
        $this->actingAs(User::factory()->create())->post(route('visitor.reservations.cancel', $reservation))->assertNotFound();

        $started = $this->paidReservation(0);
        $this->actingAs($this->client)->post(route('visitor.reservations.cancel', $started))->assertSessionHas('error');
        $this->assertSame(Reservation::STATUS_CONFIRMED, $started->fresh()->status);
    }

    public function test_reservation_page_shows_the_policy_and_the_cancel_button(): void
    {
        $reservation = $this->paidReservation(10);

        $this->actingAs($this->client)->get(route('visitor.reservations.show', $reservation))
            ->assertOk()
            ->assertSee('Annulation gratuite jusqu')
            ->assertSee('Annuler ma réservation');
    }
}
