@extends('layouts.app')

@section('title', 'Billet '.$code)
@section('page-title', 'Contrôle du billet')

@section('content')
@php
    $tone = ['success' => 'border-emerald-500/50 bg-emerald-900/30 text-emerald-200', 'warning' => 'border-amber-500/50 bg-amber-900/30 text-amber-100', 'error' => 'border-rose-500/50 bg-rose-900/30 text-rose-200'][$check['level']];
    $icon = ['success' => 'fa-circle-check', 'warning' => 'fa-triangle-exclamation', 'error' => 'fa-circle-xmark'][$check['level']];
@endphp
<div class="max-w-xl space-y-4">
    @if(session('success'))
        <div class="px-4 py-4 rounded-xl border border-emerald-500/50 bg-emerald-900/40 text-emerald-100 text-lg font-bold"><i class="fas fa-door-open mr-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 rounded-xl border border-rose-500/50 bg-rose-900/30 text-rose-200 text-sm">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border px-5 py-4 {{ $tone }}">
        <p class="text-lg font-bold"><i class="fas {{ $icon }} mr-2"></i>{{ $check['message'] }}</p>
        <p class="text-xs opacity-80 mt-1 font-mono">{{ $code }}</p>
    </div>

    @if($visit && $check['level'] !== 'error' || ($visit && $visit->checked_in_at))
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 text-sm space-y-1.5">
            <p class="text-white text-base font-semibold">{{ $visit->full_name }}</p>
            <p class="text-slate-300">{{ $visit->experience_name }} · {{ $visit->labelForType() }}</p>
            <p class="text-slate-300"><i class="fas fa-users text-orange-400/70 mr-1"></i>{{ $visit->participants_count }} participant(s){{ $visit->with_guide ? ' · avec guide' : '' }}</p>
            @if($visit->children_count || $visit->residents_count)
                <p class="text-amber-300 text-sm"><i class="fas fa-id-card mr-1"></i>dont {{ $visit->children_count ? $visit->children_count.' enfant(s)' : '' }}{{ $visit->children_count && $visit->residents_count ? ' et ' : '' }}{{ $visit->residents_count ? $visit->residents_count.' résident(s) — vérifier la pièce justificative' : '' }}</p>
            @endif
            @if($visit->session_date || $visit->desired_date)
                <p class="text-slate-300"><i class="fas fa-calendar text-orange-400/70 mr-1"></i>{{ ($visit->session_date ?? $visit->desired_date)->format('d/m/Y') }} {{ $visit->session_time_label }}</p>
            @endif
            <p class="text-slate-500 text-xs">{{ $visit->reference }} · payé {{ number_format((int) $visit->amount_total_xof, 0, ',', ' ') }} XOF</p>
        </div>
    @endif

    @if(! empty($ride))
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 text-sm space-y-1.5">
            <p class="text-white text-base font-semibold">{{ $ride->full_name }}</p>
            <p class="text-slate-300">{{ $ride->offer_name }} · {{ $ride->pickup_label }} → {{ $ride->dropoff_label }}</p>
            <p class="text-slate-300"><i class="fas fa-chair text-orange-400/70 mr-1"></i>{{ $ride->passengers }} passager(s) · sièges {{ implode(', ', $ride->seats ?? []) }}</p>
            <p class="text-slate-500 text-xs">{{ $ride->reference }} · payé {{ number_format($ride->amount_total_xof, 0, ',', ' ') }} XOF</p>
        </div>
    @endif

    @if(! empty($booking))
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 text-sm space-y-1.5">
            <p class="text-white text-base font-semibold">{{ $booking->full_name }}</p>
            <p class="text-slate-300">{{ $booking->item_name }}@if($booking->booking_time) · {{ $booking->booking_time }}@endif</p>
            <p class="text-slate-300"><i class="fas fa-users text-orange-400/70 mr-1"></i>{{ $booking->participants }} participant(s)@if($booking->tiersSummary() && ! $booking->isPackage()) — {{ $booking->tiersSummary() }}@endif</p>
            @if($booking->addons)<p class="text-slate-300"><i class="fas fa-plus text-orange-400/70 mr-1"></i>{{ collect($booking->addons)->map(fn ($a) => ($a['qty'] > 1 ? $a['qty'].' × ' : '').$a['label'])->join(', ') }}</p>@endif
            @if(! empty($booking->waiver['names']))<p class="text-emerald-300"><i class="fas fa-file-signature mr-1"></i>Décharge signée : {{ implode(', ', $booking->waiver['names']) }}</p>@endif
            @if($booking->activity?->min_age || $booking->activity?->min_height_cm)<p class="text-amber-300"><i class="fas fa-ruler-vertical mr-1"></i>{{ collect([$booking->activity->min_age ? 'dès '.$booking->activity->min_age.' ans' : null, $booking->activity->min_height_cm ? 'taille min. '.$booking->activity->min_height_cm.' cm' : null])->filter()->join(' · ') }} — à vérifier</p>@endif
            <p class="text-slate-500 text-xs">{{ $booking->reference }} · {{ $booking->gateway === 'pass' ? 'réglé par carte' : 'payé '.number_format($booking->amount_paid_xof, 0, ',', ' ').' XOF' }}</p>
        </div>
    @endif

    @if($check['ok'])
        <form method="POST" action="{{ route('provider.tickets.check-in', $code) }}">
            @csrf
            <button class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-lg font-bold px-5 py-4 rounded-xl">
                <i class="fas fa-door-open"></i> Valider l'entrée
            </button>
        </form>
    @endif

    <a href="{{ route('provider.tickets.index') }}" class="inline-flex items-center gap-2 text-slate-300 hover:text-white text-sm"><i class="fas fa-qrcode"></i> Scanner un autre billet</a>
</div>
@endsection
