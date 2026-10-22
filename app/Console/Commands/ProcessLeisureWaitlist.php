<?php

namespace App\Console\Commands;

use App\Services\LeisureBookingService;
use Illuminate\Console\Command;

/** Liste d'attente des activités : délais de réservation expirés → la place est proposée à la personne suivante. */
class ProcessLeisureWaitlist extends Command
{
    protected $signature = 'leisure:waitlist';

    protected $description = 'Fait avancer les listes d\'attente des activités de loisirs';

    public function handle(LeisureBookingService $service): int
    {
        $this->info('Personnes prévenues : '.$service->processWaitlist());

        return self::SUCCESS;
    }
}
