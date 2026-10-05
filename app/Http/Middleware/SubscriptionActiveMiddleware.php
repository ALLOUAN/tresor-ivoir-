<?php

namespace App\Http\Middleware;

use App\Models\Provider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionActiveMiddleware
{
    /**
     * $rootCategorySlug optionnel (ex. 'subscription.active:art-creations') : quand fourni,
     * exige en plus que le forfait de l'abonnement actif cible spécifiquement cette catégorie
     * racine — pas seulement « un abonnement actif » quelconque. Sans paramètre, comportement
     * strictement inchangé (tout abonnement actif suffit), pour ne rien casser sur les usages
     * existants ailleurs (ex. provider.premium-content).
     */
    public function handle(Request $request, Closure $next, ?string $rootCategorySlug = null): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'provider') {
            return $next($request);
        }

        $provider = Provider::where('user_id', $user->id)->first();

        if (! $provider) {
            return redirect()->route('provider.billing.plans')
                ->with('error', 'Créez votre fiche prestataire pour accéder aux contenus premium.');
        }

        $activeSubscription = $provider->subscriptions()
            ->with('plan.providerCategory')
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->when($rootCategorySlug, fn ($q) => $q->whereHas('plan.providerCategory', fn ($q2) => $q2->where('slug', $rootCategorySlug)))
            ->first();

        if (! $activeSubscription) {
            $message = $rootCategorySlug
                ? 'Cet espace nécessite un abonnement Art & Créations actif. Choisissez ce forfait pour continuer.'
                : 'Votre abonnement n\'est pas actif. Choisissez un forfait pour continuer.';

            return redirect()->route('provider.billing.plans')->with('error', $message);
        }

        return $next($request);
    }
}
