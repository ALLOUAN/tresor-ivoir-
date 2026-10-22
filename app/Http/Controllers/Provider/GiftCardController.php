<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\Provider;
use App\Services\GiftCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Bons cadeaux de l'établissement : activation de la vente, liste, validation d'un bon à la caisse. */
class GiftCardController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUser(Auth::id());
        abort_unless($provider, 404);

        return $provider;
    }

    public function index(Request $request): View
    {
        $provider = $this->provider();
        $cards = GiftCard::where('provider_id', $provider->id)->where('status', '!=', GiftCard::STATUS_PENDING)->latest('paid_at')->paginate(20);
        $found = null;
        if ($code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('code')))) {
            $found = GiftCard::where('provider_id', $provider->id)->where('code', $code)->first() ?? false;
        }
        $totals = [
            'sold' => (int) GiftCard::where('provider_id', $provider->id)->whereIn('status', [GiftCard::STATUS_ACTIVE, GiftCard::STATUS_REDEEMED, GiftCard::STATUS_EXPIRED])->sum('amount_xof'),
            'active' => (int) GiftCard::where('provider_id', $provider->id)->where('status', GiftCard::STATUS_ACTIVE)->sum('amount_xof'),
        ];

        return view('provider.gift-cards.index', compact('provider', 'cards', 'found', 'totals'));
    }

    public function toggle(): RedirectResponse
    {
        $provider = $this->provider();
        abort_unless(Provider::isOwner(Auth::id()), 403);
        $provider->update(['gift_cards_enabled' => ! $provider->gift_cards_enabled]);

        return back()->with('success', $provider->gift_cards_enabled ? 'La vente de bons cadeaux est activée sur votre fiche.' : 'La vente de bons cadeaux est désactivée.');
    }

    public function redeem(GiftCard $giftCard, GiftCardService $cards): RedirectResponse
    {
        $provider = $this->provider();
        abort_unless((int) $giftCard->provider_id === (int) $provider->id, 404);
        try {
            $cards->redeem($giftCard, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('provider.gift-cards.index')->with('success', 'Bon de '.number_format($giftCard->amount_xof, 0, ',', ' ').' FCFA validé : le montant est crédité sur votre portefeuille.');
    }
}
