@extends('layouts.app')

@section('title', 'Commandes sur mesure')
@section('page-title', 'Commandes sur mesure')

@section('content')
@include('provider.art._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-2 text-sm text-slate-100'; @endphp
<div class="space-y-3">
    @forelse($commissions as $c)
        <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">« {{ $c->subject }} » <span class="text-slate-400 font-normal text-sm">— {{ $c->user?->first_name }} {{ $c->user?->last_name }} · {{ $c->reference }}</span></p>
                    <p class="text-slate-300 text-sm whitespace-pre-line mt-1">{{ $c->brief }}</p>
                    <p class="text-slate-400 text-xs mt-1">{{ collect([$c->format, $c->medium, $c->palette, $c->budget_xof ? 'budget '.number_format($c->budget_xof, 0, ',', ' ').' XOF' : null, $c->wanted_by ? 'pour le '.$c->wanted_by->format('d/m/Y') : null])->filter()->join(' · ') }}</p>
                    @if($c->references)<div class="flex gap-2 mt-2">@foreach($c->references as $r)<a href="{{ $r }}" target="_blank"><img src="{{ $r }}" alt="Référence" class="w-16 h-16 rounded-lg object-cover border border-slate-700"></a>@endforeach</div>@endif
                    @if($c->revision_note && $c->status === 'revision')<p class="text-amber-300 text-sm mt-2"><i class="fas fa-pen mr-1"></i>Retouches demandées : {{ $c->revision_note }}</p>@endif
                </div>
                <div class="text-right text-xs">
                    <span class="px-3 py-1 rounded-full font-semibold bg-slate-800 text-slate-200">{{ $c->statusLabel() }}</span>
                    @if($c->quote_xof)<p class="text-slate-300 mt-2">Devis {{ number_format($c->quote_xof, 0, ',', ' ') }} XOF · payé {{ number_format($c->amount_paid_xof, 0, ',', ' ') }} XOF</p>@endif
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-end gap-2">
                @if(in_array($c->status, ['requested', 'quoted'], true))
                    <form method="POST" action="{{ route('provider.art-commissions.action', $c) }}" class="flex flex-wrap items-end gap-2">@csrf
                        <label class="text-[11px] text-slate-400">Prix (XOF)<input type="number" name="quote_xof" min="1000" required value="{{ $c->quote_xof ?? $c->budget_xof }}" class="{{ $f }} block mt-1 w-32"></label>
                        <label class="text-[11px] text-slate-400">Délai (jours)<input type="number" name="quote_days" min="1" required value="{{ $c->quote_days ?? 21 }}" class="{{ $f }} block mt-1 w-24"></label>
                        <label class="text-[11px] text-slate-400">Message<input name="message" maxlength="2000" value="{{ $c->quote_message }}" class="{{ $f }} block mt-1 w-64"></label>
                        <button name="action" value="quote" class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">{{ $c->status === 'quoted' ? 'Modifier le devis' : 'Envoyer le devis' }}</button>
                    </form>
                    <form method="POST" action="{{ route('provider.art-commissions.action', $c) }}">@csrf<button name="action" value="decline" class="text-xs text-rose-300 border border-slate-700 rounded-lg px-3 py-2">Décliner</button></form>
                @elseif(in_array($c->status, ['deposit_paid', 'revision'], true))
                    <form method="POST" action="{{ route('provider.art-commissions.action', $c) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2">@csrf
                        <label class="text-[11px] text-slate-400">Esquisse(s)<input type="file" name="sketches[]" accept="image/*" multiple required class="{{ $f }} block mt-1"></label>
                        <label class="text-[11px] text-slate-400">Note au client<input name="message" maxlength="2000" class="{{ $f }} block mt-1 w-64"></label>
                        <button name="action" value="sketch" class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">Envoyer l'esquisse</button>
                    </form>
                @elseif($c->status === 'balance_paid')
                    <form method="POST" action="{{ route('provider.art-commissions.action', $c) }}">@csrf<button name="action" value="deliver" class="bg-emerald-600 text-white text-xs font-semibold px-3 py-2 rounded-lg">Œuvre remise au client</button></form>
                @endif
                @if(! in_array($c->status, ['delivered', 'cancelled', 'declined'], true) && $c->amount_paid_xof > 0)
                    <form method="POST" action="{{ route('provider.art-commissions.action', $c) }}" onsubmit="return confirm('Annuler la commande ? Le client sera remboursé intégralement.');">@csrf<button name="action" value="cancel" class="text-xs text-rose-300 border border-slate-700 rounded-lg px-3 py-2">Annuler et rembourser</button></form>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Aucune demande sur mesure pour le moment.</div>
    @endforelse
</div>
<div class="mt-4">{{ $commissions->links() }}</div>
@endsection
