<?php

namespace App\Http\Controllers;

use App\Models\AccountSecurityEvent;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Services\AccountSecurityEventLogger;
use App\Services\CinetPayService;
use App\Services\PayoutOtpService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitorWalletController extends Controller
{
    private const MIN_TOPUP_XOF = 500;

    private const MAX_TOPUP_XOF = 5_000_000;

    public function index(): View
    {
        $user = Auth::user();
        $wallet = Wallet::forUser($user);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with('reservation')
            ->latest()
            ->paginate(20);

        $payoutRequests = PayoutRequest::where('user_id', $user->id)->latest()->take(10)->get();

        return view('visitor.wallet.index', [
            'user' => $user,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'minTopup' => self::MIN_TOPUP_XOF,
            'maxTopup' => self::MAX_TOPUP_XOF,
            'payoutRequests' => $payoutRequests,
        ]);
    }

    public function requestPayout(Request $request, WalletService $walletService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount_xof' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:'.implode(',', array_keys(PayoutRequest::METHOD_LABELS))],
            'payout_destination' => ['required', 'string', 'max:255'],
        ], [], [
            'amount_xof' => 'montant',
            'method' => 'méthode',
            'payout_destination' => 'destination',
        ]);

        try {
            $payout = $walletService->requestClientPayout(
                $user,
                (int) $validated['amount_xof'],
                $validated['method'],
                $validated['payout_destination'],
                $request->ip(),
                $request->userAgent()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($user->payout_account_number && $user->payout_account_number !== $validated['payout_destination']) {
            AccountSecurityEventLogger::log($user, AccountSecurityEvent::TYPE_PAYOUT_DESTINATION_CHANGED, $request);
        }

        $user->update([
            'payout_method' => $validated['method'],
            'payout_account_number' => $validated['payout_destination'],
        ]);

        return match ($payout->status) {
            PayoutRequest::STATUS_PENDING_VERIFICATION => back()->with('success', 'Un code de vérification vous a été envoyé par email pour confirmer ce retrait.'),
            PayoutRequest::STATUS_SUSPENDED => back()->with('error', 'Cette demande a été suspendue pour vérification par notre équipe de sécurité.'),
            default => back()->with('success', 'Demande de retrait envoyée — elle sera examinée par notre équipe.'),
        };
    }

    public function verifyPayoutOtp(Request $request, PayoutRequest $payoutRequest, WalletService $walletService): RedirectResponse
    {
        abort_unless((int) $payoutRequest->user_id === (int) Auth::id(), 403);

        $validated = $request->validate(['code' => ['required', 'digits:6']]);

        $result = $walletService->verifyPayoutOtp($payoutRequest, $validated['code']);

        if ($result !== true) {
            return back()->with('error', $result);
        }

        return back()->with('success', 'Retrait confirmé — il sera traité par notre équipe.');
    }

    public function resendPayoutOtp(PayoutRequest $payoutRequest, PayoutOtpService $otpService): RedirectResponse
    {
        abort_unless((int) $payoutRequest->user_id === (int) Auth::id(), 403);
        abort_unless($payoutRequest->status === PayoutRequest::STATUS_PENDING_VERIFICATION, 422);

        $otpService->send($payoutRequest);

        return back()->with('success', 'Un nouveau code vous a été envoyé.');
    }

    public function initiateTopup(Request $request, CinetPayService $cinetPay): JsonResponse
    {
        $validated = $request->validate([
            'amount_xof' => ['required', 'integer', 'min:'.self::MIN_TOPUP_XOF, 'max:'.self::MAX_TOPUP_XOF],
        ]);

        if (! $cinetPay->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Le paiement en ligne n\'est pas encore configuré.',
            ], 422);
        }

        $user = Auth::user();
        $wallet = Wallet::forUser($user);

        $topup = WalletTopup::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount_xof' => $validated['amount_xof'],
            'gateway' => 'cinetpay',
            'status' => WalletTopup::STATUS_PENDING,
        ]);

        [$firstName, $lastName] = $this->splitName($user->full_name);

        $result = $cinetPay->initPayment([
            'amount' => $validated['amount_xof'],
            'designation' => 'Recharge Wallet',
            'client_first_name' => $firstName,
            'client_last_name' => $lastName,
            'client_email' => $user->email,
            'success_url' => route('visitor.wallet.topup.return'),
            'failed_url' => route('visitor.wallet.topup.return'),
            'notify_url' => route('visitor.wallet.topup.webhook'),
        ]);

        if (! $result['success']) {
            $topup->update(['status' => WalletTopup::STATUS_FAILED, 'failure_reason' => $result['message'] ?? null]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.',
            ], 422);
        }

        $topup->update(['gateway_txn_id' => $result['merchant_transaction_id']]);

        session(['pending_wallet_topup_id' => $topup->id]);

        return response()->json([
            'success' => true,
            'payment_url' => $result['payment_url'],
        ]);
    }

    public function returnFromGateway(Request $request, CinetPayService $cinetPay): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id')
            ?? $request->query('payment_token')
            ?? $request->query('transaction_id')
            ?? $request->query('cpm_trans_id')
            ?? '');

        $topup = $this->resolveTopup($token);

        if (! $topup) {
            return redirect()->route('visitor.wallet.index')
                ->with('error', 'Recharge introuvable. Contactez le support si vous avez été débité.');
        }

        if ($topup->status === WalletTopup::STATUS_COMPLETED) {
            session()->forget('pending_wallet_topup_id');

            return redirect()->route('visitor.wallet.index')->with('success', 'Votre solde a été rechargé !');
        }

        $statusResult = $cinetPay->checkPaymentStatus($topup->gateway_txn_id);

        if ($statusResult['success']) {
            $this->markCompleted($topup);
            session()->forget('pending_wallet_topup_id');

            return redirect()->route('visitor.wallet.index')->with('success', 'Votre solde a été rechargé !');
        }

        if (($statusResult['status'] ?? '') === 'PENDING') {
            return redirect()->route('visitor.wallet.index')
                ->with('info', 'Votre paiement est en cours de traitement. Votre solde sera mis à jour dès confirmation.');
        }

        $this->markFailed($topup, 'Retour CinetPay : statut '.($statusResult['status'] ?? '?'));

        return redirect()->route('visitor.wallet.index')->with('error', 'Le paiement n\'a pas abouti. Vous pouvez réessayer.');
    }

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

        $topup = WalletTopup::where('gateway_txn_id', $token)->first();

        if (! $topup) {
            return response()->json(['ok' => false, 'message' => 'Recharge introuvable'], 404);
        }

        if ($topup->status === WalletTopup::STATUS_COMPLETED) {
            return response()->json(['ok' => true, 'message' => 'Déjà traité']);
        }

        $statusResult = $cinetPay->checkPaymentStatus($topup->gateway_txn_id);

        if ($statusResult['success']) {
            $this->markCompleted($topup);

            return response()->json(['ok' => true]);
        }

        $this->markFailed($topup, 'Webhook CinetPay : statut '.($statusResult['status'] ?? '?'));

        return response()->json(['ok' => false, 'message' => 'Paiement non confirmé']);
    }

    /**
     * Verrouillée + transactionnelle, même principe que ReservationPaymentController::markCompleted() :
     * webhook et retour navigateur peuvent être appelés quasi simultanément.
     */
    protected function markCompleted(WalletTopup $topup): void
    {
        DB::transaction(function () use ($topup) {
            $topup = WalletTopup::whereKey($topup->id)->lockForUpdate()->firstOrFail();
            if ($topup->status === WalletTopup::STATUS_COMPLETED) {
                return;
            }

            $topup->update(['status' => WalletTopup::STATUS_COMPLETED, 'paid_at' => now()]);

            app(WalletService::class)->creditTopup($topup);
        });
    }

    protected function markFailed(WalletTopup $topup, ?string $reason = null): void
    {
        $topup->update([
            'status' => WalletTopup::STATUS_FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    protected function resolveTopup(string $token): ?WalletTopup
    {
        if ($token !== '') {
            $topup = WalletTopup::where('gateway_txn_id', $token)->first();
            if ($topup) {
                return $topup;
            }
        }

        $topupId = session('pending_wallet_topup_id');
        if ($topupId) {
            return WalletTopup::find($topupId);
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
