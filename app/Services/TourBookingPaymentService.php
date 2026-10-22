<?php

namespace App\Services;

use App\Models\ProviderDebt;
use App\Models\TourBooking;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Argent des réservations de circuits : acompte puis solde (ou paiement intégral).
 *
 * Chaque versement (deposit, balance, full) est encaissé par la plateforme, qui retient sa commission ;
 * la part de l'agence part « en attente » sur son portefeuille et ne devient disponible qu'après le
 * départ. Les écritures sont uniques par (réservation, type) : un webhook et un retour navigateur
 * simultanés n'encaissent jamais deux fois le même versement.
 *
 * Remboursement (annulation par le voyageur selon le barème, départ annulé) : toujours crédité sur le
 * solde du client, repris sur la part en attente de l'agence et sur la commission, au prorata.
 */
class TourBookingPaymentService
{
    public const STAGES = ['deposit', 'balance', 'full'];

    public function __construct(private readonly BookingPaymentService $bookingPayments) {}

    public function commissionPercent(): float
    {
        return $this->bookingPayments->commissionPercent();
    }

    /** Montant du versement demandé pour une étape. */
    public function amountFor(TourBooking $booking, string $stage): int
    {
        return match ($stage) {
            'deposit' => (int) $booking->deposit_xof,
            'balance' => $booking->balanceXof(),
            default => (int) $booking->amount_total_xof,
        };
    }

    /** Versement confirmé par l'opérateur (CinetPay). Idempotent ; retourne true au premier encaissement. */
    public function markPaid(TourBooking $booking, string $stage, string $method, ?string $txn = null): bool
    {
        return DB::transaction(function () use ($booking, $stage, $method, $txn) {
            $booking = TourBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($this->stageCollected($booking, $stage) || $booking->status === TourBooking::STATUS_CANCELLED) {
                return false;
            }
            $this->applyPayment($booking, $stage, $this->amountFor($booking, $stage), $txn);

            return true;
        });
    }

    /** Versement depuis le solde du client. */
    public function payFromWallet(TourBooking $booking, User $client, string $stage): void
    {
        DB::transaction(function () use ($booking, $client, $stage) {
            $booking = TourBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($this->stageCollected($booking, $stage)) {
                return;
            }
            $amount = $this->amountFor($booking, $stage);
            if ($amount <= 0) {
                throw new \RuntimeException(__('Aucun montant à payer.'));
            }
            $wallet = $this->lockedWallet(Wallet::forUser($client)->id);
            if ($wallet->balance_available_xof < $amount) {
                throw new \RuntimeException(__('Solde insuffisant pour payer depuis votre portefeuille.'));
            }
            $this->row($wallet, 'balance_available_xof', 'tour_'.$stage.'_wallet_payment', -$amount, $booking, __('Paiement circuit').' — '.$booking->reference);
            $this->applyPayment($booking, $stage, $amount, null, 'wallet');
        });
    }

    private function applyPayment(TourBooking $booking, string $stage, int $amount, ?string $txn, string $method = 'cinetpay'): void
    {
        $paid = $booking->amount_paid_xof + $amount;
        $booking->update([
            'amount_paid_xof' => $paid,
            'status' => $paid >= $booking->amount_total_xof ? TourBooking::STATUS_PAID : TourBooking::STATUS_DEPOSIT_PAID,
            'pending_stage' => null,
            'pending_amount_xof' => null,
            'gateway_txn_id' => $txn ?? $booking->gateway_txn_id,
        ]);
        $this->collect($booking, $stage, $amount);
    }

    private function stageCollected(TourBooking $booking, string $stage): bool
    {
        return WalletTransaction::where('tour_booking_id', $booking->id)->where('type', 'tour_'.$stage.'_collected')->exists();
    }

    /** Écritures d'encaissement d'un versement : plateforme (vente, commission, transfert) puis part en attente de l'agence. */
    private function collect(TourBooking $booking, string $stage, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        $commission = (int) round($amount * $booking->commission_percent / 100);
        $net = max(0, $amount - $commission);

        try {
            $platform = $this->lockedWallet(Wallet::platform()->id);
            $this->row($platform, 'balance_available_xof', 'tour_'.$stage.'_collected', $amount, $booking, 'Circuit encaissé ('.$stage.') — '.$booking->reference);
        } catch (QueryException $e) {
            if ($this->isDuplicate($e)) {
                return;
            }
            throw $e;
        }

        $platform->refresh();
        WalletTransaction::create([
            'wallet_id' => $platform->id, 'type' => 'tour_'.$stage.'_commission', 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $commission, 'balance_before_xof' => $platform->balance_available_xof,
            'balance_after_xof' => $platform->balance_available_xof, 'tour_booking_id' => $booking->id,
            'description' => 'Commission plateforme — '.$booking->reference,
        ]);

        if ($net > 0 && $booking->provider) {
            $platform->refresh();
            $this->row($platform, 'balance_available_xof', 'tour_'.$stage.'_transfer', -$net, $booking, 'Transfert vers agence — '.$booking->reference);
            $providerWallet = $this->lockedWallet(Wallet::forProvider($booking->provider)->id);
            $this->row($providerWallet, 'balance_pending_xof', 'tour_'.$stage.'_credit_pending', $net, $booking, 'Circuit '.$booking->reference.' (en attente du départ)');
        }
    }

    /** Part de l'agence déjà créditée « en attente » sur cette réservation. */
    private function providerPendingCredited(TourBooking $booking): int
    {
        return (int) WalletTransaction::where('tour_booking_id', $booking->id)
            ->whereIn('type', ['tour_deposit_credit_pending', 'tour_balance_credit_pending', 'tour_full_credit_pending'])
            ->sum('amount_xof');
    }

    /**
     * Rembourse $amount au client sur son solde. La reprise est répartie entre la part de l'agence
     * (en attente) et la commission de la plateforme, au prorata de ce qui a été encaissé.
     */
    public function refund(TourBooking $booking, int $amount, string $reason): void
    {
        DB::transaction(function () use ($booking, $amount, $reason) {
            $booking = TourBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $amount = min($amount, $booking->amount_paid_xof);
            if ($amount <= 0 || WalletTransaction::where('tour_booking_id', $booking->id)->where('type', 'tour_refund_client_credit')->exists()) {
                return;
            }

            // Rien n'a transité par la plateforme (données importées ou de démonstration) : aucune écriture.
            $collected = (int) WalletTransaction::where('tour_booking_id', $booking->id)
                ->whereIn('type', ['tour_deposit_collected', 'tour_balance_collected', 'tour_full_collected'])->sum('amount_xof');
            if ($collected <= 0) {
                $booking->update(['refund_xof' => $amount]);

                return;
            }
            $amount = min($amount, $collected);

            $providerNet = $this->providerPendingCredited($booking);
            $providerShare = $booking->amount_paid_xof > 0 ? (int) round($amount * $providerNet / $booking->amount_paid_xof) : 0;
            $platformShare = $amount - $providerShare;

            if ($providerShare > 0 && $booking->provider) {
                $wallet = $this->lockedWallet(Wallet::forProvider($booking->provider)->id);
                $fromPending = min($providerShare, $wallet->balance_pending_xof);
                if ($fromPending > 0) {
                    $this->row($wallet, 'balance_pending_xof', 'tour_refund_provider_debit', -$fromPending, $booking, 'Remboursement voyageur — '.$booking->reference);
                }
                if ($providerShare - $fromPending > 0) {
                    ProviderDebt::create([
                        'provider_id' => $booking->provider_id, 'wallet_id' => $wallet->id, 'amount_xof' => $providerShare - $fromPending,
                        'reason' => 'Remboursement voyageur — circuit '.$booking->reference, 'status' => ProviderDebt::STATUS_OUTSTANDING,
                    ]);
                }
            }
            if ($platformShare > 0) {
                $platform = $this->lockedWallet(Wallet::platform()->id);
                $this->row($platform, 'balance_available_xof', 'tour_refund_platform_debit', -$platformShare, $booking, 'Remboursement voyageur (commission) — '.$booking->reference);
            }
            if ($booking->user) {
                $client = $this->lockedWallet(Wallet::forUser($booking->user)->id);
                $this->row($client, 'balance_available_xof', 'tour_refund_client_credit', $amount, $booking, $reason.' — '.$booking->reference);
            }
            $booking->update(['refund_xof' => $amount]);
        });
    }

    /** Libère la part des agences pour les départs passés (montant crédité moins les remboursements). */
    public function releaseDueCredits(): int
    {
        $released = 0;
        TourBooking::query()
            ->whereDate('departure_date', '<', now('Africa/Abidjan')->toDateString())
            ->where('amount_paid_xof', '>', 0)
            ->whereNotIn('id', WalletTransaction::where('type', 'tour_credit_available')->whereNotNull('tour_booking_id')->select('tour_booking_id'))
            ->get()
            ->each(function (TourBooking $booking) use (&$released) {
                DB::transaction(function () use ($booking, &$released) {
                    $credited = $this->providerPendingCredited($booking);
                    $debited = (int) -WalletTransaction::where('tour_booking_id', $booking->id)->where('type', 'tour_refund_provider_debit')->sum('amount_xof');
                    $amount = $credited - $debited;
                    if ($amount <= 0 || ! $booking->provider) {
                        return;
                    }
                    $wallet = $this->lockedWallet(Wallet::forProvider($booking->provider)->id);
                    if ($wallet->balance_pending_xof < $amount) {
                        return;
                    }
                    try {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id, 'type' => 'tour_credit_available', 'status' => WalletTransaction::STATUS_COMPLETED,
                            'amount_xof' => $amount, 'balance_before_xof' => $wallet->balance_available_xof,
                            'balance_after_xof' => $wallet->balance_available_xof + $amount, 'tour_booking_id' => $booking->id,
                            'description' => 'Circuit '.$booking->reference.' — départ effectué',
                        ]);
                    } catch (QueryException $e) {
                        if ($this->isDuplicate($e)) {
                            return;
                        }
                        throw $e;
                    }
                    $wallet->decrementPending($amount);
                    $wallet->incrementAvailable($amount);
                    $released++;
                });
            });

        return $released;
    }

    private function row(Wallet $wallet, string $field, string $type, int $signed, TourBooking $booking, string $description): void
    {
        $before = $wallet->{$field};
        WalletTransaction::create([
            'wallet_id' => $wallet->id, 'type' => $type, 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signed, 'balance_before_xof' => $before, 'balance_after_xof' => $before + $signed,
            'tour_booking_id' => $booking->id, 'description' => $description,
        ]);
        if ($field === 'balance_pending_xof') {
            $signed >= 0 ? $wallet->incrementPending($signed) : $wallet->decrementPending(-$signed);
        } else {
            $signed >= 0 ? $wallet->incrementAvailable($signed) : $wallet->decrementAvailable(-$signed);
        }
    }

    private function lockedWallet(int $id): Wallet
    {
        return Wallet::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function isDuplicate(QueryException $e): bool
    {
        return in_array((string) ($e->errorInfo[1] ?? ''), ['1062', '19'], true) || str_contains($e->getMessage(), 'Duplicate');
    }
}
