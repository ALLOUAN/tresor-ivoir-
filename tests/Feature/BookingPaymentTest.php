<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\BookingPaymentService;
use App\Services\CinetPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Paiement en ligne des demandes : montant fixé à la confirmation, encaissement unique, libération après la date. */
class BookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private User $providerUser;

    private Provider $provider;

    private BookingRequest $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->client = User::factory()->create();
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'resto-'.uniqid(), 'name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->providerUser->id, 'category_id' => $category->id, 'name' => 'Chez Awa', 'slug' => 'chez-awa', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $restaurant = Restaurant::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Chez Awa', 'slug' => 'chez-awa', 'is_active' => true]);
        $this->booking = BookingRequest::create([
            'provider_id' => $this->provider->id, 'user_id' => $this->client->id, 'bookable_type' => 'restaurant',
            'bookable_id' => $restaurant->id, 'bookable_name' => 'Chez Awa', 'booking_date' => now()->addDays(5)->toDateString(),
            'party_size' => 4, 'full_name' => 'Client', 'email' => $this->client->email, 'status' => 'new',
        ]);
    }

    private function confirmWithAmount(int $amount): void
    {
        $this->actingAs($this->providerUser)
            ->post(route('provider.booking-requests.respond', $this->booking), ['decision' => 'confirm', 'amount_xof' => $amount])
            ->assertRedirect();
        $this->booking->refresh();
    }

    public function test_wallet_payment_credits_the_provider_as_pending_then_releases_after_the_date(): void
    {
        $this->confirmWithAmount(20000);
        $this->assertSame(20000, $this->booking->amount_due_xof);
        $this->assertSame(2000, $this->booking->commission_amount_xof, 'Commission par défaut 10 %.');

        Wallet::forUser($this->client)->forceFill(['balance_available_xof' => 50000])->save();
        $this->actingAs($this->client)->post(route('visitor.booking-requests.pay', $this->booking), ['payment_method' => 'wallet'])
            ->assertSessionHas('success');

        $this->assertSame(BookingRequest::PAYMENT_PAID, $this->booking->fresh()->payment_status);
        $this->assertSame(30000, (int) Wallet::forUser($this->client)->fresh()->balance_available_xof);
        $providerWallet = Wallet::forProvider($this->provider)->fresh();
        $this->assertSame(18000, (int) $providerWallet->balance_pending_xof);
        $this->assertSame(0, (int) $providerWallet->balance_available_xof);

        // Avant la date : rien n'est libéré. Après : la part devient disponible, une seule fois.
        app(BookingPaymentService::class)->releaseDueCredits();
        $this->assertSame(0, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof);
        $this->travel(7)->days();
        app(BookingPaymentService::class)->releaseDueCredits();
        app(BookingPaymentService::class)->releaseDueCredits();
        $providerWallet = Wallet::forProvider($this->provider)->fresh();
        $this->assertSame(18000, (int) $providerWallet->balance_available_xof);
        $this->assertSame(0, (int) $providerWallet->balance_pending_xof);
    }

    public function test_cinetpay_webhook_and_return_never_collect_twice(): void
    {
        $this->confirmWithAmount(15000);
        $this->mock(CinetPayService::class, function ($m) {
            $m->shouldReceive('isConfigured')->andReturn(true);
            $m->shouldReceive('initPayment')->andReturn(['success' => true, 'merchant_transaction_id' => 'TX-1', 'payment_url' => 'https://pay.example/TX-1']);
            $m->shouldReceive('checkPaymentStatus')->andReturn(['success' => true]);
        });

        $this->actingAs($this->client)->post(route('visitor.booking-requests.pay', $this->booking), ['payment_method' => 'cinetpay'])
            ->assertRedirect('https://pay.example/TX-1');

        $this->post(route('booking-requests.payment.webhook'), ['merchant_transaction_id' => 'TX-1'])->assertOk();
        $this->actingAs($this->client)->get(route('booking-requests.payment.return', ['merchant_transaction_id' => 'TX-1']))->assertRedirect();

        $this->assertSame(BookingRequest::PAYMENT_PAID, $this->booking->fresh()->payment_status);
        $this->assertSame(1, WalletTransaction::where('booking_request_id', $this->booking->id)->where('type', BookingPaymentService::TYPE_SALE)->count());
        $this->assertSame(13500, (int) Wallet::forProvider($this->provider)->fresh()->balance_pending_xof);
    }

    public function test_cancelling_a_paid_booking_freezes_funds_and_admin_refund_returns_them(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $this->confirmWithAmount(20000);
        Wallet::forUser($this->client)->forceFill(['balance_available_xof' => 20000])->save();
        $this->actingAs($this->client)->post(route('visitor.booking-requests.pay', $this->booking), ['payment_method' => 'wallet']);

        $this->actingAs($this->client)->post(route('visitor.booking-requests.cancel', $this->booking))->assertRedirect();
        $this->booking->refresh();
        $this->assertSame(BookingRequest::STATUS_CANCELLED, $this->booking->status);
        $this->assertSame(20000, $this->booking->refund_due_xof);

        $this->travel(10)->days();
        app(BookingPaymentService::class)->releaseDueCredits();
        $this->assertSame(0, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof, 'Jamais libéré après annulation.');

        $this->actingAs($admin)->post(route('admin.booking-requests.refund', $this->booking))->assertSessionHas('success');
        $this->assertSame(BookingRequest::PAYMENT_REFUNDED, $this->booking->fresh()->payment_status);
        $this->assertSame(20000, (int) Wallet::forUser($this->client)->fresh()->balance_available_xof, 'Payé avec le solde : recrédité.');
        $this->assertSame(0, (int) Wallet::forProvider($this->provider)->fresh()->balance_pending_xof);
    }

    public function test_a_booking_without_amount_or_not_confirmed_cannot_be_paid(): void
    {
        $this->actingAs($this->client)->post(route('visitor.booking-requests.pay', $this->booking), ['payment_method' => 'wallet'])
            ->assertSessionHas('error');

        $this->confirmWithAmount(0);
        $this->assertFalse($this->booking->canBePaidOnline());
        $this->actingAs(User::factory()->create())->post(route('visitor.booking-requests.pay', $this->booking), ['payment_method' => 'wallet'])->assertNotFound();
    }
}
