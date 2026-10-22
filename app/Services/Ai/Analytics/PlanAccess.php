<?php

namespace App\Services\Ai\Analytics;

use Illuminate\Support\Facades\DB;

/** Niveau de statistiques et limites du forfait actif d'un prestataire. */
class PlanAccess
{
    public const RANK = ['basic' => 1, 'advanced' => 2, 'full' => 3];

    /** @return array{level:string,rank:int,plan:?string,photos_limit:?int,description_chars:?int} */
    public function for(int $providerId): array
    {
        $rows = DB::table('subscriptions')->join('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->where('subscriptions.provider_id', $providerId)->where('subscriptions.status', 'active')->where('subscriptions.ends_at', '>', now())
            ->get(['subscription_plans.code', 'subscription_plans.name_fr', 'subscription_plans.stats_level', 'subscription_plans.photos_limit', 'subscription_plans.description_chars']);
        if ($rows->isEmpty()) {
            return ['level' => 'basic', 'rank' => 1, 'plan' => null, 'photos_limit' => null, 'description_chars' => null];
        }
        $best = $rows->sortByDesc(fn ($r) => self::RANK[$r->stats_level] ?? 1)->first();

        return [
            'level' => $best->stats_level, 'rank' => self::RANK[$best->stats_level] ?? 1, 'plan' => $best->name_fr,
            'photos_limit' => (int) $best->photos_limit ?: null, 'description_chars' => (int) $best->description_chars ?: null,
        ];
    }
}
