<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\Provider;
use App\Services\CinetPayService;
use App\Services\GiftCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Achat d'un bon cadeau pour un établissement (CinetPay ou solde du client). */
class GiftCardController extends Controller
{
    private function provider(string $slug): Provider
    {
        $provider = Provider::where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($provider->gift_cards_enabled, 404);

        return $provider;
    }

    public function show(string $slug): View
    {
        return view('gift-cards.show', ['provider' => $this->provider($slug), 'amounts' => GiftCard::AMOUNTS]);
    }

    public function store(Request $request, string $slug, CinetPayService $cinetPay, GiftCardService $cards): RedirectResponse
    {
        $provider = $this->provider($slug);
        $v = $request->validate([
            'amount_xof' => ['required', 'integer', 'min:5000', 'max:2000000'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cinetpay,wallet'],
        ], [], ['amount_xof' => 'montant', 'recipient_name' => 'nom du bénéficiaire', 'recipient_email' => 'e-mail du bénéficiaire']);

        $user = Auth::user();
        $card = GiftCard::create([
            'provider_id' => $provider->id, 'code' => GiftCard::newCode(), 'amount_xof' => (int) round($v['amount_xof'] / 5) * 5,
            'buyer_user_id' => $user->id, 'buyer_name' => $user->full_name, 'buyer_email' => $user->email,
            'recipient_name' => $v['recipient_name'], 'recipient_email' => $v['recipient_email'], 'message' => $v['message'] ?? null,
            'status' => GiftCard::STATUS_PENDING,
        ]);
        $cards->priceCard($card);

        if ($v['payment_method'] === 'wallet') {
            try {
                $cards->payFromWallet($card, $user);
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }

            return redirect()->route('gift-cards.show', $slug)->with('success', __('Bon cadeau envoyé à :name !', ['name' => $card->recipient_name]));
        }

        if (! $cinetPay->isConfigured()) {
            return back()->withInput()->with('error', __('Le paiement en ligne n\'est pas encore disponible : payez avec votre solde.'));
        }
        $res = $cinetPay->initPayment([
            'amount' => $card->amount_xof, 'designation' => 'Bon cadeau — '.$provider->name,
            'client_first_name' => $user->first_name, 'client_last_name' => $user->last_name, 'client_email' => $user->email,
            'success_url' => route('gift-cards.payment.return'), 'failed_url' => route('gift-cards.payment.return'),
            'notify_url' => route('gift-cards.payment.webhook'),
        ]);
        if (! $res['success']) {
            return back()->withInput()->with('error', $res['message'] ?? __('Erreur lors de l\'initialisation du paiement.'));
        }
        $card->update(['payment_method' => 'cinetpay', 'gateway_txn_id' => $res['merchant_transaction_id']]);
        session(['pending_gift_card_id' => $card->id]);

        return redirect()->away($res['payment_url']);
    }

    public function handleReturn(Request $request, CinetPayService $cinetPay, GiftCardService $cards): RedirectResponse
    {
        $token = (string) ($request->query('merchant_transaction_id') ?? $request->query('transaction_id') ?? '');
        $card = $token !== '' ? GiftCard::where('gateway_txn_id', $token)->first() : GiftCard::find(session('pending_gift_card_id'));
        abort_unless($card, 404);
        session()->forget('pending_gift_card_id');

        if ($card->status === GiftCard::STATUS_PENDING && $card->gateway_txn_id && ($cinetPay->checkPaymentStatus($card->gateway_txn_id)['success'] ?? false)) {
            $cards->markPaid($card, 'cinetpay');
        }
        $ok = $card->fresh()->status === GiftCard::STATUS_ACTIVE;

        return redirect()->route('gift-cards.show', $card->provider->slug)->with($ok ? 'success' : 'error', $ok
            ? __('Bon cadeau envoyé à :name !', ['name' => $card->recipient_name])
            : __('Le paiement n\'a pas abouti. Vous pouvez réessayer.'));
    }

    public function webhook(Request $request, CinetPayService $cinetPay, GiftCardService $cards): JsonResponse
    {
        $token = (string) ($request->input('merchant_transaction_id') ?? $request->input('cpm_trans_id') ?? '');
        $card = $token !== '' ? GiftCard::where('gateway_txn_id', $token)->first() : null;
        if (! $card) return response()->json(['ok' => false], 404);
        if ($card->status === GiftCard::STATUS_PENDING && ($cinetPay->checkPaymentStatus($token)['success'] ?? false)) {
            $cards->markPaid($card, 'cinetpay');
        }

        return response()->json(['ok' => $card->fresh()->status === GiftCard::STATUS_ACTIVE]);
    }
}
