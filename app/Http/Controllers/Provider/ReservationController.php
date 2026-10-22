<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Mail\ReservationCancelledByProviderMail;
use App\Models\Provider;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ReservationController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
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

        $wasCancelled = $reservation->status === Reservation::STATUS_CANCELLED;
        $cancelling = ! $wasCancelled && $validated['status'] === Reservation::STATUS_CANCELLED;
        $reservation->update(['status' => $validated['status']] + ($cancelling ? [
            'cancelled_by' => Reservation::CANCELLED_BY_PROVIDER,
            'cancelled_at' => now(),
            // Annulation par l'établissement : l'acompte payé est toujours rendu en totalité.
            'refund_due_xof' => $reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID ? (int) $reservation->deposit_amount_xof : null,
        ] : []));

        // Annulation par l'établissement : le client est prévenu et, si un acompte a été payé,
        // l'administration est alertée pour le rembourser (la part du prestataire reste gelée).
        if ($cancelling) {
            if ($reservation->email) {
                Mail::to($reservation->email)->queue(new ReservationCancelledByProviderMail($reservation));
            }
            app(\App\Services\TextMessenger::class)->sendLater($reservation->phone, 'Trésors d\'Ivoire : '.$reservation->accommodation_name.' a annulé votre réservation '.$reservation->reference.'. Détails envoyés par e-mail.');

            if ($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID) {
                $admins = User::query()->where('role', 'admin')->where('is_active', true)->get();
                Notification::send($admins, new VisitorSystemNotification(
                    'Remboursement à effectuer',
                    'L\'établissement '.$reservation->accommodation_name.' a annulé la réservation '.$reservation->reference
                        .' dont l\'acompte ('.number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ').' XOF) est payé.',
                    route('admin.reservations.show', $reservation),
                ));
            }

            return back()->with('success', 'Réservation annulée. Le client a été prévenu'
                .($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID ? ' et son acompte lui sera remboursé.' : '.'));
        }

        return back()->with('success', 'Statut de la réservation mis à jour.');
    }
}
