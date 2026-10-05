<?php

namespace Tests\Feature;

use App\Models\AccountSecurityEvent;
use App\Models\PayoutRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre le moteur de risque des retraits (construit cette session) : les trois
 * décisions possibles (auto-approbation, vérification complémentaire, suspension)
 * et la règle dure "un seul retrait ouvert à la fois".
 */
class PayoutRiskEngineTest extends TestCase
{
    use RefreshDatabase;

    private function makeProviderWithWallet(int $balanceXof, bool $established = false): Provider
    {
        $user = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'test-cat-'.uniqid(), 'name_fr' => 'Test', 'name_en' => 'Test', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Prestataire Test',
            'slug' => 'prestataire-test-'.uniqid(),
            'status' => 'active',
            'payout_method' => 'orange_money',
            'payout_account_number' => '0700000001',
        ]);

        $wallet = Wallet::forProvider($provider);
        $wallet->incrementAvailable($balanceXof);

        if ($established) {
            $wallet->timestamps = false;
            $wallet->created_at = now()->subDays(60);
            $wallet->save();

            PayoutRequest::create([
                'provider_id' => $provider->id,
                'wallet_id' => $wallet->id,
                'amount_xof' => 10000,
                'method' => 'orange_money',
                'payout_destination' => '0700000001',
                'status' => PayoutRequest::STATUS_PAID,
                'paid_at' => now()->subDays(30),
                'payment_reference' => 'HIST-1',
            ]);

            AccountSecurityEvent::create([
                'user_id' => $user->id,
                'event_type' => AccountSecurityEvent::TYPE_LOGIN,
                'ip_address' => '10.0.0.99',
                'created_at' => now()->subDay(),
            ]);
        }

        return $provider;
    }

    public function test_low_risk_payout_is_auto_approved(): void
    {
        $provider = $this->makeProviderWithWallet(500000, established: true);

        $payout = app(WalletService::class)->requestPayout(
            $provider, 20000, 'orange_money', '0700000001', '10.0.0.99', 'PHPUnit'
        );

        $this->assertSame(PayoutRequest::STATUS_APPROVED, $payout->status);
        $this->assertSame(PayoutRequest::DECISION_AUTO_APPROVED, $payout->decision);
        $this->assertLessThan(25, $payout->risk_score);
    }

    public function test_high_risk_payout_requires_step_up_verification(): void
    {
        $provider = $this->makeProviderWithWallet(50000, established: false);

        $payout = app(WalletService::class)->requestPayout(
            $provider, 48000, 'mtn_momo', '0511223344'
        );

        $this->assertSame(PayoutRequest::STATUS_PENDING_VERIFICATION, $payout->status);
        $this->assertSame(PayoutRequest::DECISION_STEP_UP_REQUIRED, $payout->decision);
    }

    public function test_invalid_destination_format_is_always_suspended(): void
    {
        $provider = $this->makeProviderWithWallet(500000, established: true);

        $payout = app(WalletService::class)->requestPayout(
            $provider, 10000, 'orange_money', 'not-a-phone-number', '10.0.0.99'
        );

        $this->assertSame(PayoutRequest::STATUS_SUSPENDED, $payout->status);
        $this->assertSame(PayoutRequest::DECISION_SUSPENDED, $payout->decision);
        $this->assertSame(100, $payout->risk_score, 'Le format invalide doit toujours produire le score maximal, jamais auto-approuvé.');
    }

    public function test_cannot_submit_second_payout_while_one_is_still_open(): void
    {
        $provider = $this->makeProviderWithWallet(500000, established: false);

        app(WalletService::class)->requestPayout($provider, 10000, 'orange_money', '0700000001');

        $this->expectException(\RuntimeException::class);
        app(WalletService::class)->requestPayout($provider, 5000, 'orange_money', '0700000001');
    }

    public function test_otp_verification_moves_step_up_payout_to_approved(): void
    {
        $provider = $this->makeProviderWithWallet(50000, established: false);

        $payout = app(WalletService::class)->requestPayout(
            $provider, 48000, 'mtn_momo', '0511223344'
        );
        $this->assertSame(PayoutRequest::STATUS_PENDING_VERIFICATION, $payout->status);

        $wrong = app(WalletService::class)->verifyPayoutOtp($payout, '000000');
        $this->assertNotTrue($wrong, 'Un code incorrect ne doit jamais valider le retrait.');
        $this->assertSame(PayoutRequest::STATUS_PENDING_VERIFICATION, $payout->fresh()->status);
    }
}
