<?php

namespace App\Services;

use App\Models\ArtCommission;
use Illuminate\Database\Eloquent\Model;

/**
 * Argent des commandes sur mesure : deux versements (acompte, solde), chacun encaissé avec commission et
 * crédité à l'artiste (disponible, pour financer le matériel). Écritures rattachées à art_commission_id,
 * typées par étape (art_commission_deposit_*, art_commission_balance_*).
 */
class ArtCommissionPaymentService extends LeisurePaymentService
{
    private string $stage = 'deposit';

    public function forStage(string $stage): static
    {
        $clone = clone $this;
        $clone->stage = $stage;

        return $clone;
    }

    protected function fk(Model $owner): string
    {
        return 'art_commission_id';
    }

    protected function prefix(Model $owner): string
    {
        return 'art_commission_'.$this->stage;
    }

    public function amountDue(Model $owner): int
    {
        return $this->stage === 'deposit' ? (int) $owner->deposit_xof : $owner->balanceXof();
    }

    protected function isSettled(Model $owner): bool
    {
        return $this->stage === 'deposit'
            ? ! in_array($owner->status, ['quoted'], true)
            : ! in_array($owner->status, ['sketch_approved'], true);
    }

    protected function settle(Model $owner, string $gateway, ?string $txn): void
    {
        $amount = $this->amountDue($owner);
        $owner->update([
            'status' => $this->stage === 'deposit' ? 'deposit_paid' : 'balance_paid',
            'amount_paid_xof' => (int) $owner->amount_paid_xof + $amount,
            'pending_stage' => null, 'gateway_txn_id' => $txn ?? $owner->gateway_txn_id,
        ]);
        $this->collect($owner, $amount);
    }
}
