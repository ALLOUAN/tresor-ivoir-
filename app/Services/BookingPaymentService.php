<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Argent des demandes de réservation (restaurant, loisir, circuit, offre groupée).
 *
 * Encaissement : la plateforme encaisse, retient sa commission, et la part de l'établissement
 * part en « attente » sur son portefeuille. Elle devient disponible une fois la date de la
 * prestation passée (releaseDueCredits), sauf annulation ou remboursement entre-temps.
 * Chaque écriture est unique par (demande, type) : un double appel (webhook + retour navigateur)
 * ne crédite jamais deux fois.
 */
class BookingPaymentService
{
    public const DEFAULT_COMMISSION_PERCENT = 10.0;

    public const TYPE_SALE = 'booking_sale_collected';

    public const TYPE_COMMISSION = 'booking_commission';

    public const TYPE_TRANSFER = 'booking_transfer_to_provider';

    public const TYPE_CREDIT_PENDING = 'booking_credit_pending';

    public const TYPE_CREDIT_AVAILABLE = 'booking_credit_available';

    public function commissionPercent(): float
    {
        $value = PaymentSetting::valueOf('booking_commission_percent');

        return $value !== null ? (float) $value : self::DEFAULT_COMMISSION_PERCENT;
    }

    /** Fixe le montant à payer et la commission au moment où l'établissement confirme. */
    public function priceBooking(BookingRequest $booking, int $amountXof): void
    {
        $rate = $this->commissionPercent();
        $booking->update([
            'amount_due_xof' => $amountXof > 0 ? $amountXof : null,
            'commission_percent' => $amountXof > 0 ? $rate : null,
            'commission_amount_xof' => $amountXof > 0 ? (int) round($amountXof * $rate / 100) : null,
        ]);
    }

    /** Paiement confirmé par l'opérateur (CinetPay) : idempotent. */
    public function markPaid(BookingRequest $booking, string $method, ?string $gatewayTxnId = null): bool
    {
        return DB::transaction(function () use ($booking, $method, $gatewayTxnId) {
            $booking = BookingRequest::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->payment_status === BookingRequest::PAYMENT_PAID) {
                return false;
            }

            $booking->update([
                'payment_status' => BookingRequest::PAYMENT_PAID,
                'payment_method' => $method,
                'gateway_txn_id' => $gatewayTxnId ?? $booking->gateway_txn_id,
                'paid_at' => now(),
            ]);
            $this->collect($booking);

            return true;
        });
    }

    /** Paiement depuis le solde du client. */
    public function payFromWallet(BookingRequest $booking, User $client): void
    {
        $amount = (int) $booking->amount_due_xof;
        if ($amount <= 0) {
            throw new \RuntimeException(__('Aucun montant à payer pour cette demande.'));
        }

        DB::transaction(function () use ($booking, $client, $amount) {
            $booking = BookingRequest::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->payment_status === BookingRequest::PAYMENT_PAID) {
                return;
            }

            $wallet = $this->lockedWallet(Wallet::forUser($client)->id);
            if ($wallet->balance_available_xof < $amount) {
                throw new \RuntimeException(__('Solde insuffisant pour payer cette réservation depuis votre portefeuille.'));
            }

            $this->row($wallet, 'balance_available_xof', WalletTransaction::TYPE_WALLET_PAYMENT, -$amount, $booking, 'Paiement réservation — '.$booking->reference);
            $booking->update([
                'payment_status' => BookingRequest::PAYMENT_PAID,
                'payment_method' => 'wallet',
                'paid_at' => now(),
            ]);
            $this->collect($booking);
        });
    }

    /** Écritures d'encaissement : plateforme (vente, commission, transfert) puis part en attente du prestataire. */
    private function collect(BookingRequest $booking): void
    {
        $amount = (int) $booking->amount_due_xof;
        if ($amount <= 0) return;

        $commission = max(0, (int) $booking->commission_amount_xof);
        $net = max(0, $amount - $commission);

        try {
            $platform = $this->lockedWallet(Wallet::platform()->id);
            $this->row($platform, 'balance_available_xof', self::TYPE_SALE, $amount, $booking, 'Réservation encaissée — '.$booking->reference);
        } catch (QueryException $e) {
            if ($this->isDuplicate($e)) return; // déjà encaissée
            throw $e;
        }

        $platform->refresh();
        WalletTransaction::create([
            'wallet_id' => $platform->id, 'type' => self::TYPE_COMMISSION, 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $commission, 'balance_before_xof' => $platform->balance_available_xof,
            'balance_after_xof' => $platform->balance_available_xof, 'booking_request_id' => $booking->id,
            'description' => 'Commission plateforme — '.$booking->reference,
        ]);

        if ($net > 0 && $booking->provider) {
            $platform->refresh();
            $this->row($platform, 'balance_available_xof', self::TYPE_TRANSFER, -$net, $booking, 'Transfert vers prestataire — '.$booking->reference);
            $providerWallet = $this->lockedWallet(Wallet::forProvider($booking->provider)->id);
            $this->row($providerWallet, 'balance_pending_xof', self::TYPE_CREDIT_PENDING, $net, $booking, 'Réservation '.$booking->reference.' (en attente de la prestation)');
        }
    }

    /** Libère la part des prestataires pour les prestations passées et non annulées. Retourne le nombre libéré. */
    public function releaseDueCredits(): int
    {
        $released = 0;
        WalletTransaction::where('type', self::TYPE_CREDIT_PENDING)
            ->whereNotNull('booking_request_id')
            ->whereNotIn('booking_request_id', WalletTransaction::where('type', self::TYPE_CREDIT_AVAILABLE)->whereNotNull('booking_request_id')->select('booking_request_id'))
            ->with('bookingRequest')
            ->get()
            ->each(function (WalletTransaction $row) use (&$released) {
                $booking = $row->bookingRequest;
                if (! $booking
                    || $booking->status !== BookingRequest::STATUS_CONFIRMED
                    || $booking->payment_status !== BookingRequest::PAYMENT_PAID
                    || $booking->booking_date->isFuture()
                    || $booking->booking_date->isToday()) {
                    return;
                }
                DB::transaction(function () use ($row, $booking, &$released) {
                    $wallet = $this->lockedWallet($row->wallet_id);
                    $amount = (int) $row->amount_xof;
                    if ($wallet->balance_pending_xof < $amount) return;
                    try {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id, 'type' => self::TYPE_CREDIT_AVAILABLE, 'status' => WalletTransaction::STATUS_COMPLETED,
                            'amount_xof' => $amount, 'balance_before_xof' => $wallet->balance_available_xof,
                            'balance_after_xof' => $wallet->balance_available_xof + $amount, 'booking_request_id' => $booking->id,
                            'description' => 'Réservation '.$booking->reference.' — prestation effectuée',
                        ]);
                    } catch (QueryException $e) {
                        if ($this->isDuplicate($e)) return;
                        throw $e;
                    }
                    $wallet->decrementPending($amount);
                    $wallet->incrementAvailable($amount);
                    $released++;
                });
            });

        return $released;
    }

    /**
     * Remboursement enregistré par l'administration : reprend la part en attente du prestataire,
     * recrédite le solde du client s'il avait payé avec son solde (sinon remboursement CinetPay
     * hors système, comme pour les autres paiements).
     */
    public function refund(BookingRequest $booking, ?User $admin = null): void
    {
        DB::transaction(function () use ($booking, $admin) {
            $booking = BookingRequest::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->payment_status !== BookingRequest::PAYMENT_PAID) {
                throw new \RuntimeException(__('Seule une réservation payée peut être remboursée.'));
            }
            if (WalletTransaction::where('booking_request_id', $booking->id)->where('type', self::TYPE_CREDIT_AVAILABLE)->exists()) {
                throw new \RuntimeException(__('La part du prestataire a déjà été libérée : remboursement à régler manuellement.'));
            }

            $pending = WalletTransaction::where('booking_request_id', $booking->id)->where('type', self::TYPE_CREDIT_PENDING)->first();
            if ($pending) {
                $wallet = $this->lockedWallet($pending->wallet_id);
                $this->row($wallet, 'balance_pending_xof', WalletTransaction::TYPE_REFUND_DEBIT, -min((int) $pending->amount_xof, $wallet->balance_pending_xof), $booking, 'Remboursement client — '.$booking->reference);
            }

            if ($booking->payment_method === 'wallet' && $booking->user) {
                $clientWallet = $this->lockedWallet(Wallet::forUser($booking->user)->id);
                $this->row($clientWallet, 'balance_available_xof', WalletTransaction::TYPE_REFUND_CREDIT, (int) $booking->amount_due_xof, $booking, 'Remboursement crédité sur votre solde — '.$booking->reference);
            }

            $booking->update(['payment_status' => BookingRequest::PAYMENT_REFUNDED, 'refund_due_xof' => 0]);
        });
    }

    private function row(Wallet $wallet, string $field, string $type, int $signed, BookingRequest $booking, string $description): void
    {
        $before = $wallet->{$field};
        WalletTransaction::create([
            'wallet_id' => $wallet->id, 'type' => $type, 'status' => WalletTransaction::STATUS_COMPLETED,
            'amount_xof' => $signed, 'balance_before_xof' => $before, 'balance_after_xof' => $before + $signed,
            'booking_request_id' => $booking->id, 'description' => $description,
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
