<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class ExpireStaleReservations extends Command
{
    protected $signature = 'reservations:expire-stale';

    protected $description = 'Cancel reservations still pending payment after 3 hours, releasing the room hold';

    private const STALE_AFTER_HOURS = 3;

    public function handle(): int
    {
        $stale = Reservation::query()
            ->where('status', Reservation::STATUS_NEW)
            ->where('payment_status', Reservation::PAYMENT_PENDING)
            ->where('created_at', '<', now()->subHours(self::STALE_AFTER_HOURS))
            ->get();

        foreach ($stale as $reservation) {
            $reservation->update([
                'status' => Reservation::STATUS_CANCELLED,
                'payment_status' => Reservation::PAYMENT_FAILED,
            ]);
        }

        $this->info("Réservations expirées : {$stale->count()}");

        return self::SUCCESS;
    }
}
