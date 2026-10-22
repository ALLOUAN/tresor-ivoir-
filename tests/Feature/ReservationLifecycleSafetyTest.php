<?php

namespace Tests\Feature;

use App\Mail\ReservationCancelledByProviderMail;
use App\Models\Accommodation;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Phase 0 de l'audit : l'argent et les chambres des clients ne doivent jamais rester pris dans
 * une réservation annulée, abandonnée ou incohérente.
 */
class ReservationLifecycleSafetyTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;

    private User $providerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'category_id' => $category->id,
            'name' => 'Hôtel Test', 'slug' => 'hotel-'.uniqid(), 'status' => 'active',
        ]);
    }

    private function reservation(array $overrides = []): Reservation
    {
        return Reservation::create(array_merge([
            'provider_id' => $this->provider->id,
            'accommodation_name' => 'Hôtel Test',
            'room_name' => 'Chambre Standard',
            'check_in' => now()->subDays(3)->toDateString(),
            'check_out' => now()->subDay()->toDateString(),
            'nights' => 2, 'rooms_count' => 1, 'guests_count' => 2,
            'room_price_xof' => 30000, 'total_xof' => 60000,
            'deposit_amount_xof' => 20000, 'commission_rate_percent' => 15, 'commission_amount_xof' => 3000,
            'full_name' => 'Client Test', 'email' => 'client@example.ci',
            'status' => Reservation::STATUS_NEW, 'payment_status' => Reservation::PAYMENT_UNPAID,
        ], $overrides));
    }

    public function test_a_cancelled_paid_reservation_never_releases_funds_to_the_provider(): void
    {
        $wallets = app(WalletService::class);
        $cancelled = $this->reservation();
        $wallets->creditDepositForReservation($cancelled);
        $cancelled->update(['status' => Reservation::STATUS_CANCELLED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID]);

        $wallets->releasePendingCredits();

        $wallet = Wallet::forProvider($this->provider)->fresh();
        $this->assertSame(0, (int) $wallet->balance_available_xof, 'Aucun fonds disponible pour une réservation annulée.');
        $this->assertSame(17000, (int) $wallet->balance_pending_xof, 'La part reste gelée en attente du remboursement.');
    }

    public function test_a_confirmed_reservation_still_releases_funds_after_arrival(): void
    {
        $wallets = app(WalletService::class);
        $ok = $this->reservation(['status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID]);
        $wallets->creditDepositForReservation($ok);

        $wallets->releasePendingCredits();

        $this->assertSame(17000, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof);
    }

    public function test_unanswered_unpaid_requests_expire_and_free_the_room(): void
    {
        $old = $this->reservation(['check_in' => now()->addDays(10)->toDateString(), 'check_out' => now()->addDays(12)->toDateString()]);
        $old->forceFill(['created_at' => now()->subHours(49)])->save();
        $recent = $this->reservation(['check_in' => now()->addDays(20)->toDateString(), 'check_out' => now()->addDays(22)->toDateString()]);

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertSame(Reservation::STATUS_CANCELLED, $old->fresh()->status);
        $this->assertSame(Reservation::STATUS_NEW, $recent->fresh()->status);
    }

    public function test_a_check_in_date_in_the_past_is_refused(): void
    {
        Mail::fake();
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $a = Accommodation::create([
            'city_id' => $city->id, 'name' => 'Hôtel Dates', 'slug' => 'hotel-dates-'.uniqid(), 'type' => 'hotel', 'is_active' => true,
            'room_types' => [['id' => 'r1', 'name' => 'Standard', 'price_xof' => 30000, 'max_adults' => 2, 'max_children' => 0]],
        ]);
        $payload = [
            'accommodation_id' => $a->id, 'room_name' => 'Standard', 'room_price_xof' => 30000,
            'check_in' => now()->subDays(2)->toDateString(), 'check_out' => now()->addDay()->toDateString(),
            'rooms_count' => 1, 'guests_count' => 1, 'full_name' => 'Client', 'email' => 'c@example.ci',
        ];

        $this->actingAs(User::factory()->create())->postJson(route('reservations.store'), $payload)->assertStatus(422);
        $this->actingAs(User::factory()->create())->postJson(route('reservations.payment.initiate'), $payload)->assertStatus(422);
        $this->assertSame(0, Reservation::count());
    }

    public function test_provider_cancelling_a_paid_reservation_warns_the_client_and_the_admins(): void
    {
        Mail::fake();
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $paid = $this->reservation([
            'check_in' => now()->addDays(10)->toDateString(), 'check_out' => now()->addDays(12)->toDateString(),
            'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);

        $this->actingAs($this->providerUser)
            ->patch(route('provider.reservations.status', $paid), ['status' => Reservation::STATUS_CANCELLED])
            ->assertRedirect();

        $this->assertSame(Reservation::STATUS_CANCELLED, $paid->fresh()->status);
        Mail::assertQueued(ReservationCancelledByProviderMail::class, fn ($m) => $m->hasTo('client@example.ci'));
        Notification::assertSentTo($admin, \App\Notifications\VisitorSystemNotification::class);
    }
}
