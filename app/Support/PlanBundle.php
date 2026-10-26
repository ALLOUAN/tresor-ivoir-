<?php

namespace App\Support;

use App\Models\ProviderCategory;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Achat de plusieurs secteurs en une fois : un couple « secteur + forfait » par secteur, un seul paiement.
 * Les forfaits génériques (Bronze, Silver, Gold…) servent à tous les secteurs : le secteur est donc porté
 * à côté du forfait. Format des secteurs supplémentaires dans les URL et formulaires : « slug:idForfait,… ».
 * Règles : forfaits actifs, un forfait par secteur, un forfait ciblé ne vaut que pour son secteur,
 * montant = somme des forfaits du cycle choisi.
 */
class PlanBundle
{
    public const MAX_SECTORS = 7;

    /**
     * Tous les éléments de l'achat : le forfait principal (et son secteur) d'abord, puis les secteurs supplémentaires.
     *
     * @return Collection<int, array{plan: SubscriptionPlan, sector: ?ProviderCategory}>
     */
    public static function items(SubscriptionPlan $main, ?string $mainSectorSlug, array|string|null $extras): Collection
    {
        $mainSector = self::sectorFor($main, $mainSectorSlug);
        $items = collect([['plan' => $main, 'sector' => $mainSector]]);
        $used = [$mainSector?->id ?? 'none'];

        $pairs = collect(is_array($extras) ? $extras : explode(',', (string) $extras))
            ->map(fn ($p) => array_pad(explode(':', trim((string) $p), 2), 2, null))
            ->filter(fn ($p) => $p[0] !== '' && (int) $p[1] > 0)
            ->take(self::MAX_SECTORS - 1);
        if ($pairs->isEmpty()) {
            return $items;
        }

        $plans = SubscriptionPlan::query()->whereIn('id', $pairs->pluck(1)->map(fn ($id) => (int) $id))
            ->where('is_active', true)->with('providerCategory.parent')->get()->keyBy('id');

        foreach ($pairs as [$slug, $planId]) {
            $plan = $plans->get((int) $planId);
            $sector = $plan ? self::sectorFor($plan, $slug) : null;
            if (! $sector || in_array($sector->id, $used, true)) {
                continue;
            }
            $used[] = $sector->id;
            $items->push(['plan' => $plan, 'sector' => $sector]);
        }

        return $items;
    }

    /** Secteurs supplémentaires seulement (sans le forfait principal). */
    public static function extras(SubscriptionPlan $main, ?string $mainSectorSlug, array|string|null $extras): Collection
    {
        return self::items($main, $mainSectorSlug, $extras)->slice(1)->values();
    }

    /** Valeur « slug:id,… » des secteurs supplémentaires, à repasser dans une URL ou un champ caché. */
    public static function encode(Collection $extras): string
    {
        return $extras->map(fn ($i) => $i['sector']->slug.':'.$i['plan']->id)->implode(',');
    }

    public static function total(iterable $items, string $billingCycle): float
    {
        $sum = 0.0;
        foreach ($items as $item) {
            $plan = $item instanceof SubscriptionPlan ? $item : $item['plan'];
            $sum += $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
        }

        return $sum;
    }

    /**
     * Secteur (catégorie racine active) couvert par ce forfait : celui du forfait s'il est ciblé
     * (le slug demandé doit alors correspondre), sinon celui demandé.
     */
    public static function sectorFor(SubscriptionPlan $plan, ?string $slug): ?ProviderCategory
    {
        $planSector = $plan->providerCategory?->parent_id ? $plan->providerCategory->parent : $plan->providerCategory;
        if ($planSector) {
            return ($slug === null || $slug === '' || $slug === $planSector->slug) ? $planSector : null;
        }
        if (! $slug) {
            return null;
        }

        return ProviderCategory::query()->where('slug', $slug)->whereNull('parent_id')->where('is_active', true)->first();
    }
}
