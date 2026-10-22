@extends('layouts.app')

@section('title', 'Réservations d\'activités')
@section('page-title', 'Réservations d\'activités')

@section('header-actions')
    <a href="{{ route('provider.tickets.index') }}" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-3 py-2 rounded-lg"><i class="fas fa-qrcode"></i> Scanner un billet</a>
@endsection

@section('content')
@include('provider.leisure._nav')
<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" aria-label="Date">
    @foreach($upcoming as $u)
        <a href="?date={{ \Carbon\Carbon::parse($u->booking_date)->toDateString() }}" class="text-xs rounded-lg px-2.5 py-1.5 border {{ \Carbon\Carbon::parse($u->booking_date)->toDateString() === $date ? 'border-orange-500 text-orange-300' : 'border-slate-700 text-slate-300' }}">{{ \Carbon\Carbon::parse($u->booking_date)->locale('fr')->isoFormat('ddd D') }} · {{ $u->p }} pers.</a>
    @endforeach
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-2">
        @forelse($bookings as $b)
            <div class="bg-green-900 border border-slate-800 rounded-xl p-4 {{ $b->status !== 'paid' ? 'opacity-50' : '' }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-white font-semibold"><span class="text-orange-300">{{ $b->booking_time }}</span> · {{ $b->item_name }}</p>
                        <p class="text-slate-300 text-sm">{{ $b->full_name }} · {{ $b->phone }} · {{ $b->participants }} pers.@if($b->tiersSummary() && ! $b->isPackage()) ({{ $b->tiersSummary() }})@endif</p>
                        @if($b->addons)<p class="text-slate-400 text-xs">+ {{ collect($b->addons)->map(fn ($a) => ($a['qty'] > 1 ? $a['qty'].' × ' : '').$a['label'])->join(', ') }}</p>@endif
                        @if($b->celebrant_name)<p class="text-amber-300 text-xs"><i class="fas fa-cake-candles mr-1"></i>{{ $b->celebrant_name }}</p>@endif
                        @if($b->message)<p class="text-slate-400 text-xs italic">« {{ $b->message }} »</p>@endif
                        @if(! empty($b->waiver['accepted_at']))<p class="text-emerald-300 text-xs"><i class="fas fa-file-signature mr-1"></i>Décharge signée : {{ implode(', ', $b->waiver['names'] ?? []) }}</p>@endif
                    </div>
                    <div class="text-right text-xs">
                        <p class="text-slate-200">{{ $b->gateway === 'pass' ? 'Carte '.$b->passPurchase?->pass_name : number_format($b->amount_paid_xof, 0, ',', ' ').' XOF payés' }}</p>
                        @if($b->balanceOnSite() > 0)<p class="text-amber-300 font-semibold">{{ number_format($b->balanceOnSite(), 0, ',', ' ') }} XOF à encaisser</p>@endif
                        <p class="{{ $b->checked_in_at ? 'text-emerald-300' : 'text-slate-400' }}">{{ $b->checked_in_at ? 'Arrivé à '.$b->checked_in_at->format('H:i') : $b->statusLabel() }}</p>
                        <p class="text-slate-500">{{ $b->reference }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Aucune réservation ce jour-là.</div>
        @endforelse
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 self-start">
        <h2 class="text-white font-semibold text-sm mb-2"><i class="fas fa-bell text-orange-400 mr-1"></i>Listes d'attente</h2>
        @forelse($waitlist as $w)
            <p class="text-sm text-slate-300 border-b border-slate-800 py-1.5">{{ $w->booking_date->format('d/m') }} {{ $w->booking_time }} · {{ $w->activity?->name }} · {{ $w->participants }} pers. <span class="text-xs {{ $w->status === 'notified' ? 'text-amber-300' : 'text-slate-500' }}">{{ $w->status === 'notified' ? 'prévenu' : 'en attente' }}</span></p>
        @empty
            <p class="text-slate-400 text-sm">Personne en attente.</p>
        @endforelse
        <p class="text-slate-500 text-[11px] mt-2">Quand une place se libère (annulation, report, places ajoutées), la première personne est prévenue et a 2 h pour réserver.</p>
    </div>
</div>
@endsection
