<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Mon séjour') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Composez un séjour jour par jour en Côte d'Ivoire selon votre budget, vos envies et votre rythme.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('partials.ai-styles')
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        /* « Mon séjour » : assistant en étapes + résumé en direct. Aplats, ombre au survol seulement. */
        .sp { max-width: 1180px; margin: 0 auto; padding: 28px 16px 64px; }
        .sp-hero { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px 32px; margin-bottom: 26px; }
        .sp-kicker { display: inline-flex; align-items: center; gap: 8px; margin: 0 0 10px; padding: 6px 12px 6px 8px; border-radius: 999px; background: #F3F8F5; color: #B85600; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .sp-kicker i { font-size: 15px; }
        .sp-title { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 3vw + 18px, 56px); font-weight: 700; line-height: 1.04; letter-spacing: -.015em; color: #0E3B2A; }
        .sp-lead { margin: 12px 0 0; max-width: 620px; font-size: 17px; line-height: 1.6; color: #24503D; }
        .sp-steps-mini { display: flex; gap: 6px; margin: 0; padding: 0; list-style: none; }
        .sp-steps-mini li { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; font-size: 12.5px; font-weight: 600; color: #24503D; }
        .sp-steps-mini i { color: #EB6E00; font-size: 15px; }

        .sp-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 28px; align-items: start; }
        .sp-form { display: flex; flex-direction: column; gap: 16px; }
        .sp-step { padding: 24px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; scroll-margin-top: 96px; }
        .sp-step__head { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
        .sp-step__n { flex: none; width: 40px; height: 40px; border-radius: 13px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 19px; }
        .sp-step__title { margin: 0; font-size: 18px; font-weight: 800; color: #0E3B2A; }
        .sp-step__sub { margin: 2px 0 0; font-size: 13px; color: #557466; }
        .sp-step__badge { margin-left: auto; padding: 5px 11px; border-radius: 999px; background: #F3F8F5; color: #24503D; font-size: 12.5px; font-weight: 700; white-space: nowrap; }
        .sp-step__badge.is-full { background: #F3F8F5; color: #00874F; }

        /* Villes */
        .sp-search { display: flex; align-items: center; gap: 10px; height: 46px; margin-bottom: 14px; padding: 0 14px; border-radius: 14px; background: #F3F8F5; border: 1px solid #D8E9E0; color: #557466; }
        .sp-search:focus-within { border-color: #EB6E00; background: #fff; }
        .sp-search i { font-size: 18px; }
        .sp-search input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; font-size: 14.5px; color: #0E3B2A; }
        .sp-cities { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .sp-city { position: relative; display: block; cursor: pointer; }
        .sp-city input { position: absolute; opacity: 0; pointer-events: none; }
        .sp-city__card { position: relative; display: flex; flex-direction: column; justify-content: flex-end; height: 118px; padding: 12px; border-radius: 18px; overflow: hidden; background: #F3F8F5; border: 2px solid transparent; transition: border-color .15s ease, transform .15s ease; }
        .sp-city__card img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .sp-city.has-img .sp-city__card::after { content: ''; position: absolute; inset: 0; background: rgba(14,59,42,.38); }
        .sp-city__txt { position: relative; z-index: 1; min-width: 0; }
        .sp-city__name { display: block; font-size: 14.5px; font-weight: 800; color: #0E3B2A; line-height: 1.2; }
        .sp-city__region { display: block; margin-top: 2px; font-size: 11.5px; color: #557466; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sp-city.has-img .sp-city__name, .sp-city.has-img .sp-city__region { color: #fff; }
        .sp-city__pin { position: absolute; top: 10px; left: 12px; z-index: 1; color: #EB6E00; font-size: 18px; }
        .sp-city.has-img .sp-city__pin { display: none; }
        .sp-city__check { position: absolute; top: 9px; right: 9px; z-index: 2; width: 26px; height: 26px; border-radius: 999px; background: rgba(255,255,255,.9); border: 1.5px solid #D8E9E0; color: transparent; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
        .sp-city:hover .sp-city__card { transform: translateY(-2px); }
        .sp-city input:checked + .sp-city__card { border-color: #EB6E00; }
        .sp-city input:checked + .sp-city__card .sp-city__check { background: #EB6E00; border-color: #EB6E00; color: #fff; }
        .sp-city input:focus-visible + .sp-city__card { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .sp-city input:disabled + .sp-city__card { opacity: .4; cursor: not-allowed; transform: none; }
        .sp-city[hidden] { display: none; }
        .sp-more { display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; padding: 0; border: 0; background: none; color: #B85600; font-size: 14px; font-weight: 700; cursor: pointer; }
        .sp-more:hover { text-decoration: underline; text-underline-offset: 3px; }
        .sp-none { margin: 4px 0 0; font-size: 13.5px; color: #557466; }

        /* Champs */
        .sp-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .sp-field label, .sp-field > span { display: block; margin-bottom: 7px; font-size: 12.5px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #557466; }
        .sp-stepper { display: flex; align-items: center; justify-content: space-between; gap: 8px; height: 56px; padding: 6px; border-radius: 16px; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .sp-stepper button { flex: none; width: 44px; height: 44px; border: 0; border-radius: 12px; background: #fff; color: #0E3B2A; font-size: 13px; cursor: pointer; transition: background .15s ease, color .15s ease; }
        .sp-stepper button:hover { background: #0E3B2A; color: #fff; }
        .sp-stepper input { width: 64px; border: 0; background: transparent; text-align: center; font-size: 20px; font-weight: 800; color: #0E3B2A; -moz-appearance: textfield; }
        .sp-stepper input::-webkit-outer-spin-button, .sp-stepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .sp-stepper small { display: block; text-align: center; font-size: 11.5px; color: #557466; }
        .sp-input { width: 100%; height: 56px; padding: 0 14px; border-radius: 16px; background: #F3F8F5; border: 1px solid #D8E9E0; font-size: 15px; color: #0E3B2A; }
        .sp-input:focus, .sp-stepper:focus-within { outline: 2px solid rgba(235,110,0,.45); border-color: #EB6E00; background: #fff; }
        .sp-hint { margin: 6px 0 0; font-size: 12.5px; color: #557466; }

        /* Budget */
        .sp-budget__top { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 8px; }
        .sp-budget__val { display: flex; align-items: baseline; gap: 8px; }
        .sp-budget__val input { width: 170px; border: 0; border-bottom: 2px solid #D8E9E0; background: transparent; font-size: 30px; font-weight: 800; color: #0E3B2A; letter-spacing: -.01em; font-variant-numeric: tabular-nums; }
        .sp-budget__val input:focus { outline: 0; border-color: #EB6E00; }
        .sp-budget__val span { font-size: 15px; font-weight: 700; color: #557466; }
        .sp-budget__per { font-size: 13.5px; color: #24503D; }
        .sp-budget__per b { color: #00874F; }
        .sp-range { width: 100%; margin: 18px 0 6px; accent-color: #EB6E00; height: 6px; }
        .sp-presets { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .sp-preset { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1.5px solid #D8E9E0; font-size: 13.5px; font-weight: 700; color: #24503D; cursor: pointer; }
        .sp-preset i { color: #EB6E00; font-size: 16px; }
        .sp-preset small { font-weight: 600; color: #557466; }
        .sp-preset:hover, .sp-preset.is-on { border-color: #EB6E00; background: #F3F8F5; }

        /* Intérêts */
        .sp-tiles { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .sp-tile { position: relative; cursor: pointer; }
        .sp-tile input { position: absolute; opacity: 0; pointer-events: none; }
        .sp-tile span { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; height: 100%; padding: 14px; border-radius: 18px; background: #F3F8F5; border: 2px solid transparent; font-size: 14px; font-weight: 700; color: #0E3B2A; line-height: 1.3; transition: border-color .15s ease, background .15s ease; }
        .sp-tile span i { width: 40px; height: 40px; border-radius: 12px; background: #fff; color: #EB6E00; font-size: 20px; }
        .sp-tile:hover span { background: #F3F8F5; }
        .sp-tile input:checked + span { border-color: #EB6E00; background: #F3F8F5; }
        .sp-tile input:checked + span i { background: #EB6E00; color: #fff; }
        .sp-tile input:focus-visible + span { outline: 3px solid #EB6E00; outline-offset: 2px; }

        /* Rythme */
        .sp-paces { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .sp-pace { position: relative; cursor: pointer; }
        .sp-pace input { position: absolute; opacity: 0; pointer-events: none; }
        .sp-pace > span { display: flex; flex-direction: column; gap: 6px; height: 100%; padding: 16px; border-radius: 18px; background: #F3F8F5; border: 2px solid transparent; }
        .sp-pace i { color: #EB6E00; font-size: 26px; }
        .sp-pace b { font-size: 15px; color: #0E3B2A; }
        .sp-pace small { font-size: 12.5px; line-height: 1.45; color: #557466; }
        .sp-pace__dots { display: flex; gap: 4px; margin-top: 2px; }
        .sp-pace__dots em { width: 18px; height: 5px; border-radius: 999px; background: #D8E9E0; }
        .sp-pace__dots em.is-on { background: #EB6E00; }
        .sp-pace input:checked + span { border-color: #EB6E00; background: #F3F8F5; }
        .sp-pace input:focus-visible + span { outline: 3px solid #EB6E00; outline-offset: 2px; }

        /* Résumé */
        .sp-sum { position: sticky; top: 96px; padding: 22px; border-radius: 24px; background: #0E3B2A; color: #fff; }
        .sp-sum__title { margin: 0; display: flex; align-items: center; gap: 10px; font-family: 'Montserrat', serif; font-size: 22px; font-weight: 700; }
        .sp-sum__title i { color: #EB6E00; font-size: 22px; }
        .sp-sum__list { margin: 16px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; }
        .sp-sum__list li { display: flex; align-items: flex-start; gap: 12px; padding: 11px 0; border-top: 1px solid rgba(255,255,255,.1); font-size: 14px; }
        .sp-sum__list li i { flex: none; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,.08); color: #EB6E00; font-size: 17px; }
        .sp-sum__list small { display: block; font-size: 11.5px; letter-spacing: .06em; text-transform: uppercase; color: rgba(255,255,255,.55); }
        .sp-sum__list b { display: block; font-weight: 600; line-height: 1.4; }
        .sp-sum__list .is-empty b { color: rgba(255,255,255,.5); font-weight: 500; }
        .sp-submit { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 54px; margin-top: 16px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15.5px; font-weight: 800; cursor: pointer; transition: background .15s ease; }
        .sp-submit:hover { background: #B85600; }
        .sp-submit i { font-size: 19px; }
        .sp-submit:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .sp-sum__note { margin: 12px 0 0; font-size: 12px; line-height: 1.5; color: rgba(255,255,255,.6); }
        .sp-err { margin-bottom: 16px; padding: 14px 16px; border-radius: 16px; background: #fbe9e5; color: #a3341f; font-size: 14px; }

        /* Séjours récents */
        .sp-recent { margin-top: 34px; }
        .sp-recent h2 { margin: 0 0 12px; font-size: 18px; font-weight: 800; color: #0E3B2A; }
        .sp-recent__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; }
        .sp-recent a { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .sp-recent a:hover { border-color: #8AA99B; box-shadow: 0 14px 30px -24px rgba(14,59,42,.45); }
        .sp-recent a > i { flex: none; width: 42px; height: 42px; border-radius: 12px; background: #F3F8F5; color: #EB6E00; font-size: 20px; }
        .sp-recent b { display: block; font-size: 14.5px; color: #0E3B2A; }
        .sp-recent small { font-size: 12.5px; color: #557466; }

        /* Barre mobile */
        .sp-bar { display: none; }
        @media (max-width: 1023px) {
            .sp-grid { grid-template-columns: 1fr; }
            .sp-sum { position: static; }
            .sp-cities { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .sp-bar { position: fixed; left: 12px; right: 76px; bottom: 84px; z-index: 40; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 8px 8px 18px; border-radius: 999px; background: #0E3B2A; color: #fff; transition: transform .25s ease, opacity .25s ease; }
            .sp-bar > span { min-width: 0; }
            .sp-bar small { display: block; font-size: 11.5px; color: rgba(255,255,255,.65); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .sp-bar b { display: block; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .sp-bar button { flex: none; }
            .sp-bar button { height: 44px; padding: 0 18px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 14px; font-weight: 800; }
            .sp-bar.is-away { transform: translateY(140%); opacity: 0; pointer-events: none; }
        }
        @media (min-width: 768px) and (max-width: 1023px) { .sp-bar { left: 50%; right: auto; bottom: 20px; width: 520px; transform: translateX(-50%); } .sp-bar.is-away { transform: translate(-50%, 140%); } }
        @media (max-width: 767px) {
            .sp-steps-mini { display: none; }
            .sp-step { padding: 18px 16px; border-radius: 20px; }
            .sp-cities { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sp-city__card { height: 100px; }
            .sp-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sp-paces { grid-template-columns: 1fr; }
            .sp-row { grid-template-columns: 1fr; }
            .sp-budget__val input { width: 140px; font-size: 26px; }
        }
        @media (prefers-reduced-motion: reduce) { .sp-city__card, .sp-bar { transition: none; } }
    
        /* Boutons aux couleurs de la marque (orange) ; résumé sur fond clair. */
        .sp-stepper button:hover { background: #EB6E00; color: #fff; }
        .sp-sum { background: #fff; color: #0E3B2A; border: 1px solid #D8E9E0; }
        .sp-sum__title i { color: #EB6E00; }
        .sp-sum__list li { border-top-color: #F3F8F5; }
        .sp-sum__list li i { background: #F3F8F5; color: #EB6E00; }
        .sp-sum__list small { color: #557466; }
        .sp-sum__list .is-empty b { color: #557466; }
        .sp-sum__note { color: #557466; }
        .sp-submit:focus-visible { outline-color: #EB6E00; }
        .sp-bar { background: #EB6E00; }
        .sp-bar small { color: rgba(255,255,255,.8); }
        .sp-bar button { background: #fff; color: #B85600; }
        .sp-stepper button { background: #EB6E00; color: #fff; } .sp-stepper button:hover { background: #B85600; }
        .sp-stepper button { font-size: 22px; font-weight: 700; line-height: 1; }
        .sp-city__check { font-size: 14px; font-weight: 900; }
</style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="min-h-screen">
@include('partials.page-background')
@include('partials.public-top-nav')

@php
    $v = fn ($k, $d = null) => old($k, $defaults[$k] ?? $d);
    $pickedInterests = (array) old('interests', $defaults['interests'] ?? []);
    $pickedCities = array_map('intval', (array) old('city_ids', []));
    // Photo et région des villes (présentation) : seulement les images hébergées sur le site.
    $cityInfo = \App\Models\TouristCity::whereIn('id', collect($cities)->pluck('id'))->get(['id', 'region_administrative', 'thumbnail', 'cover_image'])->keyBy('id');
    $cityImg = function ($id) use ($cityInfo) {
        $c = $cityInfo[$id] ?? null;
        foreach ([$c?->thumbnail, $c?->cover_image] as $u) {
            if (is_string($u) && str_starts_with($u, '/storage/') && is_file(public_path(ltrim($u, '/')))) {
                return $u;
            }
        }

        return null;
    };
    $interestIcons = ['culture' => 'masks', 'nature' => 'leaf', 'gastronomie' => 'chef-hat', 'plage' => 'umbrella', 'aventure' => 'running-round', 'sorties' => 'confetti', 'artisanat' => 'palette'];
    $paces = [
        'calme' => [__('Calme'), __('2 étapes par jour, trajets courts'), 'sleeping', 1],
        'modere' => [__('Équilibré'), __('3 étapes par jour'), 'walking', 2],
        'soutenu' => [__('Soutenu'), __('4 étapes par jour, plus de route'), 'running-round', 3],
    ];
    $visibleCities = 8;
@endphp

<main class="sp">
    <header class="sp-hero">
        <div>
            <p class="sp-kicker"><x-icon name="routing" />{{ __('Planificateur') }}</p>
            <h1 class="sp-title">{{ __('Mon séjour') }}</h1>
            <p class="sp-lead">{{ __('Dites-nous où, combien de temps et ce que vous aimez : nous composons un programme jour par jour, prêt à réserver.') }}</p>
        </div>
        <ol class="sp-steps-mini" aria-hidden="true">
            <li><x-icon name="map-point" />{{ __('Destinations') }}</li>
            <li><x-icon name="calendar" />{{ __('Dates') }}</li>
            <li><x-icon name="star-shine" />{{ __('Envies') }}</li>
        </ol>
    </header>

    @if($errors->any())
        <div class="sp-err" role="alert">
            <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="sp-grid">
        <form method="POST" action="{{ route('trip.store') }}" class="sp-form" id="trip-form">
            @csrf

            {{-- 1. Destinations --}}
            <section class="sp-step" id="sp-where">
                <div class="sp-step__head">
                    <span class="sp-step__n"><x-icon name="map-point" /></span>
                    <div>
                        <h2 class="sp-step__title">{{ __('1. Où souhaitez-vous aller ?') }}</h2>
                        <p class="sp-step__sub">{{ __('(jusqu\'à') }} {{ $maxCities }} {{ __('villes)') }}</p>
                    </div>
                    <span class="sp-step__badge" data-sp-count>0 / {{ $maxCities }}</span>
                </div>
                <label class="sp-search">
                    <x-icon name="magnifier" />
                    <input type="search" data-sp-search placeholder="{{ __('Rechercher une ville') }}" aria-label="{{ __('Rechercher une ville') }}" autocomplete="off">
                </label>
                <div class="sp-cities" id="city-chips">
                    @foreach($cities as $i => $c)
                        @php $img = $cityImg($c['id']); $region = $cityInfo[$c['id']]->region_administrative ?? null; @endphp
                        <label class="sp-city{{ $img ? ' has-img' : '' }}" data-sp-city data-name="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($c['name'].' '.$region)) }}" @if($i >= $visibleCities && ! in_array($c['id'], $pickedCities, true)) data-sp-extra hidden @endif>
                            <input type="checkbox" name="city_ids[]" value="{{ $c['id'] }}" {{ in_array($c['id'], $pickedCities, true) ? 'checked' : '' }}>
                            <span class="sp-city__card">
                                @if($img)<img src="{{ $img }}" alt="" loading="lazy" decoding="async">@endif
                                <x-icon name="map-point" class="sp-city__pin" />
                                <span class="sp-city__check" aria-hidden="true">✓</span>
                                <span class="sp-city__txt">
                                    <span class="sp-city__name">{{ $c['name'] }}</span>
                                    @if($region)<span class="sp-city__region">{{ $region }}</span>@endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="sp-none" data-sp-none hidden>{{ __('Aucune ville ne correspond.') }}</p>
                @if(count($cities) > $visibleCities)
                    <button type="button" class="sp-more" data-sp-more><x-icon name="global" />{{ __('Voir toutes les villes') }} ({{ count($cities) }})</button>
                @endif
            </section>

            {{-- 2. Dates et voyageurs --}}
            <section class="sp-step">
                <div class="sp-step__head">
                    <span class="sp-step__n"><x-icon name="calendar" /></span>
                    <div>
                        <h2 class="sp-step__title">{{ __('2. Quand et avec qui ?') }}</h2>
                        <p class="sp-step__sub">{{ __('Durée, nombre de voyageurs et date de départ.') }}</p>
                    </div>
                </div>
                <div class="sp-row">
                    <div class="sp-field">
                        <label for="days">{{ __('Durée (jours)') }}</label>
                        <div class="sp-stepper">
                            <button type="button" data-sp-step="days" data-d="-1" aria-label="{{ __('Un jour de moins') }}"><span aria-hidden="true">−</span></button>
                            <div><input id="days" name="days" type="number" min="1" max="{{ $maxDays }}" required value="{{ $v('days', 3) }}"><small>{{ __('jours') }}</small></div>
                            <button type="button" data-sp-step="days" data-d="1" aria-label="{{ __('Un jour de plus') }}"><span aria-hidden="true">+</span></button>
                        </div>
                    </div>
                    <div class="sp-field">
                        <label for="travelers">{{ __('Voyageurs') }}</label>
                        <div class="sp-stepper">
                            <button type="button" data-sp-step="travelers" data-d="-1" aria-label="{{ __('Un voyageur de moins') }}"><span aria-hidden="true">−</span></button>
                            <div><input id="travelers" name="travelers" type="number" min="1" max="12" required value="{{ $v('travelers', 2) }}"><small>{{ __('personnes') }}</small></div>
                            <button type="button" data-sp-step="travelers" data-d="1" aria-label="{{ __('Un voyageur de plus') }}"><span aria-hidden="true">+</span></button>
                        </div>
                    </div>
                    <div class="sp-field">
                        <label for="start_date">{{ __('Date de départ') }}</label>
                        <input id="start_date" name="start_date" type="date" min="{{ now()->toDateString() }}" class="sp-input" value="{{ old('start_date', now()->addDays(7)->toDateString()) }}">
                    </div>
                    <div class="sp-field">
                        <label for="start_city_id">{{ __('Ville de départ') }} <span class="normal-case font-medium tracking-normal">{{ __('(facultatif)') }}</span></label>
                        <select id="start_city_id" name="start_city_id" class="sp-input">
                            <option value="">{{ __('— Non précisée —') }}</option>
                            @foreach($cities as $c)
                                <option value="{{ $c['id'] }}" {{ (string) old('start_city_id') === (string) $c['id'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                        <p class="sp-hint">{{ __('Sert à ordonner les villes et à estimer les trajets entre elles.') }}</p>
                    </div>
                </div>
            </section>

            {{-- 3. Budget --}}
            <section class="sp-step">
                <div class="sp-step__head">
                    <span class="sp-step__n"><x-icon name="wallet-money" /></span>
                    <div>
                        <h2 class="sp-step__title">{{ __('3. Votre budget total') }}</h2>
                        <p class="sp-step__sub">{{ __('Pour tout le groupe et tout le séjour.') }}</p>
                    </div>
                </div>
                <div class="sp-budget__top">
                    <label class="sp-budget__val" for="budget_xof">
                        <input id="budget_xof" name="budget_xof" type="number" min="10000" step="5000" required value="{{ $v('budget_xof', 250000) }}" inputmode="numeric">
                        <span>FCFA</span>
                    </label>
                    <p class="sp-budget__per" data-sp-per></p>
                </div>
                <input type="range" class="sp-range" min="10000" max="2000000" step="5000" data-sp-range aria-label="{{ __('Budget total (FCFA)') }}">
                <div class="sp-presets">
                    @foreach([[100000, __('Économique'), 'wallet-money'], [250000, __('Confort'), 'star'], [500000, __('Premium'), 'star-shine'], [1000000, __('Luxe'), 'cup-star']] as [$amount, $label, $icon])
                        <button type="button" class="sp-preset" data-sp-preset="{{ $amount }}"><x-icon :name="$icon" />{{ $label }} <small>{{ number_format($amount, 0, ',', ' ') }}</small></button>
                    @endforeach
                </div>
            </section>

            {{-- 4. Envies --}}
            <section class="sp-step">
                <div class="sp-step__head">
                    <span class="sp-step__n"><x-icon name="star-shine" /></span>
                    <div>
                        <h2 class="sp-step__title">{{ __('4. Vos centres d\'intérêt') }}</h2>
                        <p class="sp-step__sub">{{ __('Choisissez-en autant que vous voulez.') }}</p>
                    </div>
                </div>
                <div class="sp-tiles">
                    @foreach($interests as $key => $def)
                        <label class="sp-tile">
                            <input type="checkbox" name="interests[]" value="{{ $key }}" {{ in_array($key, $pickedInterests, true) ? 'checked' : '' }}>
                            <span><x-icon :name="$interestIcons[$key] ?? 'star-shine'" />{{ __($def['label']) }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- 5. Rythme --}}
            <section class="sp-step">
                <div class="sp-step__head">
                    <span class="sp-step__n"><x-icon name="clock-circle" /></span>
                    <div>
                        <h2 class="sp-step__title">{{ __('5. Votre rythme') }}</h2>
                        <p class="sp-step__sub">{{ __('Nombre d\'étapes par jour.') }}</p>
                    </div>
                </div>
                <div class="sp-paces">
                    @foreach($paces as $key => [$label, $desc, $icon, $level])
                        <label class="sp-pace">
                            <input type="radio" name="pace" value="{{ $key }}" {{ old('pace', $defaults['pace']) === $key ? 'checked' : '' }}>
                            <span>
                                <x-icon :name="$icon" />
                                <b>{{ $label }}</b>
                                <small>{{ $desc }}</small>
                                <span class="sp-pace__dots" aria-hidden="true">@for($d = 1; $d <= 3; $d++)<em class="{{ $d <= $level ? 'is-on' : '' }}"></em>@endfor</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        </form>

        {{-- Résumé en direct --}}
        <aside class="sp-sum" id="sp-summary" aria-label="{{ __('Votre séjour') }}">
            <p class="sp-sum__title"><x-icon name="routing" />{{ __('Votre séjour') }}</p>
            <ul class="sp-sum__list">
                <li data-sp-sum="cities"><x-icon name="map-point" /><span><small>{{ __('Destinations') }}</small><b></b></span></li>
                <li data-sp-sum="when"><x-icon name="calendar" /><span><small>{{ __('Durée') }}</small><b></b></span></li>
                <li data-sp-sum="who"><x-icon name="users-group-rounded" /><span><small>{{ __('Voyageurs') }}</small><b></b></span></li>
                <li data-sp-sum="budget"><x-icon name="wallet-money" /><span><small>{{ __('Budget') }}</small><b></b></span></li>
                <li data-sp-sum="likes"><x-icon name="star-shine" /><span><small>{{ __('Envies') }}</small><b></b></span></li>
            </ul>
            <button type="submit" form="trip-form" class="sp-submit"><x-icon name="routing" />{{ __('Composer mon séjour') }}</button>
            <p class="sp-sum__note">{{ __('Le programme est calculé à partir des adresses publiées sur la plateforme. Vous gardez la main : chaque étape renvoie vers sa fiche pour réserver.') }}</p>
        </aside>
    </div>

    @if($recent->isNotEmpty())
        <section class="sp-recent">
            <h2>{{ __('Mes derniers séjours') }}</h2>
            <div class="sp-recent__grid">
                @foreach($recent as $t)
                    <a href="{{ route('trip.show', $t->uuid) }}">
                        <x-icon name="routing" />
                        <span class="min-w-0">
                            <b class="truncate">{{ implode(', ', $t->plan['summary']['cities'] ?? []) }} · {{ $t->plan['summary']['days'] ?? '?' }} j</b>
                            <small>{{ number_format($t->plan['summary']['total_cost'] ?? 0, 0, ',', ' ') }} {{ __('FCFA') }} · {{ $t->created_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

{{-- Barre mobile --}}
<div class="sp-bar" data-sp-bar>
    <span><small data-sp-bar-sub></small><b data-sp-bar-main></b></span>
    <button type="submit" form="trip-form">{{ __('Composer') }}</button>
</div>

<script>
(function () {
    const form = document.getElementById('trip-form');
    const max = {{ (int) $maxCities }};
    const boxes = Array.from(document.querySelectorAll('#city-chips input[type=checkbox]'));
    const T = {{ \Illuminate\Support\Js::from([
        'none' => __('À choisir'), 'days' => __(':n jours'), 'day' => __(':n jour'), 'trav' => __(':n voyageurs'), 'trav1' => __(':n voyageur'),
        'per' => __('soit :a FCFA par personne et par jour'), 'free' => __('Toutes envies'), 'pick' => __('Choisissez au moins une destination.'),
        'from' => __('départ le :d'), 'more' => __('Voir toutes les villes'), 'less' => __('Voir moins de villes'),
    ]) }};
    const fmt = n => Number(n || 0).toLocaleString('fr-FR');
    const $ = s => document.querySelector(s);

    // ── Villes : limite, compteur, recherche, « voir toutes » ──
    function syncCities() {
        const n = boxes.filter(b => b.checked).length;
        boxes.forEach(b => { b.disabled = !b.checked && n >= max; });
        const badge = $('[data-sp-count]');
        badge.textContent = n + ' / ' + max; badge.classList.toggle('is-full', n > 0);
        summary();
    }
    boxes.forEach(b => b.addEventListener('change', syncCities));
    const cards = Array.from(document.querySelectorAll('[data-sp-city]'));
    let expanded = false;
    const moreBtn = $('[data-sp-more]');
    function showCities(q) {
        let shown = 0;
        cards.forEach(c => {
            const match = !q || c.dataset.name.includes(q);
            const checked = c.querySelector('input').checked;
            c.hidden = !(match && (q || expanded || !c.hasAttribute('data-sp-extra') || checked));
            if (!c.hidden) shown++;
        });
        $('[data-sp-none]').hidden = shown > 0;
        if (moreBtn) moreBtn.hidden = !!q;
    }
    const norm = s => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
    $('[data-sp-search]').addEventListener('input', e => showCities(norm(e.target.value)));
    if (moreBtn) moreBtn.addEventListener('click', () => {
        expanded = !expanded; showCities('');
        moreBtn.lastChild.textContent = (expanded ? T.less : T.more + ' ({{ count($cities) }})');
    });

    // ── Compteurs −/+ ──
    document.querySelectorAll('[data-sp-step]').forEach(b => b.addEventListener('click', () => {
        const input = document.getElementById(b.dataset.spStep);
        const next = (parseInt(input.value, 10) || 0) + Number(b.dataset.d);
        input.value = Math.min(Number(input.max), Math.max(Number(input.min), next));
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }));

    // ── Budget : champ, curseur et formules synchronisés ──
    const budget = document.getElementById('budget_xof'), range = $('[data-sp-range]'), presets = document.querySelectorAll('[data-sp-preset]');
    function syncBudget(from) {
        if (from === 'range') budget.value = range.value; else range.value = Math.min(Number(range.max), Number(budget.value) || 0);
        presets.forEach(p => p.classList.toggle('is-on', Number(p.dataset.spPreset) === Number(budget.value)));
        summary();
    }
    range.addEventListener('input', () => syncBudget('range'));
    budget.addEventListener('input', () => syncBudget('field'));
    presets.forEach(p => p.addEventListener('click', () => { budget.value = p.dataset.spPreset; syncBudget('field'); }));

    // ── Résumé en direct ──
    function setSum(key, text, empty) {
        const li = document.querySelector('[data-sp-sum="' + key + '"]');
        li.querySelector('b').textContent = text; li.classList.toggle('is-empty', !!empty);
    }
    function summary() {
        const cities = boxes.filter(b => b.checked).map(b => b.closest('[data-sp-city]').querySelector('.sp-city__name').textContent);
        const days = parseInt($('#days').value, 10) || 0, trav = parseInt($('#travelers').value, 10) || 0, money = parseInt(budget.value, 10) || 0;
        const date = $('#start_date').value ? new Date($('#start_date').value + 'T12:00:00').toLocaleDateString(document.documentElement.lang || 'fr', { day: 'numeric', month: 'long' }) : '';
        const likes = Array.from(form.querySelectorAll('input[name="interests[]"]:checked')).map(i => i.nextElementSibling.textContent.trim());
        setSum('cities', cities.length ? cities.join(' · ') : T.none, !cities.length);
        setSum('when', (days > 1 ? T.days : T.day).replace(':n', days) + (date ? ' · ' + T.from.replace(':d', date) : ''));
        setSum('who', (trav > 1 ? T.trav : T.trav1).replace(':n', trav));
        setSum('budget', fmt(money) + ' FCFA');
        setSum('likes', likes.length ? likes.join(', ') : T.free, !likes.length);
        const per = days && trav ? Math.round(money / days / trav / 500) * 500 : 0;
        $('[data-sp-per]').innerHTML = per ? T.per.replace(':a', '<b>' + fmt(per) + '</b>') : '';
        $('[data-sp-bar-sub]').textContent = (cities.length ? cities.join(', ') : T.none) ;
        $('[data-sp-bar-main]').textContent = (days > 1 ? T.days : T.day).replace(':n', days) + ' · ' + fmt(money) + ' FCFA';
    }
    form.addEventListener('input', summary);
    form.addEventListener('change', summary);

    // Barre mobile masquée quand le résumé est visible.
    const bar = $('[data-sp-bar]'), sum = document.getElementById('sp-summary');
    if ('IntersectionObserver' in window) new IntersectionObserver(es => es.forEach(e => bar.classList.toggle('is-away', e.isIntersecting)), { threshold: .2 }).observe(sum);

    // Au moins une ville avant d'envoyer.
    form.addEventListener('submit', e => {
        if (!boxes.some(b => b.checked)) {
            e.preventDefault();
            document.getElementById('sp-where').scrollIntoView({ behavior: 'smooth', block: 'start' });
            alert(T.pick);
        }
    });

    showCities(''); syncCities(); syncBudget('field');
})();
</script>

@include('partials.homepage-footer')
</body>
</html>
