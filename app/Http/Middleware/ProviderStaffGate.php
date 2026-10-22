<?php

namespace App\Http\Middleware;

use App\Models\Provider;
use App\Models\ProviderMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Comptes équipe : un membre (réception, salle, accueil) n'accède qu'aux opérations du quotidien.
 * L'argent (portefeuille, retraits, abonnement), la vérification, la fiche publique et la gestion
 * de l'équipe restent réservés au propriétaire du compte prestataire.
 */
class ProviderStaffGate
{
    /** Routes accessibles aux membres de l'équipe (préfixes de noms de route). */
    public const STAFF_ROUTES = [
        'provider.dashboard',
        'provider.agenda',
        'provider.booking-requests.',
        'provider.reservations.',
        'provider.tourist-visits.',
        'provider.tickets.',
        'provider.restaurant.tables.',
        'provider.restaurant.floor.',
        'provider.restaurant.menu-qr',
        'provider.client-conversations',
        'provider.notifications.',
        'provider.disputes.',
        'provider.gift-cards.index',
        'provider.gift-cards.redeem',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();
        // Accès retiré pendant une session ouverte : déconnexion immédiate.
        if (Auth::user() && ! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => __('Ce compte est désactivé. Contactez-nous si vous pensez qu\'il s\'agit d\'une erreur.')]);
        }
        if (! $userId || Provider::isOwner($userId) || ! ProviderMember::where('user_id', $userId)->exists()) {
            return $next($request);
        }

        $name = (string) $request->route()?->getName();
        foreach (self::STAFF_ROUTES as $allowed) {
            if ($name === rtrim($allowed, '.') || str_starts_with($name, $allowed)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403, 'Réservé au responsable du compte.');
        }

        return redirect()->route('provider.booking-requests.index')
            ->with('error', 'Cette partie est réservée au responsable du compte prestataire.');
    }

    public static function isStaff(?int $userId): bool
    {
        return $userId && ! Provider::isOwner($userId) && ProviderMember::where('user_id', $userId)->exists();
    }
}
