@php
    $kindIcons = ['documentaire' => 'videocamera-record', 'fiction' => 'clapperboard-play'];
    $kindLead = [
        'documentaire' => __('Patrimoine, histoire, société, nature : le réel ivoirien filmé par celles et ceux qui le vivent.'),
        'fiction' => __('Drames, comédies, récits historiques et courts métrages : les histoires du cinéma ivoirien.'),
    ];
    $pageTitle = $kind ? __(\App\Models\Film::KINDS[$kind]['plural']) : __('Cinéma d\'Ivoire');
    $routeFor = fn (?string $k) => $k ? route('films.kind', \App\Models\Film::KINDS[$k]['slug']) : route('films.index');
    $hasFilms = $counts['all'] > 0;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} — {{ __('Médiathèque') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', [
        'title' => $pageTitle.' — '.__('Cinéma d\'Ivoire'),
        'description' => $kind ? $kindLead[$kind] : __('Films documentaires et de fiction ivoiriens : fiches, bandes-annonces, projections et liens officiels pour les regarder.'),
        'image' => $spotlight?->backdropUrl(),
        'url' => $routeFor($kind),
    ])
    @if($filtering)<meta name="robots" content="noindex, follow">@endif
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        /* « Cinéma d'Ivoire » : salle obscure (vert nuit), affiches au format 2:3, accents orange et or.
           Aplats uniquement, ombre au survol seulement. */
        .ci { --night: #006B3F; --gold: #EB6E00; padding-bottom: 72px; color: #0E3B2A; }
        .ci-wrap { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
        .ci .ico { flex: none; }
        .ci .ico > svg { width: 100%; height: 100%; }
        .ci .ci-kind__ic > .ico, .ci .ci-badge > .ico, .ci .ci-btn > .ico, .ci .ci-kick > .ico, .ci .ci-search > .ico, .ci .ci-card__ph > .ico, .ci .ci-reel .ico, .ci .ci-spot__label > .ico, .ci .ci-more > .ico, .ci .ci-card__play > .ico { margin: 0; border: 0; border-radius: 0; background: none; padding: 0; }
        .ci-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 24px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; border: 0; cursor: pointer; transition: background .15s ease; white-space: nowrap; }
        .ci-btn:hover { background: #B85600; }
        .ci-btn .ico { width: 18px; height: 18px; }
        .ci-btn.is-ghost { background: rgba(255,255,255,.13); }
        .ci-btn.is-ghost:hover { background: rgba(255,255,255,.22); }
        .ci-btn.is-light { background: #fff; color: #B85600; border: 1px solid #D8E9E0; }
        .ci-btn.is-light:hover { background: #F3F8F5; }
        .ci a:focus-visible, .ci button:focus-visible, .ci select:focus-visible, .ci input:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }

        /* Héros : film à l'affiche (image de fond + voile uni) ou présentation de la rubrique */
        .ci-hero { position: relative; overflow: hidden; background: var(--night); color: #fff; }
        .ci-hero .ivm-g { opacity: .07 !important; }
        .ci-hero .ivm-g > * { stroke: #F7C396 !important; }
        .ci-hero__bg { position: absolute; inset: 0; }
        .ci-hero__bg img { width: 100%; height: 100%; object-fit: cover; opacity: .38; }
        .ci-hero__bg::after { content: ""; position: absolute; inset: 0; background: rgba(0,107,63,.55); }
        .ci-hero__in { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, .75fr); align-items: center; gap: 44px; max-width: 1280px; margin: 0 auto; padding: 56px 20px 60px; }
        .ci-crumbs { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin: 0 0 14px; padding: 0; list-style: none; font-size: 13px; color: #8AA99B; }
        .ci-crumbs a { color: #F3F8F5; text-decoration: none; }
        .ci-crumbs a:hover { text-decoration: underline; }
        .ci-crumbs li + li::before { content: "/"; margin-right: 6px; color: #8AA99B; }
        .ci-kick { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: var(--gold); font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .ci-kick .ico { width: 15px; height: 15px; }
        .ci-h1 { margin: 14px 0 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 4.8vw, 62px); font-weight: 700; line-height: 1.03; color: #fff; }
        .ci-h1 em { font-style: normal; color: var(--gold); }
        .ci-lead { margin: 14px 0 0; max-width: 54ch; font-size: 17px; line-height: 1.6; color: #D8E9E0; }
        .ci-facts { display: flex; flex-wrap: wrap; gap: 8px; margin: 20px 0 0; padding: 0; list-style: none; }
        .ci-facts li { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 13px; border-radius: 999px; background: rgba(255,255,255,.1); font-size: 13.5px; font-weight: 600; color: #fff; }
        /* Pastilles d'icônes (modèle du site) — variante fond sombre : pastille claire translucide, icône dorée */
        .ci-facts li { padding-left: 4px; }
        .ci-facts li > .ico, .ci-pro li > .ico { width: 26px; height: 26px; margin: 0; border-radius: 32%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); color: #F7C396; }
        .ci-facts li > .ico > svg, .ci-pro li > .ico > svg { width: 14px; height: 14px; }
        .ci-cta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }
        .ci-spot__label { display: inline-flex; align-items: center; gap: 7px; margin-top: 26px; font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--gold); }
        .ci-spot__label .ico { width: 14px; height: 14px; }
        .ci-spot h2 { margin: 6px 0 0; font-family: 'Montserrat', serif; font-size: clamp(22px, 2.4vw, 30px); line-height: 1.15; color: #fff; }
        .ci-spot p { margin: 8px 0 0; max-width: 58ch; font-size: 15px; line-height: 1.55; color: #D8E9E0; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ci-poster { justify-self: end; width: min(100%, 300px); aspect-ratio: 2 / 3; border-radius: 18px; overflow: hidden; background: #00874F; border: 4px solid rgba(255,255,255,.9); display: block; transition: transform .25s ease; }
        .ci-poster:hover { transform: translateY(-4px); }
        .ci-poster img { width: 100%; height: 100%; object-fit: cover; }
        .ci-reel { justify-self: end; display: grid; grid-template-columns: repeat(2, 132px); gap: 14px; }
        .ci-reel span { aspect-ratio: 2 / 3; border-radius: 14px; border: 2px dashed rgba(235,110,0,.45); display: flex; align-items: center; justify-content: center; color: var(--gold); }
        .ci-reel span:nth-child(2n) { translate: 0 26px; }
        .ci-reel .ico { width: 34px; height: 34px; opacity: .8; }

        /* Les deux grandes catégories */
        .ci-kinds { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-top: -34px; position: relative; z-index: 2; }
        .ci-kind { --c: 0,135,79; display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; align-items: center; gap: 18px; padding: 22px 24px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; text-decoration: none; color: inherit; transition: border-color .15s ease, box-shadow .2s ease; }
        .ci-kind.is-fiction { --c: 235,110,0; }
        .ci-kind:hover { border-color: rgba(var(--c), .45); box-shadow: 0 22px 40px -32px rgba(14,59,42,.5); }
        .ci-kind.is-on { border-color: rgb(var(--c)); box-shadow: inset 0 0 0 1px rgb(var(--c)); }
        .ci-kind__ic { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; }
        .ci-kind__ic .ico { width: 30px; height: 30px; }
        .ci-kind h2 { margin: 0; font-family: 'Montserrat', serif; font-size: 23px; line-height: 1.15; color: #0E3B2A; }
        .ci-kind p { margin: 5px 0 0; font-size: 14px; line-height: 1.5; color: #24503D; }
        .ci-kind__n { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; font-size: 12px; font-weight: 700; color: #557466; text-align: right; }
        .ci-kind__n b { font-size: 26px; line-height: 1; color: rgb(var(--c)); }
        .ci-kind__go { width: 36px; height: 36px; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: rgba(var(--c), .1); color: rgb(var(--c)); }
        .ci-kind__go .ico { width: 16px; height: 16px; }

        /* Filtres */
        .ci-bar { position: sticky; top: 64px; z-index: 20; margin-top: 30px; background: #F3F8F5; border-bottom: 1px solid #D8E9E0; }
        .ci-bar form { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px 0; }
        .ci-tabs { display: flex; gap: 6px; padding: 4px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; }
        .ci-tab { display: inline-flex; align-items: center; gap: 7px; height: 38px; padding: 0 15px; border-radius: 999px; color: #0E3B2A; font-size: 14px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .ci-tab small { font-size: 12px; color: #557466; font-weight: 700; }
        .ci-tab:hover { background: #F3F8F5; }
        .ci-tab.is-on { background: #EB6E00; color: #fff; }
        .ci-tab.is-on small { color: #D8E9E0; }
        .ci-search { position: relative; flex: 1 1 220px; min-width: 0; }
        .ci-search .ico { position: absolute; left: 14px; top: 50%; translate: 0 -50%; width: 17px; height: 17px; color: #557466; pointer-events: none; }
        .ci-search input { width: 100%; height: 46px; padding: 0 14px 0 40px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; font-size: 14.5px; color: #0E3B2A; }
        .ci-sel { height: 46px; padding: 0 34px 0 14px; border-radius: 999px; border: 1px solid #D8E9E0; background-color: #fff; font-size: 14px; font-weight: 600; color: #0E3B2A; max-width: 190px; }
        .ci-filters { display: contents; }
        .ci-reset { font-size: 13.5px; font-weight: 700; color: #B85600; text-decoration: none; white-space: nowrap; }
        .ci-reset:hover { text-decoration: underline; }
        .ci-more { display: none; }

        /* Sections */
        .ci-sec { padding-top: 34px; }
        .ci-sec__head { display: flex; align-items: end; justify-content: space-between; gap: 14px; margin-bottom: 16px; }
        .ci-h2 { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(22px, 2.4vw, 29px); font-weight: 700; color: #0E3B2A; }
        .ci-sub { margin: 4px 0 0; font-size: 14px; color: #557466; }
        .ci-count { flex: none; font-size: 14px; color: #557466; }
        .ci-count b { color: #0E3B2A; }

        /* Rangée « À l'affiche » : défilement horizontal type plateforme */
        .ci-rail { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 5 * 18px) / 6); gap: 18px; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 6px; scrollbar-width: thin; }
        .ci-rail > * { scroll-snap-align: start; }

        /* Cartes affiche */
        .ci-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 26px 20px; }
        .ci-card { display: flex; flex-direction: column; min-width: 0; text-decoration: none; color: inherit; }
        .ci-card__poster { position: relative; aspect-ratio: 2 / 3; border-radius: 16px; overflow: hidden; background: #00874F; border: 1px solid #D8E9E0; transition: transform .2s ease, box-shadow .2s ease; }
        .ci-card__poster img { width: 100%; height: 100%; object-fit: cover; }
        .ci-card:hover .ci-card__poster { transform: translateY(-4px); box-shadow: 0 22px 36px -24px rgba(0,107,63,.6); }
        .ci-card__ph { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 14px; text-align: center; color: #F7C396; font-family: 'Montserrat', serif; font-size: 17px; line-height: 1.2; }
        .ci-card__ph .ico { width: 34px; height: 34px; color: var(--gold); }
        .ci-card__badges { position: absolute; left: 8px; right: 8px; top: 8px; display: flex; justify-content: space-between; gap: 6px; }
        .ci-badge { --c: 0,135,79; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 9px; border-radius: 999px; background: #fff; color: rgb(var(--c)); font-size: 11.5px; font-weight: 800; }
        .ci-badge.is-fiction { --c: 235,110,0; }
        .ci-badge .ico { width: 13px; height: 13px; }
        .ci-badge.is-new { background: #EB6E00; color: #fff; }
        .ci-card__play { position: absolute; left: 8px; right: 8px; bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 7px; height: 40px; border-radius: 999px; background: #fff; color: #B85600; font-size: 13.5px; font-weight: 800; opacity: 0; transform: translateY(6px); transition: opacity .2s ease, transform .2s ease; }
        .ci-card__play .ico { width: 16px; height: 16px; }
        .ci-card:hover .ci-card__play, .ci-card:focus-visible .ci-card__play { opacity: 1; transform: none; }
        .ci-card h3 { margin: 11px 2px 0; font-size: 15.5px; font-weight: 700; line-height: 1.3; color: #0E3B2A; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ci-card__meta { margin: 4px 2px 0; font-size: 12.5px; color: #557466; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ci-card__dir { margin: 2px 2px 0; font-size: 12.5px; color: #557466; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .ci-empty { padding: 50px 20px; border-radius: 26px; background: #fff; border: 1px dashed #D8E9E0; text-align: center; color: #557466; }
        .ci-empty__ic { display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; }
        .ci-empty__ic .ico { width: 32px; height: 32px; }
        .ci-empty h2 { margin: 12px 0 6px; font-family: 'Montserrat', serif; font-size: 24px; color: #0E3B2A; }
        .ci-empty p { margin: 0 auto; max-width: 52ch; line-height: 1.6; }
        .ci-empty .ci-btn { margin-top: 18px; }

        /* Appel aux professionnels */
        .ci-pro { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 24px; margin-top: 44px; padding: 28px 30px; border-radius: 26px; background: var(--night); color: #fff; position: relative; overflow: hidden; }
        .ci-pro .ivm-g { opacity: .06 !important; }
        .ci-pro .ivm-g > * { stroke: #F7C396 !important; }
        .ci-pro > * { position: relative; z-index: 1; }
        .ci-pro h2 { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(21px, 2.3vw, 27px); color: #fff; }
        .ci-pro p { margin: 6px 0 0; max-width: 64ch; font-size: 15px; line-height: 1.55; color: #D8E9E0; }
        .ci-pro ul { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 12px 0 0; padding: 0; list-style: none; font-size: 13.5px; color: #F3F8F5; }
        .ci-pro li { display: inline-flex; align-items: center; gap: 6px; }

        .ci-pages { margin-top: 28px; }

        @media (max-width: 1279px) { .ci-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } .ci-rail { grid-auto-columns: calc((100% - 4 * 18px) / 5); } }
        @media (max-width: 1023px) {
            .ci-hero__in { grid-template-columns: minmax(0, 1fr) 210px; gap: 26px; padding: 40px 20px 60px; }
            .ci-reel { grid-template-columns: repeat(2, 92px); }
            .ci-kind { grid-template-columns: 52px minmax(0, 1fr); padding: 18px; gap: 14px; }
            .ci-kind__ic { width: 52px; height: 52px; }
            .ci-kind__n { display: none; }
            .ci-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ci-rail { grid-auto-columns: calc((100% - 3 * 18px) / 3.6); }
            .ci-sel { max-width: 160px; }
        }
        @media (max-width: 640px) {
            .ci-wrap { padding: 0 16px; }
            .ci-hero__in { grid-template-columns: minmax(0, 1fr) 92px; gap: 14px; padding: 14px 16px 46px; align-items: start; }
            .ci-crumbs { margin-bottom: 8px; font-size: 11.5px; }
            .ci-kick { padding: 4px 9px; font-size: 10px; }
            .ci-kick .ico { width: 13px; height: 13px; }
            .ci-h1 { margin-top: 8px; font-size: 27px; }
            .ci-lead { margin-top: 6px; font-size: 13px; line-height: 1.45; }
            .ci-facts { gap: 5px; margin-top: 10px; }
            .ci-facts li { height: 26px; font-size: 11px; padding: 0 9px 0 3px; gap: 5px; }
            .ci-facts li > .ico { width: 20px; height: 20px; }
            .ci-facts li > .ico > svg { width: 11px; height: 11px; }
            .ci-spot__label { margin-top: 12px; font-size: 10.5px; }
            .ci-spot h2 { font-size: 18px; }
            .ci-spot p { display: none; }
            .ci-cta { gap: 6px; margin-top: 12px; }
            .ci-btn { height: 40px; padding: 0 15px; font-size: 13.5px; }
            .ci-btn .ico { width: 16px; height: 16px; }
            .ci-poster { border-width: 2px; border-radius: 10px; margin-top: 30px; }
            .ci-reel { grid-template-columns: 92px; margin-top: 26px; }
            .ci-reel span:nth-child(n+2) { display: none; }
            .ci-kinds { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 10px; margin-top: -30px; }
            .ci-kind { grid-template-columns: minmax(0, 1fr); gap: 8px; padding: 12px; border-radius: 18px; }
            .ci-kind__ic { width: 38px; height: 38px; }
            .ci-kind__ic .ico { width: 20px; height: 20px; }
            .ci-kind h2 { font-size: 16px; }
            .ci-kind p { display: none; }
            .ci-bar { top: 56px; margin-top: 18px; }
            .ci-bar form { gap: 8px; padding: 10px 0; }
            .ci-tabs { order: 1; width: 100%; overflow-x: auto; scrollbar-width: none; }
            .ci-tab { height: 34px; padding: 0 12px; font-size: 13px; }
            .ci-search { order: 2; flex: 1 1 0; }
            .ci-search input { height: 42px; }
            .ci-more { order: 3; display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 42px; padding: 0 14px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; font-size: 13.5px; font-weight: 700; color: #0E3B2A; cursor: pointer; }
            .ci-more .ico { width: 16px; height: 16px; }
            .ci-filters { order: 4; display: none; width: 100%; gap: 8px; flex-wrap: wrap; }
            .ci-filters.is-open { display: flex; }
            .ci-sel { flex: 1 1 calc(50% - 4px); max-width: none; height: 42px; }
            .ci-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 12px; }
            .ci-rail { grid-auto-columns: 42%; gap: 12px; }
            .ci-card__play { display: none; }
            .ci-badge { height: 24px; font-size: 10.5px; padding: 0 8px; }
            .ci-badge:not(.is-new) { font-size: 0; gap: 0; padding: 0 7px; }
            .ci-card h3 { font-size: 14px; }
            .ci-pro { grid-template-columns: minmax(0, 1fr); padding: 20px; gap: 14px; }
            .ci-pro .ci-btn { width: 100%; }
            .ci-count { display: none; }
        }
        @media (prefers-reduced-motion: reduce) { .ci-card__poster, .ci-card__play, .ci-poster { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.public-top-nav')

<main class="ci">
    <section class="ci-hero" aria-labelledby="ci-title">
        @if($spotlight && $spotlight->backdropUrl())
            <div class="ci-hero__bg" aria-hidden="true"><img src="{{ $spotlight->backdropUrl() }}" alt="" fetchpriority="high" decoding="async"></div>
        @else
            @include('partials.ivorian-motifs', ['culture' => 'baoule'])
        @endif
        <div class="ci-hero__in">
            <div>
                <ol class="ci-crumbs" aria-label="{{ __('Fil d\'Ariane') }}">
                    <li><a href="{{ route('home') }}">{{ __('Accueil') }}</a></li>
                    <li>{{ __('Médiathèque') }}</li>
                    @if($kind)<li><a href="{{ route('films.index') }}">{{ __('Cinéma d\'Ivoire') }}</a></li>@endif
                </ol>
                <span class="ci-kick"><x-icon name="{{ $kind ? $kindIcons[$kind] : 'clapperboard-play' }}" />{{ $kind ? __('Cinéma d\'Ivoire') : __('Médiathèque') }}</span>
                <h1 class="ci-h1" id="ci-title">
                    @if($kind){{ __(\App\Models\Film::KINDS[$kind]['plural']) }}@else{{ __('Cinéma') }} <em>{{ __('d\'Ivoire') }}</em>@endif
                </h1>
                <p class="ci-lead">{{ $kind ? $kindLead[$kind] : __('Le meilleur du film documentaire et de fiction ivoirien : synopsis, bandes-annonces, projections à venir et liens officiels pour regarder les films en toute légalité.') }}</p>
                <ul class="ci-facts">
                    <li><x-icon name="clapperboard-play" />{{ trans_choice(':count film|:count films', $kind ? ($counts[$kind] ?? 0) : $counts['all'], ['count' => $kind ? ($counts[$kind] ?? 0) : $counts['all']]) }}</li>
                    <li><x-icon name="shield-check" />{{ __('Diffusion autorisée par les ayants droit') }}</li>
                </ul>

                @if($spotlight)
                    <div class="ci-spot">
                        <span class="ci-spot__label"><x-icon name="star" />{{ $spotlight->is_featured ? __('À l\'affiche') : __('Dernier ajout') }}</span>
                        <h2>{{ $spotlight->title }}</h2>
                        @if($spotlight->tagline || $spotlight->synopsis)<p>{{ $spotlight->tagline ?: \Illuminate\Support\Str::limit($spotlight->synopsis, 200) }}</p>@endif
                    </div>
                    <div class="ci-cta">
                        <a href="{{ route('films.show', $spotlight) }}" class="ci-btn"><x-icon name="play" />{{ $spotlight->trailer() ? __('Voir la bande-annonce') : __('Découvrir le film') }}</a>
                        <a href="#ci-list" class="ci-btn is-ghost">{{ __('Parcourir les films') }}</a>
                    </div>
                @else
                    <div class="ci-cta">
                        <a href="{{ route('films.submit') }}" class="ci-btn"><x-icon name="upload" />{{ __('Proposer un film') }}</a>
                    </div>
                @endif
            </div>
            @if($spotlight && $spotlight->posterUrl())
                <a href="{{ route('films.show', $spotlight) }}" class="ci-poster" aria-label="{{ $spotlight->title }}" tabindex="-1"><img src="{{ $spotlight->posterUrl() }}" alt="" decoding="async"></a>
            @else
                <div class="ci-reel" aria-hidden="true">
                    <span><x-icon name="videocamera-record" /></span><span><x-icon name="clapperboard-play" /></span>
                    <span><x-icon name="ticket" /></span><span><x-icon name="play-circle" /></span>
                </div>
            @endif
        </div>
    </section>

    <div class="ci-wrap">
        <nav class="ci-kinds" aria-label="{{ __('Catégories') }}">
            @foreach(\App\Models\Film::KINDS as $k => $meta)
                <a href="{{ $kind === $k ? route('films.index') : $routeFor($k) }}#ci-list" class="ci-kind is-{{ $k }} {{ $kind === $k ? 'is-on' : '' }}" @if($kind === $k) aria-current="page" @endif>
                    <span class="ci-kind__ic"><x-icon name="{{ $kindIcons[$k] }}" /></span>
                    <span>
                        <h2>{{ $k === 'documentaire' ? __('Film documentaire') : __('Film fiction') }}</h2>
                        <p>{{ $kindLead[$k] }}</p>
                    </span>
                    <span class="ci-kind__n"><b>{{ $counts[$k] ?? 0 }}</b>{{ trans_choice('film|films', $counts[$k] ?? 0) }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="ci-bar">
        <div class="ci-wrap">
            <form method="GET" action="{{ $routeFor($kind) }}#ci-list" role="search">
                <div class="ci-tabs" role="tablist" aria-label="{{ __('Catégorie') }}">
                    <a href="{{ route('films.index') }}#ci-list" class="ci-tab {{ ! $kind ? 'is-on' : '' }}" @if(! $kind) aria-current="page" @endif>{{ __('Tous') }} <small>{{ $counts['all'] }}</small></a>
                    @foreach(\App\Models\Film::KINDS as $k => $meta)
                        <a href="{{ $routeFor($k) }}#ci-list" class="ci-tab {{ $kind === $k ? 'is-on' : '' }}" @if($kind === $k) aria-current="page" @endif>{{ __($meta['plural']) }} <small>{{ $counts[$k] ?? 0 }}</small></a>
                    @endforeach
                </div>
                <label class="ci-search">
                    <span class="sr-only">{{ __('Rechercher un film') }}</span>
                    <x-icon name="magnifier" />
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Titre, réalisateur, thème…') }}" maxlength="80">
                </label>
                <button type="button" class="ci-more" data-ci-more aria-expanded="false" aria-controls="ci-filters"><x-icon name="filter" />{{ __('Filtres') }}</button>
                <div class="ci-filters" id="ci-filters">
                    @if($genres->isNotEmpty())
                        <select name="genre" class="ci-sel" aria-label="{{ __('Genre') }}" onchange="this.form.submit()">
                            <option value="">{{ __('Tous les genres') }}</option>
                            @foreach($genres as $g)<option value="{{ $g }}" @selected($filters['genre'] === $g)>{{ $g }}</option>@endforeach
                        </select>
                    @endif
                    @if($years->isNotEmpty())
                        <select name="annee" class="ci-sel" aria-label="{{ __('Année') }}" onchange="this.form.submit()">
                            <option value="">{{ __('Toutes les années') }}</option>
                            @foreach($years as $y)<option value="{{ $y }}" @selected($filters['year'] === (int) $y)>{{ $y }}</option>@endforeach
                        </select>
                    @endif
                    @if($countries->count() > 1)
                        <select name="pays" class="ci-sel" aria-label="{{ __('Pays') }}" onchange="this.form.submit()">
                            <option value="">{{ __('Tous les pays') }}</option>
                            @foreach($countries as $c)<option value="{{ $c }}" @selected($filters['country'] === $c)>{{ $c }}</option>@endforeach
                        </select>
                    @endif
                    @if($directors->isNotEmpty())
                        <select name="realisateur" class="ci-sel" aria-label="{{ __('Réalisateur') }}" onchange="this.form.submit()">
                            <option value="">{{ __('Tous les réalisateurs') }}</option>
                            @foreach($directors as $d)<option value="{{ $d }}" @selected($filters['director'] === $d)>{{ $d }}</option>@endforeach
                        </select>
                    @endif
                    <select name="tri" class="ci-sel" aria-label="{{ __('Trier') }}" onchange="this.form.submit()">
                        <option value="recents">{{ __('Plus récents') }}</option>
                        <option value="populaires" @selected($filters['sort'] === 'populaires')>{{ __('Plus vus') }}</option>
                    </select>
                    @if($filtering)<a href="{{ $routeFor($kind) }}#ci-list" class="ci-reset">{{ __('Effacer les filtres') }}</a>@endif
                </div>
            </form>
        </div>
    </div>

    <div class="ci-wrap">
        @if($featured->count() > 1)
            <section class="ci-sec" aria-labelledby="ci-feat-title">
                <div class="ci-sec__head">
                    <div>
                        <h2 class="ci-h2" id="ci-feat-title">{{ __('À l\'affiche') }}</h2>
                        <p class="ci-sub">{{ __('La sélection de la rédaction.') }}</p>
                    </div>
                </div>
                <div class="ci-rail">
                    @foreach($featured as $f)
                        @include('films.partials.card', ['f' => $f, 'kindIcons' => $kindIcons])
                    @endforeach
                </div>
            </section>
        @endif

        <section class="ci-sec" id="ci-list" aria-labelledby="ci-list-title" style="scroll-margin-top: 150px">
            <div class="ci-sec__head">
                <div>
                    <h2 class="ci-h2" id="ci-list-title">{{ $filtering ? __('Résultats') : ($kind ? __('Tous les :k', ['k' => mb_strtolower(__(\App\Models\Film::KINDS[$kind]['plural']))]) : __('Tous les films')) }}</h2>
                </div>
                <p class="ci-count"><b>{{ $films->total() }}</b> {{ trans_choice('film|films', $films->total()) }}</p>
            </div>
            @if($films->isEmpty())
                <div class="ci-empty">
                    <span class="ci-empty__ic"><x-icon name="clapperboard-play" /></span>
                    @if($filtering)
                        <h2>{{ __('Aucun film ne correspond') }}</h2>
                        <p>{{ __('Essayez un autre mot-clé ou retirez un filtre.') }}</p>
                        <a href="{{ $routeFor($kind) }}#ci-list" class="ci-btn is-light">{{ __('Effacer les filtres') }}</a>
                    @else
                        <h2>{{ __('La programmation arrive bientôt') }}</h2>
                        <p>{{ __('Nous sélectionnons les premiers films avec leurs réalisateurs, producteurs et distributeurs. Vous avez un film ivoirien à faire découvrir ?') }}</p>
                        <a href="{{ route('films.submit') }}" class="ci-btn"><x-icon name="upload" />{{ __('Proposer un film') }}</a>
                    @endif
                </div>
            @else
                <div class="ci-grid">
                    @foreach($films as $f)
                        @include('films.partials.card', ['f' => $f, 'kindIcons' => $kindIcons])
                    @endforeach
                </div>
                @if($films->hasPages())<div class="ci-pages">{{ $films->fragment('ci-list')->links() }}</div>@endif
            @endif
        </section>

        @if($hasFilms)
            <section class="ci-pro" aria-labelledby="ci-pro-title">
                @include('partials.ivorian-motifs', ['culture' => 'senoufo', 'band' => false])
                <div>
                    <h2 id="ci-pro-title">{{ __('Réalisateur, producteur, distributeur ?') }}</h2>
                    <p>{{ __('Faites découvrir votre film aux voyageurs et à la diaspora : fiche dédiée, bande-annonce, dates de projection et lien vers votre plateforme ou votre billetterie.') }}</p>
                    <ul>
                        <li><x-icon name="check-circle" />{{ __('Gratuit') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Validation éditoriale') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Vous gardez vos droits') }}</li>
                    </ul>
                </div>
                <a href="{{ route('films.submit') }}" class="ci-btn"><x-icon name="upload" />{{ __('Proposer un film') }}</a>
            </section>
        @endif
    </div>
</main>

@include('partials.homepage-footer')
<script>
    (function () {
        var btn = document.querySelector('[data-ci-more]');
        var box = document.getElementById('ci-filters');
        if (!btn || !box) return;
        btn.addEventListener('click', function () {
            var open = box.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    })();
</script>
</body>
</html>
