<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitorReservationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $reservations = Reservation::ownedBy($user)
            ->with('accommodation')
            ->latest()
            ->get();

        $stats = [
            'total' => $reservations->count(),
            'upcoming' => $reservations->where('computed_status', Reservation::COMPUTED_STATUS_UPCOMING)->count(),
            'ongoing' => $reservations->where('computed_status', Reservation::COMPUTED_STATUS_ONGOING)->count(),
            'completed' => $reservations->where('computed_status', Reservation::COMPUTED_STATUS_COMPLETED)->count(),
            'cancelled' => $reservations->where('computed_status', Reservation::COMPUTED_STATUS_CANCELLED)->count(),
        ];

        $statusFilter = request('statut');
        $filtered = $statusFilter && $statusFilter !== 'all'
            ? $reservations->where('computed_status', $statusFilter)
            : $reservations;

        return view('visitor.reservations.index', [
            'reservations' => $filtered->values(),
            'stats' => $stats,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function show(Reservation $reservation): View
    {
        abort_unless($reservation->isOwnedBy(Auth::user()), 404);

        $reservation->loadMissing(['accommodation', 'provider', 'payments', 'guestRegistration']);

        return view('visitor.reservations.show', [
            'reservation' => $reservation,
        ]);
    }

    public function receipts(): View
    {
        $user = Auth::user();

        $reservations = Reservation::ownedBy($user)
            ->where('payment_status', Reservation::PAYMENT_DEPOSIT_PAID)
            ->with('accommodation')
            ->latest()
            ->get();

        return view('visitor.receipts.index', [
            'reservations' => $reservations,
        ]);
    }
}
