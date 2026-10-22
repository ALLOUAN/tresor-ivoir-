<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Mail\BookingRequestMail;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Demandes de réservation reçues par l'établissement (restaurant, loisir, circuit). */
class BookingRequestController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');

        return $provider;
    }

    public function index(Request $request): View
    {
        $provider = $this->provider();
        $status = array_key_exists((string) $request->query('statut'), BookingRequest::STATUS_LABELS) ? $request->query('statut') : null;

        $bookings = BookingRequest::where('provider_id', $provider->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("status = 'new' desc")
            ->orderBy('booking_date')
            ->paginate(20)
            ->withQueryString();

        $counts = BookingRequest::where('provider_id', $provider->id)
            ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('provider.booking-requests.index', compact('bookings', 'counts', 'status'));
    }

    public function respond(Request $request, BookingRequest $bookingRequest): RedirectResponse
    {
        abort_unless((int) $bookingRequest->provider_id === (int) $this->provider()->id, 403);

        $validated = $request->validate([
            'decision' => ['required', 'in:confirm,decline'],
            'provider_note' => ['nullable', 'string', 'max:500'],
            'amount_xof' => ['nullable', 'integer', 'min:0', 'max:50000000'],
        ], [], ['amount_xof' => 'montant']);

        if ($bookingRequest->status !== BookingRequest::STATUS_NEW) {
            return back()->with('error', 'Cette demande a déjà reçu une réponse.');
        }

        $confirmed = $validated['decision'] === 'confirm';
        $bookingRequest->update([
            'status' => $confirmed ? BookingRequest::STATUS_CONFIRMED : BookingRequest::STATUS_DECLINED,
            'provider_note' => $validated['provider_note'] ?? null,
            'answered_at' => now(),
        ]);

        if ($confirmed) {
            $gross = (int) ($validated['amount_xof'] ?? 0);
            $promo = $bookingRequest->promo_code && $gross > 0
                ? \App\Models\ProviderPromoCode::where('provider_id', $bookingRequest->provider_id)->where('code', $bookingRequest->promo_code)->first()
                : null;
            $discount = $promo ? $promo->discountFor($gross) : 0;
            $bookingRequest->update(['promo_discount_xof' => $discount ?: null]);
            app(\App\Services\BookingPaymentService::class)->priceBooking($bookingRequest, $gross - $discount);
            $bookingRequest->refresh();
        }

        Mail::to($bookingRequest->email)->queue(new BookingRequestMail($bookingRequest, $confirmed ? 'confirmed' : 'declined'));
        app(\App\Services\TextMessenger::class)->sendLater($bookingRequest->phone, 'Trésors d\'Ivoire : '.$bookingRequest->bookable_name.($confirmed ? ' confirme' : ' ne peut pas accepter').' votre demande du '.$bookingRequest->booking_date->format('d/m').($bookingRequest->booking_time ? ' à '.$bookingRequest->booking_time : '').'.');
        $bookingRequest->user?->notify(new VisitorSystemNotification(
            $confirmed ? 'Réservation confirmée' : 'Demande de réservation refusée',
            $bookingRequest->bookable_name.' — '.$bookingRequest->booking_date->format('d/m/Y').'.',
            route('visitor.booking-requests.index'),
        ));

        return back()->with('success', $confirmed ? 'Réservation confirmée, le client est prévenu.' : 'Demande refusée, le client est prévenu.');
    }
}
