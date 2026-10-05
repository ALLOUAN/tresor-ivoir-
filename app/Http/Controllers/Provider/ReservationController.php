<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservationController extends Controller
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
        $allowedStatuses = array_keys(Reservation::statusOptions());
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $base = Reservation::where('provider_id', $provider->id);

        $stats = [
            'total' => (clone $base)->count(),
            'new' => (clone $base)->where('status', Reservation::STATUS_NEW)->count(),
            'confirmed' => (clone $base)->where('status', Reservation::STATUS_CONFIRMED)->count(),
            'cancelled' => (clone $base)->where('status', Reservation::STATUS_CANCELLED)->count(),
        ];

        $reservations = (clone $base)
            ->statusFilter($status !== '' ? $status : null)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('provider.reservations.index', compact('reservations', 'stats', 'status'));
    }

    public function show(Reservation $reservation): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $reservation->provider_id === (int) $provider->id, 403);

        $reservation->load('payments', 'guestRegistration');

        return view('provider.reservations.show', compact('reservation'));
    }

    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $reservation->provider_id === (int) $provider->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.Reservation::STATUS_CONFIRMED.','.Reservation::STATUS_CANCELLED],
        ]);

        $reservation->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut de la réservation mis à jour.');
    }
}
