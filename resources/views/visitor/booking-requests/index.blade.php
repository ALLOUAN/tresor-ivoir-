@extends('layouts.visitor-public')

@section('title', __('Tables & activités'))
@section('page-title', __('Tables & activités'))

@section('content')
@php
    $tones = [
        'new' => 'text-amber-300 bg-amber-500/10',
        'confirmed' => 'text-emerald-300 bg-emerald-500/10',
        'declined' => 'text-rose-300 bg-rose-500/10',
        'cancelled' => 'text-slate-400 bg-slate-800',
        'expired' => 'text-slate-400 bg-slate-800',
    ];
@endphp
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')

    <p class="text-slate-400 text-sm mb-4">{{ __('Vos demandes de réservation dans les restaurants, lieux de loisirs et agences de circuits.') }}</p>

    <div class="space-y-3">
        @forelse($bookings as $b)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-4 flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $b->bookable_name }}@if($b->item_name) <span class="text-slate-400 font-normal">— {{ $b->item_name }}</span>@endif</p>
                    <p class="text-slate-400 text-sm mt-0.5">
                        {{ $b->booking_date->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY') }}@if($b->booking_time) à {{ $b->booking_time }}@endif
                        · {{ $b->party_size }} {{ $b->unitLabel() }} · {{ $b->reference }}
                    </p>
                    @if($b->pickup_location)<p class="text-xs mt-0.5 text-slate-300"><i class="fas fa-route mr-1"></i>{{ $b->pickup_location }} → {{ $b->dropoff_location }}</p>@endif
                    @if((int) $b->amount_due_xof > 0)
                        <p class="text-sm mt-1 {{ $b->payment_status === 'paid' ? 'text-emerald-300' : 'text-slate-200' }}">
                            {{ number_format((int) $b->amount_due_xof, 0, ',', ' ') }} FCFA · {{ $b->status === 'new' ? __('Prix indicatif') : $b->paymentLabel() }}
                        </p>
                    @endif
                    @if($b->promo_code)
                        <p class="text-xs mt-0.5 text-emerald-300"><i class="fas fa-tag mr-1"></i>{{ __('Code promo') }} {{ $b->promo_code }}@if($b->promo_discount_xof) (−{{ number_format($b->promo_discount_xof, 0, ',', ' ') }} FCFA)@endif</p>
                    @endif
                    @if($b->provider_note)
                        <p class="text-slate-300 text-xs mt-1.5">{{ __('Message de l\'établissement :') }} {{ $b->provider_note }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tones[$b->status] ?? '' }}">{{ $b->statusLabel() }}</span>
                    @if($b->canBePaidOnline())
                        <form method="POST" action="{{ route('visitor.booking-requests.pay', $b) }}" class="flex items-center gap-1.5">
                            @csrf
                            <button type="submit" name="payment_method" value="cinetpay" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5">{{ __('Payer en ligne') }}</button>
                            <button type="submit" name="payment_method" value="wallet" class="text-xs text-slate-200 border border-slate-700 hover:border-slate-500 rounded-lg px-3 py-1.5">{{ __('Avec mon solde') }}</button>
                        </form>
                    @endif
                    @if($b->isOpen())
                        <form method="POST" action="{{ route('visitor.booking-requests.cancel', $b) }}" onsubmit="return confirm('Annuler cette demande ?');">
                            @csrf
                            <button type="submit" class="text-xs text-rose-300 hover:text-rose-200 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Annuler') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            @include('partials.visitor-empty', ['icon' => 'plate', 'title' => __('Aucune demande pour le moment.'), 'text' => __('Réservez une table ou une activité depuis la fiche d\'un établissement.'), 'cta' => ['url' => route('restaurant.index'), 'label' => __('Trouver un restaurant'), 'icon' => 'chef-hat']])
        @endforelse
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</div>
@endsection
