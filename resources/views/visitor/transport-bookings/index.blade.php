@extends('layouts.visitor-public')

@section('title', __('Mes trajets'))
@section('page-title', __('Mes trajets'))

@section('content')
@php
    $tones = ['paid' => 'text-emerald-300 bg-emerald-500/10', 'pending_payment' => 'text-slate-300 bg-slate-800', 'failed' => 'text-rose-300 bg-rose-500/10', 'cancelled' => 'text-slate-400 bg-slate-800', 'refunded' => 'text-sky-300 bg-sky-500/10'];
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $in = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100';
    $tz = 'Africa/Abidjan';
@endphp
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')
    @if(session('success'))<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">{{ $errors->first() }}</div>@endif

    <div class="space-y-3">
        @forelse($bookings as $b)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-white font-semibold"><i class="fas {{ \App\Models\TransportOffer::MODE_ICONS[$b->mode] ?? 'fa-car-side' }} text-orange-400 mr-1.5"></i>{{ $b->offer_name }}</p>
                        <p class="text-slate-300 text-sm mt-0.5">{{ $b->pickup_label }}@if($b->dropoff_label) → {{ $b->dropoff_label }}@endif</p>
                        <p class="text-slate-400 text-sm">{{ ucfirst($b->starts_at->timezone($tz)->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY · HH:mm')) }}@if($b->mode === 'rental') → {{ $b->ends_at->timezone($tz)->locale(app()->getLocale())->isoFormat('ddd D MMM · HH:mm') }}@endif
                            · {{ $b->passengers }} {{ __('pers.') }}@if($b->flight_number) · {{ __('vol :v', ['v' => $b->flight_number]) }}@endif @if($b->seats) · {{ __('sièges :s', ['s' => implode(', ', $b->seats)]) }}@endif · {{ $b->reference }}</p>
                        <p class="text-sm mt-1 text-slate-200">{{ $fmt($b->amount_total_xof) }}@if($b->refund_xof)<span class="text-emerald-300"> · {{ __('remboursé') }}</span>@endif</p>
                        @if($b->driver)<p class="text-sm text-emerald-300 mt-1"><i class="fas fa-user mr-1"></i>{{ $b->driver->name }} · <a href="tel:{{ $b->driver->phone }}" class="underline">{{ $b->driver->phone }}</a>@if($b->vehicle) · {{ $b->vehicle->name }} {{ $b->vehicle->plate }}@endif</p>@endif
                        @if($b->tripStatusLabel())<p class="text-xs text-amber-300 mt-0.5"><i class="fas fa-circle text-[7px] mr-1"></i>{{ $b->tripStatusLabel() }}</p>@endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tones[$b->status] ?? '' }}">{{ $b->statusLabel() }}</span>
                        @if($b->status === 'paid')<a href="{{ $b->confirmationUrl() }}" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5"><i class="fas {{ $b->mode === 'line' ? 'fa-qrcode' : 'fa-file-lines' }} mr-1"></i>{{ $b->mode === 'line' ? __('Mon billet') : __('Détails') }}</a>@endif
                    </div>
                </div>

                @if($b->mode === 'rental' && $b->status === 'paid')
                    <details class="mt-3" @if($b->needsDocuments()) open @endif>
                        <summary class="cursor-pointer text-sm {{ $b->needsDocuments() ? 'text-amber-300' : 'text-slate-300' }}"><i class="fas fa-id-card mr-1"></i>{{ $b->needsDocuments() ? __('Documents à fournir avant le retrait') : __('Documents et contrat') }}</summary>
                        <form method="POST" action="{{ route('visitor.transport-bookings.documents', $b) }}" enctype="multipart/form-data" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @csrf
                            <label class="text-xs text-slate-400">{{ __('Permis de conduire (photo ou PDF)') }} @if(! empty($b->documents['license']))<span class="text-emerald-300">✓</span>@endif<input type="file" name="license" accept="image/*,application/pdf" class="{{ $in }} w-full mt-1"></label>
                            <label class="text-xs text-slate-400">{{ __('Pièce d\'identité') }} @if(! empty($b->documents['id_card']))<span class="text-emerald-300">✓</span>@endif<input type="file" name="id_card" accept="image/*,application/pdf" class="{{ $in }} w-full mt-1"></label>
                            <label class="text-xs text-slate-400">{{ __('N° de permis') }}<input name="license_number" maxlength="40" value="{{ $b->documents['license_number'] ?? '' }}" class="{{ $in }} w-full mt-1"></label>
                            <label class="text-xs text-slate-300 flex items-start gap-2 sm:col-span-2"><input type="checkbox" name="accept_contract" value="1" @checked($b->contract_accepted_at) class="mt-0.5 rounded border-slate-600 bg-slate-800 text-orange-500">
                                <span>{{ __('J\'accepte le contrat de location : caution de :c au retrait, :km km/jour inclus (:x le km en plus), état des lieux contradictoire au départ et au retour, carburant rendu au même niveau.', ['c' => $fmt((int) ($b->deposit_on_site_xof ?? 0)), 'km' => $b->offer?->included_km ?: '∞', 'x' => $fmt((int) ($b->offer?->extra_km_xof ?? 0))]) }}@if($b->contract_accepted_at) <span class="text-emerald-300">({{ __('accepté le :d', ['d' => $b->contract_accepted_at->format('d/m/Y')]) }})</span>@endif</span></label>
                            <div class="sm:col-span-2"><button class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-2">{{ __('Enregistrer') }}</button></div>
                        </form>
                    </details>
                @endif
                @if($b->canBeModified())
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm text-slate-200"><i class="fas fa-pen mr-1"></i>{{ __('Modifier l\'heure') }}{{ $b->mode === 'transfer' ? ' '.__('ou le vol') : '' }}</summary>
                        <form method="POST" action="{{ route('visitor.transport-bookings.modify', $b) }}" class="mt-2 flex flex-wrap items-end gap-2">
                            @csrf
                            <label class="text-xs text-slate-400">{{ __('Date et heure') }}<input type="datetime-local" name="starts_at" required value="{{ $b->starts_at->timezone($tz)->format('Y-m-d\TH:i') }}" class="{{ $in }} mt-1 block"></label>
                            @if($b->mode === 'transfer')<label class="text-xs text-slate-400">{{ __('Vol') }}<input name="flight_number" maxlength="12" value="{{ $b->flight_number }}" class="{{ $in }} mt-1 block w-28 uppercase"></label>@endif
                            <button class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-2">{{ __('Enregistrer') }}</button>
                        </form>
                    </details>
                @endif
                @if($b->isCancellableByClient())
                    <form method="POST" action="{{ route('visitor.transport-bookings.cancel', $b) }}" class="mt-3" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Annuler cette réservation ?')) }});">
                        @csrf
                        <button class="text-xs text-rose-300 hover:text-rose-200 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Annuler gratuitement') }} · {{ __('remboursement :a', ['a' => $fmt($b->amount_total_xof)]) }}</button>
                        <span class="text-xs text-slate-500 ml-1">{{ __('jusqu\'au :d', ['d' => $b->cancellable_until->timezone($tz)->format('d/m H:i')]) }}</span>
                    </form>
                @endif
            </div>
        @empty
            @include('partials.visitor-empty', ['icon' => 'bus', 'title' => __('Aucun trajet réservé pour le moment.'), 'text' => __('Cars et navettes entre les villes, à réserver en ligne.'), 'cta' => ['url' => route('transport-company.index'), 'label' => __('Voir les compagnies de transport'), 'icon' => 'bus']])
        @endforelse
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</div>
@endsection
