<?php

namespace App\Services;

use App\Models\TransportBooking;
use Illuminate\Database\Eloquent\Model;

/**
 * Argent des réservations de transport : mêmes règles que les loisirs (commission retenue, part de la
 * compagnie créditée disponible, remboursement au prorata), écritures rattachées à transport_booking_id.
 */
class TransportPaymentService extends LeisurePaymentService
{
    protected function fk(Model $owner): string
    {
        return 'transport_booking_id';
    }

    protected function prefix(Model $owner): string
    {
        return 'transport';
    }

    public function amountDue(Model $owner): int
    {
        return (int) $owner->amount_total_xof;
    }

    protected function isSettled(Model $owner): bool
    {
        return $owner->status === TransportBooking::STATUS_PAID;
    }

    protected function settle(Model $owner, string $gateway, ?string $txn): void
    {
        $owner->update(['status' => TransportBooking::STATUS_PAID, 'gateway' => $gateway, 'paid_at' => now(), 'gateway_txn_id' => $txn ?? $owner->gateway_txn_id]);
        $this->collect($owner, $this->amountDue($owner));
    }
}
