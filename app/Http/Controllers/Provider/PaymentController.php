<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CynetPayService;
use App\Services\PaymentLifecycleService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // ── ÉTAPE 1 : Initiation AJAX (CynetPay configuré) ───────────────────

    public function initiateCynetPayPayment(
        Request $request,
        CynetPayService $cynetPay,
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
        ], [
            'plan_id.exists'          => 'Forfait introuvable.',
            'channel.in'              => 'Canal de paiement invalide.',
            'customer_name.required'  => 'Le prénom est requis.',
            'customer_surname.required' => 'Le nom est requis.',
            'customer_email.required' => 'L\'adresse e-mail est requise.',
            'customer_phone.required' => 'Le numéro de téléphone est requis.',
        ]);

        $provider = Provider::where('user_id', Auth::id())->firstOrFail();
        $plan     = SubscriptionPlan::findOrFail($data['plan_id']);
        $amount   = $data['billing_cycle'] === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        // Plan gratuit → abonnement immédiat sans passerelle
        if ($amount <= 0) {
            $subscriptionService->createSubscription(
                $provider, $plan, $data['billing_cycle'], 'free', 'FREE-' . strtoupper(Str::random(8))
            );

            return response()->json([
                'success'      => true,
                'free'         => true,
                'redirect_url' => route('provider.dashboard'),
                'message'      => 'Votre abonnement gratuit est maintenant actif !',
            ]);
        }

        // Construction d'un identifiant de transaction unique
        $transactionId = strtoupper($plan->code) . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));

        $result = $cynetPay->initiatePayment([
            'transaction_id'  => $transactionId,
            'amount'          => $amount,
            'currency'        => 'XOF',
            'description'     => 'Abonnement ' . strtoupper($plan->code) . ' — ' . config('app.name'),
            'return_url'      => route('payment.cynetpay.return'),
            'notify_url'      => route('webhook.cynetpay'),
            'channel'         => $data['channel'],
            'customer_name'   => $data['customer_name'],
            'customer_surname' => $data['customer_surname'],
            'customer_email'  => $data['customer_email'],
            'customer_phone'  => $data['customer_phone'],
        ]);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        // Création de la souscription en statut "pending"
        $months = $data['billing_cycle'] === 'yearly' ? 12 : 1;

        $subscription = Subscription::create([
            'provider_id'    => $provider->id,
            'plan_id'        => $plan->id,
            'status'         => 'pending',
            'billing_cycle'  => $data['billing_cycle'],
            'payment_method' => 'cynetpay',
            'starts_at'      => now(),
            'ends_at'        => now()->addMonths($months),
            'auto_renew'     => true,
        ]);

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'provider_id'     => $provider->id,
            'amount'          => $amount,
            'currency'        => 'XOF',
            'method'          => 'cynetpay',
            'gateway'         => 'cynetpay',
            'gateway_txn_id'  => $transactionId,
            'status'          => 'pending',
            'ip_address'      => $request->ip(),
            'metadata'        => [
                'channel'           => $data['channel'],
                'subscription_uuid' => $subscription->uuid,
                'provider_uuid'     => $provider->uuid,
                'plan_code'         => $plan->code,
            ],
        ]);

        // Stockage en session pour le retour
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

    // ── ÉTAPE 2A : Retour navigateur après paiement CynetPay ─────────────

    public function cynetPayReturn(
        Request $request,
        CynetPayService $cynetPay,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        // CinetPay renvoie cpm_trans_id ou transaction_id en query string
        $transactionId = (string) ($request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $payment = $this->resolvePayment($transactionId);

        if (! $payment) {
            return redirect()->route('provider.billing.plans')
                ->with('error', 'Paiement introuvable. Contactez le support si vous avez été débité.');
        }

        // Déjà traité (idempotence)
        if ($payment->status === 'completed') {
            session()->forget(['pending_payment_id', 'pending_plan_id', 'pending_billing_cycle']);

            return redirect()->route('provider.billing.confirmation', $payment)
                ->with('success', 'Votre abonnement est actif !');
        }

        // Vérification du statut auprès de CinetPay
        $statusResult = $cynetPay->checkPaymentStatus($payment->gateway_txn_id);

        return match ($statusResult['status']) {
            'PAYMENT_SUCCESS' => $this->processSuccessfulPayment($payment, $payment->gateway_txn_id, $lifecycle, $subscriptionService),
            'PAYMENT_PENDING' => redirect()->route('provider.billing.plans')
                ->with('info', 'Votre paiement est en cours de traitement. Vous serez notifié dès confirmation.'),
            default => $this->handleFailedReturn($payment, $statusResult, $lifecycle),
        };
    }

    // ── ÉTAPE 2B : Webhook CinetPay (notification serveur automatique) ───

    public function webhook(
        Request $request,
        CynetPayService $cynetPay,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        // CinetPay envoie cpm_trans_id dans le corps du webhook
        $transactionId = (string) ($request->input('cpm_trans_id')
            ?? $request->input('transaction_id')
            ?? '');

        if ($transactionId === '') {
            return response()->json(['ok' => false, 'message' => 'transaction_id manquant'], 400);
        }

        $payment = Payment::where('gateway_txn_id', $transactionId)->first();

        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'Paiement introuvable'], 404);
        }

        // Idempotence
        if ($payment->status === 'completed') {
            return response()->json(['ok' => true, 'message' => 'Déjà traité']);
        }

        // Vérification officielle du statut
        $statusResult = $cynetPay->checkPaymentStatus($transactionId);

        if ($statusResult['status'] === 'PAYMENT_SUCCESS') {
            $this->doProcessSuccessfulPayment($payment, $transactionId, $lifecycle, $subscriptionService);

            return response()->json(['ok' => true]);
        }

        $lifecycle->markAsFailed($payment, 'Webhook CinetPay : code ' . ($statusResult['code'] ?? '?'));

        return response()->json(['ok' => false, 'message' => 'Paiement non confirmé']);
    }

    // ── ÉTAPE 2C : Vérification manuelle du statut (polling) ─────────────

    public function checkStatus(
        Request $request,
        Payment $payment,
        CynetPayService $cynetPay,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): JsonResponse {
        // Vérifier que le paiement appartient bien au provider connecté
        $providerId = Provider::where('user_id', Auth::id())->value('id');

        if ((int) $payment->provider_id !== (int) $providerId) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        if ($payment->status === 'completed') {
            return response()->json([
                'status'       => 'completed',
                'redirect_url' => route('provider.billing.confirmation', $payment),
            ]);
        }

        $statusResult = $cynetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['status'] === 'PAYMENT_SUCCESS') {
            $this->doProcessSuccessfulPayment($payment, $payment->gateway_txn_id, $lifecycle, $subscriptionService);

            return response()->json([
                'status'       => 'completed',
                'redirect_url' => route('provider.billing.confirmation', $payment),
            ]);
        }

        return response()->json([
            'status'  => strtolower($statusResult['status']),
            'message' => $statusResult['message'] ?? '',
        ]);
    }

    // ── Traitement interne : paiement confirmé ────────────────────────────

    /**
     * Utilisé par cynetPayReturn() — retourne une RedirectResponse.
     */
    private function processSuccessfulPayment(
        Payment $payment,
        string $transactionId,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        $this->doProcessSuccessfulPayment($payment, $transactionId, $lifecycle, $subscriptionService);

        session()->forget(['pending_payment_id', 'pending_plan_id', 'pending_billing_cycle']);

        return redirect()->route('provider.billing.confirmation', $payment->fresh())
            ->with('success', 'Félicitations ! Votre abonnement est maintenant actif.');
    }

    /**
     * Logique de traitement partagée entre webhook et retour navigateur.
     */
    private function doProcessSuccessfulPayment(
        Payment $payment,
        string $transactionId,
        PaymentLifecycleService $lifecycle,
        SubscriptionService $subscriptionService
    ): void {
        // Marquer le paiement comme complété + créer facture + envoyer mail
        $lifecycle->markAsCompleted($payment, $transactionId);

        // Activer la souscription associée (et annuler les anciennes)
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
        $lifecycle->markAsFailed($payment, 'Retour CinetPay : code ' . ($statusResult['code'] ?? '?'));

        return redirect()->route('provider.billing.plans')
            ->with('error', 'Le paiement n\'a pas abouti (code ' . ($statusResult['code'] ?? '?') . '). Vous pouvez réessayer.');
    }

    private function resolvePayment(string $transactionId): ?Payment
    {
        // D'abord via transaction_id (retour CinetPay)
        if ($transactionId !== '') {
            $payment = Payment::where('gateway_txn_id', $transactionId)->first();
            if ($payment) {
                return $payment;
            }
        }

        // Fallback : via la session
        $paymentId = session('pending_payment_id');
        if ($paymentId) {
            return Payment::find($paymentId);
        }

        return null;
    }
}
