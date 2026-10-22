<?php

namespace Tests\Feature;

use App\Models\PayoutRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Versements automatiques CinetPay : envoi, confirmation, échec, et aucun double paiement. */
class AutoTransferPayoutsTest extends TestCase
{
    use RefreshDatabase;

    private PayoutRequest $payout;

    private Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Cache::flush();
        config(['services.cinetpay_transfer' => [
            'enabled' => true, 'api_key' => 'k', 'password' => 'p',
            'base_url' => 'https://client.cinetpay.com/v1/', 'max_auto_amount_xof' => 500000,
        ]]);

        $user = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $user->id, 'category_id' => $category->id, 'name' => 'Hôtel Virement',
            'slug' => 'hotel-virement', 'status' => 'active', 'is_verified' => true,
        ]);
        $this->wallet = Wallet::forProvider($provider);
        $this->wallet->forceFill(['balance_available_xof' => 50000])->save();
        $this->payout = PayoutRequest::create([
            'provider_id' => $provider->id, 'wallet_id' => $this->wallet->id, 'amount_xof' => 20000,
            'method' => PayoutRequest::METHOD_ORANGE_MONEY, 'payout_destination' => '0707070707',
            'status' => PayoutRequest::STATUS_APPROVED,
        ]);
    }

    private function fakeCinetPay(string $treatmentStatus): void
    {
        Http::fake([
            '*/auth/login' => Http::response(['code' => 0, 'data' => ['token' => 'tok']]),
            '*/transfer/contact*' => Http::response(['code' => 0, 'data' => [[['status' => 'success']]]]),
            '*/transfer/money/send/contact*' => Http::response(['code' => 0, 'data' => [[['transaction_id' => 'CP-123', 'treatment_status' => 'NEW']]]]),
            '*/transfer/check/money*' => Http::response(['code' => 0, 'data' => [['treatment_status' => $treatmentStatus]]]),
        ]);
    }

    public function test_an_approved_payout_is_sent_then_marked_paid_once_confirmed(): void
    {
        $this->fakeCinetPay('VAL');

        $this->artisan('payouts:auto-transfer')->assertSuccessful();
        $this->payout->refresh();
        $this->assertSame('sent', $this->payout->transfer_status);
        $this->assertSame('CP-123', $this->payout->transfer_reference);
        $this->assertSame(PayoutRequest::STATUS_APPROVED, $this->payout->status);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'money/send/contact') && str_contains($r['data'], '"phone":"0707070707"') && str_contains($r['data'], '"prefix":"225"'));

        $this->artisan('payouts:auto-transfer')->assertSuccessful();
        $this->assertSame(PayoutRequest::STATUS_PAID, $this->payout->fresh()->status);
        $this->assertSame(30000, (int) $this->wallet->fresh()->balance_available_xof);
    }

    public function test_admin_cannot_mark_paid_a_payout_already_sent_automatically(): void
    {
        $this->payout->update(['transfer_status' => 'sent', 'transfer_reference' => 'CP-123']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.wallet.payouts.mark-paid', $this->payout), ['payment_reference' => 'manuel'])
            ->assertSessionHas('error');
        $this->assertSame(PayoutRequest::STATUS_APPROVED, $this->payout->fresh()->status);
    }

    public function test_a_rejected_transfer_alerts_admins_and_leaves_the_balance_untouched(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $this->fakeCinetPay('REJ');

        $this->artisan('payouts:auto-transfer');
        $this->artisan('payouts:auto-transfer');

        $this->assertSame('failed', $this->payout->fresh()->transfer_status);
        $this->assertSame(50000, (int) $this->wallet->fresh()->balance_available_xof);
        Notification::assertSentTo($admin, \App\Notifications\VisitorSystemNotification::class);
    }

    public function test_disabled_or_bank_transfers_stay_manual(): void
    {
        Http::fake();
        $this->payout->update(['method' => PayoutRequest::METHOD_BANK_TRANSFER, 'payout_destination' => 'CI93 0000']);

        $this->artisan('payouts:auto-transfer')->assertSuccessful();
        config(['services.cinetpay_transfer.enabled' => false]);
        $this->artisan('payouts:auto-transfer')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertNull($this->payout->fresh()->transfer_status);
    }
}
