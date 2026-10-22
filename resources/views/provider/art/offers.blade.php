@extends('layouts.app')

@section('title', 'Offres reçues')
@section('page-title', 'Offres reçues sur vos œuvres')

@section('content')
@include('provider.art._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-100'; @endphp
<div class="space-y-2">
    @forelse($offers as $o)
        <div class="bg-green-900 border border-slate-800 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-white font-semibold">{{ $o->artwork?->title }} <span class="text-slate-400 font-normal text-sm">— prix affiché {{ number_format((int) $o->artwork?->price_xof, 0, ',', ' ') }} XOF</span></p>
                <p class="text-slate-300 text-sm">{{ $o->user?->first_name }} {{ $o->user?->last_name }} propose <b class="text-white">{{ number_format($o->amount_xof, 0, ',', ' ') }} XOF</b> ({{ $o->artwork?->price_xof ? round($o->amount_xof / $o->artwork->price_xof * 100) : '—' }} %) · {{ $o->created_at->format('d/m H:i') }}</p>
                @if($o->message)<p class="text-slate-400 text-xs italic">« {{ $o->message }} »</p>@endif
                @if($o->counter_amount_xof)<p class="text-amber-300 text-xs">Votre contre-proposition : {{ number_format($o->counter_amount_xof, 0, ',', ' ') }} XOF</p>@endif
            </div>
            @if($o->status === 'pending')
                <div class="flex flex-wrap items-end gap-2">
                    <form method="POST" action="{{ route('provider.art-offers.respond', $o) }}">@csrf<button name="action" value="accept" class="text-xs font-semibold bg-emerald-600 text-white rounded-lg px-3 py-1.5">Accepter</button></form>
                    <form method="POST" action="{{ route('provider.art-offers.respond', $o) }}" class="flex items-end gap-1">@csrf
                        <input type="number" name="counter_xof" min="{{ $o->amount_xof + 1 }}" max="{{ (int) $o->artwork?->price_xof - 1 }}" placeholder="Contre-offre" class="{{ $f }} w-28">
                        <button name="action" value="counter" class="text-xs border border-slate-600 text-slate-200 rounded-lg px-3 py-1.5">Contre-proposer</button></form>
                    <form method="POST" action="{{ route('provider.art-offers.respond', $o) }}">@csrf<button name="action" value="decline" class="text-xs text-rose-300 border border-slate-700 rounded-lg px-3 py-1.5">Refuser</button></form>
                </div>
            @else
                <span class="text-xs text-slate-300">{{ $o->statusLabel() }}</span>
            @endif
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Aucune offre. Activez « Accepter les offres » sur une œuvre pour en recevoir.</div>
    @endforelse
</div>
<div class="mt-4">{{ $offers->links() }}</div>
@endsection
