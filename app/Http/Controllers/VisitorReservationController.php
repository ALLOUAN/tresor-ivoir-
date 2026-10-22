<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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

    /**
     * Annulation par le client, selon la politique de l'hébergement : dans le délai d'annulation
     * gratuite, l'acompte est dû en totalité (l'administration le rembourse, la part du
     * prestataire reste gelée) ; hors délai, rien n'est remboursé et l'acompte revient au
     * prestataire. La chambre est libérée dans les deux cas.
     */
    public function cancel(Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->isOwnedBy(Auth::user()), 404);

        $reservation = DB::transaction(function () use ($reservation) {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->with('accommodation')->firstOrFail();
            if (! $locked->clientCanCancel()) {
                return null;
            }

            $locked->update([
                'status' => Reservation::STATUS_CANCELLED,
                'cancelled_by' => Reservation::CANCELLED_BY_CLIENT,
                'cancelled_at' => now(),
                'refund_due_xof' => $locked->payment_status === Reservation::PAYMENT_DEPOSIT_PAID
                    ? $locked->clientRefundIfCancelledNow()
                    : null,
            ]);

            return $locked;
        });

        if (! $reservation) {
            return back()->with('error', __('Cette réservation ne peut plus être annulée en ligne. Contactez l\'établissement.'));
        }

        $dates = $reservation->check_in->format('d/m/Y').' → '.$reservation->check_out->format('d/m/Y');
        $providerUser = $reservation->provider?->user;
        if ($providerUser) {
            $providerUser->notify(new VisitorSystemNotification(
                'Réservation annulée par le client',
                'Le client '.$reservation->full_name.' a annulé la réservation '.$reservation->reference.' ('.$reservation->room_name.', '.$dates.'). La chambre est de nouveau disponible.',
                route('provider.reservations.show', $reservation),
            ));
        }

        $refund = (int) $reservation->refund_due_xof;
        if ($refund > 0) {
            $admins = User::query()->where('role', 'admin')->where('is_active', true)->get();
            Notification::send($admins, new VisitorSystemNotification(
                'Remboursement à effectuer',
                'Le client a annulé la réservation '.$reservation->reference.' dans le délai d\'annulation gratuite : '
                    .number_format($refund, 0, ',', ' ').' XOF à lui rembourser.',
                route('admin.reservations.show', $reservation),
            ));
        }

        return redirect()->route('visitor.reservations.show', $reservation)->with('success', match (true) {
            $refund > 0 => 'Réservation annulée. Votre acompte de '.number_format($refund, 0, ',', ' ').' FCFA vous sera remboursé.',
            $reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID => 'Réservation annulée. L\'annulation intervient hors du délai d\'annulation gratuite : l\'acompte n\'est pas remboursable.',
            default => 'Réservation annulée.',
        });
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
