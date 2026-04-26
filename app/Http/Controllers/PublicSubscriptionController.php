<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Provider;
use App\Models\SubscriptionPlan;
use App\Services\CynetPayService;
use App\Services\PaymentLifecycleService;
use App\Services\SubscriptionService;
use App\Support\ProviderProfileBootstrap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicSubscriptionController extends Controller
{
    /**
     * Point d’entrée public : équivalent de GET /abonnements/{plan}/paiement.
     * Invité → inscription prestataire avec plan ; connecté prestataire → page de paiement espace pro.
     */
    public function checkout(SubscriptionPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        if (! Auth::check()) {
            return redirect()
                ->route('register', ['plan' => $plan->id, 'role' => 'provider'])
                ->with('info', 'Créez un compte prestataire pour finaliser votre abonnement.');
        }

        $user = Auth::user();
        if ($user->role !== 'provider') {
            return redirect()
                ->route('plans.public')
                ->with('error', 'Les abonnements prestataire sont réservés aux comptes « Prestataire ».');
        }

        ProviderProfileBootstrap::ensure($user);

        return redirect()->route('provider.billing.checkout', $plan);
    }

    /**
     * Paiement hors passerelle (CynetPay non configuré) : abonnement actif + paiement complété + référence TXN-…
     */
    public function processOffline(
        Request $request,
        SubscriptionPlan $plan,
        CynetPayService $cynetPay,
        SubscriptionService $subscriptionService,
        PaymentLifecycleService $lifecycle
    ): RedirectResponse {
        abort_unless($plan->is_active, 404);

        if ($cynetPay->isConfigured()) {
            return redirect()
                ->route('provider.billing.checkout', $plan)
                ->with('info', 'Utilisez le paiement en ligne depuis cette page.');
        }

        $user = Auth::user();
        if (! $user || $user->role !== 'provider') {
            abort(403);
        }

        $provider = ProviderProfileBootstrap::ensure($user);
        if (! $provider) {
            return redirect()
                ->route('provider.dashboard')
                ->with('error', 'Impossible de créer la fiche prestataire (aucune catégorie active). Contactez le support.');
        }

        $data = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'method' => ['required', 'in:orange_money,mtn_momo,wave,moov_money,card,paypal'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'payment_reference' => ['required', 'string', 'max:120'],
        ], [
            'payment_reference.required' => 'Indiquez votre numéro de transaction ou de paiement.',
        ]);

        $amount = $data['billing_cycle'] === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        $txn = 'TXN-'.now()->timestamp;
        $metadataRef = trim($data['payment_reference']);

        DB::transaction(function () use (
            $provider,
            $plan,
            $data,
            $amount,
            $txn,
            $metadataRef,
            $subscriptionService,
            $lifecycle,
            $request
        ) {
            $subscription = $subscriptionService->createSubscription(
                $provider,
                $plan,
                $data['billing_cycle'],
                $data['method'],
                $txn
            );

            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'provider_id' => $provider->id,
                'amount' => $amount,
                'currency' => 'XOF',
                'method' => $data['method'],
                'gateway' => 'manual',
                'gateway_txn_id' => $txn,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'metadata' => [
                    'mode' => 'offline_simulation',
                    'declared_reference' => $metadataRef,
                ],
            ]);

            $lifecycle->markAsCompleted($payment, $txn);
        });

        return redirect()
            ->route('provider.dashboard')
            ->with('success', 'Votre abonnement est enregistré. Vous pouvez compléter votre fiche prestataire.');
    }
}
