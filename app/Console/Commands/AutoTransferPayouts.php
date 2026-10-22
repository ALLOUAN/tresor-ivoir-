<?php

namespace App\Console\Commands;

use App\Models\PayoutAuditLog;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\CinetPayTransferService;
use App\Services\PayoutAuditLogger;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Versements automatiques : chaque retrait approuvé vers un compte mobile money part par
 * l'API de transfert CinetPay, puis est marqué payé dès que l'opérateur le confirme.
 * Les cas non pris en charge (virement bancaire, montant au-dessus du plafond) restent manuels.
 */
class AutoTransferPayouts extends Command
{
    protected $signature = 'payouts:auto-transfer';

    protected $description = 'Envoie les retraits approuvés par virement CinetPay et confirme ceux déjà envoyés';

    private const MAX_ATTEMPTS = 3;

    public function handle(CinetPayTransferService $transfers, WalletService $wallets): int
    {
        if (! $transfers->isEnabled()) {
            $this->info('Versements automatiques désactivés (CINETPAY_TRANSFER_ENABLED).');

            return self::SUCCESS;
        }

        $sent = $confirmed = $failed = 0;

        // 1. Confirmer les virements déjà envoyés.
        PayoutRequest::where('status', PayoutRequest::STATUS_APPROVED)
            ->where('transfer_status', 'sent')
            ->get()
            ->each(function (PayoutRequest $payout) use ($transfers, $wallets, &$confirmed, &$failed) {
                try {
                    $result = $transfers->check($payout);
                } catch (\Throwable $e) {
                    return;
                }
                if ($result === CinetPayTransferService::RESULT_PAID) {
                    $wallets->markPayoutPaid($payout, null, (string) $payout->transfer_reference);
                    $confirmed++;
                } elseif ($result === CinetPayTransferService::RESULT_FAILED) {
                    $payout->update(['transfer_status' => 'failed', 'transfer_error' => 'Virement rejeté par l\'opérateur.']);
                    $this->alertAdmins($payout, 'Virement rejeté par l\'opérateur.');
                    $failed++;
                }
            });

        // 2. Envoyer les retraits approuvés pas encore partis.
        PayoutRequest::with(['provider.user', 'user'])
            ->where('status', PayoutRequest::STATUS_APPROVED)
            ->whereNull('transfer_status')
            ->where('transfer_attempts', '<', self::MAX_ATTEMPTS)
            ->get()
            ->filter(fn (PayoutRequest $p) => $transfers->supports($p))
            ->each(function (PayoutRequest $payout) use ($transfers, &$sent, &$failed) {
                $owner = $payout->provider?->user ?? $payout->user;
                $name = $payout->provider?->name ?? $owner?->full_name ?? 'Prestataire';
                try {
                    $reference = $transfers->send($payout, $name, $owner?->email);
                    $payout->update([
                        'transfer_status' => 'sent', 'transfer_reference' => $reference,
                        'transfer_attempts' => $payout->transfer_attempts + 1,
                        'transfer_attempted_at' => now(), 'transfer_error' => null,
                    ]);
                    PayoutAuditLogger::log($payout, 'transfer_sent', PayoutAuditLog::ACTOR_SYSTEM, null, ['reference' => $reference]);
                    $sent++;
                } catch (\Throwable $e) {
                    $attempts = $payout->transfer_attempts + 1;
                    $payout->update([
                        'transfer_attempts' => $attempts, 'transfer_attempted_at' => now(),
                        'transfer_error' => mb_substr($e->getMessage(), 0, 500),
                        'transfer_status' => $attempts >= self::MAX_ATTEMPTS ? 'failed' : null,
                    ]);
                    if ($attempts >= self::MAX_ATTEMPTS) {
                        $this->alertAdmins($payout, $e->getMessage());
                    }
                    $failed++;
                }
            });

        $this->info("Virements envoyés : {$sent} · confirmés : {$confirmed} · en échec : {$failed}");

        return self::SUCCESS;
    }

    private function alertAdmins(PayoutRequest $payout, string $reason): void
    {
        Notification::send(
            User::query()->where('role', 'admin')->where('is_active', true)->get(),
            new VisitorSystemNotification(
                'Versement automatique en échec',
                'Le retrait de '.number_format((int) $payout->amount_xof, 0, ',', ' ').' XOF doit être payé manuellement : '.$reason,
                route('admin.wallet.payouts'),
            )
        );
    }
}
