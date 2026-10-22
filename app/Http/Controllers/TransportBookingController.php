<?php

namespace App\Http\Controllers;

use App\Models\TransportBooking;
use App\Models\TransportOffer;
use App\Services\CinetPayService;
use App\Services\TransportBookingService;
use App\Services\TransportPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Transports : page d'offre avec devis instantané (transfert, chauffeur, location, ligne), réservation et
 * paiement immédiats, confirmation (billet QR pour les lignes), « Mes trajets » : annulation, modification,
 * documents et contrat de location.
 */
class TransportBookingController extends Controller
{
    private function offer(string $slug): TransportOffer
    {
        return TransportOffer::available()->where('slug', $slug)->with('category')->firstOrFail();
    }

    public function show(string $slug): View
    {
        $offer = $this->offer($slug);
        $company = $offer->company();
        $others = TransportOffer::available()->where('provider_id', $offer->provider_id)->whereKeyNot($offer->id)->orderBy('sort_order')->take(4)->get();
        $trips = $offer->mode === 'line' ? $offer->upcomingTrips()->take(30)->get() : collect();

        return view('transport.offer', compact('offer', 'company', 'others', 'trips'));
    }

    /** @return array<string, mixed> */
    private function input(Request $request): array
    {
        return $request->validate([
            'zone' => ['nullable', 'integer', 'min:0'], 'direction' => ['nullable', 'in:from,to'], 'address' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'string', 'max:20'], 'return_at' => ['nullable', 'string', 'max:20'], 'round_trip' => ['nullable', 'boolean'],
            'flight_number' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9 ]*$/'],
            'package' => ['nullable', 'in:trip,half_day,full_day'],
            'pickup_label' => ['nullable', 'string', 'max:255'], 'dropoff_label' => ['nullable', 'string', 'max:255'],
            'pickup_lat' => ['nullable', 'numeric', 'between:-90,90'], 'pickup_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff_lat' => ['nullable', 'numeric', 'between:-90,90'], 'dropoff_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:100'], 'luggage' => ['nullable', 'integer', 'min:0', 'max:50'],
            'extras' => ['nullable', 'array', 'max:12'], 'extras.*' => ['integer', 'min:0', 'max:11'],
            'trip_id' => ['nullable', 'integer'], 'seats' => ['nullable', 'array', 'max:60'], 'seats.*' => ['integer', 'min:1', 'max:200'],
        ], ['flight_number.regex' => __('Numéro de vol invalide (ex : AF702).')]);
    }

    public function quote(Request $request, string $slug, TransportBookingService $service): JsonResponse
    {
        $offer = $this->offer($slug);
        try {
            $q = $service->quote($offer, $this->input($request));
            $vehicle = $offer->mode !== 'line' ? $service->freeVehicle($offer, $q['starts_at'], $q['ends_at'], $q['passengers']) : null;
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true, 'lines' => $q['lines'], 'total' => $q['total'], 'distance_km' => $q['distance_km'],
            'deposit_on_site' => $q['deposit_on_site'], 'vehicle' => $vehicle ? $vehicle->categoryLabel().' · '.$vehicle->seats.' '.__('places') : null,
        ]);
    }

    /** Sièges d'un départ de ligne : pris / libres. */
    public function seats(string $slug, int $trip): JsonResponse
    {
        $offer = $this->offer($slug);
        $t = $offer->upcomingTrips()->findOrFail($trip);

        return response()->json(['total' => $t->seats_total, 'per_row' => $t->seats_per_row, 'taken' => $t->takenSeats(), 'price' => $t->price()]);
    }

    public function book(Request $request, string $slug, TransportBookingService $service, TransportPaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        $offer = $this->offer($slug);
        abort_unless(Auth::user()->role === 'visitor', 403, __('Les réservations se font avec un compte visiteur.'));
        $in = $this->input($request) + $request->validate([
            'payment_method' => ['required', 'in:cinetpay,wallet'], 'phone' => ['required', 'string', 'max:30'], 'message' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($offer->mode === 'transfer' && ($in['direction'] ?? 'from') === 'from' && empty($in['flight_number'])) {
            return back()->withInput()->withErrors(['booking' => __('Indiquez votre numéro de vol : le chauffeur suit l\'heure d\'arrivée.')], 'transport');
        }
        $user = Auth::user();
        try {
            $booking = $service->create($offer, $user, $in + ['full_name' => trim($user->first_name.' '.$user->last_name) ?: $user->email, 'email' => $user->email]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['booking' => $e->getMessage()], 'transport');
        }

        if ($in['payment_method'] === 'wallet' || $booking->amount_total_xof <= 0) {
            try {
                $payments->payFromWallet($booking, $user);
            } catch (\RuntimeException $e) {
                $booking->update(['status' => TransportBooking::STATUS_FAILED]);

                return back()->withInput()->withErrors(['booking' => $e->getMessage()], 'transport');
            }
            $service->afterPayment($booking);

            return redirect()->to($booking->confirmationUrl());
        }
        if (! $cinetPay->isConfigured()) {
            $booking->update(['status' => TransportBooking::STATUS_FAILED]);

            return back()->withInput()->withErrors(['booking' => __('Le paiement en ligne n\'est pas encore disponible : payez avec votre solde.')], 'transport');
        }
        $result = $cinetPay->initPayment([
            'amount' => $booking->amount_total_xof, 'designation' => 'Transport — '.$booking->offer_name,
            'client_first_name' => $user->first_name, 'client_last_name' => $user->last_name, 'client_email' => $user->email,
            'success_url' => route('transport-booking.payment.return'), 'failed_url' => route('transport-booking.payment.return'),
            'notify_url' => route('transport-booking.payment.webhook'),
        ]);
        if (! $result['success']) {
            $booking->update(['status' => TransportBooking::STATUS_FAILED]);

            return back()->withInput()->withErrors(['booking' => $result['message'] ?? __('Erreur lors de l\'initialisation du paiement.')], 'transport');
        }
        $booking->update(['gateway' => 'cinetpay', 'gateway_txn_id' => $result['merchant_transaction_id']]);

        return redirect()->away($result['payment_url']);
    }

    public function handleReturn(Request $request, CinetPayService $cinetPay, TransportPaymentService $payments, TransportBookingService $service): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id') ?? $request->query('transaction_id') ?? $request->query('cpm_trans_id') ?? '');
        $b = $token !== '' ? TransportBooking::where('gateway_txn_id', $token)->first() : null;
        if (! $b) {
            return redirect()->route('transport-company.index')->with('error', __('Réservation introuvable. Contactez le support si vous avez été débité.'));
        }
        if (($cinetPay->checkPaymentStatus($b->gateway_txn_id)['success'] ?? false) && $payments->markPaid($b, $b->gateway_txn_id)) {
            $service->afterPayment($b);
        }

        return $b->fresh()->status === TransportBooking::STATUS_PAID
            ? redirect()->to($b->confirmationUrl())
            : redirect()->route('visitor.transport-bookings.index')->with('error', __('Le paiement n\'a pas abouti. Vous pouvez réessayer.'));
    }

    public function webhook(Request $request, CinetPayService $cinetPay, TransportPaymentService $payments, TransportBookingService $service): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id') ?? $request->input('cpm_trans_id') ?? '');
        $b = $token !== '' ? TransportBooking::where('gateway_txn_id', $token)->first() : null;
        if (! $b) {
            return response()->json(['ok' => false], 404);
        }
        if ($cinetPay->checkPaymentStatus($token)['success'] ?? false) {
            if ($payments->markPaid($b, $token)) {
                $service->afterPayment($b);
            }

            return response()->json(['ok' => true]);
        }
        if ($b->status === TransportBooking::STATUS_PENDING_PAYMENT) {
            $b->update(['status' => TransportBooking::STATUS_FAILED]);
        }

        return response()->json(['ok' => false]);
    }

    public function confirmation(TransportBooking $transportBooking): View
    {
        abort_unless(in_array($transportBooking->status, [TransportBooking::STATUS_PAID, TransportBooking::STATUS_CANCELLED, TransportBooking::STATUS_REFUNDED], true), 404);

        return view('transport.confirmation', ['b' => $transportBooking->load(['offer', 'driver', 'vehicle', 'trip']), 'company' => $transportBooking->offer?->company()]);
    }

    public function mine(): View
    {
        return view('visitor.transport-bookings.index', [
            'bookings' => TransportBooking::with(['offer', 'driver', 'vehicle'])->where('user_id', Auth::id())
                ->where(fn ($q) => $q->whereNotIn('status', [TransportBooking::STATUS_PENDING_PAYMENT, TransportBooking::STATUS_FAILED])->orWhere('created_at', '>=', now()->subDay()))
                ->orderByDesc('starts_at')->paginate(12),
        ]);
    }

    private function owned(TransportBooking $b): void
    {
        abort_unless((int) $b->user_id === (int) Auth::id(), 404);
    }

    public function cancel(TransportBooking $transportBooking, TransportBookingService $service): RedirectResponse
    {
        $this->owned($transportBooking);
        try {
            $refund = $service->cancel($transportBooking);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $refund > 0 ? __('Réservation annulée. :a FCFA ont été crédités sur votre solde.', ['a' => number_format($refund, 0, ',', ' ')]) : __('Réservation annulée.'));
    }

    public function modify(Request $request, TransportBooking $transportBooking, TransportBookingService $service): RedirectResponse
    {
        $this->owned($transportBooking);
        $data = $request->validate(['starts_at' => ['required', 'string', 'max:20'], 'flight_number' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9 ]*$/']]);
        try {
            $service->modify($transportBooking, $data['starts_at'], $data['flight_number'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Réservation modifiée.'));
    }

    /** Location : permis, pièce d'identité et acceptation du contrat avant le retrait du véhicule. */
    public function documents(Request $request, TransportBooking $transportBooking): RedirectResponse
    {
        $this->owned($transportBooking);
        abort_unless($transportBooking->mode === 'rental' && $transportBooking->status === TransportBooking::STATUS_PAID, 403);
        $request->validate([
            'license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:6144'], 'id_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:6144'],
            'license_number' => ['nullable', 'string', 'max:40'], 'accept_contract' => ['nullable', 'boolean'],
        ]);
        $docs = $transportBooking->documents ?? [];
        foreach (['license', 'id_card'] as $k) {
            if ($request->hasFile($k)) {
                // Disque privé : pièces d'identité jamais exposées publiquement (consultées via une route contrôlée).
                $docs[$k] = $request->file($k)->store('transport-documents/'.$transportBooking->id, 'local');
            }
        }
        if ($request->filled('license_number')) {
            $docs['license_number'] = $request->input('license_number');
        }
        $transportBooking->update([
            'documents' => $docs,
            'contract_accepted_at' => $request->boolean('accept_contract') ? ($transportBooking->contract_accepted_at ?? now()) : $transportBooking->contract_accepted_at,
        ]);

        return back()->with('success', __('Documents enregistrés.'));
    }
}
