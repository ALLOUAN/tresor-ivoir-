<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\SubscriptionPlan;
use App\Services\CinetPayService;
use App\Services\PaymentLifecycleService;
use App\Services\SubscriptionPlanCatalog;
use App\Services\SubscriptionService;
use App\Support\PlanBundle;
use App\Support\ProviderProfileBootstrap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicSubscriptionController extends Controller
{
    /**
     * Page publique « Abonnements » : parcours catégorie d'abord — une carte par catégorie
     * racine active, révélant ses forfaits (ciblés, ou génériques à défaut) une fois choisie.
     */
    public function index(Request $request, SubscriptionPlanCatalog $catalog): View
    {
        $rootCategories = ProviderCategory::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $plansByCategory = $rootCategories->mapWithKeys(
            fn (ProviderCategory $category) => [$category->slug => $catalog->plansForRootCategory($category)]
        );

        $cycleSettings = PaymentSetting::query()->pluck('value', 'key');
        $showMonthly = ($cycleSettings['cycle_monthly_active'] ?? '1') === '1';
        $showYearly  = ($cycleSettings['cycle_yearly_active']  ?? '1') === '1';
        $yearlySavingsLabel = $cycleSettings['cycle_yearly_savings_label'] ?? '-20%';

        // Sans catégorie choisie dans l'URL, le premier secteur est activé par défaut
        // (au lieu de laisser les forfaits masqués tant que le visiteur ne clique pas).
        $selectedCategorySlug = (string) $request->query('categorie', $rootCategories->first()?->slug ?? '');

        // Plusieurs secteurs (?secteurs=hotels,restaurants) : un forfait par secteur, total calculé sur la page.
        $validSlugs = $rootCategories->pluck('slug')->all();
        $selectedSlugs = array_values(array_intersect(
            array_unique(array_filter(explode(',', (string) $request->query('secteurs', $selectedCategorySlug)))),
            $validSlugs
        ));

        // Prestataire connecté : ses secteurs actuels sont signalés (« Votre secteur »).
        $user = Auth::user();
        $ownedSectors = $user?->role === 'provider'
            ? (\App\Models\Provider::forUser($user->id)?->sectorSlugs() ?? [])
            : [];

        return view('public.plans', compact(
            'rootCategories', 'plansByCategory', 'showMonthly', 'showYearly',
            'yearlySavingsLabel', 'selectedCategorySlug', 'selectedSlugs', 'ownedSectors'
        ));
    }

    /**
     * Point d’entrée public : équivalent de GET /abonnements/{plan}/paiement.
     * Invité → inscription prestataire avec plan ; connecté prestataire → page de paiement espace pro.
     */
    public function checkout(Request $request, SubscriptionPlan $plan, CinetPayService $cinetPay): View|RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        // Secteurs supplémentaires achetés avec ce forfait (?avec=slug:id,…), un forfait par secteur.
        $avec = PlanBundle::encode(PlanBundle::extras($plan, $request->query('categorie'), $request->query('avec')));

        if (! Auth::check()) {
            return redirect()
                ->route('register', array_filter([
                    'plan' => $plan->id,
                    'avec' => $avec ?: null,
                    'role' => 'provider',
                    'categorie' => $request->query('categorie'),
                ]))
                ->with('info', __('Créez un compte prestataire pour finaliser votre abonnement.'));
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== 'provider') {
            return redirect()
                ->route('plans.public')
                ->with('error', __('Les abonnements prestataire sont réservés aux comptes « Prestataire ».'));
        }

        if (! $user->hasVerifiedEmail()) {
            session([
                'url.intended' => route('subscriptions.checkout', array_filter(['plan' => $plan, 'categorie' => $request->query('categorie'), 'avec' => $avec ?: null])),
                'post_email_verification_subscription_plan_id' => $plan->id,
                'post_email_verification_subscription_extra_ids' => $avec,
            ]);

            return redirect()
                ->route('verification.notice')
                ->with('status', __('Validez votre e-mail pour finaliser l\'abonnement.'));
        }

        $categorySlug = $plan->providerCategory?->slug ?? $request->query('categorie');
        $provider = ProviderProfileBootstrap::ensure($user, $categorySlug);
        if (! $provider) {
            return redirect()
                ->route('plans.public')
                ->with('error', __('Impossible de créer la fiche prestataire (aucune catégorie active). Contactez le support.'));
        }

        $cinetPayConfigured = $cinetPay->isConfigured();
        $channels = $cinetPay->getAvailableChannels();
        // Tous les secteurs de l'achat (forfait principal d'abord) : récapitulatif et montant total.
        $bundleItems = PlanBundle::items($plan, $request->query('categorie'), $request->query('avec'));
        $bundleSector = $bundleItems->first()['sector']?->slug;

        return view('billing.checkout', compact('plan', 'provider', 'cinetPayConfigured', 'channels', 'bundleItems', 'bundleSector', 'avec'));
    }

    /**
     * Paiement hors passerelle (CynetPay non configuré) : abonnement actif + paiement complété + référence TXN-…
     */
    public function processOffline(
        Request $request,
        SubscriptionPlan $plan,
        CinetPayService $cinetPay,
        SubscriptionService $subscriptionService,
        PaymentLifecycleService $lifecycle
    ): RedirectResponse {
        abort_unless($plan->is_active, 404);

        if ($cinetPay->isConfigured()) {
            return redirect()
                ->route('subscriptions.checkout', $plan)
                ->with('info', __('Utilisez le paiement en ligne depuis cette page.'));
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || $user->role !== 'provider') {
            abort(403);
        }

        $categorySlug = $plan->providerCategory?->slug ?? ($request->input('sector') ?: $request->query('categorie'));
        $provider = ProviderProfileBootstrap::ensure($user, $categorySlug);
        if (! $provider) {
            return redirect()
                ->route('plans.public')
                ->with('error', __('Impossible de créer la fiche prestataire (aucune catégorie active). Contactez le support.'));
        }

        $data = $request->validate([
            'sector' => ['nullable', 'string', 'max:80'],
            'avec' => ['nullable', 'string', 'max:400'],
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

        $items = PlanBundle::items($plan, $data['sector'] ?? $request->query('categorie'), $data['avec'] ?? null);
        $amount = PlanBundle::total($items, $data['billing_cycle']);

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
            $phone,
            $items
        ) {
            // Un abonnement par secteur ; le premier porte le paiement, les autres lui sont reliés.
            $subscription = null;
            foreach ($items as $item) {
                $sub = $subscriptionService->createSubscription(
                    $provider,
                    $item['plan'],
                    $data['billing_cycle'],
                    $data['method'],
                    $txn,
                    $subscription,
                    $item['sector']
                );
                $subscription ??= $sub;
            }

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
                    'bundle' => $items->map(fn ($i) => ['plan' => $i['plan']->code, 'sector' => $i['sector']?->slug])->values()->all(),
                ],
            ]);

            $lifecycle->markAsCompleted($payment, $txn);
        });

        return redirect()
            ->route('home')
            ->with('success', __('Votre abonnement est enregistré. Bienvenue sur Trésors d\'Ivoire !'));
    }
}
