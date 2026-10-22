<?php

namespace App\Http\Controllers;

use App\Models\ArtCommission;
use App\Models\ArtistFollow;
use App\Models\ArtistProfile;
use App\Models\Artwork;
use App\Models\ArtworkOffer;
use App\Models\ArtworkOrder;
use App\Services\ArtCommissionService;
use App\Services\ArtSalesService;
use App\Services\CinetPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Art & Créations côté client : page artiste et abonnement, devis d'achat en direct, offres, réception
 * et retour d'une commande, commandes sur mesure (demande, paiement, esquisse).
 */
class ArtMarketController extends Controller
{
    public function artist(string $slug): View
    {
        $profile = ArtistProfile::where('slug', $slug)->with('provider')->firstOrFail();
        abort_unless($profile->provider && $profile->provider->status === 'active', 404);
        $works = Artwork::where('provider_id', $profile->provider_id)->whereIn('status', [Artwork::STATUS_PUBLISHED, Artwork::STATUS_SOLD])
            ->orderByRaw("status = 'sold'")->latest()->get();

        return view('art-marketplace.artist', [
            'profile' => $profile, 'works' => $works,
            'following' => Auth::check() && ArtistFollow::where('user_id', Auth::id())->where('provider_id', $profile->provider_id)->exists(),
            'followers' => $profile->followersCount(),
        ]);
    }

    public function follow(string $slug): RedirectResponse
    {
        $profile = ArtistProfile::where('slug', $slug)->firstOrFail();
        $existing = ArtistFollow::where('user_id', Auth::id())->where('provider_id', $profile->provider_id)->first();
        $existing ? $existing->delete() : ArtistFollow::create(['user_id' => Auth::id(), 'provider_id' => $profile->provider_id]);

        return back()->with('success', $existing ? __('Vous ne suivez plus :a.', ['a' => $profile->display_name]) : __('Vous suivez :a : vous serez prévenu de chaque nouvelle œuvre.', ['a' => $profile->display_name]));
    }

    /** Devis en direct du module d'achat. */
    public function quote(Request $request, Artwork $artwork, ArtSalesService $sales): JsonResponse
    {
        $in = $request->validate([
            'edition_id' => ['nullable', 'integer'], 'offer_id' => ['nullable', 'integer'], 'delivery_mode' => ['nullable', 'in:pickup,abidjan,country'],
            'insurance' => ['nullable', 'boolean'], 'plan' => ['nullable', 'in:full,layaway'],
        ]);
        try {
            $q = $sales->quote($artwork, Auth::user(), $in + ['insurance' => $request->boolean('insurance')]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'lines' => $q['lines'], 'total' => $q['total'], 'installments' => $q['installments']]);
    }

    public function makeOffer(Request $request, Artwork $artwork, ArtSalesService $sales): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'visitor', 403);
        $d = $request->validate(['amount_xof' => ['required', 'integer', 'min:1000'], 'message' => ['nullable', 'string', 'max:500']]);
        try {
            $offer = $sales->makeOffer($artwork, Auth::user(), (int) $d['amount_xof'], $d['message'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['offer' => $e->getMessage()], 'art');
        }

        return back()->with($offer->status === 'declined' ? 'error' : 'success', $offer->status === 'declined'
            ? __('L\'artiste n\'accepte pas d\'offre à ce niveau de prix. Vous pouvez proposer davantage.')
            : __('Offre envoyée : l\'artiste vous répond rapidement.'));
    }

    public function acceptCounter(ArtworkOffer $offer, ArtSalesService $sales): RedirectResponse
    {
        abort_unless((int) $offer->user_id === (int) Auth::id(), 404);
        try {
            $sales->acceptCounter($offer);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->to(route('art.show', $offer->artwork->slug).'?offre='.$offer->id.'#acheter')->with('success', __('Contre-proposition acceptée : réglez maintenant au prix convenu.'));
    }

    public function withdrawOffer(ArtworkOffer $offer): RedirectResponse
    {
        abort_unless((int) $offer->user_id === (int) Auth::id(), 404);
        if (in_array($offer->status, ['pending', 'countered', 'accepted'], true)) {
            $offer->update(['status' => 'withdrawn']);
        }

        return back()->with('success', __('Offre retirée.'));
    }

    public function confirmReceipt(ArtworkOrder $artworkOrder, ArtSalesService $sales): RedirectResponse
    {
        abort_unless((int) $artworkOrder->buyer_user_id === (int) Auth::id(), 404);
        try {
            $sales->confirmReceipt($artworkOrder);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Réception confirmée. Vous disposez de :n jours pour demander un retour.', ['n' => ArtworkOrder::RETURN_DAYS]));
    }

    public function requestReturn(Request $request, ArtworkOrder $artworkOrder, ArtSalesService $sales): RedirectResponse
    {
        abort_unless((int) $artworkOrder->buyer_user_id === (int) Auth::id(), 404);
        $reason = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']])['reason'];
        try {
            $sales->requestReturn($artworkOrder, $reason);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Demande de retour envoyée. Renvoyez l\'œuvre dans son emballage d\'origine ; le remboursement est fait à réception par l\'artiste.'));
    }

    /* ── Sur mesure ── */

    public function commissionForm(string $slug): View
    {
        $profile = ArtistProfile::where('slug', $slug)->firstOrFail();
        abort_unless($profile->accepts_commissions, 404);

        return view('art-marketplace.commission-form', compact('profile'));
    }

    public function requestCommission(Request $request, string $slug, ArtCommissionService $service): RedirectResponse
    {
        $profile = ArtistProfile::where('slug', $slug)->with('provider')->firstOrFail();
        abort_unless(Auth::user()->role === 'visitor', 403);
        $d = $request->validate([
            'subject' => ['required', 'string', 'max:200'], 'brief' => ['required', 'string', 'min:20', 'max:3000'],
            'format' => ['nullable', 'string', 'max:80'], 'medium' => ['nullable', 'string', 'max:80'], 'palette' => ['nullable', 'string', 'max:120'],
            'budget_xof' => ['nullable', 'integer', 'min:0'], 'wanted_by' => ['nullable', 'date', 'after:today'],
            'references' => ['nullable', 'array', 'max:4'], 'references.*' => ['image', 'max:6144'],
        ], ['brief.min' => __('Décrivez votre projet en quelques phrases (20 caractères minimum).')]);
        $refs = collect($request->file('references', []))->map(fn ($f) => app(\App\Services\ImageUploadSecurityService::class)->store($f, 'art-commissions/'.$profile->provider_id, 'reference'))->all();
        try {
            $service->request($profile->provider, Auth::user(), collect($d)->except('references')->all() + ['references' => $refs ?: null]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['commission' => $e->getMessage()], 'art');
        }

        return redirect()->route('visitor.art-commissions.index')->with('success', __('Demande envoyée à :a : vous recevrez son devis ici.', ['a' => $profile->display_name]));
    }

    public function myCommissions(): View
    {
        return view('visitor.art-commissions.index', [
            'commissions' => ArtCommission::with('provider')->where('user_id', Auth::id())->latest()->paginate(10),
            'offers' => ArtworkOffer::with('artwork')->where('user_id', Auth::id())->latest()->take(20)->get(),
        ]);
    }

    private function ownCommission(ArtCommission $c): void
    {
        abort_unless((int) $c->user_id === (int) Auth::id(), 404);
    }

    public function payCommission(Request $request, ArtCommission $artCommission, ArtCommissionService $service, CinetPayService $cinetPay): RedirectResponse
    {
        $this->ownCommission($artCommission);
        $method = $request->validate(['payment_method' => ['required', 'in:cinetpay,wallet']])['payment_method'];
        $stage = $artCommission->dueStage();
        abort_unless($stage, 422);
        if ($method === 'wallet') {
            try {
                $service->payFromWallet($artCommission, Auth::user());
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }

            return back()->with('success', $stage === 'deposit' ? __('Acompte versé : l\'artiste prépare l\'esquisse.') : __('Solde réglé : l\'artiste finalise votre œuvre.'));
        }
        if (! $cinetPay->isConfigured()) {
            return back()->with('error', __('Le paiement en ligne n\'est pas encore disponible : payez avec votre solde.'));
        }
        $user = Auth::user();
        $result = $cinetPay->initPayment([
            'amount' => $artCommission->dueAmount(), 'designation' => 'Sur mesure — '.$artCommission->subject,
            'client_first_name' => $user->first_name, 'client_last_name' => $user->last_name, 'client_email' => $user->email,
            'success_url' => route('art-commission.payment.return'), 'failed_url' => route('art-commission.payment.return'), 'notify_url' => route('art-commission.payment.webhook'),
        ]);
        if (! $result['success']) {
            return back()->with('error', $result['message'] ?? __('Erreur lors de l\'initialisation du paiement.'));
        }
        $artCommission->update(['pending_stage' => $stage, 'gateway_txn_id' => $result['merchant_transaction_id']]);

        return redirect()->away($result['payment_url']);
    }

    public function commissionReturn(Request $request, CinetPayService $cinetPay, ArtCommissionService $service): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id') ?? $request->query('transaction_id') ?? $request->query('cpm_trans_id') ?? '');
        $c = $token !== '' ? ArtCommission::where('gateway_txn_id', $token)->first() : null;
        if ($c && $c->pending_stage && ($cinetPay->checkPaymentStatus($token)['success'] ?? false)) {
            $service->markPaid($c, $c->pending_stage, $token);
        }

        return redirect()->route('visitor.art-commissions.index');
    }

    public function commissionWebhook(Request $request, CinetPayService $cinetPay, ArtCommissionService $service): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id') ?? $request->input('cpm_trans_id') ?? '');
        $c = $token !== '' ? ArtCommission::where('gateway_txn_id', $token)->first() : null;
        if (! $c) {
            return response()->json(['ok' => false], 404);
        }
        if ($c->pending_stage && ($cinetPay->checkPaymentStatus($token)['success'] ?? false)) {
            $service->markPaid($c, $c->pending_stage, $token);
        }

        return response()->json(['ok' => true]);
    }

    public function sketchDecision(Request $request, ArtCommission $artCommission, ArtCommissionService $service): RedirectResponse
    {
        $this->ownCommission($artCommission);
        $d = $request->validate(['decision' => ['required', 'in:approve,revise'], 'note' => ['required_if:decision,revise', 'nullable', 'string', 'max:1000']]);
        try {
            $d['decision'] === 'approve' ? $service->approveSketch($artCommission) : $service->requestRevision($artCommission, $d['note']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $d['decision'] === 'approve' ? __('Esquisse validée : réglez le solde pour la réalisation.') : __('Retouches demandées à l\'artiste.'));
    }

    public function cancelCommission(ArtCommission $artCommission, ArtCommissionService $service): RedirectResponse
    {
        $this->ownCommission($artCommission);
        try {
            $service->cancel($artCommission, false);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Demande annulée.'));
    }
}
