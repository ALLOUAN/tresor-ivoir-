@extends('layouts.visitor-public')

@section('title', __('Feuille de route'))
@section('page-title', __('Feuille de route'))

@section('content')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $tour = $booking->tour;
    $days = $tour?->days() ?? [];
    $pickup = collect($tour?->pickups() ?? [])->firstWhere('label', $booking->pickup_point);
    $cancelled = $booking->status === \App\Models\TourBooking::STATUS_CANCELLED;
@endphp
<div class="max-w-2xl mx-auto">
    <div class="bg-white border {{ $cancelled ? 'border-gray-200' : 'border-emerald-200' }} rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full {{ $cancelled ? 'bg-gray-100' : 'bg-emerald-100' }} flex items-center justify-center shrink-0">
                <i class="fas {{ $cancelled ? 'fa-ban text-gray-500' : 'fa-circle-check text-emerald-600' }} text-xl"></i>
            </div>
            <div class="min-w-0">
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900">{{ $cancelled ? $booking->statusLabel() : __('Voyage réservé') }}</h1>
                <p class="text-gray-500 text-sm">{{ $booking->tour_name }} · {{ $booking->reference }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Départ') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ ucfirst($booking->departure_date->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY')) }}</p>
                @if($booking->departure && ! $cancelled)
                    <p class="text-xs mt-0.5 {{ $booking->departure->isGuaranteed() ? 'text-emerald-700' : 'text-amber-700' }}">{{ $booking->departure->isGuaranteed() ? __('Départ garanti') : __('Départ à confirmer le :d', ['d' => $booking->departure->decisionDate()->format('d/m/Y')]) }}</p>
                @endif
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Voyageurs') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $booking->adults }} {{ __('adulte(s)') }}{{ $booking->children ? ', '.$booking->children.' '.__('enfant(s)') : '' }}{{ $booking->single_rooms ? ' · '.$booking->single_rooms.' '.__('ch. individuelle(s)') : '' }}</p>
            </div>
            @if($booking->pickup_point)
                <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 sm:col-span-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Prise en charge') }}</p>
                    <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $booking->pickup_point }}@if($pickup['time'] ?? null) — {{ str_replace(':', ' h ', $pickup['time']) }}@endif</p>
                    @if($pickup['note'] ?? null)<p class="text-gray-500 text-xs mt-0.5">{{ $pickup['note'] }}</p>@endif
                </div>
            @endif
            @if($agency)
                <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 sm:col-span-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Votre agence') }}</p>
                    <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $agency->name }}@if($agency->phone) · <a href="tel:{{ $agency->phone }}" class="text-orange-700">{{ $agency->phone }}</a>@endif @if($agency->email) · {{ $agency->email }}@endif</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 mb-6 space-y-1.5">
            @foreach($booking->price_lines ?? [] as $l)
                <div class="flex items-center justify-between gap-3 text-xs text-gray-600"><span>{{ $l['label'] }}</span><span class="whitespace-nowrap">{{ $fmt((int) $l['amount_xof']) }}</span></div>
            @endforeach
            <div class="flex items-center justify-between text-sm font-bold text-gray-900 pt-1.5 border-t border-orange-200/70"><span>{{ __('Total') }}</span><span>{{ $fmt($booking->amount_total_xof) }}</span></div>
            <div class="flex items-center justify-between text-sm text-emerald-700"><span>{{ __('Déjà payé') }}</span><span>{{ $fmt($booking->amount_paid_xof) }}</span></div>
            @if(! $cancelled && $booking->balanceXof() > 0)
                <div class="flex items-center justify-between text-sm text-orange-700 font-semibold"><span>{{ __('Solde avant le :d', ['d' => $booking->balance_due_on?->format('d/m/Y')]) }}</span><span>{{ $fmt($booking->balanceXof()) }}</span></div>
            @endif
            @if($booking->refund_xof)
                <div class="flex items-center justify-between text-sm text-emerald-700"><span>{{ __('Remboursé sur votre solde') }}</span><span>{{ $fmt($booking->refund_xof) }}</span></div>
            @endif
        </div>

        @if($days && ! $cancelled)
            <h2 class="font-serif text-lg font-bold text-gray-900 mb-3">{{ __('Votre programme') }}</h2>
            <ol class="space-y-3 mb-6">
                @foreach($days as $d)
                    <li class="flex gap-3">
                        <span class="w-9 h-9 shrink-0 rounded-full bg-orange-600 text-white text-xs font-bold flex items-center justify-center">J{{ $loop->iteration }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $booking->departure_date->copy()->addDays($loop->index)->locale(app()->getLocale())->isoFormat('ddd D MMM') }} — {{ $d['title'] }}</p>
                            @if($d['description'])<p class="text-xs text-gray-600 mt-0.5 whitespace-pre-line">{{ $d['description'] }}</p>@endif
                            @if($d['meals'] || $d['accommodation'])<p class="text-xs text-gray-500 mt-0.5">{{ collect([$d['meals'] ? __('Repas').' : '.$d['meals'] : null, $d['accommodation'] ? __('Nuit').' : '.$d['accommodation'] : null])->filter()->join(' · ') }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if(! $cancelled && $booking->cancellation_policy)
            <p class="text-xs text-gray-500 mb-6"><i class="fas fa-rotate-left mr-1"></i>{{ __('Annulation :') }}
                {{ collect($booking->cancellation_policy)->map(fn ($r) => ($r['days'] > 0 ? __('jusqu\'à :d j avant', ['d' => $r['days']]) : __('ensuite')).' : '.$r['percent'].' %')->join(' · ') }}
            </p>
        @endif

        <div class="flex flex-wrap gap-2">
            @if($tour && ($url = $tour->publicUrl()))
                <a href="{{ $url }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-semibold text-sm transition"><i class="fas fa-route text-xs"></i>{{ __('Page du circuit') }}</a>
            @endif
            @auth
                @if((int) auth()->id() === (int) $booking->user_id)
                    <a href="{{ route('visitor.tour-bookings.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-800 font-semibold text-sm hover:border-gray-400 transition"><i class="fas fa-suitcase-rolling text-xs"></i>{{ __('Mes voyages') }}</a>
                @endif
            @endauth
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-800 font-semibold text-sm hover:border-gray-400 transition"><i class="fas fa-print text-xs"></i>{{ __('Imprimer') }}</button>
        </div>
    </div>
</div>
@endsection
