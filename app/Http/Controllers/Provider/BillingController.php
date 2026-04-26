<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CynetPayService;
use App\Services\PaymentGatewayService;
use App\Services\PaymentLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function plans(): View
    {
        $provider = Provider::where('user_id', Auth::id())->first();

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $currentSubscription = null;
        $daysRemaining       = null;

        if ($provider) {
            $currentSubscription = Subscription::query()
                ->with('plan')
                ->where('provider_id', $provider->id)
                ->where('status', 'active')
                ->where('ends_at', '>=', now())
                ->latest('ends_at')
                ->first();

            if ($currentSubscription?->ends_at) {
                $daysRemaining = max(0, now()->startOfDay()->diffInDays(
                    $currentSubscription->ends_at->copy()->startOfDay(), false
                ));
            }
        }

        return view('billing.plans', compact('plans', 'currentSubscription', 'daysRemaining'));
    }

    public function checkout(SubscriptionPlan $plan, CynetPayService $cynetPay): View|RedirectResponse
    {
        $provider = Provider::where('user_id', Auth::id())->first();

        if (! $provider) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Créez d\'abord votre fiche prestataire.');
        }

        $cynetPayConfigured = $cynetPay->isConfigured();
        $channels           = $cynetPay->getAvailableChannels();

        return view('billing.checkout', compact('plan', 'provider', 'cynetPayConfigured', 'channels'));
    }

    public function initiate(Request $request, SubscriptionPlan $plan, PaymentGatewayService $gatewayService): RedirectResponse
    {
        $provider = Provider::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'gateway'       => ['required', 'in:cinetpay,stripe,paypal'],
            'method'        => ['required', 'in:orange_money,mtn_momo,wave,moov_money,card,paypal'],
            'promo_code'    => ['nullable', 'string', 'max:40'],
        ]);

        $amount = $validated['billing_cycle'] === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        // Apply promo code if provided
        $appliedPromo = null;
        if (! empty($validated['promo_code'])) {
            $promo = PromoCode::where('code', strtoupper(trim($validated['promo_code'])))->first();
            if ($promo && $promo->isValid($plan->id)) {
                $amount       = $promo->applyDiscount($amount);
                $appliedPromo = $promo;
            }
        }

        $durationMonths = $validated['billing_cycle'] === 'yearly' ? 12 : 1;

        // Track which subscription this upgrades/replaces
        $previousSubscription = Subscription::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->latest('ends_at')
            ->first();

        $subscription = Subscription::create([
            'provider_id'       => $provider->id,
            'plan_id'           => $plan->id,
            'status'            => 'pending',
            'billing_cycle'     => $validated['billing_cycle'],
            'payment_method'    => $validated['method'],
            'starts_at'         => now(),
            'ends_at'           => now()->addMonths($durationMonths),
            'auto_renew'        => true,
            'upgraded_from_id'  => $previousSubscription?->id,
        ]);

        $callbackUrl = route('provider.billing.callback');
        $result = $gatewayService->initiate(
            $validated['gateway'],
            $provider,
            $subscription,
            $amount,
            $validated['method'],
            $callbackUrl
        );

        if ($appliedPromo) {
            $appliedPromo->incrementUsage();
        }

        return redirect()->away($result['payment_url']);
    }

    public function callback(Request $request, PaymentLifecycleService $lifecycleService): RedirectResponse
    {
        $paymentUuid = (string) $request->query('payment_uuid', '');
        $status = (string) $request->query('status', 'failed');
        $txn = (string) $request->query('txn', '');

        $payment = Payment::where('uuid', $paymentUuid)->first();

        if (! $payment) {
            return redirect()->route('provider.billing.plans')->with('error', 'Paiement introuvable.');
        }

        if ($status === 'success') {
            $lifecycleService->markAsCompleted($payment, $txn ?: null);

            // Cancel any previously active subscription for this provider
            $newSub = $payment->subscription;
            if ($newSub) {
                Subscription::where('provider_id', $newSub->provider_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $newSub->id)
                    ->update([
                        'status'              => 'cancelled',
                        'cancelled_at'        => now(),
                        'cancellation_reason' => 'Remplacé par forfait ' . strtoupper($newSub->plan?->code ?? ''),
                    ]);
            }

            return redirect()->route('provider.billing.confirmation', ['payment' => $payment->id]);
        }

        $lifecycleService->markAsFailed($payment, 'Échec signalé par callback');

        return redirect()->route('provider.billing.plans')->with('error', 'Le paiement a échoué.');
    }

    public function webhook(Request $request, string $gateway, PaymentLifecycleService $lifecycleService)
    {
        $paymentUuid = (string) ($request->input('payment_uuid') ?? $request->input('transaction_id') ?? '');
        $status = (string) ($request->input('status') ?? '');
        $txnId = (string) ($request->input('gateway_txn_id') ?? $request->input('transaction_id') ?? '');

        $payment = Payment::where('uuid', $paymentUuid)->first();

        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'payment_not_found'], 404);
        }

        if (in_array(strtolower($status), ['success', 'completed', 'paid'], true)) {
            $lifecycleService->markAsCompleted($payment, $txnId ?: null);
        } else {
            $lifecycleService->markAsFailed($payment, "Webhook {$gateway}: {$status}");
        }

        return response()->json(['ok' => true]);
    }

    public function confirmation(Payment $payment): View
    {
        $payment->load(['subscription.plan', 'invoice', 'provider']);

        return view('billing.confirmation', compact('payment'));
    }

    public function validatePromo(Request $request): JsonResponse
    {
        $request->validate([
            'code'          => ['required', 'string', 'max:40'],
            'plan_id'       => ['required', 'integer'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $promo = PromoCode::where('code', strtoupper(trim($request->code)))->first();

        if (! $promo) {
            return response()->json(['valid' => false, 'message' => 'Code promo invalide.']);
        }

        if (! $promo->isValid((int) $request->plan_id)) {
            if ($promo->ends_at?->isPast()) {
                return response()->json(['valid' => false, 'message' => 'Ce code a expiré.']);
            }
            if ($promo->max_uses !== null && $promo->used_count >= $promo->max_uses) {
                return response()->json(['valid' => false, 'message' => 'Ce code a atteint sa limite d\'utilisation.']);
            }
            if ($promo->plan_id && $promo->plan_id !== (int) $request->plan_id) {
                return response()->json(['valid' => false, 'message' => 'Ce code ne s\'applique pas à ce forfait.']);
            }

            return response()->json(['valid' => false, 'message' => 'Code promo invalide ou inactif.']);
        }

        $plan       = SubscriptionPlan::findOrFail($request->plan_id);
        $baseAmount = $request->billing_cycle === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        $discountAmount = $promo->discountAmount($baseAmount);
        $finalAmount    = $promo->applyDiscount($baseAmount);

        return response()->json([
            'valid'          => true,
            'message'        => $promo->description ?: 'Code promo appliqué avec succès !',
            'discount_type'  => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
            'discount_amount' => $discountAmount,
            'final_amount'   => $finalAmount,
            'base_amount'    => $baseAmount,
        ]);
    }

    public function invoices(): View|RedirectResponse
    {
        $provider = Provider::where('user_id', Auth::id())->first();

        if (! $provider) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Créez d\'abord votre fiche prestataire.');
        }

        $invoices = Invoice::query()
            ->where('provider_id', $provider->id)
            ->with('payment')
            ->latest('issued_at')
            ->paginate(15);

        return view('billing.invoices', compact('invoices'));
    }
}
