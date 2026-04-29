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
use Illuminate\View\View;

class PublicSubscriptionController extends Controller
{
    /**
     * Point d’entrée public : équivalent de GET /abonnements/{plan}/paiement.
     * Invité → inscription prestataire avec plan ; connecté prestataire → page de paiement espace pro.
     */
    public function checkout(SubscriptionPlan $plan, CynetPayService $cynetPay): View|RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        if (! Auth::check()) {
            return redirect()
                ->route('register', ['plan' => $plan->id, 'role' => 'provider'])
                ->with('info', 'Créez un compte prestataire pour finaliser votre abonnement.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== 'provider') {
            return redirect()
                ->route('plans.public')
                ->with('error', 'Les abonnements prestataire sont réservés aux comptes « Prestataire ».');
        }

        if (! $user->hasVerifiedEmail()) {
            session([
                'url.intended' => route('subscriptions.checkout', $plan),
                'post_email_verification_subscription_plan_id' => $plan->id,
            ]);

            return redirect()
                ->route('verification.notice')
                ->with('status', 'Validez votre e-mail pour finaliser l\'abonnement.');
        }

        $provider = ProviderProfileBootstrap::ensure($user);
        if (! $provider) {
            return redirect()
                ->route('plans.public')
                ->with('error', 'Impossible de créer la fiche prestataire (aucune catégorie active). Contactez le support.');
        }

        $cynetPayConfigured = $cynetPay->isConfigured();
        $channels = $cynetPay->getAvailableChannels();

        return view('billing.checkout', compact('plan', 'provider', 'cynetPayConfigured', 'channels'));
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
                ->route('subscriptions.checkout', $plan)
                ->with('info', 'Utilisez le paiement en ligne depuis cette page.');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || $user->role !== 'provider') {
            abort(403);
        }

        $provider = ProviderProfileBootstrap::ensure($user);
        if (! $provider) {
            return redirect()
                ->route('plans.public')
                ->with('error', 'Impossible de créer la fiche prestataire (aucune catégorie active). Contactez le support.');
        }

        $data = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'method' => ['required', 'in:orange_money,mtn_momo,wave,moov_money,card,paypal'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'payment_reference' => ['required', 'string', 'max:120'],
            'accept_cgu' => ['accepted'],
            'billing_phone' => ['nullable', 'string', 'max:30'],
            'billing_address' => ['nullable', 'string', 'max:200'],
            'billing_city' => ['nullable', 'string', 'max:100'],
        ], [
            'payment_reference.required' => 'Indiquez votre numéro de transaction ou de paiement.',
            'accept_cgu.accepted' => 'Vous devez accepter les conditions générales et la politique de confidentialité.',
        ]);

        $amount = $data['billing_cycle'] === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        $phone = isset($data['billing_phone']) ? trim((string) $data['billing_phone']) : null;
        if ($phone !== null && $phone !== '') {
            $digits = preg_replace('/\D+/', '', $phone);
            if ($digits !== '' && ! str_starts_with($digits, '225')) {
                $phone = '+225'.$digits;
            } elseif ($digits !== '' && str_starts_with($digits, '225')) {
                $phone = '+'.$digits;
            }
        }

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $phone ?: $user->phone,
        ]);

        $provider->fill(array_filter([
            'address' => $data['billing_address'] ?? null,
            'city' => $data['billing_city'] ?? null,
            'phone' => $phone ?: $provider->phone,
        ], fn ($v) => $v !== null && $v !== ''));
        $provider->save();

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
            $request,
            $phone
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
                    'billing_phone' => $phone,
                    'billing_address' => $data['billing_address'] ?? null,
                    'billing_city' => $data['billing_city'] ?? null,
                ],
            ]);

            $lifecycle->markAsCompleted($payment, $txn);
        });

        return redirect()
            ->route('home')
            ->with('success', 'Votre abonnement est enregistré. Bienvenue sur Trésors d\'Ivoire !');
    }
}
