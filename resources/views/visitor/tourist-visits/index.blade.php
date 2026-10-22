@extends('layouts.visitor-public')

@section('title', __('Mes visites'))
@section('page-title', __('Mes visites'))

@section('content')
@php
    $tones = [
        'paid' => 'text-emerald-300 bg-emerald-500/10',
        'booked' => 'text-amber-300 bg-amber-500/10',
        'pending_payment' => 'text-slate-300 bg-slate-800',
        'cancelled' => 'text-slate-400 bg-slate-800',
        'refunded' => 'text-sky-300 bg-sky-500/10',
    ];
@endphp
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')

    <p class="text-slate-400 text-sm mb-4">{{ __('Vos billets de visite de sites touristiques. Présentez le QR code à l\'entrée.') }}</p>

    <div class="space-y-3">
        @forelse($visits as $v)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-4 flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $v->experience_name }} <span class="text-slate-400 font-normal">— {{ $v->labelForType() }}</span></p>
                    <p class="text-slate-400 text-sm mt-0.5">
                        @if($date = $v->session_date ?? $v->desired_date)
                            {{ $date->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY') }}@if($v->visit_time) · {{ str_replace(':', ' h ', $v->visit_time) }}@elseif($v->session_time_label) · {{ $v->session_time_label }}@endif
                        @else
                            {{ __('Date à convenir') }}
                        @endif
                        · {{ $v->participants_count }} {{ $v->participants_count > 1 ? __('personnes') : __('personne') }} · {{ $v->reference }}
                    </p>
                    @if($v->tiersSummary())<p class="text-xs mt-0.5 text-slate-300">{{ $v->tiersSummary() }}@if($v->language) · {{ $v->language }}@endif</p>@endif
                    @if($v->addons)<p class="text-xs mt-0.5 text-slate-300"><i class="fas fa-plus mr-1"></i>{{ collect($v->addons)->pluck('label')->join(', ') }}</p>@endif
                    <p class="text-sm mt-1 {{ $v->status === 'paid' ? 'text-emerald-300' : 'text-slate-200' }}">
                        {{ number_format((int) $v->amount_total_xof, 0, ',', ' ') }} FCFA
                        @if($v->status === \App\Models\TouristVisit::STATUS_BOOKED) · {{ __('à régler sur place') }}@endif
                    </p>
                    @if($v->cancellable_until && $v->hasValidTicket())
                        <p class="text-xs mt-1 {{ $v->isCancellableByClient() ? 'text-emerald-300' : 'text-slate-500' }}">
                            <i class="fas fa-rotate-left mr-1"></i>
                            {{ $v->isCancellableByClient()
                                ? __('Annulation gratuite jusqu\'au :d', ['d' => $v->cancellable_until->timezone('Africa/Abidjan')->format('d/m/Y à H:i')])
                                : __('Délai d\'annulation gratuite dépassé') }}
                        </p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tones[$v->status] ?? '' }}">{{ $v->labelForStatus() }}</span>
                    @if($v->hasValidTicket())
                        <a href="{{ $v->confirmationUrl() }}" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5"><i class="fas fa-qrcode mr-1"></i>{{ __('Mon billet') }}</a>
                    @endif
                    @if($v->isCancellableByClient())
                        <form method="POST" action="{{ route('visitor.tourist-visits.cancel', $v) }}"
                              onsubmit="return confirm({{ \Illuminate\Support\Js::from($v->status === 'paid' ? __('Annuler cette visite ? Le montant sera crédité sur votre solde.') : __('Annuler cette visite ?')) }});">
                            @csrf
                            <button type="submit" class="text-xs text-rose-300 hover:text-rose-200 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Annuler gratuitement') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            @include('partials.visitor-empty', ['icon' => 'map-point', 'title' => __('Aucune visite pour le moment.'), 'text' => __('Réservez une visite guidée depuis la fiche d\'un site touristique.'), 'cta' => ['url' => route('tourist-experience.index'), 'label' => __('Découvrir les sites touristiques'), 'icon' => 'map-point']])
        @endforelse
    </div>
    <div class="mt-5">{{ $visits->links() }}</div>
</div>
@endsection
