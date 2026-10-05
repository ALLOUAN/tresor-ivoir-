<?php

namespace App\Http\Controllers;

use App\Mail\ReservationResumeMail;
use App\Models\Accommodation;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use App\Services\CinetPayService;
use App\Services\ReservationPricingService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReservationPaymentController extends Controller
{
    public function confirmation(Reservation $reservation): View
    {
        abort_unless($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID, 404);

        $reservation->load('provider', 'accommodation', 'guestRegistration');

        return view('reservations.confirmation', compact('reservation'));
    }

    // ── ÉTAPE 1 : Initiation AJAX ─────────────────────────────────────────

    public function initiate(
        Request $request,
        ReservationPricingService $pricing
    ): JsonResponse {
        $validated = $request->validate([
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'room_id' => ['nullable', 'string', 'max:40'],
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
            'payment_method' => ['nullable', 'in:cinetpay,wallet'],
        ]);
        $paymentMethod = $validated['payment_method'] ?? 'cinetpay';

        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

        $room = ! empty($validated['room_id'])
            ? $accommodation->findRoomById($validated['room_id'])
            : $accommodation->findRoomByName($validated['room_name']);
        if (! $room) {
            return response()->json([
                'success' => false,
                'message' => 'Chambre introuvable pour cet hébergement.',
            ], 422);
        }
        // Le prix vient toujours de la donnée serveur, jamais de celui soumis par le client
        // (qui pourrait être falsifié dans la requête AJAX) : une chambre sans tarif
        // n'est pas réservable en ligne.
        if ((int) ($room['price_xof'] ?? 0) <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Cette chambre n'est pas réservable en ligne : son tarif n'est pas défini.",
            ], 422);
        }
        $roomName = (string) $room['name'];
        $roomId = $room['id'] ?? null;
        $validated['room_price_xof'] = (int) $room['price_xof'];

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

        // Verrou + vérification + création dans la même transaction (cf. ReservationController::store
        // pour le détail du raisonnement) : évite qu'une requête concurrente sur la même chambre ne
        // passe le contrôle de chevauchement avant que celle-ci n'ait committé.
        $reservation = DB::transaction(function () use ($accommodation, $roomName, $roomId, $validated, $calc, $paymentMethod) {
            Accommodation::whereKey($accommodation->id)->lockForUpdate()->first();

            if (Reservation::hasConflict($accommodation->id, $roomName, $validated['check_in'], $validated['check_out'], null, $roomId)) {
                return null;
            }

            return Reservation::create([
                'user_id' => Auth::id(),
                'accommodation_id' => $accommodation->id,
                'accommodation_name' => $accommodation->name,
                'provider_id' => $accommodation->provider_id,
                'room_name' => $roomName,
                'room_id' => $roomId,
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
                'payment_method' => $paymentMethod,
                'deposit_amount_xof' => $calc['deposit_xof'],
                'commission_rate_percent' => $calc['commission_rate_percent'],
                'commission_amount_xof' => $calc['commission_xof'],
            ]);
        });

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Ces dates ne sont plus disponibles pour cette chambre. Merci de choisir d\'autres dates.',
            ], 422);
        }

        if ($reservation->email) {
            Mail::to($reservation->email)->queue(new ReservationResumeMail($reservation));
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $reservation->guestRegistrationUrl(),
        ]);
    }

    // ── ÉTAPE 1B : Déclenchement du paiement (après la fiche d'enregistrement) ─

    public function pay(Request $request, Reservation $reservation, CinetPayService $cinetPay): RedirectResponse
    {
        abort_unless($reservation->guestRegistration, 403);

        if ($reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID) {
            return redirect()->to($reservation->confirmationUrl());
        }

        if ($reservation->payment_method === 'wallet') {
            try {
                app(WalletService::class)->payReservationFromWallet($reservation, Auth::user());
            } catch (\RuntimeException $e) {
                $reservation->update(['payment_status' => Reservation::PAYMENT_FAILED]);

                return redirect()->to($reservation->guestRegistrationUrl())->with('error', $e->getMessage());
            }

            ReservationPayment::create([
                'reservation_id' => $reservation->id,
                'amount' => $reservation->deposit_amount_xof,
                'currency' => 'XOF',
                'gateway' => 'wallet',
                'status' => 'completed',
                'paid_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->to($reservation->confirmationUrl());
        }

        if (! $cinetPay->isConfigured()) {
            return redirect()->to($reservation->guestRegistrationUrl())
                ->with('error', 'Le paiement en ligne n\'est pas encore configuré. Contactez l\'hôtel directement.');
        }

        [$firstName, $lastName] = $this->splitName($reservation->full_name);

        $result = $cinetPay->initPayment([
            'amount' => $reservation->deposit_amount_xof,
            'designation' => 'Acompte réservation — '.$reservation->accommodation_name,
            'client_first_name' => $firstName,
            'client_last_name' => $lastName,
            'client_email' => $reservation->email,
            'success_url' => route('reservations.payment.return'),
            'failed_url' => route('reservations.payment.return'),
            'notify_url' => route('reservations.payment.webhook'),
        ]);

        if (! $result['success']) {
            $reservation->update(['payment_status' => Reservation::PAYMENT_FAILED]);

            return redirect()->to($reservation->guestRegistrationUrl())
                ->with('error', $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.');
        }

        $payment = ReservationPayment::create([
            'reservation_id' => $reservation->id,
            'amount' => $reservation->deposit_amount_xof,
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

        return redirect()->away($result['payment_url']);
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

            return redirect()->to($payment->reservation->confirmationUrl())
                ->with('success', 'Votre acompte a bien été payé !');
        }

        $statusResult = $cinetPay->checkPaymentStatus($payment->gateway_txn_id);

        if ($statusResult['success']) {
            $this->markCompleted($payment);
            session()->forget('pending_reservation_payment_id');

            return redirect()->to($payment->reservation->confirmationUrl())
                ->with('success', 'Votre acompte a bien été payé !');
        }

        if (($statusResult['status'] ?? '') === 'PENDING') {
            return redirect()->to($payment->reservation->establishmentUrl())
                ->with('info', 'Votre paiement est en cours de traitement. Vous serez notifié dès confirmation.');
        }

        $this->markFailed($payment, 'Retour CinetPay : statut '.($statusResult['status'] ?? '?'));

        return redirect()->to($payment->reservation->establishmentUrl())
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

    /**
     * Verrouillée + transactionnelle : webhook et retour navigateur peuvent appeler ceci
     * quasi simultanément (course réaliste, pas un cas limite) — sans lockForUpdate() ici,
     * les deux pourraient lire le statut 'pending' avant que l'un des deux ne commite,
     * ce qui doublerait désormais le crédit du wallet (pas seulement un email en double
     * comme c'était le cas avant l'ajout du wallet).
     */
    protected function markCompleted(ReservationPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment = ReservationPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === 'completed') {
                return;
            }

            $payment->update(['status' => 'completed', 'paid_at' => now()]);

            $reservation = Reservation::whereKey($payment->reservation_id)->lockForUpdate()->firstOrFail();
            $reservation->update(['payment_status' => Reservation::PAYMENT_DEPOSIT_PAID]);

            app(WalletService::class)->creditDepositForReservation($reservation);
            app(WalletService::class)->notifyReservationConfirmed($reservation);
        });
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
