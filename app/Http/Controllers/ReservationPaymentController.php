<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use App\Services\CinetPayService;
use App\Services\ReservationPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationPaymentController extends Controller
{
    public function confirmation(Reservation $reservation): View
    {
        abort_unless($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID, 404);

        $reservation->load('provider', 'accommodation');

        return view('reservations.confirmation', compact('reservation'));
    }

    // ── ÉTAPE 1 : Initiation AJAX ─────────────────────────────────────────

    public function initiate(
        Request $request,
        CinetPayService $cinetPay,
        ReservationPricingService $pricing
    ): JsonResponse {
        $validated = $request->validate([
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'room_name' => ['required', 'string', 'max:255'],
            'room_price_xof' => ['required', 'integer', 'min:1'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms_count' => ['required', 'integer', 'min:1', 'max:20'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

        $calc = $pricing->compute(
            $validated['check_in'],
            $validated['check_out'],
            $validated['rooms_count'],
            $validated['room_price_xof']
        );

        if (! $calc['deposit_xof']) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de calculer le montant de l\'acompte pour cette chambre.',
            ], 422);
        }

        if (! $cinetPay->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Le paiement en ligne n\'est pas encore configuré. Contactez l\'hôtel directement.',
            ], 422);
        }

        $reservation = Reservation::create([
            'accommodation_id' => $accommodation->id,
            'accommodation_name' => $accommodation->name,
            'provider_id' => $accommodation->provider_id,
            'room_name' => $validated['room_name'],
            'room_price_xof' => $validated['room_price_xof'],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'nights' => $calc['nights'],
            'rooms_count' => $validated['rooms_count'],
            'guests_count' => $validated['guests_count'],
            'total_xof' => $calc['total_xof'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => Reservation::STATUS_NEW,
            'payment_status' => Reservation::PAYMENT_PENDING,
            'deposit_amount_xof' => $calc['deposit_xof'],
            'commission_rate_percent' => $calc['commission_rate_percent'],
            'commission_amount_xof' => $calc['commission_xof'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['full_name']);

        $result = $cinetPay->initPayment([
            'amount' => $calc['deposit_xof'],
            'designation' => 'Acompte réservation — '.$accommodation->name,
            'client_first_name' => $firstName,
            'client_last_name' => $lastName,
            'client_email' => $validated['email'],
            'client_phone_number' => $validated['phone'] ?? '',
            'success_url' => route('reservations.payment.return'),
            'failed_url' => route('reservations.payment.return'),
            'notify_url' => route('reservations.payment.webhook'),
        ]);

        if (! $result['success']) {
            $reservation->update(['payment_status' => Reservation::PAYMENT_FAILED]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        $payment = ReservationPayment::create([
            'reservation_id' => $reservation->id,
            'amount' => $calc['deposit_xof'],
            'currency' => 'XOF',
            'gateway' => 'cinetpay',
            'gateway_txn_id' => $result['merchant_transaction_id'],
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'metadata' => [
                'payment_token' => $result['payment_token'] ?? null,
                'notify_token' => $result['notify_token'] ?? null,
            ],
        ]);

        session([
            'pending_reservation_payment_id' => $payment->id,
        ]);

        return response()->json([
            'success' => true,
            'payment_url' => $result['payment_url'],
        ]);
    }

    // ── ÉTAPE 2A : Retour navigateur après paiement ───────────────────────

    public function returnFromGateway(Request $request, CinetPayService $cinetPay): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id')
            ?? $request->query('payment_token')
            ?? $request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $payment = $this->resolvePayment($token);

        if (! $payment) {
            return redirect()->route('home')
                ->with('error', 'Paiement introuvable. Contactez le support si vous avez été débité.');
        }

        if ($payment->status === 'completed') {
            session()->forget('pending_reservation_payment_id');

            return redirect()->route('reservations.payment.confirmation', $payment->reservation)
                ->with('success', 'Votre acompte a bien été payé !');
        }

        $statusResult = $cinetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['success']) {
            $this->markCompleted($payment);
            session()->forget('pending_reservation_payment_id');

            return redirect()->route('reservations.payment.confirmation', $payment->reservation)
                ->with('success', 'Votre acompte a bien été payé !');
        }

        if (($statusResult['status'] ?? '') === 'PENDING') {
            return redirect()->route('providers.show', $payment->reservation->provider?->slug ?? '')
                ->with('info', 'Votre paiement est en cours de traitement. Vous serez notifié dès confirmation.');
        }

        $this->markFailed($payment, 'Retour CinetPay : statut '.($statusResult['status'] ?? '?'));

        return redirect()->route('providers.show', $payment->reservation->provider?->slug ?? '')
            ->with('error', 'Le paiement n\'a pas abouti. Vous pouvez réessayer.');
    }

    // ── ÉTAPE 2B : Webhook CinetPay ───────────────────────────────────────

    public function webhook(Request $request, CinetPayService $cinetPay): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id')
            ?? $request->input('cpm_trans_id')
            ?? $request->input('transaction_id')
            ?? $request->input('payment_token')
            ?? '');

        if ($token === '') {
            return response()->json(['ok' => false, 'message' => 'token manquant'], 400);
        }

        $payment = ReservationPayment::where('gateway_txn_id', $token)->first();

        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'Paiement introuvable'], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json(['ok' => true, 'message' => 'Déjà traité']);
        }

        $statusResult = $cinetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['success']) {
            $this->markCompleted($payment);

            return response()->json(['ok' => true]);
        }

        $this->markFailed($payment, 'Webhook CinetPay : statut '.($statusResult['status'] ?? '?'));

        return response()->json(['ok' => false, 'message' => 'Paiement non confirmé']);
    }

    // ── Traitement interne ────────────────────────────────────────────────

    protected function markCompleted(ReservationPayment $payment): void
    {
        if ($payment->status === 'completed') {
            return;
        }

        $payment->update(['status' => 'completed', 'paid_at' => now()]);
        $payment->reservation()->update(['payment_status' => Reservation::PAYMENT_DEPOSIT_PAID]);
    }

    protected function markFailed(ReservationPayment $payment, ?string $reason = null): void
    {
        $payment->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
        $payment->reservation()->update(['payment_status' => Reservation::PAYMENT_FAILED]);
    }

    protected function resolvePayment(string $token): ?ReservationPayment
    {
        if ($token !== '') {
            $payment = ReservationPayment::where('gateway_txn_id', $token)->first();
            if ($payment) {
                return $payment;
            }
        }

        $paymentId = session('pending_reservation_payment_id');
        if ($paymentId) {
            return ReservationPayment::find($paymentId);
        }

        return null;
    }

    /** @return array{0:string,1:string} */
    protected function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0] ?? $fullName, $parts[1] ?? '-'];
    }
}
