<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CinetPayService;
use App\Services\PaymentLifecycleService;
use App\Services\SubscriptionService;
use App\Support\PlanBundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // ── ÉTAPE 1 : Initiation AJAX ─────────────────────────────────────────

    public function initiateCynetPayPayment(
        Request $request,
        CinetPayService $cinetPay,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        $data = $request->validate([
            'plan_id'          => ['required', 'integer', 'exists:subscription_plans,id'],
            'billing_cycle'    => ['required', 'in:monthly,yearly'],
            'channel'          => ['required', 'string', 'in:ALL,MOBILE_MONEY,CREDIT_CARD,WALLET'],
            'customer_name'    => ['required', 'string', 'max:100'],
            'customer_surname' => ['required', 'string', 'max:100'],
            'customer_email'   => ['required', 'email', 'max:255'],
            'customer_phone'   => ['required', 'string', 'max:20'],
            // Achat multi-secteurs : secteur du forfait principal et secteurs supplémentaires « slug:idForfait,… ».
            'sector'           => ['nullable', 'string', 'max:80'],
            'extras'           => ['nullable', 'string', 'max:400'],
        ], [
            'plan_id.exists'              => 'Forfait introuvable.',
            'channel.in'                  => 'Canal de paiement invalide.',
            'customer_name.required'      => 'Le prénom est requis.',
            'customer_surname.required'   => 'Le nom est requis.',
            'customer_email.required'     => 'L\'adresse e-mail est requise.',
            'customer_phone.required'     => 'Le numéro de téléphone est requis.',
        ]);

        $provider = Provider::forUserQuery(Auth::id())->firstOrFail();
        $plan     = SubscriptionPlan::findOrFail($data['plan_id']);
        // Plusieurs secteurs en un seul paiement : un abonnement par secteur, montant = somme des forfaits.
        $items    = PlanBundle::items($plan, $data['sector'] ?? null, $data['extras'] ?? null);
        $amount   = PlanBundle::total($items, $data['billing_cycle']);

        // Plan gratuit → abonnement immédiat sans passerelle
        if ($amount <= 0) {
            $main = null;
            foreach ($items as $item) {
                $sub = $subscriptionService->createSubscription(
                    $provider, $item['plan'], $data['billing_cycle'], 'free', 'FREE-' . strtoupper(Str::random(8)), $main, $item['sector']
                );
                $main ??= $sub;
            }

            return response()->json([
                'success'      => true,
                'free'         => true,
                'redirect_url' => route('provider.dashboard'),
                'message'      => 'Votre abonnement gratuit est maintenant actif !',
            ]);
        }

        $result = $cinetPay->initPayment([
            'amount'              => $amount,
            'designation'         => 'Abonnement ' . $items->map(fn ($i) => strtoupper($i['plan']->code).($i['sector'] ? ' '.$i['sector']->slug : ''))->implode(' + ') . ' — ' . config('app.name'),
            'client_first_name'   => $data['customer_name'],
            'client_last_name'    => $data['customer_surname'],
            'client_email'        => $data['customer_email'],
            'client_phone_number' => $data['customer_phone'],
            'success_url'         => route('payment.cynetpay.return'),
            'failed_url'          => route('payment.cynetpay.return'),
            'notify_url'          => route('webhook.cynetpay'),
        ]);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        // On stocke le merchant_transaction_id comme gateway_txn_id pour le retrouver au retour
        $transactionId = $result['merchant_transaction_id'];

        $months = $data['billing_cycle'] === 'yearly' ? 12 : 1;

        // Un abonnement par secteur, en attente : le principal porte le paiement, les autres lui sont reliés
        // et sont activés avec lui une fois le paiement confirmé.
        $subscription = null;
        foreach ($items as $item) {
            $sub = Subscription::create([
                'provider_id'        => $provider->id,
                'plan_id'            => $item['plan']->id,
                'sector_category_id' => $subscriptionService->resolveSector($provider, $item['plan'], $item['sector'])?->id,
                'status'             => 'pending',
                'billing_cycle'      => $data['billing_cycle'],
                'payment_method'     => 'cinetpay',
                'starts_at'          => now(),
                'ends_at'            => now()->addMonths($months),
                'auto_renew'         => true,
                'bundle_parent_id'   => $subscription?->id,
            ]);
            $subscription ??= $sub;
        }

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'provider_id'     => $provider->id,
            'amount'          => $amount,
            'currency'        => 'XOF',
            'method'          => 'cinetpay',
            'gateway'         => 'cinetpay',
            'gateway_txn_id'  => $transactionId,
            'status'          => 'pending',
            'ip_address'      => $request->ip(),
            'metadata'        => [
                'channel'       => $data['channel'],
                'payment_token' => $result['payment_token'] ?? null,
                'notify_token'  => $result['notify_token'] ?? null,
                'plan_code'     => $plan->code,
                'bundle'        => $items->map(fn ($i) => ['plan' => $i['plan']->code, 'sector' => $i['sector']?->slug])->values()->all(),
            ],
        ]);

        session([
            'pending_payment_id'    => $payment->id,
            'pending_plan_id'       => $plan->id,
            'pending_billing_cycle' => $data['billing_cycle'],
        ]);

        return response()->json([
            'success'     => true,
            'payment_url' => $result['payment_url'],
        ]);
    }

    // ── ÉTAPE 2A : Retour navigateur après paiement ───────────────────────

    public function cynetPayReturn(
        Request $request,
        CinetPayService $cinetPay,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        // CinetPay renvoie merchant_transaction_id (ou payment_token) en query string
        $token = (string) ($request->query('merchant_transaction_id')
            ?? $request->query('payment_token')
            ?? $request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $payment = $this->resolvePayment($token);

        if (! $payment) {
            return redirect()->route('provider.billing.plans')
                ->with('error', 'Paiement introuvable. Contactez le support si vous avez été débité.');
        }

        if ($payment->status === 'completed') {
            session()->forget(['pending_payment_id', 'pending_plan_id', 'pending_billing_cycle']);

            return redirect()->route('provider.billing.confirmation', $payment)
                ->with('success', 'Votre abonnement est actif !');
        }

        $statusResult = $cinetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['success']) {
            return $this->processSuccessfulPayment($payment, $payment->gateway_txn_id, $lifecycle, $subscriptionService);
        }

        if (($statusResult['status'] ?? '') === 'PENDING') {
            return redirect()->route('provider.billing.plans')
                ->with('info', 'Votre paiement est en cours de traitement. Vous serez notifié dès confirmation.');
        }

        return $this->handleFailedReturn($payment, $statusResult, $lifecycle);
    }

    // ── ÉTAPE 2B : Webhook CinetPay ───────────────────────────────────────

    public function webhook(
        Request $request,
        CinetPayService $cinetPay,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        // CinetPay envoie merchant_transaction_id = notre gateway_txn_id en base
        $token = (string) ($request->input('merchant_transaction_id')
            ?? $request->input('cpm_trans_id')
            ?? $request->input('transaction_id')
            ?? $request->input('payment_token')
            ?? '');

        if ($token === '') {
            return response()->json(['ok' => false, 'message' => 'token manquant'], 400);
        }

        $payment = Payment::where('gateway_txn_id', $token)->first();

        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'Paiement introuvable'], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json(['ok' => true, 'message' => 'Déjà traité']);
        }

        $statusResult = $cinetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['success']) {
            $this->doProcessSuccessfulPayment($payment, $payment->gateway_txn_id, $lifecycle, $subscriptionService);

            return response()->json(['ok' => true]);
        }

        $lifecycle->markAsFailed($payment, 'Webhook CinetPay : statut ' . ($statusResult['status'] ?? '?'));

        return response()->json(['ok' => false, 'message' => 'Paiement non confirmé']);
    }

    // ── ÉTAPE 2C : Vérification manuelle du statut (polling) ─────────────

    public function checkStatus(
        Payment $payment,
        CinetPayService $cinetPay,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        $providerId = Provider::forUserQuery(Auth::id())->value('id');

        if ((int) $payment->provider_id !== (int) $providerId) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        if ($payment->status === 'completed') {
            return response()->json([
                'status'       => 'completed',
                'redirect_url' => route('provider.billing.confirmation', $payment),
            ]);
        }

        $statusResult = $cinetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['success']) {
            $this->doProcessSuccessfulPayment($payment, $payment->gateway_txn_id, $lifecycle, $subscriptionService);

            return response()->json([
                'status'       => 'completed',
                'redirect_url' => route('provider.billing.confirmation', $payment),
            ]);
        }

        return response()->json([
            'status'  => strtolower($statusResult['status'] ?? 'pending'),
            'message' => $statusResult['message'] ?? '',
        ]);
    }

    // ── Traitement interne ────────────────────────────────────────────────

    private function processSuccessfulPayment(
        Payment $payment,
        string $token,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        $this->doProcessSuccessfulPayment($payment, $token, $lifecycle, $subscriptionService);

        session()->forget(['pending_payment_id', 'pending_plan_id', 'pending_billing_cycle']);

        return redirect()->route('provider.billing.confirmation', $payment->fresh())
            ->with('success', 'Félicitations ! Votre abonnement est maintenant actif.');
    }

    private function doProcessSuccessfulPayment(
        Payment $payment,
        string $token,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): void {
        $lifecycle->markAsCompleted($payment, $token);

        $subscription = $payment->fresh()->subscription;
        if ($subscription) {
            $subscriptionService->activatePendingSubscription($subscription);
        }
    }

    private function handleFailedReturn(
        Payment $payment,
        array $statusResult,
        PaymentLifecycleService $lifecycle
    ): RedirectResponse {
        $lifecycle->markAsFailed($payment, 'Retour CinetPay : statut ' . ($statusResult['status'] ?? '?'));

        return redirect()->route('provider.billing.plans')
            ->with('error', 'Le paiement n\'a pas abouti. Vous pouvez réessayer.');
    }

    private function resolvePayment(string $token): ?Payment
    {
        if ($token !== '') {
            $payment = Payment::where('gateway_txn_id', $token)->first();
            if ($payment) {
                return $payment;
            }
        }

        $paymentId = session('pending_payment_id');
        if ($paymentId) {
            return Payment::find($paymentId);
        }

        return null;
    }
}
