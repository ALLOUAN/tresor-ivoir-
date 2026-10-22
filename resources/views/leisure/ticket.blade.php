@extends('layouts.visitor-public')

@section('title', __('Mon billet'))
@section('page-title', __('Mon billet'))

@section('content')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $a = $booking->activity;
    $ok = $booking->status === \App\Models\ActivityBooking::STATUS_PAID;
    $conds = $a?->conditions() ?? [];
    $bring = \App\Models\Activity::lines($a?->to_bring);
@endphp
<div class="max-w-2xl mx-auto">
    <div class="bg-white border {{ $ok ? 'border-emerald-200' : 'border-gray-200' }} rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full {{ $ok ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center shrink-0"><i class="fas {{ $ok ? 'fa-circle-check text-emerald-600' : 'fa-ban text-gray-500' }} text-xl"></i></div>
            <div class="min-w-0">
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900">{{ $ok ? __('Réservation confirmée') : $booking->statusLabel() }}</h1>
                <p class="text-gray-500 text-sm">{{ $booking->item_name }} · {{ $booking->reference }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Date et heure') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ ucfirst($booking->booking_date->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY')) }}@if($booking->booking_time) · {{ str_replace(':', ' h ', $booking->booking_time) }}@endif</p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Participants') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $booking->tiersSummary() && ! $booking->isPackage() ? $booking->tiersSummary() : $booking->participants }}@if($booking->celebrant_name) · {{ __('pour :n', ['n' => $booking->celebrant_name]) }}@endif</p>
            </div>
        </div>

        @if($ok && $booking->ticketCheckUrl())
            <div class="rounded-2xl border border-gray-200 px-4 py-5 mb-6 text-center">
                <p class="text-sm font-semibold text-gray-900">{{ __('Votre billet électronique') }}</p>
                <img src="{{ \App\Support\Qr::dataUri($booking->ticketCheckUrl(), 260) }}" alt="{{ __('QR code du billet') }}" class="w-48 h-48 mx-auto my-3">
                <p class="font-mono text-lg tracking-widest text-gray-900">{{ $booking->ticketCode() }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Présentez ce QR code à l\'accueil. Il ne peut être utilisé qu\'une fois.') }}</p>
            </div>
        @endif

        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 mb-6 space-y-1.5">
            @foreach($booking->tier_breakdown ?? [] as $t)
                <div class="flex justify-between gap-3 text-xs text-gray-600"><span>{{ $t['qty'] }} × {{ $t['label'] }}</span><span>{{ $fmt($t['qty'] * $t['unit_price_xof']) }}</span></div>
            @endforeach
            @foreach($booking->addons ?? [] as $x)
                <div class="flex justify-between gap-3 text-xs text-gray-600"><span>{{ $x['qty'] > 1 ? $x['qty'].' × ' : '' }}{{ $x['label'] }}</span><span>{{ $fmt($x['qty'] * $x['unit_price_xof']) }}</span></div>
            @endforeach
            @if($booking->gateway === 'pass')
                <div class="flex justify-between text-sm font-bold text-emerald-700"><span>{{ __('Réglé avec votre carte') }}</span><span>{{ $booking->passPurchase?->pass_name }}</span></div>
            @else
                <div class="flex justify-between text-sm font-bold text-gray-900 pt-1.5 border-t border-orange-200/70"><span>{{ __('Total') }}</span><span>{{ $fmt($booking->amount_total_xof) }}</span></div>
                <div class="flex justify-between text-sm text-emerald-700"><span>{{ __('Payé en ligne') }}</span><span>{{ $fmt($booking->amount_paid_xof) }}</span></div>
                @if($booking->balanceOnSite() > 0)<div class="flex justify-between text-sm font-semibold text-orange-700"><span>{{ __('Solde à régler sur place') }}</span><span>{{ $fmt($booking->balanceOnSite()) }}</span></div>@endif
            @endif
            @if($booking->refund_xof)<div class="flex justify-between text-sm text-emerald-700"><span>{{ __('Remboursé sur votre solde') }}</span><span>{{ $fmt($booking->refund_xof) }}</span></div>@endif
        </div>

        @if($ok && ($conds || $bring))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6 text-sm">
                @if($conds)<div><p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1">{{ __('Conditions') }}</p><ul class="space-y-1 text-gray-700">@foreach($conds as $c)<li><i class="fas fa-circle-exclamation text-orange-600 text-xs mr-1.5"></i>{{ $c }}</li>@endforeach</ul></div>@endif
                @if($bring)<div><p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1">{{ __('À prévoir') }}</p><ul class="space-y-1 text-gray-700">@foreach($bring as $c)<li><i class="fas fa-check text-emerald-600 text-xs mr-1.5"></i>{{ $c }}</li>@endforeach</ul></div>@endif
            </div>
        @endif
        @if(! empty($booking->waiver['accepted_at']))
            <p class="text-xs text-gray-500 mb-4"><i class="fas fa-file-signature mr-1"></i>{{ __('Décharge acceptée le :d pour : :n', ['d' => \Carbon\Carbon::parse($booking->waiver['accepted_at'])->timezone('Africa/Abidjan')->format('d/m/Y H:i'), 'n' => implode(', ', $booking->waiver['names'] ?? [])]) }}</p>
        @endif
        @if($ok && $booking->cancellable_until)
            <p class="text-sm mb-6 {{ $booking->isCancellableByClient() ? 'text-emerald-700' : 'text-gray-500' }}"><i class="fas fa-rotate-left mr-1.5"></i>
                {{ $booking->isCancellableByClient() ? __('Annulation ou report gratuits jusqu\'au :d, depuis « Mes activités ».', ['d' => $booking->cancellable_until->timezone('Africa/Abidjan')->format('d/m/Y à H:i')]) : __('Le délai d\'annulation gratuite est dépassé.') }}</p>
        @endif

        <div class="flex flex-wrap gap-2">
            @if($a?->slug)<a href="{{ route('activity.show', $a->slug) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-semibold text-sm"><i class="fas fa-arrow-left text-xs"></i>{{ __('Page de l\'activité') }}</a>@endif
            @auth @if((int) auth()->id() === (int) $booking->user_id)
                <a href="{{ route('visitor.activity-bookings.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-800 font-semibold text-sm"><i class="fas fa-masks-theater text-xs"></i>{{ __('Mes activités') }}</a>
            @endif @endauth
        </div>
    </div>
</div>
@endsection
