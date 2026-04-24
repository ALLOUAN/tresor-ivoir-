@extends('layouts.app')

@section('title', 'Confirmation paiement')
@section('page-title', 'Paiement confirmé')

@section('content')
<div class="max-w-2xl bg-slate-900 border border-slate-800 rounded-xl p-5">
    <h2 class="text-white font-semibold mb-2">Paiement validé</h2>
    <p class="text-slate-400 text-sm mb-4">Votre transaction est confirmée et votre facture a été générée.</p>
    <div class="space-y-2 text-sm">
        <p class="text-slate-300">Montant: <span class="text-white font-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</span></p>
        <p class="text-slate-300">Transaction: <span class="text-white font-semibold">{{ $payment->gateway_txn_id ?: $payment->uuid }}</span></p>
        <p class="text-slate-300">Forfait: <span class="text-white font-semibold">{{ strtoupper($payment->subscription->plan->code ?? 'N/A') }}</span></p>
        <p class="text-slate-300">Facture: <span class="text-white font-semibold">{{ $payment->invoice->number ?? 'En cours' }}</span></p>
    </div>
    <a href="{{ route('provider.dashboard') }}" class="inline-flex mt-5 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
        Retour au tableau de bord
    </a>
</div>
@endsection
