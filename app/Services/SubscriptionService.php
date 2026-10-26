<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Support\PlanBundle;

/**
 * Abonnements prestataire. Un prestataire multi-secteurs a un abonnement actif par secteur
 * (subscriptions.sector_category_id) : un nouveau forfait ne remplace que l'abonnement du même secteur.
 * Les abonnements achetés ensemble (un seul paiement) sont reliés par bundle_parent_id.
 * Abonnements antérieurs sans secteur enregistré : rattachés au secteur principal du prestataire.
 */
class SubscriptionService
{
    /**
     * @param  Subscription|null  $bundleParent  abonnement principal d'un achat groupé (plusieurs secteurs, un paiement)
     * @param  ProviderCategory|null  $sector  secteur couvert (à défaut : celui du forfait, sinon le secteur principal)
     */
    public function createSubscription(
        Provider $provider,
        SubscriptionPlan $plan,
        string $billingCycle,
        string $method,
        string $transactionId,
        ?Subscription $bundleParent = null,
        ?ProviderCategory $sector = null
    ): Subscription {
        $sector = $this->resolveSector($provider, $plan, $sector);
        $this->cancelActiveInSector($provider, $sector?->id, null, $plan->code);

        $months = $billingCycle === 'yearly' ? 12 : 1;

        $subscription = Subscription::create([
            'provider_id'        => $provider->id,
            'plan_id'            => $plan->id,
            'sector_category_id' => $sector?->id,
            'status'             => 'active',
            'billing_cycle'      => $billingCycle,
            'payment_method'     => $method,
            'starts_at'          => now(),
            'ends_at'            => now()->addMonths($months),
            'auto_renew'         => true,
            'bundle_parent_id'   => $bundleParent?->id,
        ]);
        if ($sector) {
            $provider->addSector($sector->slug);
        }

        return $subscription;
    }

    /** Active un abonnement payé, et les abonnements des autres secteurs achetés avec lui. */
    public function activatePendingSubscription(Subscription $subscription): void
    {
        $group = collect([$subscription])->merge(
            Subscription::where('bundle_parent_id', $subscription->id)->where('status', 'pending')->get()
        );

        foreach ($group as $sub) {
            $sub->loadMissing('plan', 'provider');
            if ($sub->provider) {
                $this->cancelActiveInSector($sub->provider, $sub->sector_category_id, $sub->id, (string) $sub->plan?->code);
            }
            $sub->update(['status' => 'active']);
            if ($sub->provider && $sub->sector_category_id) {
                $slug = ProviderCategory::whereKey($sub->sector_category_id)->value('slug');
                $slug && $sub->provider->addSector($slug);
            }
        }
    }

    /** Secteur couvert : celui demandé, sinon celui du forfait ciblé, sinon le secteur principal du prestataire. */
    public function resolveSector(Provider $provider, SubscriptionPlan $plan, ?ProviderCategory $sector): ?ProviderCategory
    {
        if ($sector) {
            return $sector;
        }

        return PlanBundle::sectorFor($plan, null) ?? $this->primarySector($provider);
    }

    /** Annule l'abonnement actif du même secteur (et lui seul). */
    public function cancelActiveInSector(Provider $provider, ?int $sectorId, ?int $exceptId, string $planCode): void
    {
        $primaryId = $this->primarySector($provider)?->id;

        Subscription::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where(function ($q) use ($sectorId, $primaryId) {
                $sectorId ? $q->where('sector_category_id', $sectorId) : $q->whereNull('sector_category_id');
                // Abonnements antérieurs sans secteur enregistré = secteur principal.
                if ($sectorId && $sectorId === $primaryId) {
                    $q->orWhereNull('sector_category_id');
                }
            })
            ->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancellation_reason' => 'Remplacé par forfait '.strtoupper($planCode),
            ]);
    }

    private function primarySector(Provider $provider): ?ProviderCategory
    {
        $category = $provider->category;

        return $category?->parent_id ? $category->parent : $category;
    }
}
