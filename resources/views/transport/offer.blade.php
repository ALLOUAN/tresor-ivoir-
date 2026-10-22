<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $offer->name }}@if($company) — {{ $company->name }}@endif — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @if($offer->mode === 'chauffeur')<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">@endif
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .tx-seg { display: grid; grid-auto-flow: column; grid-auto-columns: 1fr; gap: 4px; padding: 4px; border-radius: 14px; background: #f1ece2; }
        .tx-seg label { position: relative; text-align: center; padding: 9px 6px; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; color: #5c5548; }
        .tx-seg input { position: absolute; opacity: 0; }
        .tx-seg label:has(input:checked) { background: #fff; color: #9a4308; box-shadow: 0 2px 8px rgba(29,26,22,.12); }
        .tx-seg label:has(input:focus-visible) { outline: 3px solid #ef7c2b; }
        .tx-map { height: 260px; border-radius: 16px; border: 1px solid #ece5d6; z-index: 0; }
        .tx-trip { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; align-items: center; padding: 10px 12px; margin-top: 6px; border: 1.5px solid #ece5d6; border-radius: 14px; background: #fff; cursor: pointer; font-size: 13px; }
        .tx-trip:has(input:checked) { border-color: #b9500a; background: #fdf4ec; }
        .tx-trip input { position: absolute; opacity: 0; }
        .tx-trip.is-full { opacity: .5; cursor: not-allowed; }
        .tx-bus { display: grid; gap: 6px; padding: 12px; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
        .tx-seat { height: 38px; border-radius: 10px 10px 6px 6px; border: 1.5px solid #cfe5d6; background: #eef7f1; color: #1b6b3a; font-size: 12px; font-weight: 800; cursor: pointer; }
        .tx-seat:disabled { background: #ece8e1; border-color: #ddd6c9; color: #a39c8e; cursor: not-allowed; }
        .tx-seat.is-on { background: #b9500a; border-color: #b9500a; color: #fff; }
        .tx-seat:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 1px; }
        .tx-aisle { visibility: hidden; }
    </style>
    @include('partials.detail-theme')
    @include('partials.booking-page-theme')
    @include('partials.offer-page-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $images = collect($offer->images ?? [])->filter()->values();
    $extras = $offer->extrasList();
    $user = auth()->user();
    $min = now('Africa/Abidjan')->addHours(3)->format('Y-m-d\TH:i');
    $def = now('Africa/Abidjan')->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');
    $perLabel = ['booking' => __('/ réservation'), 'day' => __('/ jour'), 'passenger' => __('/ passager')];
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    @if($company)<a href="{{ route('transport-company.show', $company->slug) }}" class="hx-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ $company->name }}</a>@endif
    @php
        $hrs = fn ($m) => intdiv($m, 60).' h '.str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT);
        $zones = $offer->mode === 'transfer' ? $offer->zones() : [];
    @endphp
    <header class="ox-head">
        <div class="min-w-0">
            <div class="hx-pills">
                <span class="hx-pill"><i class="fas {{ \App\Models\TransportOffer::MODE_ICONS[$offer->mode] ?? 'fa-car-side' }}" aria-hidden="true"></i>{{ $offer->modeLabel() }}</span>
                @if($offer->vehicle_category)<span class="hx-pill"><i class="fas fa-car" aria-hidden="true"></i>{{ __(\App\Models\TransportOffer::VEHICLE_CATEGORIES[$offer->vehicle_category]) }}</span>@endif
            </div>
            <h1 class="hx-title">{{ $offer->name }}</h1>
            <p class="ox-where">
                @if($company)<a href="{{ route('transport-company.show', $company->slug) }}"><x-icon name="buildings" />{{ $company->name }}</a>@endif
                @if($offer->mode === 'line')<span><x-icon name="route" />{{ $offer->route_from }} → {{ $offer->route_to }}</span>@endif
                @if($offer->mode === 'transfer' && $offer->origin_label)<span><x-icon name="plane" />{{ $offer->origin_label }}</span>@endif
            </p>
        </div>
        <div class="ox-head__side">
            <button type="button" class="ti-round" data-place-share="{{ url()->current() }}" data-place-title="{{ $offer->name }}" aria-label="{{ __('Partager :name', ['name' => $offer->name]) }}" title="{{ __('Partager') }}"><x-icon name="share" /></button>
        </div>
    </header>

    @if($images->isNotEmpty())
        <div class="tp-gal is-{{ min($images->count(), 5) }} mb-5">@foreach($images->take(5) as $i => $img)<button type="button" data-lb="{{ $i }}" aria-label="{{ __('Agrandir la photo :n', ['n' => $i + 1]) }}"><img src="{{ $img }}" alt="{{ $offer->name }}" loading="{{ $i ? 'lazy' : 'eager' }}">@if($i === 4 && $images->count() > 5)<span class="tp-gal__more">+{{ $images->count() - 5 }}</span>@endif</button>@endforeach</div>
    @endif

    {{-- Infos clés --}}
    <ul class="ox-keys">
        @if($offer->max_passengers)<li><x-icon name="users-group-rounded" /><span><small>{{ __('Passagers') }}</small><b>{{ __(':n max', ['n' => $offer->max_passengers]) }}</b></span></li>@endif
        @if($offer->max_luggage)<li><x-icon name="suitcase" /><span><small>{{ __('Bagages') }}</small><b>{{ __(':n bagages', ['n' => $offer->max_luggage]) }}</b></span></li>@endif
        @if($offer->mode === 'transfer' && $offer->free_waiting_minutes)<li><x-icon name="hourglass" /><span><small>{{ __('Attente offerte') }}</small><b>{{ $offer->free_waiting_minutes }} min</b></span></li>@endif
        @if($offer->mode === 'line' && $offer->trip_minutes)<li><x-icon name="clock-circle" /><span><small>{{ __('Durée') }}</small><b>{{ __('Environ :h', ['h' => $hrs($offer->trip_minutes)]) }}</b></span></li>@endif
        @if($offer->mode === 'rental' && $offer->included_km)<li><x-icon name="route" /><span><small>{{ __('Kilométrage') }}</small><b>{{ __(':n km / jour inclus', ['n' => $offer->included_km]) }}</b></span></li>@endif
        @if($offer->cancellationLabel())<li class="is-ok"><x-icon name="restart" /><span><small>{{ __('Annulation') }}</small><b>{{ $offer->cancellationLabel() }}</b></span></li>@endif
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        <a href="#apercu">{{ __('Aperçu') }}</a>
        @if($zones)<a href="#tarifs">{{ __('Tarifs') }}</a>@endif
        @if($extras)<a href="#options">{{ __('Options') }}</a>@endif
        @if($others->isNotEmpty())<a href="#autres">{{ __('Autres offres') }}</a>@endif
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start mt-8">
        <div class="lg:col-span-3 space-y-12 min-w-0">
            <section class="ox-sec" id="apercu">
                <p class="hx-kicker">{{ __('L\'offre') }}</p>
                <h2 class="tp-h2">{{ __('En bref') }}</h2>
                @if($offer->description)<p class="hx-text">{{ $offer->description }}</p>@endif
                <ul class="ox-hl">
                    @if($offer->mode === 'transfer')
                        <li><x-icon name="tag-price" /><span><b>{{ __('Prix fixe, sans compteur') }}</b>{{ __('Le prix affiché est celui que vous payez.') }}</span></li>
                        <li><x-icon name="plane" /><span><b>{{ __('Suivi de votre vol') }}</b>{{ __('Le chauffeur s\'adapte en cas de retard.') }}</span></li>
                        <li><x-icon name="user-id" /><span><b>{{ __('Accueil personnalisé') }}</b>{{ __('Accueil avec pancarte à votre nom') }}</span></li>
                        @if($offer->free_waiting_minutes)<li><x-icon name="hourglass" /><span><b>{{ __('Attente incluse') }}</b>{{ __(':n min d\'attente gratuite', ['n' => $offer->free_waiting_minutes]) }}</span></li>@endif
                    @elseif($offer->mode === 'chauffeur')
                        <li><x-icon name="calculator" /><span><b>{{ __('Prix connu d\'avance') }}</b>{{ __('Prix calculé avant de réserver') }}</span></li>
                        @if($offer->half_day_xof)<li><x-icon name="clock-circle" /><span><b>{{ __('Demi-journée') }}</b>{{ $fmt($offer->half_day_xof) }}</span></li>@endif
                        @if($offer->full_day_xof)<li><x-icon name="sun" /><span><b>{{ __('Journée') }}</b>{{ $fmt($offer->full_day_xof) }}</span></li>@endif
                    @elseif($offer->mode === 'rental')
                        <li><x-icon name="calendar" /><span><b>{{ __(':a / jour', ['a' => $fmt((int) $offer->price_xof)]) }}</b>@if($offer->weekly_daily_xof){{ __(':a / jour dès 7 jours', ['a' => $fmt($offer->weekly_daily_xof)]) }}@endif</span></li>
                        @if($offer->included_km)<li><x-icon name="route" /><span><b>{{ __(':n km / jour inclus', ['n' => $offer->included_km]) }}</b>@if($offer->extra_km_xof){{ __(':a le km en plus', ['a' => $fmt($offer->extra_km_xof)]) }}@endif</span></li>@endif
                        @if($offer->deposit_xof)<li><x-icon name="shield-check" /><span><b>{{ __('Caution') }}</b>{{ __('Caution de :a au retrait', ['a' => $fmt($offer->deposit_xof)]) }}</span></li>@endif
                        <li><x-icon name="user-id" /><span><b>{{ __('Permis et pièce d\'identité en ligne') }}</b>@if($offer->min_driver_age){{ __('conducteur :n ans min.', ['n' => $offer->min_driver_age]) }}@endif</span></li>
                    @elseif($offer->mode === 'line')
                        <li><x-icon name="chair" /><span><b>{{ __('Choix du siège') }}</b>{{ __('Sur le plan du car, au moment de réserver.') }}</span></li>
                        <li><x-icon name="qr-code" /><span><b>{{ __('Billet QR') }}</b>{{ __('Billet QR à présenter à l\'embarquement') }}</span></li>
                    @endif
                    @if($offer->night_surcharge_percent)<li><x-icon name="moon" /><span><b>{{ __('Tarif de nuit') }}</b>{{ __('Nuit (22 h – 6 h) : +:p %', ['p' => $offer->night_surcharge_percent]) }}</span></li>@endif
                </ul>
            </section>
            @if($zones)
                <section class="ox-sec" id="tarifs">
                    <p class="hx-kicker">{{ __('Tarifs') }}</p>
                    <h2 class="tp-h2">{{ __('Prix fixe par quartier') }}</h2>
                    <div class="ox-route"><x-icon name="plane" /><span><b>{{ $offer->origin_label }}</b><small>{{ __('Dans les deux sens, même prix') }}@if($offer->round_trip_discount_percent) · {{ __('Aller-retour : −:p %', ['p' => $offer->round_trip_discount_percent]) }}@endif</small></span></div>
                    <ul class="ox-zones">
                        @foreach($zones as $i => $z)
                            <li><button type="button" class="ox-zone" data-ox-zone="{{ $i }}"><span>{{ $z['label'] }}</span><b>{{ $fmt($z['price_xof']) }}</b></button></li>
                        @endforeach
                    </ul>
                </section>
            @endif
            @if($extras)
                <section class="ox-sec" id="options">
                    <p class="hx-kicker">{{ __('À la carte') }}</p>
                    <h2 class="tp-h2">{{ __('Options') }}</h2>
                    <ul class="ox-opts">@foreach($extras as $e)<li><span>{{ $e['label'] }}</span><b>{{ $fmt($e['price_xof']) }} <small>{{ $perLabel[$e['per']] }}</small></b></li>@endforeach</ul>
                </section>
            @endif
            @if($others->isNotEmpty())
                <section class="ox-sec" id="autres">
                    <p class="hx-kicker">{{ $company?->name }}</p>
                    <h2 class="tp-h2">{{ __('Autres offres') }}</h2>
                    <div class="dx-offers">@foreach($others as $o)<a href="{{ $o->slug ? route('transport-offer.show', $o->slug) : '#' }}" class="dx-offer"><span class="dx-offer__media"><i class="fas {{ \App\Models\TransportOffer::MODE_ICONS[$o->mode] ?? 'fa-car-side' }}" aria-hidden="true"></i></span><span class="dx-offer__body"><span class="dx-offer__name">{{ $o->name }}</span><span class="text-xs text-[#6b6458]">{{ $o->modeLabel() }}</span></span><span class="dx-offer__side"><span class="dx-offer__price font-bold">{{ $fmt($o->fromPrice()) }}</span><span class="dx-offer__ask"><i class="fas fa-arrow-right" aria-hidden="true"></i></span></span></a>@endforeach</div>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-2 w-full" id="reserver">
            <div class="tp-card ox-card">
                <div class="ox-card__head">
                    <p class="tp-card__price">{{ __('À partir de') }} <b>{{ $fmt($offer->fromPrice()) }}</b></p>
                    <div class="ox-card__badges">
                        @if($offer->cancellationLabel())<span><x-icon name="restart" />{{ __('Annulation gratuite') }}</span>@endif
                        @if($offer->mode === 'transfer')<span class="is-neutral"><x-icon name="tag-price" />{{ __('Prix fixe') }}</span>@endif
                    </div>
                </div>
                <div class="ox-card__body">
                @if(! $offer->bookableOnline())
                    <p class="tp-note">{{ __('Cette offre se réserve sur demande : la compagnie vous répond avec un prix.') }}</p>
                    @if($company)<a href="{{ route('transport-company.show', $company->slug) }}#reserver" class="tp-submit inline-flex items-center justify-center gap-2"><x-icon name="chat-round-dots" />{{ __('Faire une demande') }}</a>@endif
                @elseif(! $user)
                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="tp-submit inline-flex items-center justify-center gap-2"><i class="fas fa-right-to-bracket" aria-hidden="true"></i>{{ __('Se connecter pour réserver') }}</a>
                @elseif($user->role !== 'visitor')
                    <p class="tp-note">{{ __('Les réservations se font avec un compte visiteur.') }}</p>
                @else
                    <form method="POST" action="{{ route('transport-offer.book', $offer->slug) }}" id="tx-form" data-quote="{{ route('transport-offer.quote', $offer->slug) }}">
                        @csrf
                        @if($offer->mode === 'transfer')
                            <div class="tx-seg" role="radiogroup" aria-label="{{ __('Sens du transfert') }}">
                                <label><input type="radio" name="direction" value="from" @checked(old('direction', 'from') === 'from')>{{ __('Depuis') }} {{ \Illuminate\Support\Str::limit($offer->origin_label, 18) }}</label>
                                <label><input type="radio" name="direction" value="to" @checked(old('direction') === 'to')>{{ __('Vers') }} {{ \Illuminate\Support\Str::limit($offer->origin_label, 18) }}</label>
                            </div>
                            <label for="tx-zone" class="tp-label">{{ __('Destination') }}</label>
                            <select id="tx-zone" name="zone" class="tp-select">@foreach($offer->zones() as $i => $z)<option value="{{ $i }}" @selected(old('zone') == $i)>{{ $z['label'] }} — {{ $fmt($z['price_xof']) }}</option>@endforeach</select>
                            <label for="tx-addr" class="tp-label">{{ __('Adresse ou hôtel') }}</label>
                            <input id="tx-addr" name="address" maxlength="255" value="{{ old('address') }}" class="tp-input" placeholder="{{ __('ex : Hôtel Ivoire, Cocody') }}">
                            <label for="tx-start" class="tp-label" data-label-from="{{ __('Atterrissage prévu') }}" data-label-to="{{ __('Heure de prise en charge') }}">{{ __('Atterrissage prévu') }}</label>
                            <input id="tx-start" type="datetime-local" name="starts_at" required min="{{ $min }}" value="{{ old('starts_at', $def) }}" class="tp-input">
                            <div id="tx-flight-wrap"><label for="tx-flight" class="tp-label">{{ __('Numéro de vol') }}</label>
                                <input id="tx-flight" name="flight_number" maxlength="12" value="{{ old('flight_number') }}" class="tp-input uppercase" placeholder="AF702"></div>
                            <label class="tp-extra mt-3"><input type="checkbox" name="round_trip" value="1" id="tx-rt" @checked(old('round_trip'))><span>{{ __('Aller-retour') }}@if($offer->round_trip_discount_percent) <small class="text-[#1b6b3a] font-bold">−{{ $offer->round_trip_discount_percent }} %</small>@endif</span><span></span></label>
                            <div id="tx-return" hidden><label for="tx-ret" class="tp-label">{{ __('Retour le') }}</label><input id="tx-ret" type="datetime-local" name="return_at" min="{{ $min }}" value="{{ old('return_at') }}" class="tp-input"></div>
                        @elseif($offer->mode === 'chauffeur')
                            <div class="tx-seg" role="radiogroup" aria-label="{{ __('Formule') }}">
                                @if($offer->per_km_xof)<label><input type="radio" name="package" value="trip" @checked(old('package', 'trip') === 'trip')>{{ __('Trajet') }}</label>@endif
                                @if($offer->half_day_xof)<label><input type="radio" name="package" value="half_day" @checked(old('package', $offer->per_km_xof ? '' : 'half_day') === 'half_day')>{{ __('Demi-journée') }}</label>@endif
                                @if($offer->full_day_xof)<label><input type="radio" name="package" value="full_day" @checked(old('package') === 'full_day')>{{ __('Journée') }}</label>@endif
                            </div>
                            <div id="tx-map-wrap">
                                <p class="tp-note">{{ __('Touchez la carte : 1er point = départ, 2e point = arrivée.') }} <button type="button" id="tx-map-reset" class="font-bold text-[#9a4308]">{{ __('Recommencer') }}</button></p>
                                <div id="tx-map" class="tx-map" role="application" aria-label="{{ __('Carte : choisissez le départ puis l\'arrivée') }}"></div>
                                <input type="hidden" name="pickup_lat" value="{{ old('pickup_lat') }}"><input type="hidden" name="pickup_lng" value="{{ old('pickup_lng') }}">
                                <input type="hidden" name="dropoff_lat" value="{{ old('dropoff_lat') }}"><input type="hidden" name="dropoff_lng" value="{{ old('dropoff_lng') }}">
                            </div>
                            <label for="tx-pl" class="tp-label">{{ __('Adresse de départ') }}</label><input id="tx-pl" name="pickup_label" required maxlength="255" value="{{ old('pickup_label') }}" class="tp-input" placeholder="{{ __('ex : Riviera 2, rue des Jardins') }}">
                            <label for="tx-dl" class="tp-label">{{ __('Adresse d\'arrivée') }}</label><input id="tx-dl" name="dropoff_label" maxlength="255" value="{{ old('dropoff_label') }}" class="tp-input" placeholder="{{ __('ex : Plateau, tour BICICI') }}">
                            <label for="tx-start" class="tp-label">{{ __('Date et heure') }}</label><input id="tx-start" type="datetime-local" name="starts_at" required min="{{ $min }}" value="{{ old('starts_at', $def) }}" class="tp-input">
                        @elseif($offer->mode === 'rental')
                            <label for="tx-start" class="tp-label">{{ __('Retrait') }}</label><input id="tx-start" type="datetime-local" name="starts_at" required min="{{ $min }}" value="{{ old('starts_at', $def) }}" class="tp-input">
                            <label for="tx-ret" class="tp-label">{{ __('Retour') }}</label><input id="tx-ret" type="datetime-local" name="return_at" required min="{{ $min }}" value="{{ old('return_at', now('Africa/Abidjan')->addDays(4)->setTime(10, 0)->format('Y-m-d\TH:i')) }}" class="tp-input">
                            <label for="tx-pl" class="tp-label">{{ __('Lieu de retrait') }}</label><input id="tx-pl" name="pickup_label" maxlength="255" value="{{ old('pickup_label', $company?->adresse) }}" class="tp-input">
                        @elseif($offer->mode === 'line')
                            <span class="tp-label">{{ __('Départ') }}</span>
                            <div class="max-h-80 overflow-y-auto pr-1 -mr-1" role="radiogroup" aria-label="{{ __('Départ') }}">
                            @forelse($trips as $t)
                                @php $left = $t->seatsLeft(); @endphp
                                <label class="tx-trip relative {{ $left < 1 ? 'is-full' : '' }}"><input type="radio" name="trip_id" value="{{ $t->id }}" @disabled($left < 1) @checked(old('trip_id', $loop->first ? $t->id : null) == $t->id)>
                                    <span><b>{{ ucfirst($t->departs_at->timezone('Africa/Abidjan')->locale(app()->getLocale())->isoFormat('ddd D MMM · HH:mm')) }}</b><br><small class="text-[#6b6458]">{{ $left < 1 ? __('Complet') : trans_choice(':count place libre|:count places libres', $left) }}</small></span>
                                    <b>{{ $fmt($t->price()) }}</b></label>
                            @empty
                                <p class="tp-note">{{ __('Aucun départ programmé pour le moment.') }}</p>
                            @endforelse
                            </div>
                            <span class="tp-label">{{ __('Vos sièges') }}</span>
                            <div class="tx-bus" id="tx-bus" data-url="{{ route('transport-offer.seats', [$offer->slug, '__T__']) }}" role="group" aria-label="{{ __('Plan des sièges') }}"></div>
                            <p class="tp-note"><span class="inline-block w-3 h-3 rounded bg-[#eef7f1] border border-[#cfe5d6] align-middle"></span> {{ __('libre') }} · <span class="inline-block w-3 h-3 rounded bg-[#ece8e1] align-middle"></span> {{ __('pris') }} · <span class="inline-block w-3 h-3 rounded bg-[#b9500a] align-middle"></span> {{ __('votre choix') }}</p>
                            <div id="tx-seat-inputs"></div>
                        @endif

                        @if($offer->mode !== 'line' && $offer->mode !== 'rental')
                            <div class="grid grid-cols-2 gap-2">
                                <label class="tp-label">{{ __('Passagers') }}<input type="number" name="passengers" min="1" max="{{ $offer->max_passengers ?: 50 }}" value="{{ old('passengers', 1) }}" class="tp-input mt-1.5" data-quote-input></label>
                                <label class="tp-label">{{ __('Bagages') }}<input type="number" name="luggage" min="0" max="{{ $offer->max_luggage ?: 20 }}" value="{{ old('luggage', 1) }}" class="tp-input mt-1.5"></label>
                            </div>
                        @endif
                        @if($extras)
                            <span class="tp-label">{{ __('Options') }}</span>
                            @foreach($extras as $i => $e)<label class="tp-extra"><input type="checkbox" name="extras[]" value="{{ $i }}" @checked(in_array($i, old('extras', [])))><span>{{ $e['label'] }}</span><span class="font-bold whitespace-nowrap">+ {{ $fmt($e['price_xof']) }} <small class="font-medium text-[#6b6458]">{{ $perLabel[$e['per']] }}</small></span></label>@endforeach
                        @endif
                        <label for="tx-phone" class="tp-label">{{ __('Téléphone (joignable le jour J)') }}</label><input id="tx-phone" type="tel" name="phone" required maxlength="30" value="{{ old('phone', $user->phone) }}" class="tp-input">
                        <label for="tx-msg" class="tp-label">{{ __('Précisions pour le chauffeur') }}</label><textarea id="tx-msg" name="message" rows="2" maxlength="1000" class="tp-input">{{ old('message') }}</textarea>
                        <span class="tp-label">{{ __('Paiement') }}</span>
                        <div class="tp-choice">
                            <label><input type="radio" name="payment_method" value="cinetpay" @checked(old('payment_method', 'cinetpay') === 'cinetpay')>{{ __('Mobile Money / carte') }}<small>CinetPay</small></label>
                            <label><input type="radio" name="payment_method" value="wallet" @checked(old('payment_method') === 'wallet')>{{ __('Mon solde') }}<small>Trésors d'Ivoire</small></label>
                        </div>
                        <div class="tp-sum" id="tx-sum" aria-live="polite"><p>{{ __('Calcul du prix…') }}</p></div>
                        @if($errors->transport->any())<p class="tp-err">{{ $errors->transport->first() }}</p>@endif
                        <p class="tp-err hidden" id="tx-err"></p>
                        <button type="submit" class="tp-submit" id="tx-submit"><i class="fas fa-lock mr-1.5" aria-hidden="true"></i><span id="tx-label">{{ __('Réserver') }}</span></button>
                        @if($offer->cancellationLabel())<p class="tp-note"><x-icon name="restart" class="text-[#1b6b3a] mr-1" />{{ $offer->cancellationLabel() }}.</p>@endif
                    </form>
                @endif
                </div>
            </div>
        </aside>
    </div>
</div>

@if($offer->bookableOnline())
<div class="ox-bar" data-ox-bar>
    <span><small>{{ __('À partir de') }}</small><b>{{ $fmt($offer->fromPrice()) }}</b></span>
    <a href="#reserver"><x-icon name="calendar" />{{ __('Réserver') }}</a>
</div>
@endif

@if($images->isNotEmpty())
<div class="tp-lb" id="tp-lb" role="dialog" aria-modal="true" aria-label="{{ __('Photos') }}">
    <button type="button" data-lb-close style="top:16px;right:16px" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <button type="button" data-lb-step="-1" style="left:16px;top:50%" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left"></i></button>
    <img src="" alt="{{ $offer->name }}">
    <button type="button" data-lb-step="1" style="right:16px;top:50%" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right"></i></button>
</div>
<script>
(function () {
    const photos = @json($images->values());
    const lb = document.getElementById('tp-lb'), lbImg = lb.querySelector('img');
    let at = 0;
    const show = i => { at = (i + photos.length) % photos.length; lbImg.src = photos[at]; };
    document.querySelectorAll('[data-lb]').forEach(b => b.addEventListener('click', () => { show(+b.dataset.lb); lb.classList.add('is-open'); }));
    lb.querySelector('[data-lb-close]').addEventListener('click', () => lb.classList.remove('is-open'));
    lb.querySelectorAll('[data-lb-step]').forEach(b => b.addEventListener('click', () => show(at + (+b.dataset.lbStep))));
    document.addEventListener('keydown', e => { if (lb.classList.contains('is-open') && e.key === 'Escape') lb.classList.remove('is-open'); });
})();
</script>
@endif

{{-- Tarifs par quartier : un clic présélectionne la destination dans le formulaire. --}}
<script>
(function () {
    const sel = document.getElementById('tx-zone'), zones = document.querySelectorAll('[data-ox-zone]');
    const mark = v => zones.forEach(z => z.classList.toggle('is-on', z.dataset.oxZone === String(v)));
    zones.forEach(z => z.addEventListener('click', () => {
        if (sel) { sel.value = z.dataset.oxZone; sel.dispatchEvent(new Event('change', { bubbles: true })); mark(sel.value); }
        document.getElementById('reserver')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
    if (sel) { mark(sel.value); sel.addEventListener('change', () => mark(sel.value)); }
})();
</script>

@if($offer->mode === 'chauffeur' && $user?->role === 'visitor')<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>@endif
<script>
(function () {
    const form = document.getElementById('tx-form'); if (!form) return;
    const mode = @json($offer->mode);
    const T = {{ \Illuminate\Support\Js::from(['total' => __('Total'), 'pay' => __('Réserver et payer :a'), 'error' => __('Une erreur est survenue.'), 'deposit' => __('Caution à verser au retrait'), 'vehicle' => __('Véhicule'), 'seat' => __('Siège :n'), 'pickSeat' => __('Choisissez vos sièges sur le plan.'), 'pickMap' => __('Placez le départ puis l\x27arrivée sur la carte pour voir le prix.')]) }};
    const fmt = n => Number(n).toLocaleString('fr-FR') + ' FCFA';
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const sum = document.getElementById('tx-sum'), err = document.getElementById('tx-err'), label = document.getElementById('tx-label'), submit = document.getElementById('tx-submit');
    let seq = 0, timer = null;
    function refresh() { clearTimeout(timer); timer = setTimeout(quote, 250); }
    function quote() {
        if (mode === 'chauffeur' && (form.querySelector('[name=package]:checked') || {}).value === 'trip' && !(form.querySelector('[name=pickup_lat]').value && form.querySelector('[name=dropoff_lat]').value)) {
            seq++; err.classList.add('hidden'); submit.disabled = true; sum.innerHTML = '<p>' + T.pickMap + '</p>'; return;
        }
        const params = new URLSearchParams(new FormData(form)); params.delete('_token');
        const my = ++seq;
        fetch(form.dataset.quote + '?' + params.toString(), { headers: { Accept: 'application/json' } }).then(r => r.json().then(j => [r.ok, j])).then(([ok, q]) => {
            if (my !== seq) return;
            if (!ok || !q.ok) { err.textContent = q.message || T.error; err.classList.remove('hidden'); submit.disabled = true; sum.innerHTML = ''; return; }
            err.classList.add('hidden'); submit.disabled = false;
            sum.innerHTML = q.lines.map(l => '<p><span>' + esc(l.label) + '</span><span>' + (l.amount_xof < 0 ? '−' + fmt(-l.amount_xof) : fmt(l.amount_xof)) + '</span></p>').join('')
                + (q.vehicle ? '<p><span>' + T.vehicle + '</span><span>' + esc(q.vehicle) + '</span></p>' : '')
                + '<p class="is-total"><span>' + T.total + '</span><span>' + fmt(q.total) + '</span></p>'
                + (q.deposit_on_site ? '<p><span>' + T.deposit + '</span><span>' + fmt(q.deposit_on_site) + '</span></p>' : '');
            label.textContent = T.pay.replace(':a', fmt(q.total));
        }).catch(() => { err.textContent = T.error; err.classList.remove('hidden'); });
    }
    form.addEventListener('change', refresh);
    form.addEventListener('input', e => { if (e.target.matches('[data-quote-input], [type=datetime-local]')) refresh(); });

    if (mode === 'transfer') {
        const rt = document.getElementById('tx-rt'), ret = document.getElementById('tx-return'), fw = document.getElementById('tx-flight-wrap'), sl = document.querySelector('label[for=tx-start]');
        const sync = () => { ret.hidden = !rt.checked; const from = form.querySelector('[name=direction]:checked').value === 'from'; fw.hidden = !from; sl.textContent = from ? sl.dataset.labelFrom : sl.dataset.labelTo; };
        form.addEventListener('change', sync); sync();
    }
    if (mode === 'chauffeur' && window.L) {
        const wrap = document.getElementById('tx-map-wrap');
        const map = L.map('tx-map').setView([5.3364, -4.0267], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
        const f = n => form.querySelector('[name=' + n + ']');
        let a = null, b = null, line = null;
        const draw = () => { if (line) map.removeLayer(line); if (a && b) line = L.polyline([a.getLatLng(), b.getLatLng()], { color: '#b9500a', dashArray: '6 6' }).addTo(map); };
        const set = (m, p) => { f(p + '_lat').value = m.getLatLng().lat.toFixed(6); f(p + '_lng').value = m.getLatLng().lng.toFixed(6); draw(); refresh(); };
        if (f('pickup_lat').value) { a = L.marker([+f('pickup_lat').value, +f('pickup_lng').value], { draggable: true }).addTo(map).on('dragend', () => set(a, 'pickup')); }
        if (f('dropoff_lat').value) { b = L.marker([+f('dropoff_lat').value, +f('dropoff_lng').value], { draggable: true }).addTo(map).on('dragend', () => set(b, 'dropoff')); draw(); }
        map.on('click', e => {
            if (!a) { a = L.marker(e.latlng, { draggable: true }).addTo(map).bindTooltip('A').on('dragend', () => set(a, 'pickup')); set(a, 'pickup'); }
            else if (!b) { b = L.marker(e.latlng, { draggable: true }).addTo(map).bindTooltip('B').on('dragend', () => set(b, 'dropoff')); set(b, 'dropoff'); }
        });
        document.getElementById('tx-map-reset').addEventListener('click', () => { [a, b, line].forEach(m => m && map.removeLayer(m)); a = b = line = null; ['pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng'].forEach(n => f(n).value = ''); refresh(); });
        const syncPkg = () => { const trip = (form.querySelector('[name=package]:checked') || {}).value === 'trip'; wrap.hidden = !trip; if (trip) setTimeout(() => map.invalidateSize(), 50); };
        form.addEventListener('change', syncPkg); syncPkg();
    }
    if (mode === 'line') {
        const bus = document.getElementById('tx-bus'), inputs = document.getElementById('tx-seat-inputs');
        let chosen = @json(array_map('intval', old('seats', [])));
        function loadSeats() {
            const t = form.querySelector('[name=trip_id]:checked'); if (!t) { bus.innerHTML = ''; return; }
            fetch(bus.dataset.url.replace('__T__', t.value), { headers: { Accept: 'application/json' } }).then(r => r.json()).then(s => {
                chosen = chosen.filter(n => !s.taken.includes(n));
                const per = s.per_row, half = Math.floor(per / 2);
                bus.style.gridTemplateColumns = 'repeat(' + (per + 1) + ', minmax(0, 1fr))';
                let html = '';
                for (let n = 1; n <= s.total; n++) {
                    const col = (n - 1) % per;
                    if (col === half) html += '<span class="tx-aisle"></span>';
                    const taken = s.taken.includes(n), on = chosen.includes(n);
                    html += '<button type="button" class="tx-seat' + (on ? ' is-on' : '') + '" data-seat="' + n + '"' + (taken ? ' disabled' : '') + ' aria-pressed="' + on + '" aria-label="' + T.seat.replace(':n', n) + '">' + n + '</button>';
                }
                bus.innerHTML = html; syncInputs();
            });
        }
        function syncInputs() {
            inputs.innerHTML = chosen.map(n => '<input type="hidden" name="seats[]" value="' + n + '">').join('');
            if (!chosen.length) { clearTimeout(timer); seq++; err.classList.add('hidden'); submit.disabled = true; sum.innerHTML = '<p>' + T.pickSeat + '</p>'; return; }
            refresh();
        }
        bus.addEventListener('click', e => { const b = e.target.closest('.tx-seat'); if (!b || b.disabled) return; const n = +b.dataset.seat; chosen = chosen.includes(n) ? chosen.filter(x => x !== n) : chosen.concat(n); b.classList.toggle('is-on'); b.setAttribute('aria-pressed', chosen.includes(n)); syncInputs(); });
        form.addEventListener('change', e => { if (e.target.name === 'trip_id') { chosen = []; loadSeats(); } });
        loadSeats();
    } else { quote(); }
})();
</script>
@include('partials.homepage-footer')
</body>
</html>
