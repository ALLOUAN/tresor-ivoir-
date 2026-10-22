@extends('layouts.app')

@section('title', __('Confirmation paiement'))
@section('page-title', __('Paiement confirmé'))

@section('content')
<div class="max-w-2xl bg-green-900 border border-slate-800 rounded-xl p-5">
    <h2 class="text-white font-semibold mb-2">{{ __('Paiement validé') }}</h2>
    <p class="text-slate-400 text-sm mb-4">{{ __('Votre transaction est confirmée et votre facture a été générée.') }}</p>
    <div class="space-y-2 text-sm">
        <p class="text-slate-300">{{ __('Montant:') }} <span class="text-white font-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</span></p>
        <p class="text-slate-300">{{ __('Transaction:') }} <span class="text-white font-semibold">{{ $payment->gateway_txn_id ?: $payment->uuid }}</span></p>
        <p class="text-slate-300">{{ __('Forfait:') }} <span class="text-white font-semibold">{{ strtoupper($payment->subscription->plan->code ?? 'N/A') }}</span></p>
        <p class="text-slate-300">{{ __('Facture:') }} <span class="text-white font-semibold">{{ $payment->invoice->number ?? __('En cours') }}</span></p>
    </div>
    <a href="{{ route('provider.dashboard') }}" class="inline-flex mt-5 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
        {{ __('Retour au tableau de bord') }}
    </a>
</div>
@endsection
