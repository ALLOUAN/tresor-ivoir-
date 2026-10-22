<?php

namespace App\Console\Commands;

use App\Models\PaymentSetting;
use App\Models\Reservation;
use Illuminate\Console\Command;

class ExpireStaleReservations extends Command
{
    protected $signature = 'reservations:expire-stale';

    protected $description = 'Libère les chambres bloquées : paiements abandonnés (3 h) et demandes sans réponse ni paiement (48 h par défaut)';

    private const STALE_AFTER_HOURS = 3;

    private const DEFAULT_REQUEST_EXPIRY_HOURS = 48;

    public function handle(): int
    {
        // 1. Paiement d'acompte commencé mais jamais abouti.
        $stale = Reservation::query()
            ->where('status', Reservation::STATUS_NEW)
            ->where('payment_status', Reservation::PAYMENT_PENDING)
            ->where('created_at', '<', now()->subHours(self::STALE_AFTER_HOURS))
            ->get();

        foreach ($stale as $reservation) {
            $reservation->update([
                'status' => Reservation::STATUS_CANCELLED,
                'payment_status' => Reservation::PAYMENT_FAILED,
                'cancelled_by' => Reservation::CANCELLED_BY_SYSTEM,
                'cancelled_at' => now(),
            ]);
        }

        // 2. Demande de réservation sans paiement que l'établissement n'a ni confirmée ni refusée :
        //    sans expiration, elle bloquerait la chambre indéfiniment.
        $requestHours = (int) (PaymentSetting::where('key', 'reservation_request_expiry_hours')->value('value') ?: self::DEFAULT_REQUEST_EXPIRY_HOURS);
        $requests = Reservation::query()
            ->where('status', Reservation::STATUS_NEW)
            ->where('payment_status', Reservation::PAYMENT_UNPAID)
            ->where('created_at', '<', now()->subHours(max(1, $requestHours)))
            ->get();

        foreach ($requests as $reservation) {
            $reservation->update([
                'status' => Reservation::STATUS_CANCELLED,
                'cancelled_by' => Reservation::CANCELLED_BY_SYSTEM,
                'cancelled_at' => now(),
            ]);
        }

        // 3. Demandes de table / activité / circuit restées sans réponse une fois la date passée.
        $bookings = \App\Models\BookingRequest::query()
            ->where('status', \App\Models\BookingRequest::STATUS_NEW)
            ->whereDate('booking_date', '<', now()->toDateString())
            ->update(['status' => \App\Models\BookingRequest::STATUS_EXPIRED]);

        $this->info("Paiements abandonnés expirés : {$stale->count()} · demandes sans réponse expirées : {$requests->count()} · demandes de table/activité expirées : {$bookings}");

        return self::SUCCESS;
    }
}