@extends('layouts.visitor-public')

@section('title', __('Ma réservation de transport'))
@section('page-title', __('Ma réservation de transport'))

@section('content')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $ok = $b->status === \App\Models\TransportBooking::STATUS_PAID;
    $tz = 'Africa/Abidjan';
@endphp
<div class="max-w-2xl mx-auto">
    <div class="bg-white border {{ $ok ? 'border-emerald-200' : 'border-gray-200' }} rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full {{ $ok ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center shrink-0"><i class="fas {{ $ok ? 'fa-circle-check text-emerald-600' : 'fa-ban text-gray-500' }} text-xl"></i></div>
            <div class="min-w-0">
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900">{{ $ok ? __('Transport confirmé') : $b->statusLabel() }}</h1>
                <p class="text-gray-500 text-sm">{{ $b->offer_name }} · {{ $b->reference }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 sm:col-span-2">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $b->mode === 'rental' ? __('Location') : __('Trajet') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $b->pickup_label ?: '—' }}@if($b->dropoff_label) → {{ $b->dropoff_label }}@endif</p>
                <p class="text-gray-600 text-sm">{{ ucfirst($b->starts_at->timezone($tz)->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY · HH:mm')) }}
                    @if($b->mode === 'rental') → {{ $b->ends_at->timezone($tz)->locale(app()->getLocale())->isoFormat('ddd D MMM · HH:mm') }}@endif</p>
                @if($b->round_trip && $b->return_at)<p class="text-gray-600 text-sm">{{ __('Retour') }} : {{ $b->return_at->timezone($tz)->locale(app()->getLocale())->isoFormat('ddd D MMM · HH:mm') }}</p>@endif
                @if($b->flight_number)<p class="text-gray-600 text-sm"><i class="fas fa-plane mr-1"></i>{{ __('Vol :v — le chauffeur suit votre arrivée', ['v' => $b->flight_number]) }}@if($b->offer?->free_waiting_minutes), {{ __(':n min d\'attente gratuite', ['n' => $b->offer->free_waiting_minutes]) }}@endif</p>@endif
                @if($b->distance_km)<p class="text-gray-500 text-xs">{{ __('Distance estimée : :km km', ['km' => number_format($b->distance_km, 1, ',', ' ')]) }}</p>@endif
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Passagers') }}</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $b->passengers }}@if($b->seats) · {{ __('sièges :s', ['s' => implode(', ', $b->seats)]) }}@endif @if($b->luggage) · {{ __(':n bagage(s)', ['n' => $b->luggage]) }}@endif</p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Chauffeur et véhicule') }}</p>
                @if($b->driver)<p class="text-gray-800 text-sm font-medium mt-0.5">{{ $b->driver->name }} · <a href="tel:{{ $b->driver->phone }}" class="text-orange-700">{{ $b->driver->phone }}</a></p>@endif
                @if($b->vehicle)<p class="text-gray-600 text-sm">{{ $b->vehicle->name }}@if($b->vehicle->plate) · <span class="font-mono">{{ $b->vehicle->plate }}</span>@endif</p>@endif
                @if(! $b->driver)<p class="text-gray-500 text-sm mt-0.5">{{ __('Communiqués dès l\'affectation par la compagnie.') }}</p>@endif
                @if($b->tripStatusLabel())<p class="text-emerald-700 text-xs font-semibold mt-1"><i class="fas fa-circle text-[7px] mr-1"></i>{{ $b->tripStatusLabel() }}</p>@endif
            </div>
        </div>

        @if($ok && $b->mode === 'line' && $b->ticketCheckUrl())
            <div class="rounded-2xl border border-gray-200 px-4 py-5 mb-6 text-center">
                <p class="text-sm font-semibold text-gray-900">{{ __('Votre billet') }}</p>
                <img src="{{ \App\Support\Qr::dataUri($b->ticketCheckUrl(), 260) }}" alt="{{ __('QR code du billet') }}" class="w-48 h-48 mx-auto my-3">
                <p class="font-mono text-lg tracking-widest text-gray-900">{{ $b->ticketCode() }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('À présenter à l\'embarquement, 15 minutes avant le départ.') }}</p>
            </div>
        @endif

        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 mb-6 space-y-1.5">
            @foreach($b->price_lines ?? [] as $l)
                <div class="flex justify-between gap-3 text-xs text-gray-600"><span>{{ $l['label'] }}</span><span class="whitespace-nowrap">{{ $l['amount_xof'] < 0 ? '−'.$fmt(-$l['amount_xof']) : $fmt($l['amount_xof']) }}</span></div>
            @endforeach
            <div class="flex justify-between text-sm font-bold text-gray-900 pt-1.5 border-t border-orange-200/70"><span>{{ __('Payé') }}</span><span>{{ $fmt($b->amount_total_xof) }}</span></div>
            @if($b->deposit_on_site_xof)<div class="flex justify-between text-sm text-orange-700"><span>{{ __('Caution à verser au retrait (rendue au retour)') }}</span><span>{{ $fmt($b->deposit_on_site_xof) }}</span></div>@endif
            @if($b->refund_xof)<div class="flex justify-between text-sm text-emerald-700"><span>{{ __('Remboursé sur votre solde') }}</span><span>{{ $fmt($b->refund_xof) }}</span></div>@endif
        </div>

        @if($ok && $b->needsDocuments())
            <p class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800 mb-6"><i class="fas fa-id-card mr-1.5"></i>{{ __('Avant le retrait : déposez votre permis et votre pièce d\'identité, et acceptez le contrat depuis « Mes trajets ».') }}</p>
        @endif
        @if($ok && $b->cancellable_until)
            <p class="text-sm mb-6 {{ $b->isCancellableByClient() ? 'text-emerald-700' : 'text-gray-500' }}"><i class="fas fa-rotate-left mr-1.5"></i>{{ $b->isCancellableByClient() ? __('Annulation et modification gratuites jusqu\'au :d, depuis « Mes trajets ».', ['d' => $b->cancellable_until->timezone($tz)->format('d/m/Y à H:i')]) : __('Le délai d\'annulation gratuite est dépassé.') }}</p>
        @endif

        <div class="flex flex-wrap gap-2">
            @if($b->offer?->slug)<a href="{{ route('transport-offer.show', $b->offer->slug) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-semibold text-sm"><i class="fas fa-arrow-left text-xs"></i>{{ __('Page de l\'offre') }}</a>@endif
            @if($company?->phone)<a href="tel:{{ $company->phone }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-800 font-semibold text-sm"><i class="fas fa-phone text-xs"></i>{{ $company->name }}</a>@endif
            @auth @if((int) auth()->id() === (int) $b->user_id)<a href="{{ route('visitor.transport-bookings.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-gray-800 font-semibold text-sm"><i class="fas fa-car-side text-xs"></i>{{ __('Mes trajets') }}</a>@endif @endauth
        </div>
    </div>
</div>
@endsection
