<?php

namespace App\Services;

use App\Models\ActivityBooking;
use App\Models\LeisurePassPurchase;
use App\Models\ProviderDebt;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Argent des loisirs : réservations d'activités / formules (acompte) et cartes multi-séances.
 *
 * Comme pour les visites de sites, la plateforme encaisse, retient sa commission et crédite aussitôt la
 * part du lieu (disponible). Un remboursement reprend la part du lieu (dette si son solde ne suffit pas)
 * et la commission au prorata, puis crédite le solde du client. Écritures uniques par (objet, type).
 */
class LeisurePaymentService
{
    public function __construct(private readonly BookingPaymentService $bookingPayments) {}

    public function commissionPercent(): float
    {
        return $this->bookingPayments->commissionPercent();
    }

    protected function fk(Model $owner): string
    {
        return $owner instanceof LeisurePassPurchase ? 'leisure_pass_purchase_id' : 'activity_booking_id';
    }

    protected function prefix(Model $owner): string
    {
        return $owner instanceof LeisurePassPurchase ? 'leisure_pass' : 'leisure';
    }

    /** Montant à encaisser : acompte d'une formule, ou totalité. */
    public function amountDue(Model $owner): int
    {
        return $owner instanceof LeisurePassPurchase ? (int) $owner->amount_xof : (int) $owner->amount_paid_xof;
    }

    /** Paiement depuis le solde du client (réservation ou carte). */
    public function payFromWallet(Model $owner, User $client): void
    {
        DB::transaction(function () use ($owner, $client) {
            $owner = $owner::whereKey($owner->getKey())->lockForUpdate()->firstOrFail();
            if ($this->isSettled($owner)) {
                return;
            }
            $amount = $this->amountDue($owner);
            $wallet = $this->lockedWallet(Wallet::forUser($client)->id);
            if ($wallet->balance_available_xof < $amount) {
                throw new \RuntimeException(__('Solde insuffisant pour payer depuis votre portefeuille.'));
            }
            if ($amount > 0) {
                $this->row($wallet, $owner, $this->prefix($owner).'_wallet_payment', -$amount, __('Paiement').' — '.$owner->reference);
            }
            $this->settle($owner, 'wallet', null);
        });
    }

    /** Paiement confirmé par CinetPay (webhook ou retour). Idempotent : true au premier encaissement. */
    public function markPaid(Model $owner, ?string $txn = null): bool
    {
        return DB::transaction(function () use ($owner, $txn) {
            $owner = $owner::whereKey($owner->getKey())->lockForUpdate()->firstOrFail();
            if ($this->isSettled($owner) || in_array($owner->status, ['cancelled', 'refunded'], true)) {
                return false;
            }
            $this->settle($owner, 'cinetpay', $txn);

            return true;
        });
    }

    protected function isSettled(Model $owner): bool
    {
        return $owner instanceof LeisurePassPurchase ? $owner->status === LeisurePassPurchase::STATUS_ACTIVE : $owner->status === ActivityBooking::STATUS_PAID;
    }

    protected function settle(Model $owner, string $gateway, ?string $txn): void
    {
        if ($owner instanceof LeisurePassPurchase) {
            $owner->update(['status' => LeisurePassPurchase::STATUS_ACTIVE, 'gateway' => $gateway, 'paid_at' => now(),
                'gateway_txn_id' => $txn ?? $owner->gateway_txn_id, 'expires_on' => now('Africa/Abidjan')->addDays((int) ($owner->pass?->validity_days ?? 180))->toDateString()]);
        } else {
            $owner->update(['status' => ActivityBooking::STATUS_PAID, 'gateway' => $gateway, 'paid_at' => now(), 'gateway_txn_id' => $txn ?? $owner->gateway_txn_id]);
        }
        $this->collect($owner, $this->amountDue($owner));
    }

    /** Encaissement : plateforme (vente + commission), part du lieu créditée disponible. */
    protected function collect(Model $owner, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        $commission = (int) round($amount * (float) $owner->commission_percent / 100);
        $net = max(0, $amount - $commission);
        $p = $this->prefix($owner);
        try {
            $platform = $this->lockedWallet(Wallet::platform()->id);
            $this->row($platform, $owner, $p.'_collected', $amount, 'Loisirs encaissé — '.$owner->reference);
        } catch (QueryException $e) {
            if ($this->isDuplicate($e)) {
                return;
            }
            throw $e;
        }
        if ($owner instanceof ActivityBooking) {
            $owner->update(['commission_amount_xof' => $commission]);
        }
        if ($net > 0 && $owner->provider) {
            $platform->refresh();
            $this->row($platform, $owner, $p.'_transfer', -$net, 'Transfert vers prestataire — '.$owner->reference);
            $this->row($this->lockedWallet(Wallet::forProvider($owner->provider)->id), $owner, $p.'_credit', $net, 'Loisirs '.$owner->reference);
        }
    }

    /** Rembourse $amount sur le solde du client ; reprise au prorata sur le lieu et la commission. */
    public function refund(Model $booking, int $amount, string $reason): void
    {
        DB::transaction(function () use ($booking, $amount, $reason) {
            $booking = $booking::whereKey($booking->getKey())->lockForUpdate()->firstOrFail();
            $fk = $this->fk($booking);
            $p = $this->prefix($booking);
            $collected = (int) WalletTransaction::where($fk, $booking->getKey())->where('type', $p.'_collected')->value('amount_xof');
            $amount = min($amount, $collected);
            if ($amount <= 0 || WalletTransaction::where($fk, $booking->getKey())->where('type', $p.'_refund_client')->exists()) {
                return;
            }
            $net = (int) WalletTransaction::where($fk, $booking->getKey())->where('type', $p.'_credit')->value('amount_xof');
            $providerShare = (int) round($amount * $net / $collected);
            $platformShare = $amount - $providerShare;

            if ($providerShare > 0 && $booking->provider) {
                $wallet = $this->lockedWallet(Wallet::forProvider($booking->provider)->id);
                $taken = min($providerShare, $wallet->balance_available_xof);
                if ($taken > 0) {
                    $this->row($wallet, $booking, $p.'_refund_provider', -$taken, 'Remboursement client — '.$booking->reference);
                }
                if ($providerShare - $taken > 0) {
                    ProviderDebt::create(['provider_id' => $booking->provider_id, 'wallet_id' => $wallet->id, 'amount_xof' => $providerShare - $taken,
                        'reason' => 'Remboursement client — '.$booking->reference, 'status' => ProviderDebt::STATUS_OUTSTANDING]);
                }
            }
            if ($platformShare > 0) {
                $this->row($this->lockedWallet(Wallet::platform()->id), $booking, $p.'_refund_platform', -$platformShare, 'Remboursement client (commission) — '.$booking->reference);
            }
            if ($booking->user) {
                $this->row($this->lockedWallet(Wallet::forUser($booking->user)->id), $booking, $p.'_refund_client', $amount, $reason.' — '.$booking->reference);
            }
        });
    }

    protected function row(Wallet $wallet, Model $owner, string $type, int $signed, string $description): void
    {
        $before = $wallet->balance_available_xof;
        WalletTransaction::create([
            'wallet_id' => $wallet->id, 'type' => $type, 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signed, 'balance_before_xof' => $before, 'balance_after_xof' => $before + $signed,
            $this->fk($owner) => $owner->getKey(), 'description' => $description,
        ]);
        $signed >= 0 ? $wallet->incrementAvailable($signed) : $wallet->decrementAvailable(-$signed);
    }

    protected function lockedWallet(int $id): Wallet
    {
        return Wallet::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function isDuplicate(QueryException $e): bool
    {
        return in_array((string) ($e->errorInfo[1] ?? ''), ['1062', '19'], true) || str_contains($e->getMessage(), 'Duplicate');
    }
}
