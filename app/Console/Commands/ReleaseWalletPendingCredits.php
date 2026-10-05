<?php

namespace App\Console\Commands;

use App\Services\WalletService;
use Illuminate\Console\Command;

class ReleaseWalletPendingCredits extends Command
{
    protected $signature = 'wallet:release-pending';

    protected $description = 'Bascule les crédits prestataires en attente vers le solde disponible selon le délai configuré';

    public function handle(WalletService $wallet): int
    {
        $wallet->releasePendingCredits();

        $this->info('Crédits en attente traités.');

        return self::SUCCESS;
    }
}
