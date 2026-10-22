<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderPromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Codes promo de l'établissement (séjours, tables, activités, circuits…). */
class PromoCodeController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUser(Auth::id());
        abort_unless($provider, 404);

        return $provider;
    }

    public function index(): View
    {
        $provider = $this->provider();
        $codes = ProviderPromoCode::where('provider_id', $provider->id)->latest()->get();

        return view('provider.promo-codes.index', compact('provider', 'codes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->provider();
        $request->merge(['code' => ProviderPromoCode::normalize($request->input('code'))]);
        $v = $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:30', Rule::unique('provider_promo_codes')->where('provider_id', $provider->id)],
            'kind' => ['required', 'in:percent,amount'],
            'value' => ['required', 'integer', 'min:1', $request->input('kind') === 'percent' ? 'max:90' : 'max:5000000'],
            'min_amount_xof' => ['nullable', 'integer', 'min:0', 'max:50000000'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on', 'after_or_equal:today'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ], ['code.unique' => 'Vous avez déjà un code avec ce nom.'], ['value' => 'valeur de la remise', 'ends_on' => 'date de fin']);

        ProviderPromoCode::create([
            'provider_id' => $provider->id,
            'code' => $v['code'],
            'percent_off' => $v['kind'] === 'percent' ? $v['value'] : null,
            'amount_off_xof' => $v['kind'] === 'amount' ? $v['value'] : null,
            'min_amount_xof' => ($v['min_amount_xof'] ?? null) ?: null,
            'starts_on' => $v['starts_on'] ?? null,
            'ends_on' => $v['ends_on'] ?? null,
            'max_uses' => $v['max_uses'] ?? null,
        ]);

        return back()->with('success', 'Code '.$v['code'].' créé. Partagez-le sur vos réseaux, il sera accepté à la réservation.');
    }

    public function toggle(ProviderPromoCode $promoCode): RedirectResponse
    {
        abort_unless((int) $promoCode->provider_id === (int) $this->provider()->id, 404);
        $promoCode->update(['is_active' => ! $promoCode->is_active]);

        return back()->with('success', $promoCode->is_active ? 'Code réactivé.' : 'Code désactivé.');
    }
}
