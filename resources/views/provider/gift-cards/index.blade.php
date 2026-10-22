@extends('layouts.app')

@section('title', 'Bons cadeaux')
@section('page-title', 'Bons cadeaux')

@section('content')
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <p class="text-slate-400 text-xs">Vendus au total</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($totals['sold'], 0, ',', ' ') }} XOF</p>
        <p class="text-slate-500 text-xs mt-1">dont {{ number_format($totals['active'], 0, ',', ' ') }} XOF encore à utiliser</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 lg:col-span-2 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-white font-semibold">{{ $provider->gift_cards_enabled ? 'Vente activée' : 'Vente désactivée' }}</p>
            <p class="text-slate-400 text-sm">Un bouton « Offrir un bon cadeau » apparaît sur votre fiche. Le montant (moins la commission) vous revient quand le bon est utilisé, ou à son expiration.</p>
            @if($provider->gift_cards_enabled)
                <a href="{{ route('gift-cards.show', $provider->slug) }}" target="_blank" class="text-orange-300 hover:text-orange-200 text-xs">Voir la page publique →</a>
            @endif
        </div>
        @if(\App\Models\Provider::isOwner(auth()->id()))
            <form method="POST" action="{{ route('provider.gift-cards.toggle') }}">
                @csrf
                <button class="text-sm font-semibold px-4 py-2 rounded-lg {{ $provider->gift_cards_enabled ? 'bg-slate-700 text-white' : 'bg-orange-500 text-black' }}">{{ $provider->gift_cards_enabled ? 'Désactiver' : 'Activer la vente' }}</button>
            </form>
        @endif
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold mb-2"><i class="fas fa-cash-register text-orange-400 mr-1"></i> Valider un bon à la caisse</h2>
    <form method="GET" action="{{ route('provider.gift-cards.index') }}" class="flex gap-2 max-w-md">
        <input name="code" required value="{{ request('code') }}" placeholder="Code du bon (ex. ABCD-EFGH-JKLM)" autocapitalize="characters" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 uppercase">
        <button class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Vérifier</button>
    </form>
    @if($found === false)
        <p class="mt-3 text-rose-300 text-sm">Code inconnu pour votre établissement.</p>
    @elseif($found)
        <div class="mt-4 rounded-xl border px-4 py-3 {{ $found->isUsable() ? 'border-emerald-500/50 bg-emerald-900/20' : 'border-amber-500/50 bg-amber-900/20' }}">
            <p class="text-white font-semibold">{{ number_format($found->amount_xof, 0, ',', ' ') }} XOF · {{ $found->recipient_name }} <span class="text-slate-400 font-normal text-sm">(offert par {{ $found->buyer_name }})</span></p>
            <p class="text-slate-300 text-sm">{{ $found->statusLabel() }}@if($found->expires_at) · valable jusqu'au {{ $found->expires_at->format('d/m/Y') }}@endif @if($found->redeemed_at) · utilisé le {{ $found->redeemed_at->format('d/m/Y H:i') }}@endif</p>
            @if($found->isUsable())
                <form method="POST" action="{{ route('provider.gift-cards.redeem', $found) }}" class="mt-3" onsubmit="return confirm('Valider ce bon ? Il ne pourra plus être utilisé.');">
                    @csrf
                    <button class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-lg"><i class="fas fa-check"></i> Valider l'utilisation</button>
                </form>
            @endif
        </div>
    @endif
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-x-auto">
    <table class="w-full text-sm min-w-[640px]">
        <thead><tr class="text-left text-slate-400 border-b border-slate-800"><th class="px-4 py-3">Code</th><th class="px-4 py-3">Bénéficiaire</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Expire le</th></tr></thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($cards as $c)
                <tr class="text-slate-200">
                    <td class="px-4 py-3 font-mono text-xs">{{ $c->formattedCode() }}</td>
                    <td class="px-4 py-3">{{ $c->recipient_name }}<br><span class="text-slate-500 text-xs">de {{ $c->buyer_name }}</span></td>
                    <td class="px-4 py-3">{{ number_format($c->amount_xof, 0, ',', ' ') }} XOF</td>
                    <td class="px-4 py-3">{{ $c->statusLabel() }}</td>
                    <td class="px-4 py-3">{{ $c->expires_at?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Aucun bon vendu pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $cards->links() }}</div>
@endsection
