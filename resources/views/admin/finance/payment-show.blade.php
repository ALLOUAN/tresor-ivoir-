@extends('layouts.app')

@section('title', 'Détail paiement')
@section('page-title', 'Détail de transaction')

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-white font-semibold">Transaction {{ $payment->gateway_txn_id ?: '#'.$payment->id }}</h2>
        <a href="{{ route('admin.payments.index') }}" class="text-amber-400 text-sm">Retour aux paiements</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="bg-slate-800 rounded-lg p-4">
            <p class="text-slate-500 text-xs">Prestataire</p>
            <p class="text-white font-semibold">{{ $payment->provider->name ?? '—' }}</p>
            <p class="text-slate-400 text-xs mt-1">{{ $payment->provider->user->email ?? '—' }}</p>
        </div>
        <div class="bg-slate-800 rounded-lg p-4">
            <p class="text-slate-500 text-xs">Montant</p>
            <p class="text-white font-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</p>
        </div>
        <div class="bg-slate-800 rounded-lg p-4">
            <p class="text-slate-500 text-xs">Statut</p>
            <p class="text-white font-semibold">{{ $payment->status }}</p>
            <p class="text-slate-400 text-xs mt-1">Méthode: {{ $payment->method }}</p>
        </div>
        <div class="bg-slate-800 rounded-lg p-4">
            <p class="text-slate-500 text-xs">Abonnement</p>
            <p class="text-white font-semibold">{{ strtoupper($payment->subscription->plan->code ?? 'N/A') }}</p>
            <p class="text-slate-400 text-xs mt-1">{{ optional($payment->subscription->starts_at)->format('d/m/Y') }} - {{ optional($payment->subscription->ends_at)->format('d/m/Y') }}</p>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 md:col-span-2">
            <p class="text-slate-500 text-xs">Passerelle / Métadonnées</p>
            <p class="text-white">Gateway: {{ $payment->gateway }}</p>
            <p class="text-slate-300 mt-2 break-all">IP: {{ $payment->ip_address ?: '—' }}</p>
            <pre class="text-slate-400 text-xs mt-2 whitespace-pre-wrap">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @if($payment->invoice)
            <div class="bg-slate-800 rounded-lg p-4 md:col-span-2">
                <p class="text-slate-500 text-xs">Facture automatique</p>
                <p class="text-white">N° {{ $payment->invoice->number }} - TTC {{ number_format((float) $payment->invoice->amount_ttc, 0, ',', ' ') }} FCFA</p>
            </div>
        @endif
    </div>
</div>
@endsection
