<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Provider;
use App\Models\ProviderDebt;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $platformWallet = Wallet::platform();

        $stats = [
            'platform_balance_xof' => $platformWallet->balance_available_xof,
            'commission_revenue_xof' => (int) WalletTransaction::where('type', WalletTransaction::TYPE_COMMISSION)->sum('amount_xof'),
            'provider_pending_xof' => (int) Wallet::query()->whereNotNull('provider_id')->sum('balance_pending_xof'),
            'provider_available_xof' => (int) Wallet::query()->whereNotNull('provider_id')->sum('balance_available_xof'),
            'payouts_pending_count' => PayoutRequest::where('status', PayoutRequest::STATUS_PENDING)->count(),
            'debts_outstanding_xof' => (int) ProviderDebt::outstanding()->sum('amount_xof'),
            // Argent détenu pour le compte des clients (recharges non dépensées) — à ne jamais
            // compter comme revenu plateforme, distinct du solde plateforme ci-dessus.
            'client_balances_xof' => (int) Wallet::query()->whereNotNull('user_id')->sum('balance_available_xof'),
        ];

        $providerId = $request->query('provider_id');
        $type = (string) $request->query('type', '');
        $from = $this->sanitizeDate($request->query('from'));
        $to = $this->sanitizeDate($request->query('to'));

        $transactions = WalletTransaction::query()
            ->with(['wallet.provider', 'reservation'])
            ->when($providerId, fn ($q) => $q->whereHas('wallet', fn ($w) => $w->where('provider_id', $providerId)))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $providers = Provider::orderBy('name')->get(['id', 'name']);

        return view('admin.finance.wallet.index', [
            'stats' => $stats,
            'transactions' => $transactions,
            'providers' => $providers,
            'providerId' => $providerId,
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $providerId = $request->query('provider_id');
        $type = (string) $request->query('type', '');
        $from = $this->sanitizeDate($request->query('from'));
        $to = $this->sanitizeDate($request->query('to'));

        $rows = WalletTransaction::query()
            ->with(['wallet.provider', 'reservation'])
            ->when($providerId, fn ($q) => $q->whereHas('wallet', fn ($w) => $w->where('provider_id', $providerId)))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('id')
            ->get();

        $filename = 'wallet-transactions-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['id', 'date', 'type', 'statut', 'wallet', 'prestataire', 'montant_xof', 'solde_avant_xof', 'solde_apres_xof', 'reservation', 'description'], ';');
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->id,
                    $t->created_at?->format('Y-m-d H:i:s'),
                    $t->labelForType(),
                    $t->labelForStatus(),
                    $t->wallet?->provider_id ? 'Prestataire' : 'Plateforme',
                    $t->wallet?->provider?->name,
                    $t->amount_xof,
                    $t->balance_before_xof,
                    $t->balance_after_xof,
                    $t->reservation?->reference,
                    $t->description,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function providerShow(Provider $provider): View
    {
        $wallet = Wallet::forProvider($provider);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with('reservation')
            ->latest()
            ->paginate(30);

        $debts = ProviderDebt::where('provider_id', $provider->id)->latest()->get();
        $payoutRequests = PayoutRequest::where('provider_id', $provider->id)->latest()->get();

        return view('admin.finance.wallet.provider-show', compact('provider', 'wallet', 'transactions', 'debts', 'payoutRequests'));
    }

    public function userShow(User $user): View
    {
        $wallet = Wallet::forUser($user);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with('reservation')
            ->latest()
            ->paginate(30);

        $payoutRequests = PayoutRequest::where('user_id', $user->id)->latest()->get();

        return view('admin.finance.wallet.user-show', compact('user', 'wallet', 'transactions', 'payoutRequests'));
    }

    public function payouts(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $holder = (string) $request->query('holder', '');
        $riskLevel = (string) $request->query('risk_level', '');

        $payoutRequests = PayoutRequest::query()
            ->with(['provider', 'user', 'reviewedBy', 'auditLogs.actor'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($holder === 'provider', fn ($q) => $q->whereNotNull('provider_id'))
            ->when($holder === 'client', fn ($q) => $q->whereNotNull('user_id'))
            ->when($riskLevel !== '', fn ($q) => $q->where('risk_level', $riskLevel))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.finance.wallet.payouts', compact('payoutRequests', 'status', 'holder', 'riskLevel'));
    }

    public function payoutApprove(Request $request, PayoutRequest $payoutRequest, WalletService $wallet): RedirectResponse
    {
        try {
            $wallet->approvePayout($payoutRequest, Auth::user(), $request->input('admin_note'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Demande de retrait approuvée.');
    }

    public function payoutReject(Request $request, PayoutRequest $payoutRequest, WalletService $wallet): RedirectResponse
    {
        try {
            $wallet->rejectPayout($payoutRequest, Auth::user(), $request->input('admin_note'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Demande de retrait refusée.');
    }

    public function payoutMarkPaid(Request $request, PayoutRequest $payoutRequest, WalletService $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:255'],
        ]);

        try {
            $wallet->markPayoutPaid($payoutRequest, Auth::user(), $validated['payment_reference']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Retrait marqué comme payé.');
    }

    public function refund(Request $request, Reservation $reservation, WalletService $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'amount_xof' => ['required', 'integer', 'min:1', 'max:'.max(1, (int) $reservation->deposit_amount_xof)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($reservation->payment_status !== Reservation::PAYMENT_DEPOSIT_PAID) {
            return back()->with('error', "Cette réservation n'a pas d'acompte payé à rembourser.");
        }

        $wallet->recordManualRefund($reservation, (int) $validated['amount_xof'], Auth::user(), $validated['note'] ?? null);

        return back()->with('success', 'Remboursement enregistré.');
    }

    private function sanitizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }
}
