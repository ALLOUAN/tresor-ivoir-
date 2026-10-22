<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Provider;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Litiges sur une réservation payée : ouverture par le client, réponse de l'établissement,
 * arbitrage par l'administration (avec remboursement éventuel via WalletService).
 */
class DisputeController extends Controller
{
    /* ── Client ─────────────────────────────────────────────────────────── */

    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->isOwnedBy(Auth::user()), 404);

        $validated = $request->validate([
            'reason' => ['required', 'in:'.implode(',', array_keys(Dispute::REASONS))],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
        ], [], ['reason' => 'motif', 'description' => 'description']);

        if (! Dispute::canOpenFor($reservation)) {
            return back()->with('error', 'Un problème ne peut être signalé que sur une réservation payée, à partir du jour d\'arrivée et jusqu\'à '.Dispute::OPEN_WINDOW_DAYS.' jours après le départ.');
        }

        $dispute = Dispute::create([
            'reservation_id' => $reservation->id,
            'user_id' => Auth::id(),
            'provider_id' => $reservation->provider_id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => Dispute::STATUS_OPEN,
        ]);

        $reservation->provider?->user?->notify(new VisitorSystemNotification(
            'Litige ouvert par un client',
            'Le client '.$reservation->full_name.' signale un problème sur la réservation '.$reservation->reference.' : '.$dispute->reasonLabel().'. Donnez votre version des faits.',
            route('provider.disputes.index'),
        ));
        Notification::send($this->admins(), new VisitorSystemNotification(
            'Nouveau litige',
            $reservation->reference.' — '.$dispute->reasonLabel(),
            route('admin.disputes.index'),
        ));

        return back()->with('success', __('Votre signalement est enregistré. Notre équipe l\'examine et vous répond sous 72 h ; le paiement à l\'établissement est suspendu en attendant.'));
    }

    /* ── Prestataire ────────────────────────────────────────────────────── */

    public function providerIndex(): View
    {
        $provider = Provider::forUserQuery(Auth::id())->firstOrFail();
        $disputes = Dispute::with('reservation')->where('provider_id', $provider->id)->latest()->paginate(20);

        return view('provider.disputes.index', compact('disputes'));
    }

    public function providerRespond(Request $request, Dispute $dispute): RedirectResponse
    {
        $provider = Provider::forUserQuery(Auth::id())->firstOrFail();
        abort_unless((int) $dispute->provider_id === (int) $provider->id, 403);
        abort_unless($dispute->isOpen(), 422);

        $validated = $request->validate(['provider_response' => ['required', 'string', 'min:10', 'max:3000']], [], ['provider_response' => 'réponse']);
        $dispute->update(['provider_response' => $validated['provider_response'], 'provider_responded_at' => now()]);

        Notification::send($this->admins(), new VisitorSystemNotification(
            'Réponse de l\'établissement à un litige',
            $dispute->reservation?->reference.' — '.$provider->name.' a répondu.',
            route('admin.disputes.index'),
        ));

        return back()->with('success', __('Réponse transmise à l\'équipe d\'arbitrage.'));
    }

    /* ── Administration ─────────────────────────────────────────────────── */

    public function adminIndex(Request $request): View
    {
        $status = array_key_exists((string) $request->query('statut'), Dispute::STATUS_LABELS) ? $request->query('statut') : Dispute::STATUS_OPEN;
        $disputes = Dispute::with(['reservation', 'provider', 'user', 'resolver'])
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $counts = Dispute::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.disputes.index', compact('disputes', 'status', 'counts'));
    }

    public function adminResolve(Request $request, Dispute $dispute, WalletService $wallets): RedirectResponse
    {
        abort_unless($dispute->isOpen(), 422);
        $reservation = $dispute->reservation;
        $maxRefund = max(0, (int) $reservation->deposit_amount_xof);

        $validated = $request->validate([
            'decision' => ['required', 'in:client,provider'],
            'refund_xof' => ['nullable', 'integer', 'min:0', 'max:'.max(1, $maxRefund)],
            'resolution' => ['required', 'string', 'max:2000'],
        ], [], ['refund_xof' => 'montant remboursé', 'resolution' => 'motif de la décision']);

        $forClient = $validated['decision'] === 'client';
        $refund = $forClient ? (int) ($validated['refund_xof'] ?? $maxRefund) : 0;

        DB::transaction(function () use ($dispute, $reservation, $forClient, $refund, $validated, $wallets) {
            if ($forClient && $refund > 0 && $reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID) {
                $wallets->recordManualRefund($reservation, $refund, Auth::user(), 'Litige #'.$dispute->id.' : '.$validated['resolution']);
            }
            $dispute->update([
                'status' => $forClient ? Dispute::STATUS_RESOLVED_CLIENT : Dispute::STATUS_RESOLVED_PROVIDER,
                'resolution' => $validated['resolution'].($refund > 0 ? ' — Remboursement : '.number_format($refund, 0, ',', ' ').' XOF.' : ''),
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);
        });

        $message = 'Décision sur le litige '.$reservation->reference.' : '.$dispute->fresh()->statusLabel().'. '.$validated['resolution'];
        $dispute->user?->notify(new VisitorSystemNotification('Litige traité', $message, route('visitor.reservations.show', $reservation)));
        $dispute->provider?->user?->notify(new VisitorSystemNotification('Litige traité', $message, route('provider.disputes.index')));

        return back()->with('success', 'Litige clôturé'.($refund > 0 ? ' et remboursement de '.number_format($refund, 0, ',', ' ').' XOF enregistré.' : '.'));
    }

    private function admins()
    {
        return User::query()->where('role', 'admin')->where('is_active', true)->get();
    }
}
