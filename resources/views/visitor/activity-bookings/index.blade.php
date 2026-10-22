@extends('layouts.visitor-public')

@section('title', __('Mes activités'))
@section('page-title', __('Mes activités'))

@section('content')
@php
    $tones = ['paid' => 'text-emerald-300 bg-emerald-500/10', 'pending_payment' => 'text-slate-300 bg-slate-800', 'failed' => 'text-rose-300 bg-rose-500/10', 'cancelled' => 'text-slate-400 bg-slate-800', 'refunded' => 'text-sky-300 bg-sky-500/10'];
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $in = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100';
@endphp
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')
    @if(session('success'))<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">{{ session('error') }}</div>@endif

    @if($passes->isNotEmpty())
        <h2 class="text-white font-semibold mb-2"><i class="fas fa-id-card text-orange-400 mr-1.5"></i>{{ __('Mes cartes') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            @foreach($passes as $p)
                <div class="bg-green-900 border border-slate-800 rounded-2xl p-4">
                    <p class="text-white font-semibold">{{ $p->pass_name }}</p>
                    <p class="text-sm mt-0.5 {{ $p->expires_on?->isPast() ? 'text-rose-300' : 'text-slate-300' }}">
                        {{ $p->remaining() === null ? __('Accès illimité') : trans_choice(':count séance restante|:count séances restantes', $p->remaining()) }}
                        · {{ $p->expires_on?->isPast() ? __('expirée le :d', ['d' => $p->expires_on->format('d/m/Y')]) : __('valable jusqu\'au :d', ['d' => $p->expires_on?->format('d/m/Y')]) }}
                    </p>
                    @if($p->sessions_total)<div class="mt-2 h-1.5 rounded-full bg-slate-800 overflow-hidden"><div class="h-full bg-orange-500" style="width: {{ min(100, round($p->sessions_used / max(1, $p->sessions_total) * 100)) }}%"></div></div>@endif
                </div>
            @endforeach
        </div>
    @endif

    @if($waitlist->isNotEmpty())
        <h2 class="text-white font-semibold mb-2"><i class="fas fa-bell text-orange-400 mr-1.5"></i>{{ __('Listes d\'attente') }}</h2>
        <div class="space-y-2 mb-6">
            @foreach($waitlist as $w)
                <div class="bg-green-900 border {{ $w->status === 'notified' ? 'border-amber-500/60' : 'border-slate-800' }} rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-white font-semibold">{{ $w->activity?->name }}</p>
                        <p class="text-slate-400 text-sm">{{ $w->booking_date->format('d/m/Y') }} · {{ $w->booking_time }} · {{ $w->participants }} {{ __('pers.') }}</p>
                        @if($w->status === 'notified')<p class="text-amber-300 text-sm mt-1"><i class="fas fa-bolt mr-1"></i>{{ __('Une place s\'est libérée : réservez avant :t.', ['t' => $w->expires_at->timezone('Africa/Abidjan')->format('H:i')]) }}</p>@endif
                    </div>
                    <div class="flex gap-2">
                        @if($w->status === 'notified' && $w->activity)<a href="{{ route('activity.show', $w->activity->slug) }}?date={{ $w->booking_date->toDateString() }}&heure={{ $w->booking_time }}#reserver" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5">{{ __('Réserver maintenant') }}</a>@endif
                        <form method="POST" action="{{ route('visitor.activity-waitlist.leave', $w) }}">@csrf @method('DELETE')<button class="text-xs text-slate-300 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Quitter') }}</button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="text-white font-semibold mb-2"><i class="fas fa-ticket text-orange-400 mr-1.5"></i>{{ __('Mes réservations') }}</h2>
    <div class="space-y-3">
        @forelse($bookings as $b)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-white font-semibold">{{ $b->item_name }}@if($b->isPackage())<span class="text-slate-400 font-normal"> — {{ $b->package?->kindLabel() }}</span>@endif</p>
                        <p class="text-slate-400 text-sm mt-0.5">{{ ucfirst($b->booking_date->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY')) }}@if($b->booking_time) · {{ str_replace(':', ' h ', $b->booking_time) }}@endif · {{ $b->participants }} {{ __('pers.') }} · {{ $b->reference }}</p>
                        <p class="text-sm mt-1 text-slate-200">
                            @if($b->gateway === 'pass'){{ __('Réglé avec ma carte') }}@else{{ $fmt($b->amount_paid_xof) }} {{ __('payé') }}@if($b->balanceOnSite() > 0) · {{ __(':a à régler sur place', ['a' => $fmt($b->balanceOnSite())]) }}@endif @endif
                            @if($b->refund_xof)<span class="text-emerald-300"> · {{ __('remboursé :a', ['a' => $fmt($b->refund_xof)]) }}</span>@endif
                        </p>
                        @if($b->cancellable_until && $b->status === 'paid')
                            <p class="text-xs mt-1 {{ $b->isCancellableByClient() ? 'text-emerald-300' : 'text-slate-500' }}"><i class="fas fa-rotate-left mr-1"></i>{{ $b->isCancellableByClient() ? __('Annulation / report gratuits jusqu\'au :d', ['d' => $b->cancellable_until->timezone('Africa/Abidjan')->format('d/m/Y H:i')]) : __('Délai d\'annulation gratuite dépassé') }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tones[$b->status] ?? '' }}">{{ $b->statusLabel() }}</span>
                        @if($b->status === 'paid')<a href="{{ $b->ticketUrl() }}" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5"><i class="fas fa-qrcode mr-1"></i>{{ __('Mon billet') }}</a>@endif
                    </div>
                </div>
                @if($b->canReschedule())
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm text-slate-200"><i class="fas fa-calendar-days mr-1"></i>{{ __('Reporter') }} <span class="text-slate-500 text-xs">({{ __(':n report(s) restant(s)', ['n' => \App\Models\ActivityBooking::MAX_RESCHEDULES - $b->reschedule_count]) }})</span></summary>
                        <form method="POST" action="{{ route('visitor.activity-bookings.reschedule', $b) }}" class="mt-2 flex flex-wrap items-end gap-2">
                            @csrf
                            <label class="text-xs text-slate-400">{{ __('Nouvelle date') }}<input type="date" name="date" required min="{{ now('Africa/Abidjan')->toDateString() }}" class="{{ $in }} mt-1 block"></label>
                            <label class="text-xs text-slate-400">{{ __('Heure') }}
                                <select name="time" required class="{{ $in }} mt-1 block">@foreach($b->activity?->slots() ?? [] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></label>
                            <button class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-2">{{ __('Reporter') }}</button>
                        </form>
                    </details>
                @endif
                @if($b->isCancellableByClient())
                    <form method="POST" action="{{ route('visitor.activity-bookings.cancel', $b) }}" class="mt-3" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Annuler cette réservation ?')) }});">
                        @csrf
                        <button class="text-xs text-rose-300 hover:text-rose-200 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Annuler gratuitement') }}{{ $b->amount_paid_xof ? ' · '.__('remboursement :a', ['a' => $fmt($b->amount_paid_xof)]) : ($b->gateway === 'pass' ? ' · '.__('séances recréditées') : '') }}</button>
                    </form>
                @endif
            </div>
        @empty
            @include('partials.visitor-empty', ['icon' => 'ferris-wheel', 'title' => __('Aucune activité réservée pour le moment.'), 'text' => __('Parcs, sports, sorties culturelles : tout se réserve en ligne.'), 'cta' => ['url' => route('leisure.index'), 'label' => __('Découvrir les loisirs'), 'icon' => 'ferris-wheel']])
        @endforelse
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</div>
@endsection
