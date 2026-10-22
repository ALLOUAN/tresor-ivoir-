<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\BookingPaymentService;
use App\Services\CinetPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Paiement en ligne d'une demande de réservation confirmée (CinetPay ou solde du client).
 * La confirmation définitive vient du webhook ou du retour navigateur, après vérification
 * du statut auprès de CinetPay ; l'encaissement est idempotent (BookingPaymentService).
 */
class BookingPaymentController extends Controller
{
    public function pay(Request $request, BookingRequest $bookingRequest, CinetPayService $cinetPay, BookingPaymentService $payments): RedirectResponse
    {
        abort_unless((int) $bookingRequest->user_id === (int) Auth::id(), 404);
        $validated = $request->validate(['payment_method' => ['required', 'in:cinetpay,wallet']]);

        if (! $bookingRequest->canBePaidOnline()) {
            return back()->with('error', __('Cette demande ne peut pas être payée en ligne.'));
        }

        if ($validated['payment_method'] === 'wallet') {
            try {
                $payments->payFromWallet($bookingRequest, Auth::user());
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
            $this->notifyPaid($bookingRequest->fresh());

            return back()->with('success', __('Paiement effectué depuis votre solde. Merci !'));
        }

        if (! $cinetPay->isConfigured()) {
            return back()->with('error', __('Le paiement en ligne n\'est pas encore disponible. Réglez directement auprès de l\'établissement.'));
        }

        $user = Auth::user();
        $result = $cinetPay->initPayment([
            'amount' => (int) $bookingRequest->amount_due_xof,
            'designation' => 'Réservation — '.$bookingRequest->bookable_name,
            'client_first_name' => $user->first_name,
            'client_last_name' => $user->last_name,
            'client_email' => $user->email,
            'success_url' => route('booking-requests.payment.return'),
            'failed_url' => route('booking-requests.payment.return'),
            'notify_url' => route('booking-requests.payment.webhook'),
        ]);

        if (! $result['success']) {
            return back()->with('error', $result['message'] ?? __('Erreur lors de l\'initialisation du paiement.'));
        }

        $bookingRequest->update([
            'payment_status' => BookingRequest::PAYMENT_PENDING,
            'payment_method' => 'cinetpay',
            'gateway_txn_id' => $result['merchant_transaction_id'],
        ]);
        session(['pending_booking_id' => $bookingRequest->id]);

        return redirect()->away($result['payment_url']);
    }

    public function handleReturn(Request $request, CinetPayService $cinetPay, BookingPaymentService $payments): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id') ?? $request->query('transaction_id') ?? $request->query('cpm_trans_id') ?? '');
        $booking = $token !== ''
            ? BookingRequest::where('gateway_txn_id', $token)->first()
            : BookingRequest::find(session('pending_booking_id'));

        if (! $booking) {
            return redirect()->route('visitor.booking-requests.index')->with('error', __('Réservation introuvable. Contactez le support si vous avez été débité.'));
        }
        session()->forget('pending_booking_id');

        if ($booking->payment_status !== BookingRequest::PAYMENT_PAID && $booking->gateway_txn_id) {
            if (($cinetPay->checkPaymentStatus($booking->gateway_txn_id)['success'] ?? false) && $payments->markPaid($booking, 'cinetpay')) {
                $this->notifyPaid($booking->fresh());
            }
        }

        return redirect()->route('visitor.booking-requests.index')->with(
            $booking->fresh()->payment_status === BookingRequest::PAYMENT_PAID ? 'success' : 'error',
            $booking->fresh()->payment_status === BookingRequest::PAYMENT_PAID
                ? __('Paiement confirmé ! Votre réservation est réglée.')
                : __('Le paiement n\'a pas abouti. Vous pouvez réessayer.')
        );
    }

    public function webhook(Request $request, CinetPayService $cinetPay, BookingPaymentService $payments): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id') ?? $request->input('cpm_trans_id') ?? '');
        $booking = $token !== '' ? BookingRequest::where('gateway_txn_id', $token)->first() : null;
        if (! $booking) {
            return response()->json(['ok' => false], 404);
        }
        if ($booking->payment_status === BookingRequest::PAYMENT_PAID) {
            return response()->json(['ok' => true]);
        }

        if ($cinetPay->checkPaymentStatus($token)['success'] ?? false) {
            if ($payments->markPaid($booking, 'cinetpay')) {
                $this->notifyPaid($booking->fresh());
            }

            return response()->json(['ok' => true]);
        }

        $booking->update(['payment_status' => BookingRequest::PAYMENT_FAILED]);

        return response()->json(['ok' => false]);
    }

    /** Remboursement enregistré par l'administration (annulation d'une réservation payée). */
    public function adminRefund(BookingRequest $bookingRequest, BookingPaymentService $payments): RedirectResponse
    {
        try {
            $payments->refund($bookingRequest, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $bookingRequest->user?->notify(new VisitorSystemNotification(
            __('Remboursement effectué'),
            $bookingRequest->reference.' — '.number_format((int) $bookingRequest->amount_due_xof, 0, ',', ' ').' FCFA.',
            route('visitor.booking-requests.index'),
        ));

        return back()->with('success', 'Remboursement enregistré.');
    }

    private function notifyPaid(BookingRequest $booking): void
    {
        $booking->provider?->user?->notify(new VisitorSystemNotification(
            'Réservation payée en ligne',
            $booking->full_name.' a réglé '.number_format((int) $booking->amount_due_xof, 0, ',', ' ').' FCFA pour '.$booking->reference.' ('.$booking->booking_date->format('d/m/Y').').',
            route('provider.booking-requests.index'),
        ));
    }
}
