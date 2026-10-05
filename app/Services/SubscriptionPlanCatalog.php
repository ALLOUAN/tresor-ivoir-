<?php

namespace App\Services;

use App\Models\ProviderCategory;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionPlanCatalog
{
    /**
     * Forfaits actifs disponibles pour une catégorie racine : les forfaits ciblés sur cette
     * racine si il en existe, sinon repli sur les forfaits génériques (provider_category_id null).
     */
    public function plansForRootCategory(ProviderCategory $root): Collection
    {
        $scopedPlans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->where('provider_category_id', $root->id)
            ->orderBy('sort_order')
            ->get();

        return $scopedPlans->isNotEmpty()
            ? $scopedPlans
            : SubscriptionPlan::query()->where('is_active', true)->whereNull('provider_category_id')->orderBy('sort_order')->get();
    }
}
