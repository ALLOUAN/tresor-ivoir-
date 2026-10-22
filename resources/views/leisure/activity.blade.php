<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $activity->name }}@if($place) — {{ $place->name }}@endif — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $activity->description), 155) }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
    @include('partials.detail-theme')
    @include('partials.booking-page-theme')
    @include('partials.offer-page-theme')
    <style>
        .lx-date { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin; }
        .lx-date button { flex: none; width: 58px; padding: 8px 0; border-radius: 14px; border: 1.5px solid #ece5d6; background: #fff; text-align: center; line-height: 1.1; cursor: pointer; }
        .lx-date button small { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #8a7f6b; }
        .lx-date button b { display: block; font-size: 17px; color: #1d1a16; }
        .lx-date button.is-active { border-color: #b9500a; background: #b9500a; } .lx-date button.is-active * { color: #fff; }
        .lx-date button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .lx-slots { display: grid; grid-template-columns: repeat(auto-fill, minmax(86px, 1fr)); gap: 6px; }
        .lx-slot { position: relative; display: flex; flex-direction: column; align-items: center; gap: 1px; padding: 8px 4px; border-radius: 12px; border: 1.5px solid #ece5d6; background: #fff; cursor: pointer; }
        .lx-slot strong { font-size: 14px; font-weight: 800; color: #1d1a16; } .lx-slot small { font-size: 10.5px; font-weight: 600; color: #1b6b3a; }
        .lx-slot em { position: absolute; top: -7px; right: -4px; padding: 0 5px; border-radius: 999px; font-size: 9.5px; font-style: normal; font-weight: 800; background: #febf48; color: #3d2b00; }
        .lx-slot em.is-up { background: #f4efe4; color: #5c5548; }
        .lx-slot.is-full { background: #f1eee8; border-style: dashed; } .lx-slot.is-full strong { color: #a39c8e; text-decoration: line-through; } .lx-slot.is-full small { color: #a3341f; }
        .lx-slot.is-past { opacity: .45; cursor: not-allowed; }
        .lx-slot.is-active { border-color: #b9500a; background: #b9500a; } .lx-slot.is-active * { color: #fff !important; }
        .lx-slot:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .lx-cond { display: flex; flex-wrap: wrap; gap: 6px; }
        .lx-pk { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px; }
        .lx-pk a, .lx-pass { display: flex; flex-direction: column; gap: 6px; padding: 16px; border-radius: 20px; background: #fff; border: 1px solid #ece5d6; color: inherit; }
        .lx-pk a:hover { border-color: #ef7c2b; }
        .lx-pk__icon { width: 40px; height: 40px; border-radius: 12px; background: #fdf4ec; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; }
        .lx-wait { margin-top: 10px; padding: 12px 14px; border-radius: 16px; background: #fff8e6; border: 1px solid rgba(254,191,72,.6); font-size: 13px; color: #5c4a12; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $images = collect($activity->images ?? [])->filter()->values();
    $tiers = $activity->tiers();
    $addons = $activity->addonsList();
    $conds = $activity->conditions();
    $bring = \App\Models\Activity::lines($activity->to_bring);
    $user = auth()->user();
    $placeUrl = $place instanceof \App\Models\LeisureVenue ? route('leisure.show', $place->slug) : ($place ? route('tourist-experience.show', $place->slug) : null);
    $minutes = $activity->duration_minutes;
    $dur = $minutes ? ($minutes >= 60 ? intdiv($minutes, 60).' h'.($minutes % 60 ? ' '.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) : '') : $minutes.' min') : null;
    $today = now('Africa/Abidjan')->startOfDay();
    $dates = collect(range(0, 20))->map(fn ($i) => $today->copy()->addDays($i))->filter(fn ($d) => $activity->isOpenOn($d))->take(14)->values();
    $startDate = $preDate && \Carbon\Carbon::hasFormat($preDate, 'Y-m-d') ? $preDate : ($dates->first()?->toDateString() ?? $today->toDateString());
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    @if($placeUrl)<a href="{{ $placeUrl }}" class="hx-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ $place->name }}</a>@endif

    <header class="ox-head">
        <div class="min-w-0">
            <div class="hx-pills">
                <span class="hx-pill"><x-icon name="masks" />{{ $activity->category?->name_fr ?? __('Activité') }}</span>
                @if($activity->level)<span class="hx-pill"><i class="fas fa-signal" aria-hidden="true"></i>{{ __(\App\Models\Activity::LEVELS[$activity->level] ?? $activity->level) }}</span>@endif
            </div>
            <h1 class="hx-title">{{ $activity->name }}</h1>
            @if($place)
                <p class="ox-where">
                    @if($placeUrl)<a href="{{ $placeUrl }}"><x-icon name="map-point" />{{ $place->name }}</a>@else<span><x-icon name="map-point" />{{ $place->name }}</span>@endif
                    @if($place->city?->name)<span>{{ $place->city->name }}</span>@endif
                </p>
            @endif
        </div>
        <div class="ox-head__side">
            <button type="button" class="ti-round" data-place-share="{{ url()->current() }}" data-place-title="{{ $activity->name }}" aria-label="{{ __('Partager :name', ['name' => $activity->name]) }}" title="{{ __('Partager') }}"><x-icon name="share" /></button>
        </div>
    </header>

    @if($images->isNotEmpty())
        <div class="tp-gal is-{{ min($images->count(), 5) }} mb-5">
            @foreach($images->take(5) as $i => $img)
                <button type="button" data-lb="{{ $i }}" aria-label="{{ __('Agrandir la photo :n', ['n' => $i + 1]) }}"><img src="{{ $img }}" alt="{{ $activity->name }}" loading="{{ $i ? 'lazy' : 'eager' }}">@if($i === 4 && $images->count() > 5)<span class="tp-gal__more">+{{ $images->count() - 5 }}</span>@endif</button>
            @endforeach
        </div>
    @endif

    {{-- Infos clés --}}
    <ul class="ox-keys">
        @if($dur)<li><x-icon name="clock-circle" /><span><small>{{ __('Durée') }}</small><b>{{ $dur }}</b></span></li>@endif
        @if($activity->min_age)<li><x-icon name="accessibility" /><span><small>{{ __('Âge') }}</small><b>{{ __('Dès :n ans', ['n' => $activity->min_age]) }}</b></span></li>@endif
        @if($activity->slot_capacity)<li><x-icon name="users-group-rounded" /><span><small>{{ __('Par séance') }}</small><b>{{ trans_choice(':count place|:count places', $activity->slot_capacity) }}</b></span></li>@endif
        <li><x-icon name="ticket" /><span><small>{{ __('Réservation') }}</small><b>{{ $activity->bookableOnline() ? __('Billet QR immédiat') : __('Sur demande') }}</b></span></li>
        @if($activity->cancellationLabel())<li class="is-ok"><x-icon name="restart" /><span><small>{{ __('Annulation') }}</small><b>{{ $activity->cancellationLabel() }}</b></span></li>@endif
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        <a href="#apercu">{{ __('Aperçu') }}</a>
        @if($conds || $bring || $activity->requires_waiver)<a href="#conditions">{{ __('Conditions') }}</a>@endif
        @if($packages->isNotEmpty())<a href="#formules">{{ __('Formules') }}</a>@endif
        @if($passes->isNotEmpty())<a href="#cartes">{{ __('Abonnements') }}</a>@endif
        @if($others->isNotEmpty())<a href="#autres">{{ __('Autres activités') }}</a>@endif
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start mt-8">
        <div class="lg:col-span-3 space-y-12 dx-main min-w-0">
            <section class="tp-sec ox-sec" id="apercu">
                <p class="hx-kicker">{{ __('L\'activité') }}</p>
                <h2 class="tp-h2">{{ __('En bref') }}</h2>
                @if($activity->description)<p class="hx-text">{{ $activity->description }}</p>@endif
                <ul class="ox-hl">
                    @if($activity->bookableOnline())
                        <li><x-icon name="bolt" /><span><b>{{ __('Confirmation immédiate') }}</b>{{ __('Votre billet QR arrive dès le paiement.') }}</span></li>
                    @else
                        <li><x-icon name="chat-round-dots" /><span><b>{{ __('Sur demande') }}</b>{{ __('Le lieu confirme votre créneau.') }}</span></li>
                    @endif
                    @if($activity->offpeak_discount_percent)
                        <li><x-icon name="sun" /><span><b>{{ __('−:p % en heures creuses', ['p' => $activity->offpeak_discount_percent]) }}</b>{{ __('Pour les séances avant :h.', ['h' => str_replace(':', ' h ', $activity->offpeak_until)]) }}</span></li>
                    @endif
                    @if($activity->slot_capacity)
                        <li><x-icon name="users-group-two-rounded" /><span><b>{{ __('Petits groupes') }}</b>{{ __(':n places par séance', ['n' => $activity->slot_capacity]) }}</span></li>
                    @endif
                    @if($activity->cancellationLabel())
                        <li><x-icon name="restart" /><span><b>{{ __('Flexible') }}</b>{{ $activity->cancellationLabel() }}</span></li>
                    @endif
                </ul>
            </section>

            @if($conds || $bring || $activity->requires_waiver)
                <section class="tp-sec ox-sec" id="conditions">
                    <p class="hx-kicker">{{ __('Avant de réserver') }}</p>
                    <h2 class="tp-h2">{{ __('Conditions de participation') }}</h2>
                    <div class="tp-incl">
                        @if($conds)<div class="tp-box"><h3>{{ __('Conditions') }}</h3><ul class="tp-list">@foreach($conds as $c)<li><x-icon name="danger-circle" class="text-[#b9500a]" />{{ $c }}</li>@endforeach</ul></div>@endif
                        @if($bring)<div class="tp-box"><h3>{{ __('À prévoir') }}</h3><ul class="tp-list">@foreach($bring as $c)<li><i class="fas fa-check text-[#1b6b3a]" aria-hidden="true"></i>{{ $c }}</li>@endforeach</ul></div>@endif
                    </div>
                    @if($activity->requires_waiver)
                        <details class="tp-box mt-3"><summary class="cursor-pointer font-bold text-sm"><i class="fas fa-file-signature text-[#b9500a] mr-1.5" aria-hidden="true"></i>{{ __('Décharge de responsabilité à accepter en réservant') }}</summary><p class="hx-text text-sm mt-2">{{ $activity->waiver_text }}</p></details>
                    @endif
                </section>
            @endif

            @if($packages->isNotEmpty())
                <section class="tp-sec ox-sec" id="formules">
                    <p class="hx-kicker">{{ __('Anniversaires, groupes, entreprises') }}</p>
                    <h2 class="tp-h2">{{ __('Nos formules') }}</h2>
                    <div class="lx-pk">
                        @foreach($packages as $p)
                            <a href="{{ route('leisure-package.show', $p->slug) }}">
                                <span class="lx-pk__icon"><i class="fas {{ \App\Models\LeisurePackage::KIND_ICONS[$p->kind] ?? 'fa-star' }}" aria-hidden="true"></i></span>
                                <strong class="text-[15px]">{{ $p->name }}</strong>
                                <span class="text-xs text-[#6b6458]">{{ $p->kindLabel() }} · {{ __(':n participants inclus', ['n' => $p->included_participants]) }}</span>
                                <span class="font-extrabold">{{ __('dès') }} {{ $fmt((int) $p->weekday_price_xof) }}</span>
                                @if($p->deposit_xof)<span class="tp-badge is-ok self-start">{{ __('Acompte :a pour bloquer la date', ['a' => $fmt($p->deposit_xof)]) }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($passes->isNotEmpty())
                <section class="tp-sec ox-sec" id="cartes">
                    <p class="hx-kicker">{{ __('Revenez souvent') }}</p>
                    <h2 class="tp-h2">{{ __('Cartes et abonnements') }}</h2>
                    <div class="lx-pk">
                        @foreach($passes as $p)
                            <div class="lx-pass">
                                <span class="lx-pk__icon"><x-icon name="user-id" /></span>
                                <strong class="text-[15px]">{{ $p->name }}</strong>
                                <span class="text-xs text-[#6b6458]">{{ $p->summary() }}</span>
                                @if($p->description)<span class="text-xs text-[#4a443a]">{{ $p->description }}</span>@endif
                                <span class="font-extrabold">{{ $fmt((int) $p->price_xof) }}</span>
                                @if($user?->role === 'visitor')
                                    <form method="POST" action="{{ route('leisure-pass.buy', $p) }}" class="flex flex-wrap gap-1.5 mt-1">
                                        @csrf
                                        <button name="payment_method" value="cinetpay" class="text-xs font-bold rounded-full bg-[#b9500a] text-white px-3 py-2">{{ __('Acheter') }}</button>
                                        <button name="payment_method" value="wallet" class="text-xs font-bold rounded-full border border-[#ece5d6] px-3 py-2">{{ __('Avec mon solde') }}</button>
                                    </form>
                                @elseif(! $user)
                                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="text-xs font-bold text-[#9a4308]">{{ __('Se connecter pour acheter') }}</a>
                                @endif
                                <span class="text-[11px] text-[#8a7f6b]">{{ __('Nominative, non remboursable, non cessible.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($others->isNotEmpty())
                <section class="tp-sec ox-sec" id="autres">
                    <p class="hx-kicker">{{ $place?->name }}</p>
                    <h2 class="tp-h2">{{ __('Autres activités') }}</h2>
                    <div class="dx-offers">
                        @foreach($others as $o)
                            <a href="{{ route('activity.show', $o->slug) }}" class="dx-offer">
                                <span class="dx-offer__media">@if($o->images[0] ?? null)<img src="{{ $o->images[0] }}" alt="" loading="lazy" class="w-full h-full object-cover">@else<x-icon name="masks" />@endif</span>
                                <span class="dx-offer__body"><span class="dx-offer__name">{{ $o->name }}</span>@if($o->duration_minutes)<span class="text-xs text-[#6b6458]">{{ $o->duration_minutes }} min</span>@endif</span>
                                <span class="dx-offer__side"><span class="dx-offer__price font-bold">{{ $fmt($o->fromPrice()) }}</span><span class="dx-offer__ask"><i class="fas fa-arrow-right" aria-hidden="true"></i></span></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-2 w-full" id="reserver">
            <div class="tp-card ox-card">
                <div class="ox-card__head">
                    <p class="tp-card__price">{{ __('À partir de') }} <b>{{ $fmt($activity->fromPrice()) }}</b> / {{ __('pers.') }}</p>
                    <div class="ox-card__badges">
                        @if($activity->cancellationLabel())<span><x-icon name="restart" />{{ __('Annulation gratuite') }}</span>@endif
                        @if($activity->bookableOnline())<span class="is-neutral"><x-icon name="qr-code" />{{ __('Billet QR') }}</span>@endif
                    </div>
                </div>
                <div class="ox-card__body">
                @if(session('waitlist_ok'))<p class="lx-wait"><i class="fas fa-bell mr-1" aria-hidden="true"></i>{{ session('waitlist_ok') }}</p>@endif

                @if(! $activity->bookableOnline())
                    <p class="tp-note">{{ __('Cette activité se réserve sur demande auprès du lieu.') }}</p>
                    @if($placeUrl)<a href="{{ $placeUrl }}#reserver" class="tp-submit inline-flex items-center justify-center gap-2"><x-icon name="chat-round-dots" />{{ __('Faire une demande') }}</a>@endif
                @elseif(! $user)
                    <p class="tp-note">{{ __('Connectez-vous pour réserver : confirmation immédiate et billet QR.') }}</p>
                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="tp-submit inline-flex items-center justify-center gap-2"><i class="fas fa-right-to-bracket" aria-hidden="true"></i>{{ __('Se connecter pour réserver') }}</a>
                @elseif($user->role !== 'visitor')
                    <p class="tp-note">{{ __('Les réservations se font avec un compte visiteur.') }}</p>
                @else
                    <form method="POST" action="{{ route('activity.book', $activity->slug) }}" id="lx-form" data-slots="{{ route('activity.slots', $activity->slug) }}">
                        @csrf
                        <input type="hidden" name="date" id="lx-date-input" value="{{ old('date', $startDate) }}">
                        <input type="hidden" name="time" id="lx-time-input" value="{{ old('time', $preTime) }}">
                        <span class="tp-label">{{ __('Date') }}</span>
                        <div class="lx-date" role="radiogroup" aria-label="{{ __('Date') }}">
                            @foreach($dates as $d)
                                <button type="button" data-date="{{ $d->toDateString() }}" role="radio" aria-checked="false" aria-label="{{ $d->locale(app()->getLocale())->isoFormat('dddd D MMMM') }}">
                                    <small>{{ $d->locale(app()->getLocale())->isoFormat('ddd') }}</small><b>{{ $d->format('d') }}</b><small>{{ $d->locale(app()->getLocale())->isoFormat('MMM') }}</small>
                                </button>
                            @endforeach
                        </div>
                        <span class="tp-label">{{ __('Heure') }}</span>
                        <div class="lx-slots" id="lx-slots" role="radiogroup" aria-label="{{ __('Heure') }}"><p class="tp-note m-0">{{ __('Chargement…') }}</p></div>
                        <p class="tp-note" id="lx-slot-note"></p>

                        <span class="tp-label">{{ __('Participants') }}</span>
                        @foreach($tiers as $i => $t)
                            <div class="tp-step"><span>{{ $t['label'] }}<small>{{ $t['price_xof'] > 0 ? $fmt($t['price_xof']) : __('Gratuit') }}</small></span>
                                <span class="tp-step__ctl"><button type="button" data-tier="{{ $i }}" data-d="-1" aria-label="{{ __('Retirer un') }} {{ $t['label'] }}"><i class="fas fa-minus"></i></button><output data-tier-out="{{ $i }}">{{ old('tiers.'.$i, $i === 0 ? 1 : 0) }}</output><button type="button" data-tier="{{ $i }}" data-d="1" aria-label="{{ __('Ajouter un') }} {{ $t['label'] }}"><i class="fas fa-plus"></i></button></span></div>
                            <input type="hidden" name="tiers[{{ $i }}]" value="{{ old('tiers.'.$i, $i === 0 ? 1 : 0) }}" data-tier-input="{{ $i }}">
                        @endforeach

                        @if($addons)
                            <span class="tp-label">{{ __('Options') }}</span>
                            @foreach($addons as $i => $a)
                                <label class="tp-extra"><input type="checkbox" name="addons[]" value="{{ $i }}" @checked(in_array($i, old('addons', []))) data-addon>
                                    <span>{{ $a['label'] }}</span><span class="font-bold whitespace-nowrap">+ {{ $fmt($a['price_xof']) }} <small class="font-medium text-[#6b6458]">{{ $a['per'] === 'booking' ? __('/ réservation') : __('/ pers.') }}</small></span></label>
                            @endforeach
                        @endif

                        @if($activity->requires_waiver)
                            <span class="tp-label">{{ __('Participants (décharge)') }}</span>
                            <div id="lx-waiver-names" class="space-y-1.5"></div>
                            <label class="tp-extra mt-2" style="grid-template-columns:auto 1fr"><input type="checkbox" name="waiver_accept" value="1" @checked(old('waiver_accept')) required>
                                <span class="text-xs">{{ __('J\'ai lu et j\'accepte la décharge de responsabilité au nom de chaque participant.') }}</span></label>
                        @endif

                        <span class="tp-label">{{ __('Paiement') }}</span>
                        <div class="tp-choice">
                            <label><input type="radio" name="payment_method" value="cinetpay" @checked(old('payment_method', $myPasses->isEmpty() ? 'cinetpay' : 'pass') === 'cinetpay')>{{ __('Mobile Money / carte') }}<small>CinetPay</small></label>
                            <label><input type="radio" name="payment_method" value="wallet" @checked(old('payment_method') === 'wallet')>{{ __('Mon solde') }}<small>Trésors d'Ivoire</small></label>
                            @foreach($myPasses as $mp)
                                <label class="col-span-2"><input type="radio" name="payment_method" value="pass" data-pass="{{ $mp->id }}" @checked(old('payment_method', 'pass') === 'pass' && $loop->first)>{{ $mp->pass_name }}<small>{{ $mp->remaining() === null ? __('Illimitée') : trans_choice(':count séance restante|:count séances restantes', $mp->remaining()) }} · {{ __('jusqu\'au :d', ['d' => $mp->expires_on?->format('d/m/Y')]) }}</small></label>
                            @endforeach
                        </div>
                        <input type="hidden" name="pass_id" id="lx-pass-id" value="{{ old('pass_id', $myPasses->first()?->id) }}">

                        <div class="tp-sum" id="lx-sum" aria-live="polite"></div>
                        @if($errors->leisure->any())<p class="tp-err">{{ $errors->leisure->first() }}</p>@endif
                        <p class="tp-err hidden" id="lx-err"></p>
                        <button type="submit" class="tp-submit" id="lx-submit"><x-icon name="ticket" class="mr-1.5" /><span id="lx-submit-label">{{ __('Réserver') }}</span></button>
                        @if($activity->cancellationLabel())<p class="tp-note"><x-icon name="restart" class="text-[#1b6b3a] mr-1" />{{ $activity->cancellationLabel() }}.</p>@endif
                    </form>
                    <form method="POST" action="{{ route('activity.waitlist', $activity->slug) }}" id="lx-wait-form" class="lx-wait hidden">
                        @csrf
                        <input type="hidden" name="date"><input type="hidden" name="time"><input type="hidden" name="participants">
                        <p class="m-0 mb-2"><i class="fas fa-bell mr-1" aria-hidden="true"></i><span id="lx-wait-text"></span></p>
                        <button type="submit" class="text-xs font-bold rounded-full bg-[#1d1a16] text-white px-4 py-2">{{ __('M\'inscrire sur la liste d\'attente') }}</button>
                    </form>
                @endif
                </div>
            </div>
        </aside>
    </div>
</div>

@if($activity->bookableOnline())
<div class="ox-bar" data-ox-bar>
    <span><small>{{ __('À partir de') }}</small><b>{{ $fmt($activity->fromPrice()) }}</b></span>
    <a href="#reserver"><x-icon name="ticket" />{{ __('Réserver') }}</a>
</div>
@endif

<div class="tp-lb" id="tp-lb" role="dialog" aria-modal="true" aria-label="{{ __('Photos') }}">
    <button type="button" data-lb-close style="top:16px;right:16px" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <button type="button" data-lb-step="-1" style="left:16px;top:50%" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left"></i></button>
    <img src="" alt="{{ $activity->name }}">
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

    const form = document.getElementById('lx-form');
    if (!form) return;
    const tiers = @json($tiers), addons = @json($addons), max = {{ (int) ($activity->max_participants ?: 50) }};
    const waiver = {{ $activity->requires_waiver ? 'true' : 'false' }};
    const oldNames = @json(old('waiver_names', []));
    const T = {{ \Illuminate\Support\Js::from([
        'full' => __('Complet'), 'past' => __('Passé'), 'left' => __('place(s)'), 'last' => __('Dernière place'), 'free' => __('Gratuit'),
        'noSlot' => __('Aucune séance ce jour-là.'), 'closed' => __('Fermé ce jour-là.'), 'pick' => __('Choisissez une heure.'),
        'total' => __('Total'), 'pay' => __('Réserver et payer :a'), 'payPass' => __('Réserver avec ma carte'), 'payFree' => __('Réserver gratuitement'),
        'wait' => __('Le créneau de :t est complet. Inscrivez-vous : nous vous prévenons si une place se libère.'),
        'small' => __('Plus que :n place(s) à :t pour votre groupe.'), 'max' => __('Maximum :n participants par réservation.'),
        'name' => __('Participant :n — nom complet'), 'deal' => __('Tarif réduit'), 'up' => __('Tarif week-end'), 'error' => __('Erreur de chargement des créneaux.'),
    ]) }};
    const fmt = n => Number(n).toLocaleString('fr-FR') + ' FCFA';
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const dateInput = document.getElementById('lx-date-input'), timeInput = document.getElementById('lx-time-input');
    const slotsBox = document.getElementById('lx-slots'), note = document.getElementById('lx-slot-note'), sum = document.getElementById('lx-sum');
    const err = document.getElementById('lx-err'), label = document.getElementById('lx-submit-label'), submit = document.getElementById('lx-submit');
    const waitForm = document.getElementById('lx-wait-form');
    let slots = [], adjust = 0;

    const qty = () => tiers.map((t, i) => +form.querySelector('[data-tier-input="' + i + '"]').value || 0);
    const people = () => qty().reduce((a, b) => a + b, 0);

    form.querySelectorAll('[data-tier]').forEach(b => b.addEventListener('click', () => {
        const i = b.dataset.tier, input = form.querySelector('[data-tier-input="' + i + '"]');
        const next = Math.max(0, (+input.value || 0) + (+b.dataset.d));
        if (people() - (+input.value || 0) + next > max) { err.textContent = T.max.replace(':n', max); err.classList.remove('hidden'); return; }
        err.classList.add('hidden'); input.value = next; form.querySelector('[data-tier-out="' + i + '"]').textContent = next;
        renderSlots(); renderNames(); quote();
    }));
    form.querySelectorAll('[data-addon]').forEach(c => c.addEventListener('change', quote));
    form.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', () => { if (r.dataset.pass) document.getElementById('lx-pass-id').value = r.dataset.pass; quote(); }));

    document.querySelectorAll('.lx-date button').forEach(b => b.addEventListener('click', () => pickDate(b.dataset.date)));
    function pickDate(d) {
        dateInput.value = d;
        document.querySelectorAll('.lx-date button').forEach(b => { const on = b.dataset.date === d; b.classList.toggle('is-active', on); b.setAttribute('aria-checked', on ? 'true' : 'false'); });
        loadSlots();
    }
    function loadSlots() {
        fetch(form.dataset.slots + '?date=' + dateInput.value, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json()).then(j => { slots = j.slots || []; renderSlots(); })
            .catch(() => { slotsBox.innerHTML = '<p class="tp-note m-0">' + T.error + '</p>'; });
    }
    function renderSlots() {
        if (!slots.length) { slotsBox.innerHTML = '<p class="tp-note m-0">' + T.noSlot + '</p>'; return; }
        if (slots.every(s => s.closed)) { slotsBox.innerHTML = '<p class="tp-note m-0">' + T.closed + '</p>'; return; }
        const n = Math.max(1, people());
        slotsBox.innerHTML = slots.map(s => {
            const full = !s.past && s.remaining !== null && s.remaining < n;
            const sub = s.past ? T.past : (s.remaining === null ? '' : (s.remaining <= 0 ? T.full : (s.remaining === 1 ? T.last : s.remaining + ' ' + T.left)));
            const pct = s.adjust_percent ? '<em class="' + (s.adjust_percent > 0 ? 'is-up' : '') + '">' + (s.adjust_percent > 0 ? '+' : '−') + Math.abs(s.adjust_percent) + ' %</em>' : '';
            return '<button type="button" class="lx-slot' + (s.past ? ' is-past' : '') + (full ? ' is-full' : '') + (timeInput.value === s.time ? ' is-active' : '') + '" role="radio" aria-checked="' + (timeInput.value === s.time) + '" data-time="' + s.time + '"' + (s.past ? ' disabled' : '') + '><strong>' + s.time.replace(':', ' h ') + '</strong><small>' + sub + '</small>' + pct + '</button>';
        }).join('');
        const cur = slots.find(s => s.time === timeInput.value);
        if (cur && (cur.past || (cur.remaining !== null && cur.remaining < n))) { if (cur.past) timeInput.value = ''; }
        afterPick();
    }
    slotsBox.addEventListener('click', e => {
        const b = e.target.closest('.lx-slot'); if (!b || b.disabled) return;
        timeInput.value = b.dataset.time;
        slotsBox.querySelectorAll('.lx-slot').forEach(x => { x.classList.toggle('is-active', x === b); x.setAttribute('aria-checked', x === b ? 'true' : 'false'); });
        afterPick();
    });
    function afterPick() {
        const s = slots.find(x => x.time === timeInput.value), n = Math.max(1, people());
        adjust = s ? s.adjust_percent : 0;
        waitForm.classList.add('hidden'); note.textContent = ''; submit.disabled = false;
        if (s && s.remaining !== null && s.remaining < n) {
            submit.disabled = true;
            if (s.remaining > 0) note.textContent = T.small.replace(':n', s.remaining).replace(':t', s.time);
            waitForm.querySelector('[name=date]').value = dateInput.value; waitForm.querySelector('[name=time]').value = s.time; waitForm.querySelector('[name=participants]').value = n;
            document.getElementById('lx-wait-text').textContent = T.wait.replace(':t', s.time.replace(':', ' h '));
            waitForm.classList.remove('hidden');
        } else if (!s) { note.textContent = T.pick; }
        quote();
    }
    function renderNames() {
        if (!waiver) return;
        const box = document.getElementById('lx-waiver-names'), n = people();
        const current = Array.from(box.querySelectorAll('input')).map(i => i.value);
        box.innerHTML = Array.from({ length: n }, (_, i) => '<input type="text" name="waiver_names[]" required maxlength="120" class="tp-input" placeholder="' + esc(T.name.replace(':n', i + 1)) + '" aria-label="' + esc(T.name.replace(':n', i + 1)) + '" value="' + esc(current[i] ?? oldNames[i] ?? '') + '">').join('');
    }
    function quote() {
        const q = qty(), lines = [];
        const ap = p => adjust ? Math.round(p * (100 + adjust) / 100) : p;
        tiers.forEach((t, i) => { if (q[i] > 0) lines.push([q[i] + ' × ' + t.label, q[i] * ap(t.price_xof)]); });
        form.querySelectorAll('[data-addon]:checked').forEach(c => { const a = addons[+c.value], k = a.per === 'booking' ? 1 : people(); lines.push([(k > 1 ? k + ' × ' : '') + a.label, k * a.price_xof]); });
        const total = lines.reduce((s, l) => s + l[1], 0);
        const pass = form.querySelector('input[name="payment_method"]:checked')?.value === 'pass';
        sum.innerHTML = lines.map(l => '<p><span>' + esc(l[0]) + '</span><span>' + fmt(l[1]) + '</span></p>').join('')
            + (adjust ? '<p><span>' + (adjust < 0 ? T.deal : T.up) + '</span><span>' + (adjust > 0 ? '+' : '−') + Math.abs(adjust) + ' %</span></p>' : '')
            + '<p class="is-total"><span>' + T.total + '</span><span>' + (pass ? '—' : (total > 0 ? fmt(total) : T.free)) + '</span></p>';
        label.textContent = pass ? T.payPass : (total > 0 ? T.pay.replace(':a', fmt(total)) : T.payFree);
    }
    renderNames();
    pickDate(dateInput.value);
})();
</script>

@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
