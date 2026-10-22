<?php

namespace App\Console\Commands;

use App\Services\TourBookingService;
use Illuminate\Console\Command;

/** Circuits : relances du solde, annulations pour impayé, départs garantis ou annulés, libération des crédits agences. */
class ProcessTourBookings extends Command
{
    protected $signature = 'tours:process';

    protected $description = 'Traite les réservations de circuits (solde, départs garantis, crédits agences)';

    public function handle(TourBookingService $service): int
    {
        $stats = $service->processDaily();
        $this->info(sprintf(
            'Relances : %d · annulées (solde impayé) : %d · départs garantis : %d · départs annulés : %d · crédits libérés : %d',
            $stats['reminded'], $stats['cancelled_unpaid'], $stats['guaranteed'], $stats['cancelled_departures'], $stats['released'],
        ));

        return self::SUCCESS;
    }
}
