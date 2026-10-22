<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tour->name }} — {{ $agency->name }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($tour->summary ?: strip_tags((string) $tour->description), 155) }}">
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
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $images = collect($tour->images ?? [])->filter()->values();
    $days = $tour->days();
    $incl = \App\Models\TourPackage::lines($tour->included);
    $excl = \App\Models\TourPackage::lines($tour->not_included);
    $pickups = $tour->pickups();
    $extras = $tour->extrasList();
    $rules = $tour->cancellationRules();
    $tiers = $tour->groupPrices();
    $user = auth()->user();
    $canBook = $tour->instant_booking && $departures->isNotEmpty();
    $depData = $departures->map(fn ($d) => [
        'id' => $d->id, 'left' => $d->seatsLeft(), 'guaranteed' => $d->isGuaranteed(), 'missing' => $d->missingForGuarantee(),
        'deposit' => $tour->depositAllowedFor($d->departure_date), 'price' => $tour->adultPrice(1, $d),
    ])->keyBy('id');
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    <a href="{{ route('travel-agency.tours', $agency->slug) }}" class="hx-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Circuits de :a', ['a' => $agency->name]) }}</a>

    <header class="hx-head">
        <div class="min-w-0">
            <div class="hx-pills">
                <span class="hx-pill"><i class="fas fa-route" aria-hidden="true"></i>{{ $tour->category?->name_fr ?? __('Circuit') }}</span>
                @if($tour->difficulty)<span class="hx-pill"><i class="fas fa-person-hiking" aria-hidden="true"></i>{{ __(\App\Models\TourPackage::DIFFICULTIES[$tour->difficulty] ?? $tour->difficulty) }}</span>@endif
            </div>
            <h1 class="hx-title">{{ $tour->name }}</h1>
            @if($tour->summary)<p class="hx-lead mt-3 mb-0">{{ $tour->summary }}</p>@endif
            <p class="hx-meta">
                <span><i class="fas fa-building text-[#b9500a] mr-1" aria-hidden="true"></i><a href="{{ route('travel-agency.show', $agency->slug) }}" class="font-semibold underline-offset-2 hover:underline">{{ $agency->name }}</a></span>
                @if($tour->duration_days)<span><i class="fas fa-calendar-days text-[#b9500a] mr-1" aria-hidden="true"></i>{{ trans_choice(':count jour|:count jours', $tour->duration_days) }}</span>@endif
                @if($tour->max_participants)<span><i class="fas fa-users text-[#b9500a] mr-1" aria-hidden="true"></i>{{ __(':n voyageurs max', ['n' => $tour->max_participants]) }}</span>@endif
                @if($tour->languagesList())<span><i class="fas fa-language text-[#b9500a] mr-1" aria-hidden="true"></i>{{ implode(', ', $tour->languagesList()) }}</span>@endif
            </p>
            <a href="#reserver" class="lg:hidden mt-4 inline-flex items-center gap-2 rounded-full bg-[#b9500a] hover:bg-[#9a4308] text-white font-bold text-sm px-5 py-3">
                <i class="fas fa-calendar-check" aria-hidden="true"></i>{{ __('Réserver') }} · {{ __('dès') }} {{ $fmt($tour->fromPrice()) }}
            </a>
        </div>
    </header>

    @if($images->isNotEmpty())
        <div class="tp-gal is-{{ min($images->count(), 5) }}">
            @foreach($images->take(5) as $i => $img)
                <button type="button" data-lb="{{ $i }}" aria-label="{{ __('Agrandir la photo :n', ['n' => $i + 1]) }}">
                    <img src="{{ $img }}" alt="{{ $tour->name }}" loading="{{ $i ? 'lazy' : 'eager' }}">
                    @if($i === 4 && $images->count() > 5)<span class="tp-gal__more">+{{ $images->count() - 5 }}</span>@endif
                </button>
            @endforeach
        </div>
    @else
        <div class="tp-gal__empty"><i class="fas fa-route" aria-hidden="true"></i></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start mt-8">
        <div class="lg:col-span-3 space-y-12 dx-main min-w-0">
            <nav class="hx-tabs" aria-label="{{ __('Sections') }}">
                <a href="#apercu" class="hx-tab">{{ __('Aperçu') }}</a>
                @if($days)<a href="#programme" class="hx-tab">{{ __('Programme') }}</a>@endif
                @if($departures->isNotEmpty())<a href="#departs" class="hx-tab">{{ __('Départs') }}</a>@endif
                <a href="#conditions" class="hx-tab">{{ __('Conditions') }}</a>
            </nav>

            <section id="apercu" class="tp-sec">
                <p class="hx-kicker">{{ __('Aperçu') }}</p>
                <h2 class="tp-h2">{{ __('Le circuit') }}</h2>
                @if($tour->description)<p class="hx-text">{{ $tour->description }}</p>@endif
                <ul class="hx-facts dx-facts">
                    @if($tour->duration_days)<li><span class="hx-facts__icon"><i class="fas fa-calendar-days" aria-hidden="true"></i></span><span>{{ trans_choice(':count jour|:count jours', $tour->duration_days) }}</span></li>@endif
                    <li><span class="hx-facts__icon"><i class="fas fa-users" aria-hidden="true"></i></span><span>{{ $tour->min_participants ? __('Départ garanti dès :n voyageurs', ['n' => $tour->min_participants]) : __('Départ toujours garanti') }}</span></li>
                    <li><span class="hx-facts__icon"><i class="fas fa-bolt" aria-hidden="true"></i></span><span>{{ $tour->instant_booking ? __('Confirmation immédiate') : __('Sur demande à l\'agence') }}</span></li>
                    @if($tour->deposit_percent)<li><span class="hx-facts__icon"><i class="fas fa-wallet" aria-hidden="true"></i></span><span>{{ __('Acompte de :p %', ['p' => $tour->deposit_percent]) }}</span></li>@endif
                    @if($pickups)<li><span class="hx-facts__icon"><i class="fas fa-van-shuttle" aria-hidden="true"></i></span><span>{{ trans_choice(':count lieu de prise en charge|:count lieux de prise en charge', count($pickups)) }}</span></li>@endif
                    @if($rules && $rules[0]['percent'] > 0)<li><span class="hx-facts__icon"><i class="fas fa-rotate-left" aria-hidden="true"></i></span><span>{{ __('Remboursable à :p % jusqu\'à :d jours avant', ['p' => $rules[0]['percent'], 'd' => $rules[0]['days']]) }}</span></li>@endif
                </ul>
            </section>

            @if($days)
                <section id="programme" class="tp-sec">
                    <p class="hx-kicker">{{ __('Programme') }}</p>
                    <h2 class="tp-h2">{{ __('Jour par jour') }}</h2>
                    <ol class="tp-days">
                        @foreach($days as $d)
                            <li class="tp-day">
                                <span class="tp-day__n" aria-hidden="true">J{{ $loop->iteration }}</span>
                                <div class="tp-day__card">
                                    <h3 class="tp-day__title"><span class="sr-only">{{ __('Jour') }} {{ $loop->iteration }} : </span>{{ $d['title'] }}</h3>
                                    @if($d['description'])<p class="tp-day__text">{{ $d['description'] }}</p>@endif
                                    @if($d['meals'] || $d['accommodation'])
                                        <div class="tp-day__tags">
                                            @if($d['meals'])<span class="tp-tag"><i class="fas fa-utensils" aria-hidden="true"></i>{{ $d['meals'] }}</span>@endif
                                            @if($d['accommodation'])<span class="tp-tag"><i class="fas fa-bed" aria-hidden="true"></i>{{ $d['accommodation'] }}</span>@endif
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if($incl || $excl)
                <section class="tp-sec">
                    <p class="hx-kicker">{{ __('Le prix comprend') }}</p>
                    <h2 class="tp-h2">{{ __('Inclus et non inclus') }}</h2>
                    <div class="tp-incl">
                        @if($incl)<div class="tp-box"><h3 class="text-[#1b6b3a]">{{ __('Inclus') }}</h3><ul class="tp-list">@foreach($incl as $l)<li><i class="fas fa-check text-[#1b6b3a]" aria-hidden="true"></i>{{ $l }}</li>@endforeach</ul></div>@endif
                        @if($excl)<div class="tp-box"><h3 class="text-[#a3341f]">{{ __('Non inclus') }}</h3><ul class="tp-list">@foreach($excl as $l)<li><i class="fas fa-xmark text-[#a3341f]" aria-hidden="true"></i>{{ $l }}</li>@endforeach</ul></div>@endif
                    </div>
                </section>
            @endif

            @if($pickups)
                <section class="tp-sec">
                    <p class="hx-kicker">{{ __('Départ') }}</p>
                    <h2 class="tp-h2">{{ __('Lieux de prise en charge') }}</h2>
                    <ul class="tp-list tp-box">
                        @foreach($pickups as $p)
                            <li><i class="fas fa-location-dot text-[#b9500a]" aria-hidden="true"></i><span><b>{{ $p['label'] }}</b>@if($p['time']) — {{ str_replace(':', ' h ', $p['time']) }}@endif @if($p['note'])<span class="text-[#6b6458]"> · {{ $p['note'] }}</span>@endif</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($departures->isNotEmpty())
                <section id="departs" class="tp-sec">
                    <p class="hx-kicker">{{ __('Calendrier') }}</p>
                    <h2 class="tp-h2">{{ __('Prochains départs') }}</h2>
                    <div class="tp-deps">
                        @foreach($departures as $d)
                            @php $left = $d->seatsLeft(); @endphp
                            <div class="tp-dep {{ $left < 1 ? 'is-full' : '' }}">
                                <div class="tp-dep__date"><b>{{ $d->departure_date->format('d') }}</b><small>{{ $d->departure_date->locale(app()->getLocale())->isoFormat('MMM YY') }}</small></div>
                                <div class="tp-dep__info">
                                    <strong>{{ ucfirst($d->departure_date->locale(app()->getLocale())->isoFormat('dddd D MMMM')) }}@if($tour->duration_days > 1) → {{ $d->departure_date->copy()->addDays($tour->duration_days - 1)->locale(app()->getLocale())->isoFormat('D MMMM') }}@endif</strong>
                                    <span class="flex flex-wrap gap-1.5 mt-1">
                                        @if($d->isGuaranteed())<span class="tp-badge is-ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Départ garanti') }}</span>
                                        @else<span class="tp-badge is-warn">{{ trans_choice('Encore :count voyageur pour garantir le départ|Encore :count voyageurs pour garantir le départ', $d->missingForGuarantee()) }}</span>@endif
                                        @if($left < 1)<span class="tp-badge is-off">{{ __('Complet') }}</span>
                                        @elseif($left <= 4)<span class="tp-badge is-warn">{{ trans_choice('Dernière place|Plus que :count places', $left) }}</span>
                                        @else<span class="tp-badge">{{ __(':n places disponibles', ['n' => $left]) }}</span>@endif
                                    </span>
                                </div>
                                <div class="tp-dep__price">{{ $fmt($tour->adultPrice(1, $d)) }}<small class="block text-[11px] font-semibold text-[#6b6458]">/ {{ __('pers.') }}</small></div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="conditions" class="tp-sec">
                <p class="hx-kicker">{{ __('Conditions') }}</p>
                <h2 class="tp-h2">{{ __('Paiement et annulation') }}</h2>
                <div class="tp-incl">
                    <div class="tp-box">
                        <h3>{{ __('Paiement') }}</h3>
                        <ul class="tp-list">
                            @if($tour->deposit_percent)
                                <li><i class="fas fa-wallet text-[#b9500a]" aria-hidden="true"></i>{{ __('Acompte de :p % à la réservation', ['p' => $tour->deposit_percent]) }}</li>
                                <li><i class="fas fa-calendar-check text-[#b9500a]" aria-hidden="true"></i>{{ __('Solde à régler :d jours avant le départ (rappel automatique)', ['d' => $tour->balance_due_days]) }}</li>
                                <li><i class="fas fa-circle-info text-[#b9500a]" aria-hidden="true"></i>{{ __('Réservation à moins de :d jours : paiement intégral', ['d' => $tour->balance_due_days]) }}</li>
                            @else
                                <li><i class="fas fa-wallet text-[#b9500a]" aria-hidden="true"></i>{{ __('Paiement intégral à la réservation') }}</li>
                            @endif
                            <li><i class="fas fa-mobile-screen text-[#b9500a]" aria-hidden="true"></i>{{ __('Mobile Money, carte bancaire (CinetPay) ou solde Trésors d\'Ivoire') }}</li>
                            @if($tour->child_price_xof !== null)<li><i class="fas fa-child text-[#b9500a]" aria-hidden="true"></i>{{ __('Enfant:age : :p', ['age' => $tour->child_age_max ? ' ('.__('jusqu\'à :n ans', ['n' => $tour->child_age_max]).')' : '', 'p' => $fmt((int) $tour->child_price_xof)]) }}</li>@endif
                            @if($tour->single_supplement_xof)<li><i class="fas fa-bed text-[#b9500a]" aria-hidden="true"></i>{{ __('Supplément chambre individuelle : :p', ['p' => $fmt((int) $tour->single_supplement_xof)]) }}</li>@endif
                            @foreach($tiers as $t)<li><i class="fas fa-people-group text-[#b9500a]" aria-hidden="true"></i>{{ __('Dès :n voyageurs : :p / pers.', ['n' => $t['min'], 'p' => $fmt($t['price_xof'])]) }}</li>@endforeach
                        </ul>
                    </div>
                    <div class="tp-box">
                        <h3>{{ __('Annulation') }}</h3>
                        @if($rules)
                            <ul class="tp-policy">
                                @foreach($rules as $r)
                                    <li><span>{{ $r['days'] > 0 ? __('Jusqu\'à :d jours avant', ['d' => $r['days']]) : __('Moins de :d jours avant', ['d' => $rules[$loop->index - 1]['days'] ?? 1]) }}</span><b>{{ $r['percent'] > 0 ? __(':p % remboursé', ['p' => $r['percent']]) : __('Non remboursé') }}</b></li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-[#4a443a] m-0">{{ __('Non remboursable, sauf annulation du départ par l\'agence.') }}</p>
                        @endif
                        <p class="tp-note">{{ __('Départ annulé par l\'agence ou minimum de voyageurs non atteint : remboursement intégral sur votre solde.') }}</p>
                    </div>
                </div>
            </section>

            @if($others->isNotEmpty())
                <section class="tp-sec">
                    <p class="hx-kicker">{{ $agency->name }}</p>
                    <h2 class="tp-h2">{{ __('Autres circuits de l\'agence') }}</h2>
                    <div class="dx-offers">
                        @foreach($others as $o)
                            <a href="{{ route('travel-agency.tour', [$agency->slug, $o->slug]) }}" class="dx-offer">
                                <span class="dx-offer__media">@if($o->images[0] ?? null)<img src="{{ $o->images[0] }}" alt="" loading="lazy" class="w-full h-full object-cover">@else<i class="fas fa-route" aria-hidden="true"></i>@endif</span>
                                <span class="dx-offer__body"><span class="dx-offer__name">{{ $o->name }}</span>@if($o->duration_days)<span class="text-xs text-[#6b6458]">{{ trans_choice(':count jour|:count jours', $o->duration_days) }}</span>@endif</span>
                                <span class="dx-offer__side"><span class="dx-offer__price font-bold">{{ $fmt($o->fromPrice()) }}</span><span class="dx-offer__ask"><i class="fas fa-arrow-right" aria-hidden="true"></i></span></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- ── Réservation ── --}}
        <aside class="lg:col-span-2 w-full" id="reserver">
            <div class="tp-card">
                <p class="tp-card__price">{{ __('À partir de') }} <b>{{ $fmt($tour->fromPrice()) }}</b> / {{ __('pers.') }}</p>

                @if(! $canBook)
                    <p class="tp-note">{{ $departures->isEmpty() ? __('Aucun départ programmé pour le moment : demandez une date à l\'agence.') : __('Ce circuit se réserve sur demande : l\'agence vous répond avec une confirmation.') }}</p>
                    <a href="{{ route('travel-agency.show', $agency->slug) }}#reserver" class="tp-submit inline-flex items-center justify-center gap-2 no-underline"><i class="fas fa-paper-plane" aria-hidden="true"></i>{{ __('Demander à l\'agence') }}</a>
                @elseif(! $user)
                    <p class="tp-note">{{ __('Connectez-vous pour réserver : confirmation immédiate et paiement en ligne.') }}</p>
                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="tp-submit inline-flex items-center justify-center gap-2"><i class="fas fa-right-to-bracket" aria-hidden="true"></i>{{ __('Se connecter pour réserver') }}</a>
                @elseif($user->role !== 'visitor')
                    <p class="tp-note">{{ __('Les réservations se font avec un compte visiteur.') }}</p>
                @else
                    <form method="POST" action="{{ route('travel-agency.tour.book', [$agency->slug, $tour->slug]) }}" id="tp-form" data-quote="{{ route('travel-agency.tour.quote', [$agency->slug, $tour->slug]) }}">
                        @csrf
                        <label for="tp-dep" class="tp-label">{{ __('Départ') }}</label>
                        <select id="tp-dep" name="departure_id" class="tp-select" required>
                            @foreach($departures as $d)
                                @php $left = $depData[$d->id]['left']; @endphp
                                <option value="{{ $d->id }}" @disabled($left < 1) @selected(old('departure_id') == $d->id)>
                                    {{ ucfirst($d->departure_date->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY')) }} · {{ $left < 1 ? __('complet') : trans_choice(':count place|:count places', $left) }}{{ $d->isGuaranteed() ? ' · '.__('garanti') : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p id="tp-dep-info" class="tp-note mt-1.5"></p>

                        <span class="tp-label">{{ __('Voyageurs') }}</span>
                        <div class="tp-step"><span>{{ __('Adultes') }}</span><span class="tp-step__ctl"><button type="button" data-step="adults" data-d="-1" aria-label="{{ __('Un adulte de moins') }}"><i class="fas fa-minus"></i></button><output id="tp-adults-out">{{ old('adults', 2) }}</output><button type="button" data-step="adults" data-d="1" aria-label="{{ __('Un adulte de plus') }}"><i class="fas fa-plus"></i></button></span></div>
                        <input type="hidden" name="adults" id="tp-adults" value="{{ old('adults', 2) }}">
                        @if($tour->child_price_xof !== null)
                            <div class="tp-step"><span>{{ __('Enfants') }}<small>{{ $tour->child_age_max ? __('jusqu\'à :n ans', ['n' => $tour->child_age_max]).' · ' : '' }}{{ $fmt((int) $tour->child_price_xof) }}</small></span><span class="tp-step__ctl"><button type="button" data-step="children" data-d="-1" aria-label="{{ __('Un enfant de moins') }}"><i class="fas fa-minus"></i></button><output id="tp-children-out">{{ old('children', 0) }}</output><button type="button" data-step="children" data-d="1" aria-label="{{ __('Un enfant de plus') }}"><i class="fas fa-plus"></i></button></span></div>
                            <input type="hidden" name="children" id="tp-children" value="{{ old('children', 0) }}">
                        @endif
                        @if($tour->single_supplement_xof)
                            <div class="tp-step"><span>{{ __('Chambres individuelles') }}<small>+ {{ $fmt((int) $tour->single_supplement_xof) }} {{ __('chacune') }}</small></span><span class="tp-step__ctl"><button type="button" data-step="single_rooms" data-d="-1" aria-label="{{ __('Une chambre individuelle de moins') }}"><i class="fas fa-minus"></i></button><output id="tp-single_rooms-out">{{ old('single_rooms', 0) }}</output><button type="button" data-step="single_rooms" data-d="1" aria-label="{{ __('Une chambre individuelle de plus') }}"><i class="fas fa-plus"></i></button></span></div>
                            <input type="hidden" name="single_rooms" id="tp-single_rooms" value="{{ old('single_rooms', 0) }}">
                        @endif

                        @if($pickups)
                            <label for="tp-pickup" class="tp-label">{{ __('Prise en charge') }}</label>
                            <select id="tp-pickup" name="pickup_point" class="tp-select" required>
                                @foreach($pickups as $p)<option value="{{ $p['label'] }}" @selected(old('pickup_point') === $p['label'])>{{ $p['label'] }}{{ $p['time'] ? ' — '.str_replace(':', ' h ', $p['time']) : '' }}</option>@endforeach
                            </select>
                        @endif

                        @if($extras)
                            <span class="tp-label">{{ __('Options') }}</span>
                            @foreach($extras as $i => $e)
                                <label class="tp-extra"><input type="checkbox" name="extras[]" value="{{ $i }}" @checked(in_array($i, old('extras', []))) data-quote-input>
                                    <span>{{ $e['label'] }}</span><span class="font-bold whitespace-nowrap">+ {{ $fmt($e['price_xof']) }} <small class="font-medium text-[#6b6458]">{{ $e['per'] === 'booking' ? __('/ réservation') : __('/ pers.') }}</small></span></label>
                            @endforeach
                        @endif

                        <span class="tp-label">{{ __('Je paie') }}</span>
                        <div class="tp-choice" id="tp-modes">
                            <label><input type="radio" name="payment_mode" value="deposit" @checked(old('payment_mode', 'deposit') === 'deposit') data-quote-input>{{ __('L\'acompte') }}<small id="tp-deposit-hint">{{ $tour->deposit_percent ? $tour->deposit_percent.' %' : '' }}</small></label>
                            <label><input type="radio" name="payment_mode" value="full" @checked(old('payment_mode') === 'full' || ! $tour->deposit_percent) data-quote-input>{{ __('La totalité') }}<small>{{ __('rien à payer ensuite') }}</small></label>
                        </div>
                        <span class="tp-label">{{ __('Moyen de paiement') }}</span>
                        <div class="tp-choice">
                            <label><input type="radio" name="payment_method" value="cinetpay" @checked(old('payment_method', 'cinetpay') === 'cinetpay')>{{ __('Mobile Money / carte') }}<small>CinetPay</small></label>
                            <label><input type="radio" name="payment_method" value="wallet" @checked(old('payment_method') === 'wallet')>{{ __('Mon solde') }}<small>Trésors d'Ivoire</small></label>
                        </div>
                        <label for="tp-phone" class="tp-label">{{ __('Téléphone (joignable pendant le voyage)') }}</label>
                        <input id="tp-phone" type="tel" name="phone" maxlength="30" value="{{ old('phone', $user->phone) }}" class="tp-input">

                        <div class="tp-sum" id="tp-sum" aria-live="polite"><p>{{ __('Calcul du prix…') }}</p></div>
                        @if($errors->tour->any())<p class="tp-err">{{ $errors->tour->first() }}</p>@endif
                        <p class="tp-err hidden" id="tp-err"></p>
                        <button type="submit" class="tp-submit" id="tp-submit"><i class="fas fa-lock mr-1.5" aria-hidden="true"></i><span id="tp-submit-label">{{ __('Réserver') }}</span></button>
                        <p class="tp-note"><i class="fas fa-bolt text-[#b9500a] mr-1" aria-hidden="true"></i>{{ __('Confirmation immédiate. Vous remplirez ensuite les noms des voyageurs depuis « Mes voyages ».') }}</p>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</div>

<div class="tp-lb" id="tp-lb" role="dialog" aria-modal="true" aria-label="{{ __('Photos du circuit') }}">
    <button type="button" data-lb-close style="top:16px;right:16px" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <button type="button" data-lb-step="-1" style="left:16px;top:50%" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left"></i></button>
    <img src="" alt="{{ $tour->name }}">
    <button type="button" data-lb-step="1" style="right:16px;top:50%" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right"></i></button>
</div>

<script>
(function () {
    // Visionneuse
    const photos = @json($images->values());
    const lb = document.getElementById('tp-lb'), lbImg = lb.querySelector('img');
    let at = 0;
    const show = i => { at = (i + photos.length) % photos.length; lbImg.src = photos[at]; };
    document.querySelectorAll('[data-lb]').forEach(b => b.addEventListener('click', () => { show(+b.dataset.lb); lb.classList.add('is-open'); lb.querySelector('[data-lb-close]').focus(); }));
    lb.querySelector('[data-lb-close]').addEventListener('click', () => lb.classList.remove('is-open'));
    lb.querySelectorAll('[data-lb-step]').forEach(b => b.addEventListener('click', () => show(at + (+b.dataset.lbStep))));
    lb.addEventListener('click', e => { if (e.target === lb) lb.classList.remove('is-open'); });
    document.addEventListener('keydown', e => { if (!lb.classList.contains('is-open')) return; if (e.key === 'Escape') lb.classList.remove('is-open'); if (e.key === 'ArrowLeft') show(at - 1); if (e.key === 'ArrowRight') show(at + 1); });

    // Réservation : devis en direct (même calcul que le serveur).
    const form = document.getElementById('tp-form');
    if (!form) return;
    const deps = @json($depData);
    const T = {{ \Illuminate\Support\Js::from([
        'guaranteed' => __('Départ garanti'), 'missing' => __('Encore :n voyageur(s) pour garantir ce départ. S\'il n\'est pas atteint, vous êtes remboursé à 100 %.'),
        'left' => __(':n place(s) restante(s)'), 'total' => __('Total'), 'now' => __('À payer maintenant'), 'later' => __('Solde avant le :d'),
        'pay' => __('Réserver et payer :a'), 'noDeposit' => __('Départ proche : paiement intégral'), 'error' => __('Une erreur est survenue.'),
    ]) }};
    const fmt = n => Number(n).toLocaleString('fr-FR') + ' FCFA';
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const sum = document.getElementById('tp-sum'), err = document.getElementById('tp-err'), submit = document.getElementById('tp-submit'), label = document.getElementById('tp-submit-label');
    const depSel = document.getElementById('tp-dep'), info = document.getElementById('tp-dep-info');
    const depositInput = form.querySelector('input[name="payment_mode"][value="deposit"]'), fullInput = form.querySelector('input[name="payment_mode"][value="full"]');
    const limits = { adults: [1, 50], children: [0, 50], single_rooms: [0, 50] };

    form.querySelectorAll('[data-step]').forEach(b => b.addEventListener('click', () => {
        const k = b.dataset.step, input = document.getElementById('tp-' + k), out = document.getElementById('tp-' + k + '-out');
        const v = Math.max(limits[k][0], Math.min(limits[k][1], (+input.value || 0) + (+b.dataset.d)));
        input.value = v; out.textContent = v; refresh();
    }));
    form.querySelectorAll('[data-quote-input]').forEach(i => i.addEventListener('change', refresh));
    depSel.addEventListener('change', refresh);

    let seq = 0;
    function refresh() {
        const d = deps[depSel.value];
        if (d) {
            info.textContent = (d.guaranteed ? '✓ ' + T.guaranteed : T.missing.replace(':n', d.missing)) + ' · ' + T.left.replace(':n', d.left);
            depositInput.disabled = !d.deposit;
            if (!d.deposit) fullInput.checked = true;
            document.getElementById('tp-deposit-hint').textContent = d.deposit ? @json($tour->deposit_percent ? $tour->deposit_percent.' %' : '') : T.noDeposit;
        }
        const params = new URLSearchParams(new FormData(form)); params.delete('_token');
        const my = ++seq;
        fetch(form.dataset.quote + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json().then(j => [r.ok, j]))
            .then(([ok, q]) => {
                if (my !== seq) return;
                if (!ok || !q.ok) { err.textContent = q.message || T.error; err.classList.remove('hidden'); submit.disabled = true; return; }
                err.classList.add('hidden'); submit.disabled = false;
                const withDeposit = q.deposit !== null && depositInput.checked;
                const now = withDeposit ? q.deposit : q.total;
                sum.innerHTML = q.lines.map(l => '<p><span>' + esc(l.label) + '</span><span>' + fmt(l.amount_xof) + '</span></p>').join('')
                    + '<p class="is-total"><span>' + T.total + '</span><span>' + fmt(q.total) + '</span></p>'
                    + '<p class="is-now"><span>' + T.now + '</span><span>' + fmt(now) + '</span></p>'
                    + (withDeposit ? '<p><span>' + T.later.replace(':d', q.balance_due_on) + '</span><span>' + fmt(q.total - q.deposit) + '</span></p>' : '');
                label.textContent = T.pay.replace(':a', fmt(now));
            })
            .catch(() => { err.textContent = T.error; err.classList.remove('hidden'); });
    }
    refresh();
})();
</script>

@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
