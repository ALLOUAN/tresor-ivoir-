@extends('layouts.visitor-public')

@section('title', __('Visite confirmée'))
@section('page-title', __('Visite confirmée'))

@section('content')
@php
    $vt = $visit->visitType;
    $onSite = $visit->status === \App\Models\TouristVisit::STATUS_BOOKED;
@endphp
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-emerald-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <i class="fas fa-circle-check text-emerald-600 text-xl"></i>
            </div>
            <div>
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900">{{ $onSite ? __('Visite réservée') : __('Visite confirmée') }}</h1>
                <p class="text-gray-500 text-sm">{{ $visit->experience_name }}</p>
            </div>
        </div>

        <p class="text-gray-600 text-sm leading-relaxed mb-6">
            {{ __('Votre réservation a bien été enregistrée. Vous recevrez toutes les informations utiles à') }}
            <span class="font-semibold text-gray-800">{{ $visit->email }}</span>.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Type de visite') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->labelForType() }}</p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Date') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">
                    {{ optional($visit->session_date ?? $visit->desired_date)->format('d/m/Y') ?? __('À convenir avec le prestataire') }}
                    @if($visit->session_time_label) · {{ $visit->session_time_label }} @endif
                    @if($visit->visit_time) · {{ str_replace(':', ' h ', $visit->visit_time) }} @endif
                </p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Participants') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->participants_count }} {{ $visit->participants_count > 1 ? 'personnes' : 'personne' }}{{ $visit->with_guide ? __(' · avec guide') : '' }}</p>
                @if($visit->tiersSummary())<p class="text-gray-500 text-xs mt-0.5">{{ $visit->tiersSummary() }}</p>@endif
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Référence') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->reference }}</p>
            </div>
            @if($visit->language)
                <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Langue de la visite') }}</p>
                    <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->language }}</p>
                </div>
            @endif
            @if($vt?->meeting_point)
                <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Point de rendez-vous') }}</p>
                    <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $vt->meeting_point }}</p>
                </div>
            @endif
        </div>

        @if($vt && ($vt->included || $vt->to_bring))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6 text-sm">
                @if($vt->included)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1">{{ __('Inclus') }}</p>
                        <ul class="space-y-1 text-gray-700">@foreach(\App\Models\TouristVisitType::lines($vt->included) as $l)<li><i class="fas fa-check text-emerald-600 text-xs mr-1.5"></i>{{ $l }}</li>@endforeach</ul>
                    </div>
                @endif
                @if($vt->to_bring)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1">{{ __('À prévoir') }}</p>
                        <ul class="space-y-1 text-gray-700">@foreach(\App\Models\TouristVisitType::lines($vt->to_bring) as $l)<li><i class="fas fa-circle-info text-amber-600 text-xs mr-1.5"></i>{{ $l }}</li>@endforeach</ul>
                    </div>
                @endif
            </div>
        @endif

        @if($visit->ticketCheckUrl())
            <div class="rounded-2xl border border-gray-200 bg-white px-4 py-5 mb-6 text-center">
                <p class="text-sm font-semibold text-gray-900">{{ __('Votre billet électronique') }}</p>
                <img src="{{ \App\Support\Qr::dataUri($visit->ticketCheckUrl(), 260) }}" alt="{{ __('QR code du billet') }}" class="w-48 h-48 mx-auto my-3">
                <p class="font-mono text-lg tracking-widest text-gray-900">{{ $visit->ticketCode() }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Présentez ce QR code à l\'entrée (sur votre téléphone ou imprimé). Il ne peut être utilisé qu\'une fois.') }}</p>
            </div>
        @endif

        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 mb-6 space-y-1.5">
            @foreach($visit->tier_breakdown ?? [] as $t)
                <div class="flex items-center justify-between text-xs text-gray-600"><span>{{ $t['qty'] }} × {{ $t['label'] }}</span><span>{{ number_format($t['qty'] * $t['unit_price_xof'], 0, ',', ' ') }} {{ __('FCFA') }}</span></div>
            @endforeach
            @foreach($visit->addons ?? [] as $a)
                <div class="flex items-center justify-between text-xs text-gray-600"><span>{{ $a['qty'] > 1 ? $a['qty'].' × ' : '' }}{{ $a['label'] }}</span><span>{{ number_format($a['qty'] * $a['unit_price_xof'], 0, ',', ' ') }} {{ __('FCFA') }}</span></div>
            @endforeach
            @if($visit->tier_breakdown && $visit->guide_supplement_xof)
                <div class="flex items-center justify-between text-xs text-gray-600"><span>{{ __('Supplément guide') }}</span><span>{{ number_format($visit->guide_supplement_xof, 0, ',', ' ') }} {{ __('FCFA') }}</span></div>
            @endif
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">{{ $onSite ? __('À régler sur place') : ($visit->amount_total_xof > 0 ? __('Montant payé') : __('Visite gratuite')) }}</span>
                <span class="{{ $onSite ? 'text-orange-700' : 'text-emerald-700' }} font-bold">{{ number_format((int) $visit->amount_total_xof, 0, ',', ' ') }} {{ __('FCFA') }}</span>
            </div>
        </div>

        @if($visit->cancellable_until)
            <p class="text-sm mb-6 {{ $visit->isCancellableByClient() ? 'text-emerald-700' : 'text-gray-500' }}">
                <i class="fas fa-rotate-left mr-1.5"></i>
                @if($visit->isCancellableByClient())
                    {{ __('Annulation gratuite jusqu\'au :d, depuis « Mes visites ».', ['d' => $visit->cancellable_until->timezone('Africa/Abidjan')->format('d/m/Y à H:i')]) }}
                @else
                    {{ __('Le délai d\'annulation gratuite est dépassé.') }}
                @endif
            </p>
        @endif

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tourist-experience.show', $visit->experience?->slug) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white font-semibold text-sm transition">
                <i class="fas fa-arrow-left text-xs"></i> {{ __('Retour à la fiche du site') }}
            </a>
            @auth
                @if(auth()->user()->role === 'visitor' && (int) auth()->id() === (int) $visit->user_id)
                    <a href="{{ route('visitor.tourist-visits.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-800 font-semibold text-sm hover:border-gray-400 transition">
                        <i class="fas fa-ticket text-xs"></i> {{ __('Mes visites') }}
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection
