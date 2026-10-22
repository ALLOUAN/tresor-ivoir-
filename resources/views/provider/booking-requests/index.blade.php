@extends('layouts.app')

@section('title', 'Demandes de réservation')
@section('page-title', 'Demandes de réservation')

@section('content')
@php
    $statusLabels = \App\Models\BookingRequest::STATUS_LABELS;
    $tones = [
        'new' => 'text-amber-300 bg-amber-500/10',
        'confirmed' => 'text-emerald-300 bg-emerald-500/10',
        'declined' => 'text-rose-300 bg-rose-500/10',
        'cancelled' => 'text-slate-400 bg-slate-800',
        'expired' => 'text-slate-400 bg-slate-800',
    ];
@endphp
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') }}</div>
@endif

<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('provider.booking-requests.index') }}" class="px-3 py-1.5 rounded-lg text-sm border transition {{ ! $status ? 'bg-orange-500 border-orange-500 text-black font-semibold' : 'border-slate-700 text-slate-300 hover:text-white' }}">Toutes</a>
    @foreach($statusLabels as $key => $label)
        <a href="{{ route('provider.booking-requests.index', ['statut' => $key]) }}"
           class="px-3 py-1.5 rounded-lg text-sm border transition {{ $status === $key ? 'bg-orange-500 border-orange-500 text-black font-semibold' : 'border-slate-700 text-slate-300 hover:text-white' }}">
            {{ $label }} <span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<div class="space-y-3">
    @forelse($bookings as $b)
        <div class="bg-green-900 border {{ $b->status === 'new' ? 'border-amber-600/40' : 'border-slate-800' }} rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $b->full_name }} <span class="text-slate-500 text-xs font-normal">{{ $b->reference }}</span></p>
                    <p class="text-slate-300 text-sm mt-0.5">
                        <i class="fas fa-calendar text-orange-400/70 text-xs"></i>
                        {{ $b->booking_date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}@if($b->booking_time) à {{ $b->booking_time }}@endif
                        · {{ $b->party_size }} {{ $b->unitLabel() }}
                        @if($b->item_name) · {{ $b->item_name }}@endif
                    </p>
                    @if($b->pickup_location)<p class="text-xs mt-0.5 text-sky-300"><i class="fas fa-route mr-1"></i>{{ $b->pickup_location }} → {{ $b->dropoff_location }}</p>@endif
                    <p class="text-slate-400 text-xs mt-1">
                        <a href="mailto:{{ $b->email }}" class="hover:text-white">{{ $b->email }}</a>
                        @if($b->phone) · <a href="tel:{{ $b->phone }}" class="hover:text-white">{{ $b->phone }}</a>@endif
                    </p>
                    @if($b->message)
                        <p class="text-slate-300 text-sm mt-2 bg-slate-800/60 rounded-lg px-3 py-2">« {{ $b->message }} »</p>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-1 shrink-0">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tones[$b->status] ?? '' }}">{{ $b->statusLabel() }}</span>
                    @if((int) $b->amount_due_xof > 0 && $b->status !== 'new')
                        <span class="text-xs {{ $b->payment_status === 'paid' ? 'text-emerald-300' : 'text-slate-400' }}">{{ number_format((int) $b->amount_due_xof, 0, ',', ' ') }} FCFA · {{ $b->paymentLabel() }}</span>
                    @endif
                </div>
            </div>

            @if($b->promo_code)
                <p class="mt-2 text-xs text-emerald-300"><i class="fas fa-tag mr-1"></i>Code promo {{ $b->promo_code }}@if($b->promo_discount_xof) : −{{ number_format($b->promo_discount_xof, 0, ',', ' ') }} FCFA déduits
                    @elseif($b->status === 'new') — la remise sera déduite automatiquement du montant saisi
                    @endif
                </p>
            @endif
            @if($b->status === 'new')
                <form method="POST" action="{{ route('provider.booking-requests.respond', $b) }}" class="mt-3 pt-3 border-t border-slate-800 flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="text" name="provider_note" maxlength="500" placeholder="Message au client (facultatif)"
                           class="flex-1 min-w-[200px] bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <label class="flex items-center gap-2 text-xs text-slate-400">
                        À payer en ligne
                        <input type="number" name="amount_xof" min="0" step="5" value="{{ $b->amount_due_xof }}" placeholder="0"
                               title="Montant que le client réglera en ligne (acompte ou total). Vide ou 0 = règlement sur place."
                               class="w-32 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        FCFA
                    </label>
                    <button type="submit" name="decision" value="confirm" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        <i class="fas fa-check text-xs"></i> Confirmer
                    </button>
                    <button type="submit" name="decision" value="decline" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        <i class="fas fa-xmark text-xs"></i> Refuser
                    </button>
                </form>
            @elseif($b->provider_note)
                <p class="text-slate-500 text-xs mt-2">Votre message : {{ $b->provider_note }}</p>
            @endif
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center text-slate-400 text-sm">
            Aucune demande pour le moment. Les clients réservent depuis votre fiche publique, carte « Réserver ».
        </div>
    @endforelse
</div>
<div class="mt-5">{{ $bookings->links() }}</div>
@endsection
