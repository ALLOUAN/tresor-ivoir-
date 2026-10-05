<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\AccountSecurityEvent;
use App\Models\PayoutRequest;
use App\Models\Provider;
use App\Models\ProviderDebt;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\AccountSecurityEventLogger;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(): View
    {
        $provider = $this->provider();
        $wallet = Wallet::forProvider($provider);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with('reservation')
            ->latest()
            ->paginate(20);

        $outstandingDebt = (int) ProviderDebt::outstanding()->where('provider_id', $provider->id)->sum('amount_xof');
        $payoutRequests = PayoutRequest::where('provider_id', $provider->id)->latest()->take(10)->get();

        return view('provider.wallet.index', [
            'provider' => $provider,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'outstandingDebt' => $outstandingDebt,
            'payoutRequests' => $payoutRequests,
            'netAvailable' => max(0, $wallet->balance_available_xof - $outstandingDebt),
        ]);
    }

    public function requestPayout(Request $request, WalletService $walletService): RedirectResponse
    {
        $provider = $this->provider();

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
            $payout = $walletService->requestPayout(
                $provider,
                (int) $validated['amount_xof'],
                $validated['method'],
                $validated['payout_destination'],
                $request->ip(),
                $request->userAgent()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        // Le changement de destination est loggué avant mise à jour, pour que la comparaison
        // faite par le moteur de risque (déjà exécutée dans requestPayout ci-dessus) porte sur
        // l'ancienne valeur enregistrée, pas la nouvelle qu'on écrit maintenant.
        if ($provider->payout_account_number && $provider->payout_account_number !== $validated['payout_destination'] && $provider->user) {
            AccountSecurityEventLogger::log($provider->user, AccountSecurityEvent::TYPE_PAYOUT_DESTINATION_CHANGED, $request);
        }

        // Mémorise les coordonnées pour préremplir les prochaines demandes.
        $provider->update([
            'payout_method' => $validated['method'],
            'payout_account_number' => $validated['payout_destination'],
        ]);

        return match ($payout->status) {
            \App\Models\PayoutRequest::STATUS_PENDING_VERIFICATION => back()->with('success', 'Un code de vérification vous a été envoyé par email pour confirmer ce retrait.'),
            \App\Models\PayoutRequest::STATUS_SUSPENDED => back()->with('error', 'Cette demande a été suspendue pour vérification par notre équipe de sécurité.'),
            default => back()->with('success', 'Demande de retrait envoyée — elle sera examinée par notre équipe.'),
        };
    }

    public function verifyPayoutOtp(Request $request, PayoutRequest $payoutRequest, WalletService $walletService): RedirectResponse
    {
        $provider = $this->provider();
        abort_unless((int) $payoutRequest->provider_id === (int) $provider->id, 403);

        $validated = $request->validate(['code' => ['required', 'digits:6']]);

        $result = $walletService->verifyPayoutOtp($payoutRequest, $validated['code']);

        if ($result !== true) {
            return back()->with('error', $result);
        }

        return back()->with('success', 'Retrait confirmé — il sera traité par notre équipe.');
    }

    public function resendPayoutOtp(PayoutRequest $payoutRequest, \App\Services\PayoutOtpService $otpService): RedirectResponse
    {
        $provider = $this->provider();
        abort_unless((int) $payoutRequest->provider_id === (int) $provider->id, 403);
        abort_unless($payoutRequest->status === PayoutRequest::STATUS_PENDING_VERIFICATION, 422);

        $otpService->send($payoutRequest);

        return back()->with('success', 'Un nouveau code vous a été envoyé.');
    }

    private function provider(): Provider
    {
        return Provider::where('user_id', Auth::id())->firstOrFail();
    }
}
