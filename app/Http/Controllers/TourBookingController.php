<?php

namespace App\Http\Controllers;

use App\Models\TourBooking;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\TravelAgency;
use App\Services\CinetPayService;
use App\Services\TourBookingPaymentService;
use App\Services\TourBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Circuits : page détaillée, réservation immédiate d'un départ programmé (acompte ou totalité,
 * CinetPay ou solde), paiement du solde, feuille de route, « Mes voyages », annulation, voyageurs.
 */
class TourBookingController extends Controller
{
    private function resolve(string $agencySlug, string $tourSlug): array
    {
        $agency = TravelAgency::active()->where('slug', $agencySlug)->with(['city', 'provider'])->firstOrFail();
        abort_unless($agency->provider_id, 404);
        $tour = TourPackage::available()->where('provider_id', $agency->provider_id)->where('slug', $tourSlug)->with('category')->firstOrFail();

        return [$agency, $tour];
    }

    public function show(string $agencySlug, string $tourSlug): View
    {
        [$agency, $tour] = $this->resolve($agencySlug, $tourSlug);
        $departures = $tour->upcomingDepartures()->get();
        $others = TourPackage::available()->where('provider_id', $tour->provider_id)->whereKeyNot($tour->id)->orderBy('sort_order')->take(3)->get();

        return view('travel-agencies.tour', compact('agency', 'tour', 'departures', 'others'));
    }

    /** Devis en direct pour le module de réservation (même calcul que l'enregistrement). */
    public function quote(Request $request, string $agencySlug, string $tourSlug): JsonResponse
    {
        [, $tour] = $this->resolve($agencySlug, $tourSlug);
        $data = $this->validatedQuote($request);
        $departure = $tour->upcomingDepartures()->find($data['departure_id']);
        if (! $departure) {
            return response()->json(['ok' => false, 'message' => __('Choisissez un départ.')], 422);
        }
        try {
            $q = $tour->quote($departure, $data['adults'], $data['children'] ?? 0, $data['single_rooms'] ?? 0, $data['extras'] ?? []);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
        $deposit = $tour->depositAllowedFor($departure->departure_date);

        return response()->json([
            'ok' => true, 'lines' => $q['lines'], 'total' => $q['total'], 'travelers' => $q['travelers'],
            'deposit' => $deposit ? $q['deposit'] : null,
            'balance_due_on' => $deposit ? $departure->departure_date->copy()->subDays((int) $tour->balance_due_days)->format('d/m/Y') : null,
            'seats_left' => $departure->seatsLeft(),
        ]);
    }

    public function store(Request $request, string $agencySlug, string $tourSlug, TourBookingService $service, TourBookingPaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        [$agency, $tour] = $this->resolve($agencySlug, $tourSlug);
        abort_unless(Auth::user()->role === 'visitor', 403, __('Les réservations se font avec un compte visiteur.'));
        abort_unless($tour->instant_booking, 404);

        $data = $this->validatedQuote($request) + $request->validate([
            'pickup_point' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['required', 'in:deposit,full'],
            'payment_method' => ['required', 'in:cinetpay,wallet'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);
        $departure = $tour->upcomingDepartures()->find($data['departure_id']);
        if (! $departure) {
            return back()->withInput()->withErrors(['departure_id' => __('Choisissez l\'un des départs proposés.')], 'tour');
        }

        $user = Auth::user();
        try {
            $booking = $service->create($tour, $departure, $user, [
                'adults' => (int) $data['adults'], 'children' => (int) ($data['children'] ?? 0), 'single_rooms' => (int) ($data['single_rooms'] ?? 0),
                'pickup_point' => $data['pickup_point'] ?? null, 'extras' => $data['extras'] ?? [], 'payment_mode' => $data['payment_mode'],
                'full_name' => trim($user->first_name.' '.$user->last_name) ?: $user->email, 'email' => $user->email,
                'phone' => ($data['phone'] ?? null) ?: $user->phone, 'message' => $data['message'] ?? null,
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['tour' => $e->getMessage()], 'tour');
        }

        return $this->startPayment($booking, $service->firstStage($booking), $data['payment_method'], $service, $payments, $cinetPay);
    }

    /** Paiement du solde (ou nouvelle tentative du premier versement). */
    public function pay(Request $request, TourBooking $tourBooking, TourBookingService $service, TourBookingPaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        abort_unless((int) $tourBooking->user_id === (int) Auth::id(), 404);
        $method = $request->validate(['payment_method' => ['required', 'in:cinetpay,wallet']])['payment_method'];

        if ($tourBooking->canPayBalance()) {
            return $this->startPayment($tourBooking, 'balance', $method, $service, $payments, $cinetPay);
        }
        if (in_array($tourBooking->status, [TourBooking::STATUS_PENDING_PAYMENT, TourBooking::STATUS_FAILED], true) && $tourBooking->departure?->isOpenForBooking()) {
            $tourBooking->update(['status' => TourBooking::STATUS_PENDING_PAYMENT]);

            return $this->startPayment($tourBooking, $service->firstStage($tourBooking), $method, $service, $payments, $cinetPay);
        }

        return back()->with('error', __('Rien à payer pour cette réservation.'));
    }

    private function startPayment(TourBooking $booking, string $stage, string $method, TourBookingService $service, TourBookingPaymentService $payments, CinetPayService $cinetPay): RedirectResponse
    {
        if ($method === 'wallet') {
            try {
                $payments->payFromWallet($booking, Auth::user(), $stage);
            } catch (\RuntimeException $e) {
                if ($booking->amount_paid_xof === 0) {
                    $booking->update(['status' => TourBooking::STATUS_FAILED]);
                }

                return redirect()->route('visitor.tour-bookings.index')->with('error', $e->getMessage());
            }
            $service->afterPayment($booking);

            return redirect()->to($booking->confirmationUrl());
        }

        if (! $cinetPay->isConfigured()) {
            return redirect()->route('visitor.tour-bookings.index')->with('error', __('Le paiement en ligne n\'est pas encore disponible : payez avec votre solde.'));
        }
        $amount = $payments->amountFor($booking, $stage);
        $user = Auth::user();
        $result = $cinetPay->initPayment([
            'amount' => $amount,
            'designation' => ($stage === 'deposit' ? 'Acompte' : ($stage === 'balance' ? 'Solde' : 'Circuit')).' — '.$booking->tour_name,
            'client_first_name' => $user->first_name, 'client_last_name' => $user->last_name, 'client_email' => $user->email,
            'success_url' => route('tour-booking.payment.return'), 'failed_url' => route('tour-booking.payment.return'),
            'notify_url' => route('tour-booking.payment.webhook'),
        ]);
        if (! $result['success']) {
            return redirect()->route('visitor.tour-bookings.index')->with('error', $result['message'] ?? __('Erreur lors de l\'initialisation du paiement.'));
        }
        $booking->update(['pending_stage' => $stage, 'pending_amount_xof' => $amount, 'gateway_txn_id' => $result['merchant_transaction_id']]);
        session(['pending_tour_booking_id' => $booking->id]);

        return redirect()->away($result['payment_url']);
    }

    public function handleReturn(Request $request, CinetPayService $cinetPay, TourBookingPaymentService $payments, TourBookingService $service): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id') ?? $request->query('transaction_id') ?? $request->query('cpm_trans_id') ?? '');
        $booking = $token !== '' ? TourBooking::where('gateway_txn_id', $token)->first() : TourBooking::find(session('pending_tour_booking_id'));
        session()->forget('pending_tour_booking_id');
        if (! $booking) {
            return redirect()->route('travel-agency.index')->with('error', __('Réservation introuvable. Contactez le support si vous avez été débité.'));
        }
        if ($booking->pending_stage && $booking->gateway_txn_id && ($cinetPay->checkPaymentStatus($booking->gateway_txn_id)['success'] ?? false)) {
            if ($payments->markPaid($booking, $booking->pending_stage, 'cinetpay')) {
                $service->afterPayment($booking);
            }
        }
        $booking->refresh();

        return $booking->isActive()
            ? redirect()->to($booking->confirmationUrl())
            : redirect()->route('visitor.tour-bookings.index')->with('error', __('Le paiement n\'a pas abouti. Vous pouvez réessayer.'));
    }

    public function webhook(Request $request, CinetPayService $cinetPay, TourBookingPaymentService $payments, TourBookingService $service): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id') ?? $request->input('cpm_trans_id') ?? '');
        $booking = $token !== '' ? TourBooking::where('gateway_txn_id', $token)->first() : null;
        if (! $booking) {
            return response()->json(['ok' => false], 404);
        }
        if (! $booking->pending_stage) {
            return response()->json(['ok' => true]);
        }
        if ($cinetPay->checkPaymentStatus($token)['success'] ?? false) {
            if ($payments->markPaid($booking, $booking->pending_stage, 'cinetpay')) {
                $service->afterPayment($booking);
            }

            return response()->json(['ok' => true]);
        }
        if ($booking->amount_paid_xof === 0) {
            $booking->update(['status' => TourBooking::STATUS_FAILED]);
        }

        return response()->json(['ok' => false]);
    }

    /** Feuille de route : programme, prise en charge, paiements, contacts de l'agence. */
    public function confirmation(TourBooking $tourBooking): View
    {
        abort_unless($tourBooking->isActive() || $tourBooking->status === TourBooking::STATUS_CANCELLED, 404);
        $tourBooking->load(['tour', 'departure', 'provider']);

        return view('tour-bookings.confirmation', [
            'booking' => $tourBooking,
            'agency' => TravelAgency::where('provider_id', $tourBooking->provider_id)->first(),
        ]);
    }

    public function mine(): View
    {
        $bookings = TourBooking::with(['tour', 'departure'])->where('user_id', Auth::id())
            ->where(fn ($q) => $q->whereNotIn('status', [TourBooking::STATUS_PENDING_PAYMENT, TourBooking::STATUS_FAILED])->orWhere('created_at', '>=', now()->subDay()))
            ->orderByDesc('departure_date')->paginate(10);

        return view('visitor.tour-bookings.index', compact('bookings'));
    }

    public function cancel(TourBooking $tourBooking, TourBookingService $service): RedirectResponse
    {
        abort_unless((int) $tourBooking->user_id === (int) Auth::id(), 404);
        try {
            $refund = $service->cancelByClient($tourBooking);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $refund > 0
            ? __('Réservation annulée. :a FCFA ont été crédités sur votre solde.', ['a' => number_format($refund, 0, ',', ' ')])
            : __('Réservation annulée. Selon les conditions du circuit, aucun remboursement n\'est dû.'));
    }

    /** Liste nominative des voyageurs et contact d'urgence. */
    public function updateTravelers(Request $request, TourBooking $tourBooking): RedirectResponse
    {
        abort_unless((int) $tourBooking->user_id === (int) Auth::id(), 404);
        abort_unless($tourBooking->isActive(), 403);
        $data = $request->validate([
            'travelers' => ['required', 'array', 'max:'.$tourBooking->travelers_count],
            'travelers.*.name' => ['nullable', 'string', 'max:120'],
            'travelers.*.id_number' => ['nullable', 'string', 'max:40'],
            'travelers.*.phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact' => ['nullable', 'string', 'max:200'],
        ]);
        $travelers = collect($data['travelers'])->map(fn ($t) => [
            'name' => trim((string) ($t['name'] ?? '')), 'id_number' => trim((string) ($t['id_number'] ?? '')), 'phone' => trim((string) ($t['phone'] ?? '')),
        ])->values()->all();
        $tourBooking->update(['travelers' => $travelers, 'emergency_contact' => $data['emergency_contact'] ?? null]);

        return back()->with('success', __('Liste des voyageurs enregistrée.'));
    }

    /** @return array<string, mixed> */
    private function validatedQuote(Request $request): array
    {
        return $request->validate([
            'departure_id' => ['required', 'integer'],
            'adults' => ['required', 'integer', 'min:1', 'max:50'],
            'children' => ['nullable', 'integer', 'min:0', 'max:50'],
            'single_rooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'extras' => ['nullable', 'array', 'max:12'],
            'extras.*' => ['integer', 'min:0', 'max:11'],
        ], [], ['departure_id' => __('départ'), 'adults' => __('adultes')]);
    }
}
