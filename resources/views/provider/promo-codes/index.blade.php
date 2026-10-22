@extends('layouts.app')

@section('title', 'Codes promo')
@section('page-title', 'Codes promo')

@section('content')
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold mb-1"><i class="fas fa-tag text-orange-400 mr-1"></i> Nouveau code</h2>
    <p class="text-slate-400 text-sm mb-4">Le client saisit le code en réservant. Séjours : la remise est déduite du total (et donc de l'acompte). Tables, activités, circuits : elle est déduite automatiquement du montant que vous fixez en confirmant.</p>
    <form method="POST" action="{{ route('provider.promo-codes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @csrf
        <label class="text-xs text-slate-400">Code
            <input name="code" required maxlength="30" value="{{ old('code') }}" placeholder="PAQUES26" class="mt-1 w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 uppercase">
        </label>
        <div class="text-xs text-slate-400">Remise
            <div class="mt-1 flex gap-2">
                <input type="number" name="value" required min="1" value="{{ old('value', 10) }}" class="w-24 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <select name="kind" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-100">
                    <option value="percent" @selected(old('kind', 'percent') === 'percent')>%</option>
                    <option value="amount" @selected(old('kind') === 'amount')>FCFA</option>
                </select>
            </div>
        </div>
        <label class="text-xs text-slate-400">Montant minimum (FCFA)
            <input type="number" name="min_amount_xof" min="0" value="{{ old('min_amount_xof') }}" placeholder="Aucun" class="mt-1 w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </label>
        <label class="text-xs text-slate-400">Utilisations max.
            <input type="number" name="max_uses" min="1" value="{{ old('max_uses') }}" placeholder="Illimité" class="mt-1 w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </label>
        <label class="text-xs text-slate-400">Valable à partir du
            <input type="date" name="starts_on" value="{{ old('starts_on') }}" class="mt-1 w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </label>
        <label class="text-xs text-slate-400">Jusqu'au
            <input type="date" name="ends_on" value="{{ old('ends_on') }}" class="mt-1 w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </label>
        <div class="sm:col-span-2 flex items-end">
            <button class="bg-orange-500 hover:bg-orange-400 text-black text-sm font-semibold px-5 py-2.5 rounded-lg">Créer le code</button>
        </div>
    </form>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-x-auto">
    <table class="w-full text-sm min-w-[640px]">
        <thead><tr class="text-left text-slate-400 border-b border-slate-800"><th class="px-4 py-3">Code</th><th class="px-4 py-3">Remise</th><th class="px-4 py-3">Validité</th><th class="px-4 py-3">Utilisations</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($codes as $c)
                <tr class="text-slate-200 {{ $c->is_active ? '' : 'opacity-60' }}">
                    <td class="px-4 py-3 font-mono">{{ $c->code }}</td>
                    <td class="px-4 py-3">{{ $c->label() }}@if($c->min_amount_xof)<br><span class="text-slate-500 text-xs">dès {{ number_format($c->min_amount_xof, 0, ',', ' ') }} FCFA</span>@endif</td>
                    <td class="px-4 py-3 text-xs">{{ $c->starts_on ? 'du '.$c->starts_on->format('d/m/Y') : '' }} {{ $c->ends_on ? 'au '.$c->ends_on->format('d/m/Y') : '' }}{{ ! $c->starts_on && ! $c->ends_on ? 'Sans limite' : '' }}</td>
                    <td class="px-4 py-3">{{ $c->uses_count }}{{ $c->max_uses ? ' / '.$c->max_uses : '' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('provider.promo-codes.toggle', $c) }}">
                            @csrf
                            <button class="text-xs font-semibold px-3 py-1.5 rounded-lg {{ $c->is_active ? 'bg-slate-700 text-white' : 'bg-orange-500 text-black' }}">{{ $c->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Aucun code promo pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
