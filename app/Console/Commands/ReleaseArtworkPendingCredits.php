<?php

namespace App\Console\Commands;

use App\Services\WalletService;
use Illuminate\Console\Command;

class ReleaseArtworkPendingCredits extends Command
{
    protected $signature = 'art:release-pending';

    protected $description = 'Filet de sécurité : libère automatiquement les crédits artiste restés "expédiés" sans confirmation de livraison au-delà du délai configuré';

    public function handle(WalletService $wallet): int
    {
        $wallet->releaseStaleArtworkCredits();
        // Part libérée après le délai de retour, échéances relancées / annulées, offres expirées.
        $stats = app(\App\Services\ArtSalesService::class)->processDaily();
        $this->line(json_encode($stats));

        $this->info('Crédits œuvres en attente traités.');

        return self::SUCCESS;
    }
}
