@extends('layouts.app')

@section('title', 'Paiement')
@section('page-title', 'Finaliser le paiement')

@section('content')
<div class="max-w-2xl bg-slate-900 border border-slate-800 rounded-xl p-5">
    <h2 class="text-white font-semibold mb-1">Forfait {{ strtoupper($plan->code) }} - {{ $plan->name_fr }}</h2>
    <p class="text-slate-400 text-sm mb-4">{{ $provider->name }}</p>

    <form method="POST" action="{{ route('provider.billing.pay', $plan) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-slate-300 text-xs mb-1">Cycle</label>
            <select name="billing_cycle" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="monthly">Mensuel - {{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA</option>
                <option value="yearly">Annuel - {{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} FCFA</option>
            </select>
        </div>
        <div>
            <label class="block text-slate-300 text-xs mb-1">Passerelle</label>
            <select name="gateway" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="cinetpay">CinetPay</option>
                <option value="stripe">Stripe</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>
        <div>
            <label class="block text-slate-300 text-xs mb-1">Méthode</label>
            <select name="method" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="orange_money">Orange Money</option>
                <option value="mtn_momo">MTN MoMo</option>
                <option value="wave">Wave</option>
                <option value="moov_money">Moov Money</option>
                <option value="card">Carte bancaire</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>
        <button class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            Procéder au paiement sécurisé
        </button>
    </form>
</div>
@endsection
