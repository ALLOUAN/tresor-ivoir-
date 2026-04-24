<?php

namespace App\Http\Middleware;

use App\Models\Provider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
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
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();

        if (! $activeSubscription) {
            return redirect()->route('provider.billing.plans')
                ->with('error', 'Votre abonnement n\'est pas actif. Choisissez un forfait pour continuer.');
        }

        return $next($request);
    }
}
