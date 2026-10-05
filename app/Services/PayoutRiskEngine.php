<?php

namespace App\Services;

use App\Models\AccountSecurityEvent;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use Illuminate\Support\Carbon;

/**
 * Analyse un PayoutRequest fraîchement créé (statut encore 'pending') et retourne un score de
 * risque 0-100 + le détail des signaux déclenchés. N'écrit rien elle-même — WalletService
 * persiste le résultat et prend la décision finale.
 */
class PayoutRiskEngine
{
    public function __construct(
        private readonly PayoutDestinationValidator $destinationValidator,
    ) {}

    /**
     * @return array{score:int, level:string, flags:array<int, array{code:string, weight:int, message:string}>}
     */
    public function assess(PayoutRequest $payout): array
    {
        $flags = [];
        $addFlag = function (string $code, string $message) use (&$flags) {
            $weight = (int) config("payout_risk.weights.{$code}", 0);
            $flags[] = ['code' => $code, 'weight' => $weight, 'message' => $message];
        };

        $wallet = $payout->wallet ?? Wallet::find($payout->wallet_id);
        $holderUser = $payout->holderUser();

        // 1. Montant vs solde
        if ($wallet && $wallet->balance_available_xof > 0) {
            $ratio = $payout->amount_xof / $wallet->balance_available_xof;
            if ($ratio >= (float) config('payout_risk.amount_near_full_balance_ratio', 0.9)) {
                $addFlag('amount_near_full_balance', 'Le retrait vide (quasi) totalement le solde disponible.');
            }
        }

        $averagePastAmount = $this->averagePastPayoutAmount($payout);
        if ($averagePastAmount > 0 && $payout->amount_xof >= $averagePastAmount * (int) config('payout_risk.amount_average_multiplier', 3)) {
            $addFlag('amount_far_above_average', 'Montant très supérieur à la moyenne des retraits précédents.');
        }

        // 2. Historique
        $walletAgeDays = $wallet?->created_at ? $wallet->created_at->diffInDays(now()) : 0;
        $priorPaidCount = $this->countPastPayouts($payout, PayoutRequest::STATUS_PAID);
        if ($walletAgeDays < (int) config('payout_risk.new_account_days', 7) || $priorPaidCount === 0) {
            $addFlag('new_account', 'Compte récent ou sans historique de retrait abouti.');
        }

        if ($this->countPastPayouts($payout, PayoutRequest::STATUS_REJECTED) > 0) {
            $addFlag('prior_rejected_payout', 'Au moins un retrait précédent a été refusé.');
        }

        // 3. Fréquence
        $count24h = $this->countRecentPayouts($payout, now()->subDay());
        if ($count24h > (int) config('payout_risk.frequency_24h_max', 2)) {
            $addFlag('frequency_24h', 'Plus de '.config('payout_risk.frequency_24h_max', 2).' demandes de retrait en 24h.');
        }

        $count7d = $this->countRecentPayouts($payout, now()->subDays(7));
        if ($count7d > (int) config('payout_risk.frequency_7d_max', 5)) {
            $addFlag('frequency_7d', 'Plus de '.config('payout_risk.frequency_7d_max', 5).' demandes de retrait en 7 jours.');
        }

        // 4. Cohérence bénéficiaire
        if (! $this->destinationValidator->isValidFormat($payout->method, (string) $payout->payout_destination)) {
            $addFlag('invalid_destination_format', 'Format de la destination incohérent avec la méthode choisie.');
        }

        if ($this->destinationChanged($payout, $holderUser)) {
            $addFlag('destination_changed', 'Destination différente de celle déjà enregistrée sur le compte.');
        }

        // 5. Changements sensibles récents
        if ($holderUser && $this->hasRecentSensitiveChange($holderUser)) {
            $addFlag('recent_sensitive_change', 'Informations sensibles du compte modifiées récemment (moins de '.config('payout_risk.sensitive_change_window_hours', 72).'h).');
        }

        // 6. Connexion inhabituelle
        if ($holderUser) {
            $loginCheck = $this->unusualLoginCheck($holderUser, $payout->ip_address);
            if ($loginCheck === 'unusual') {
                $addFlag('unusual_login_ip', 'Adresse IP différente de la dernière connexion connue.');
            } elseif ($loginCheck === 'no_history') {
                $addFlag('no_login_history', 'Aucun historique de connexion disponible pour comparaison.');
            }
        }

        // 7. Anomalies / doublons (la règle "un seul retrait ouvert" est appliquée en amont dans WalletService)
        if ($this->hasDuplicateRecentRequest($payout)) {
            $addFlag('duplicate_recent_request', 'Demande identique (montant + destination) répétée récemment.');
        }

        $score = min(100, array_sum(array_column($flags, 'weight')));
        $level = match (true) {
            $score >= (int) config('payout_risk.suspend_at_or_above', 70) => PayoutRequest::RISK_HIGH,
            $score >= (int) config('payout_risk.auto_approve_below', 25) => PayoutRequest::RISK_MEDIUM,
            default => PayoutRequest::RISK_LOW,
        };

        return ['score' => $score, 'level' => $level, 'flags' => $flags];
    }

    private function averagePastPayoutAmount(PayoutRequest $payout): int
    {
        $query = PayoutRequest::query()
            ->where('status', PayoutRequest::STATUS_PAID)
            ->where('id', '!=', $payout->id ?? 0)
            ->latest('id')
            ->limit(5);

        $this->scopeToHolder($query, $payout);

        return (int) round($query->avg('amount_xof') ?? 0);
    }

    private function countPastPayouts(PayoutRequest $payout, string $status): int
    {
        $query = PayoutRequest::query()
            ->where('status', $status)
            ->where('id', '!=', $payout->id ?? 0);

        $this->scopeToHolder($query, $payout);

        return $query->count();
    }

    private function countRecentPayouts(PayoutRequest $payout, Carbon $since): int
    {
        $query = PayoutRequest::query()
            ->where('id', '!=', $payout->id ?? 0)
            ->where('created_at', '>=', $since);

        $this->scopeToHolder($query, $payout);

        return $query->count();
    }

    private function hasDuplicateRecentRequest(PayoutRequest $payout): bool
    {
        $windowMinutes = (int) config('payout_risk.duplicate_request_window_minutes', 30);

        $query = PayoutRequest::query()
            ->where('id', '!=', $payout->id ?? 0)
            ->where('amount_xof', $payout->amount_xof)
            ->where('payout_destination', $payout->payout_destination)
            ->where('created_at', '>=', now()->subMinutes($windowMinutes));

        $this->scopeToHolder($query, $payout);

        return $query->exists();
    }

    private function destinationChanged(PayoutRequest $payout, $holderUser): bool
    {
        $onFile = $payout->provider_id
            ? $payout->provider?->payout_account_number
            : $holderUser?->payout_account_number;

        return $onFile !== null && $onFile !== '' && $onFile !== $payout->payout_destination;
    }

    private function hasRecentSensitiveChange($holderUser): bool
    {
        $windowHours = (int) config('payout_risk.sensitive_change_window_hours', 72);

        return AccountSecurityEvent::where('user_id', $holderUser->id)
            ->whereIn('event_type', AccountSecurityEvent::SENSITIVE_TYPES)
            ->where('created_at', '>=', now()->subHours($windowHours))
            ->exists();
    }

    /** @return 'unusual'|'no_history'|'ok' */
    private function unusualLoginCheck($holderUser, ?string $currentIp): string
    {
        if (! $currentIp) {
            return 'ok';
        }

        $lastLogin = AccountSecurityEvent::where('user_id', $holderUser->id)
            ->where('event_type', AccountSecurityEvent::TYPE_LOGIN)
            ->latest('created_at')
            ->first();

        if (! $lastLogin || ! $lastLogin->ip_address) {
            return 'no_history';
        }

        return $lastLogin->ip_address !== $currentIp ? 'unusual' : 'ok';
    }

    private function scopeToHolder($query, PayoutRequest $payout): void
    {
        if ($payout->provider_id) {
            $query->where('provider_id', $payout->provider_id);
        } else {
            $query->where('user_id', $payout->user_id);
        }
    }
}
