<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FinanceManagementController extends Controller
{
    public function payments(): View
    {
        $search = trim((string) request('q', ''));
        $status = (string) request('status', '');
        $method = (string) request('method', '');
        $from = (string) request('from', '');
        $to = (string) request('to', '');
        $minAmount = (string) request('min_amount', '');
        $maxAmount = (string) request('max_amount', '');

        $baseQuery = Payment::query()
            ->with(['provider', 'subscription.plan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('gateway_txn_id', 'like', "%{$search}%")
                        ->orWhereHas('provider', function ($providerQuery) use ($search) {
                            $providerQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($method !== '', fn ($query) => $query->where('method', $method))
            ->when($from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->when($minAmount !== '', fn ($query) => $query->where('amount', '>=', (float) $minAmount))
            ->when($maxAmount !== '', fn ($query) => $query->where('amount', '<=', (float) $maxAmount));

        $payments = (clone $baseQuery)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $totalCount = (clone $baseQuery)->count();
        $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
        $failedCount = (clone $baseQuery)->where('status', 'failed')->count();
        $successRate = ($completedCount + $failedCount) > 0
            ? round(($completedCount / ($completedCount + $failedCount)) * 100, 2)
            : 0;

        $revenueByPlan = (clone $baseQuery)
            ->where('payments.status', 'completed')
            ->join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
            ->join('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->selectRaw('subscription_plans.code as plan_code, SUM(payments.amount) as total')
            ->groupBy('subscription_plans.code')
            ->pluck('total', 'plan_code');

        $stats = [
            'total' => $totalCount,
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'completed' => $completedCount,
            'failed' => $failedCount,
            'refunded' => (clone $baseQuery)->where('status', 'refunded')->count(),
            'revenue' => (clone $baseQuery)->where('status', 'completed')->sum('amount'),
            'success_rate' => $successRate,
            'revenue_by_plan' => $revenueByPlan,
        ];

        return view('admin.finance.payments', compact(
            'payments',
            'search',
            'status',
            'method',
            'from',
            'to',
            'minAmount',
            'maxAmount',
            'stats'
        ));
    }

    public function subscriptions(): View
    {
        $search = trim((string) request('q', ''));
        $status = (string) request('status', '');
        $cycle = (string) request('billing_cycle', '');
        $expiringSoonOnly = request()->boolean('expiring_soon');

        $subscriptions = Subscription::query()
            ->with(['provider', 'plan', 'lastEditedBy'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('provider', fn ($providerQuery) => $providerQuery->where('name', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($cycle !== '', fn ($query) => $query->where('billing_cycle', $cycle))
            ->when($expiringSoonOnly, function ($query) {
                $query->where('status', 'active')
                    ->whereBetween('ends_at', [now(), now()->addDays(30)]);
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'renewing' => Subscription::where('auto_renew', true)->count(),
            'expiring_soon' => Subscription::where('status', 'active')
                ->whereBetween('ends_at', [now(), now()->addDays(30)])
                ->count(),
        ];

        $providers = Provider::query()->orderBy('name')->get(['id', 'name']);
        $plans = SubscriptionPlan::query()->orderBy('sort_order')->orderBy('id')->get(['id', 'code', 'name_fr']);
        $expiringSoonSubscriptions = Subscription::query()
            ->with(['provider', 'plan'])
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays(30)])
            ->orderBy('ends_at')
            ->take(10)
            ->get();

        return view('admin.finance.subscriptions', compact(
            'subscriptions',
            'search',
            'status',
            'cycle',
            'expiringSoonOnly',
            'stats',
            'providers',
            'plans',
            'expiringSoonSubscriptions'
        ));
    }

    public function storeSubscription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'status' => ['required', 'in:active,suspended,cancelled,expired'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:orange_money,mtn_momo,wave,moov_money,card,paypal'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'auto_renew' => ['nullable', 'boolean'],
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        Subscription::create([
            'provider_id' => $validated['provider_id'],
            'plan_id' => $validated['plan_id'],
            'status' => $validated['status'],
            'billing_cycle' => $validated['billing_cycle'],
            'payment_method' => $validated['payment_method'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'auto_renew' => $request->boolean('auto_renew'),
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : null,
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        return back()->with('success', 'Nouvel abonnement créé avec succès.');
    }

    public function updateSubscription(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'status' => ['required', 'in:active,suspended,cancelled,expired'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:orange_money,mtn_momo,wave,moov_money,card,paypal'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'auto_renew' => ['nullable', 'boolean'],
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        $subscription->update([
            'provider_id' => $validated['provider_id'],
            'plan_id' => $validated['plan_id'],
            'status' => $validated['status'],
            'billing_cycle' => $validated['billing_cycle'],
            'payment_method' => $validated['payment_method'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'auto_renew' => $request->boolean('auto_renew'),
            'cancelled_at' => $validated['status'] === 'cancelled' ? ($subscription->cancelled_at ?? now()) : null,
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            'last_edited_by_user_id' => Auth::id(),
            'last_edited_at' => now(),
        ]);

        return back()->with('success', 'Abonnement modifié avec succès.');
    }

    public function extendSubscription(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'extend_by_months' => ['required', 'integer', 'in:1,3,6,12'],
        ]);

        $baseDate = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at->copy()
            : now();

        $subscription->update([
            'ends_at' => $baseDate->addMonths((int) $validated['extend_by_months']),
            'status' => 'active',
            'last_edited_by_user_id' => Auth::id(),
            'last_edited_at' => now(),
        ]);

        return back()->with('success', 'Abonnement prolongé avec succès.');
    }

    public function paymentShow(Payment $payment): View
    {
        $payment->load(['provider.user', 'subscription.plan', 'invoice']);

        return view('admin.finance.payment-show', compact('payment'));
    }

    public function settings(): View
    {
        $settings = Schema::hasTable('payment_settings')
            ? PaymentSetting::query()->pluck('value', 'key')
            : collect();

        return view('admin.finance.settings', compact('settings'));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('payment_settings')) {
            return back()->with('error', 'La table payment_settings est absente. Lancez php artisan migrate.');
        }

        $data = $request->validate([
            'currency' => ['nullable', 'string', 'max:10'],
            'transaction_fee_percent' => ['nullable', 'numeric', 'min:0'],
            'auto_invoice_enabled' => ['nullable', 'boolean'],
            'method_orange_money' => ['nullable', 'boolean'],
            'method_mtn_momo' => ['nullable', 'boolean'],
            'method_wave' => ['nullable', 'boolean'],
            'method_moov_money' => ['nullable', 'boolean'],
            'method_card' => ['nullable', 'boolean'],
            'method_paypal' => ['nullable', 'boolean'],
            'gateway_orange_key' => ['nullable', 'string'],
            'gateway_mtn_key' => ['nullable', 'string'],
            'gateway_wave_key' => ['nullable', 'string'],
            'gateway_paypal_key' => ['nullable', 'string'],
        ]);

        $booleanKeys = [
            'auto_invoice_enabled',
            'method_orange_money',
            'method_mtn_momo',
            'method_wave',
            'method_moov_money',
            'method_card',
            'method_paypal',
        ];

        foreach ($booleanKeys as $key) {
            $data[$key] = $request->boolean($key) ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            PaymentSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Configuration des paiements enregistrée.');
    }
}
