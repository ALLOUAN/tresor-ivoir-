<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TouristVisit;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Visites reçues sur les sites touristiques — volontairement séparé de
 * Provider\ReservationController (hôtels) pour ne prendre aucun risque de
 * régression sur ce flux existant, même patron que Provider\ArtworkOrderController.
 */
class TouristVisitController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    public function index(Request $request): View
    {
        $provider = $this->getProvider();

        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(TouristVisit::STATUS_LABELS);
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $base = TouristVisit::where('provider_id', $provider->id);

        $stats = [
            'total' => (clone $base)->count(),
            'pending_payment' => (clone $base)->where('status', TouristVisit::STATUS_PENDING_PAYMENT)->count(),
            'paid' => (clone $base)->where('status', TouristVisit::STATUS_PAID)->count(),
            'cancelled' => (clone $base)->where('status', TouristVisit::STATUS_CANCELLED)->count(),
        ];

        $visits = (clone $base)
            ->with('session')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('provider.tourist-visits.index', compact('visits', 'stats', 'status'));
    }

    public function show(TouristVisit $touristVisit): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $touristVisit->provider_id === (int) $provider->id, 403);

        $touristVisit->load('session', 'experience');

        return view('provider.tourist-visits.show', ['visit' => $touristVisit]);
    }

    public function updateStatus(Request $request, TouristVisit $touristVisit): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $touristVisit->provider_id === (int) $provider->id, 403);

        $request->validate([
            'status' => ['required', 'string', 'in:'.TouristVisit::STATUS_CANCELLED],
        ]);

        // Le prestataire ne peut qu'annuler : la confirmation vient toujours du paiement
        // (voir TouristVisitController::completeVisit), jamais d'une action manuelle ici.
        if (! in_array($touristVisit->status, [TouristVisit::STATUS_PENDING_PAYMENT, TouristVisit::STATUS_PAID], true)) {
            return back()->with('error', 'Cette visite ne peut plus être annulée.');
        }

        $touristVisit->update(['status' => TouristVisit::STATUS_CANCELLED, 'cancelled_at' => now()]);

        if ($touristVisit->user_id && ($client = User::find($touristVisit->user_id))) {
            $client->notify(new VisitorSystemNotification(
                'Visite annulée',
                'Votre réservation pour "'.$touristVisit->experience_name.'" a été annulée par le prestataire.',
                route('tourist-experience.index'),
            ));
        }

        return back()->with('success', 'Visite annulée.');
    }
}
