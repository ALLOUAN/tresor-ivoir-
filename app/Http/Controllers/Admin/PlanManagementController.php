<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanManagementController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->withCount(['subscriptions as subscriptions_count'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $promoCodes = Schema::hasTable('promo_codes')
            ? PromoCode::query()
                ->with('plan')
                ->latest()
                ->paginate(10, ['*'], 'promo_page')
            : new LengthAwarePaginator(collect(), 0, 10, 1, [
                'path' => request()->url(),
                'pageName' => 'promo_page',
            ]);

        $subscriptionsByPlan = Subscription::query()
            ->selectRaw('subscription_plans.code as plan_code, count(*) as total')
            ->join('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->groupBy('subscription_plans.code')
            ->pluck('total', 'plan_code');

        return view('admin.finance.plans', compact('plans', 'promoCodes', 'subscriptionsByPlan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePlan($request);
        SubscriptionPlan::create($data);

        return back()->with('success', 'Forfait créé avec succès.');
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $this->validatePlan($request, $plan->id);
        $plan->update($data);

        return back()->with('success', 'Forfait mis à jour.');
    }

    public function toggle(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('success', 'Statut du forfait mis à jour.');
    }

    public function storePromo(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('promo_codes')) {
            return back()->with('error', 'La table promo_codes est absente. Lancez php artisan migrate.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:promo_codes,code'],
            'plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        PromoCode::create($validated);

        return back()->with('success', 'Code promo créé.');
    }

    public function togglePromo(PromoCode $promo): RedirectResponse
    {
        if (! Schema::hasTable('promo_codes')) {
            return back()->with('error', 'La table promo_codes est absente. Lancez php artisan migrate.');
        }

        $promo->update(['is_active' => ! $promo->is_active]);

        return back()->with('success', 'Statut du code promo mis à jour.');
    }

    private function validatePlan(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', Rule::in(['bronze', 'silver', 'gold']), Rule::unique('subscription_plans', 'code')->ignore($ignoreId)],
            'name_fr' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'benefits_text' => ['nullable', 'string'],
            'covered_levels' => ['nullable', 'string', 'max:255'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_quarterly' => ['nullable', 'numeric', 'min:0'],
            'price_semiannual' => ['nullable', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'photos_limit' => ['required', 'integer', 'min:0'],
            'description_chars' => ['required', 'integer', 'min:0'],
            'min_duration_months' => ['required', 'integer', Rule::in([1, 3, 6, 12])],
            'stats_level' => ['required', Rule::in(['basic', 'advanced', 'full'])],
            'support_level' => ['required', Rule::in(['email', 'chat', 'dedicated'])],
            'group_target' => ['nullable', 'string', 'max:120'],
            'promo_starts_at' => ['nullable', 'date'],
            'promo_ends_at' => ['nullable', 'date', 'after_or_equal:promo_starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_promotional' => ['nullable', 'boolean'],
            'is_unlimited_features' => ['nullable', 'boolean'],
            'has_video' => ['nullable', 'boolean'],
            'has_newsletter' => ['nullable', 'boolean'],
            'has_homepage' => ['nullable', 'boolean'],
            'has_social_posts' => ['nullable', 'boolean'],
            'has_verified_badge' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'is_promotional' => $request->boolean('is_promotional'),
            'is_unlimited_features' => $request->boolean('is_unlimited_features'),
            'has_video' => $request->boolean('has_video'),
            'has_newsletter' => $request->boolean('has_newsletter'),
            'has_homepage' => $request->boolean('has_homepage'),
            'has_social_posts' => $request->boolean('has_social_posts'),
            'has_verified_badge' => $request->boolean('has_verified_badge'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
