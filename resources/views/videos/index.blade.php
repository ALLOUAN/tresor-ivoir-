<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $shareVideo = $openSlug ? $videos->firstWhere('slug', $openSlug) : null;
    @endphp
    <title>{{ $shareVideo ? $shareVideo->title.' — ' : '' }}{{ __('Vidéos') }} — {{ __('Médiathèque') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', [
        'title' => $shareVideo?->title ?? __('La Côte d\'Ivoire en vidéo'),
        'description' => $shareVideo?->description ?: __('Destinations, gastronomie, culture et aventure en vidéo, à regarder puis à réserver.'),
        'image' => $shareVideo?->poster(),
    ])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        /* Médiathèque — vidéos, charte du site : crème, vert forêt, orange, Playfair. Mosaïque « bento » en tête,
           rangées par rubrique, cartes éditoriales (texte sous l'image). Aplats, ombre au survol seulement. */
        .vd { background: #F3F8F5; color: #0E3B2A; padding-bottom: 72px; }
        .vd-wrap { max-width: 1320px; margin: 0 auto; padding: 0 20px; }
        .vd-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding: 26px 0 18px; }
        .vd-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; }
        .vd-tabs a { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; font-weight: 700; color: #24503D; text-decoration: none; }
        .vd-tabs a i { font-size: 18px; color: #EB6E00; }
        .vd-tabs a:hover { background: #F3F8F5; color: #B85600; }
        .vd-tabs a.is-on { background: #EB6E00; color: #fff; }
        .vd-tabs a.is-on i { color: #fff; }
        .vd-stats { display: flex; flex-wrap: wrap; gap: 6px 20px; margin: 0; padding: 0; list-style: none; font-size: 14px; color: #24503D; }
        .vd-stats li { display: inline-flex; align-items: center; gap: 8px; }
        .vd-stats i { color: #EB6E00; font-size: 18px; }
        .vd-stats b { color: #0E3B2A; }

        /* Mosaïque bento */
        .vd-bento { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(220px, auto)); gap: 14px; }
        .vd-tile { position: relative; overflow: hidden; border-radius: 26px; background: #D8E9E0; border: 1px solid #D8E9E0; }
        .vd-intro { grid-column: span 2; display: flex; flex-direction: column; justify-content: space-between; gap: 20px; padding: 30px; background: #00874F; border-color: #00874F; color: #fff; }
        .vd-kicker { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #EB6E00; }
        .vd-kicker i { font-size: 16px; }
        .vd-title { margin: 12px 0 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 2.6vw + 16px, 56px); font-weight: 700; line-height: 1.02; letter-spacing: -.015em; color: #fff; }
        .vd-lead { margin: 14px 0 0; max-width: 520px; font-size: 16.5px; line-height: 1.6; color: rgba(255,255,255,.85); }
        .vd-cta { align-self: flex-start; display: inline-flex; align-items: center; gap: 10px; height: 50px; padding: 0 8px 0 20px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15px; font-weight: 800; cursor: pointer; transition: background .15s ease; }
        .vd-cta:hover { background: #B85600; }
        .vd-cta span { width: 36px; height: 36px; border-radius: 999px; background: #fff; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
        .vd-cta:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .vd-feature { grid-column: 3 / span 2; grid-row: span 2; cursor: pointer; min-height: 460px; }
        .vd-mini { cursor: pointer; }
        .vd-tile img, .vd-tile video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .vd-tile video[data-preview] { opacity: 0; transition: opacity .3s ease; }
        .vd-tile.is-preview video[data-preview] { opacity: 1; }
        .vd-tile__txt { position: absolute; left: 14px; right: 14px; bottom: 14px; padding: 14px 16px; border-radius: 18px; background: rgba(255,255,255,.95); }
        .vd-tile__txt small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #EB6E00; }
        .vd-tile__txt b { display: block; margin-top: 3px; font-size: 16px; line-height: 1.3; color: #0E3B2A; }
        .vd-feature .vd-tile__txt b { font-family: 'Montserrat', serif; font-size: 24px; }
        .vd-tile__txt em { display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; font-style: normal; font-size: 12.5px; color: #557466; }
        .vd-tile__txt em i { color: #EB6E00; font-size: 14px; }
        .vd-play { position: absolute; z-index: 2; top: 18px; right: 18px; width: 54px; height: 54px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; transition: transform .15s ease, background .15s ease; }
        .vd-feature .vd-play { width: 64px; height: 64px; font-size: 28px; }
        .vd-tile:hover .vd-play { transform: scale(1.06); background: #B85600; }
        .vd-tile:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .vd-badge { position: absolute; z-index: 2; top: 18px; left: 18px; display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: #EB6E00; color: #0E3B2A; font-size: 12px; font-weight: 800; }

        /* Rubriques */
        .vd-bar { position: sticky; top: 72px; z-index: 20; margin-top: 26px; background: #F3F8F5; border-bottom: 1px solid #D8E9E0; }
        .vd-chips { display: flex; gap: 8px; padding: 12px 0; overflow-x: auto; scrollbar-width: none; }
        .vd-chips::-webkit-scrollbar { display: none; }
        .vd-chip { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px 0 12px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; color: #24503D; font-size: 14px; font-weight: 600; text-decoration: none; transition: border-color .15s ease, background .15s ease; }
        .vd-chip i { font-size: 18px; color: #EB6E00; }
        .vd-chip small { font-size: 12px; color: #557466; }
        .vd-chip:hover { border-color: #EB6E00; }
        .vd-chip.is-on { background: #EB6E00; border-color: #EB6E00; color: #fff; }
        .vd-chip.is-on i, .vd-chip.is-on small { color: #fff; }

        /* Rangées */
        .vd-row { margin-top: 34px; }
        .vd-row__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .vd-row__title { display: flex; align-items: center; gap: 12px; margin: 0; font-family: 'Montserrat', serif; font-size: clamp(22px, 1vw + 16px, 30px); font-weight: 700; color: #0E3B2A; }
        .vd-row__title i { width: 42px; height: 42px; border-radius: 13px; background: #F3F8F5; color: #EB6E00; font-size: 20px; }
        .vd-row__tools { display: flex; align-items: center; gap: 8px; }
        .vd-row__all { font-size: 14px; font-weight: 700; color: #B85600; text-decoration: none; }
        .vd-row__all:hover { text-decoration: underline; text-underline-offset: 3px; }
        .vd-arrow { width: 40px; height: 40px; border: 1px solid #D8E9E0; border-radius: 999px; background: #fff; color: #0E3B2A; font-size: 18px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .vd-arrow:hover { border-color: #EB6E00; color: #EB6E00; }
        .vd-arrow:disabled { opacity: .35; cursor: default; }
        .vd-arrow[hidden] { display: none; }
        .vd-rail { display: grid; grid-auto-flow: column; grid-auto-columns: 230px; gap: 14px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding-bottom: 4px; }
        .vd-rail::-webkit-scrollbar { display: none; }
        .vd-rail > * { scroll-snap-align: start; }
        .vd-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-top: 26px; }

        /* Carte éditoriale */
        .vd-card { display: flex; flex-direction: column; gap: 10px; padding: 8px 8px 14px; border: 1px solid #D8E9E0; border-radius: 24px; background: #fff; text-align: left; color: inherit; cursor: pointer; transition: border-color .2s ease, box-shadow .25s ease; }
        .vd-card:hover { border-color: #8AA99B; box-shadow: 0 22px 40px -30px rgba(14,59,42,.5); }
        .vd-card:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .vd-card__media { position: relative; display: block; aspect-ratio: 9 / 16; border-radius: 18px; overflow: hidden; background: #D8E9E0; }
        .vd-card__media img, .vd-card__media video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .vd-card__media video[data-preview] { opacity: 0; transition: opacity .3s ease; }
        .vd-card.is-preview video[data-preview] { opacity: 1; }
        .vd-card__empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #EB6E00; font-size: 40px; }
        .vd-card__play { position: absolute; left: 50%; top: 50%; width: 56px; height: 56px; margin: -28px 0 0 -28px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; opacity: 0; transform: scale(.9); transition: opacity .2s ease, transform .2s ease; }
        .vd-card:hover .vd-card__play, .vd-card:focus-visible .vd-card__play { opacity: 1; transform: none; }
        .vd-card__dur { position: absolute; right: 8px; bottom: 8px; display: inline-flex; align-items: center; gap: 4px; height: 24px; padding: 0 8px; border-radius: 999px; background: rgba(14,59,42,.78); color: #fff; font-size: 11.5px; font-weight: 700; }
        .vd-card__book { position: absolute; left: 8px; top: 8px; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; background: #00874F; color: #fff; font-size: 11.5px; font-weight: 700; }
        .vd-card__book i, .vd-card__dur i { font-size: 13px; }
        .vd-card__body { display: flex; flex-direction: column; gap: 3px; padding: 0 6px; min-width: 0; }
        .vd-card__cat { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #EB6E00; }
        .vd-card__title { font-size: 15px; font-weight: 700; line-height: 1.3; color: #0E3B2A; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .vd-card__meta { display: flex; flex-wrap: wrap; gap: 4px 12px; margin-top: 2px; font-size: 12.5px; color: #557466; }
        .vd-card__meta span { display: inline-flex; align-items: center; gap: 4px; }
        .vd-card__meta i { font-size: 14px; color: #557466; }

        .vd-empty { margin-top: 30px; padding: 54px 24px; border-radius: 26px; background: #fff; border: 1px dashed #8AA99B; text-align: center; color: #24503D; }
        .vd-empty > i { width: 76px; height: 76px; border-radius: 22px; background: #F3F8F5; color: #EB6E00; font-size: 38px; }
        .vd-empty h2 { margin: 16px 0 6px; font-family: 'Montserrat', serif; font-size: 28px; color: #0E3B2A; }
        .vd-empty p { margin: 0 auto; max-width: 520px; }

        /* Lecteur plein écran (façon Reels) */
        .vp { position: fixed; inset: 0; z-index: 200; background: #000; }
        .vp[hidden] { display: none; }
        .vp-feed { height: 100%; overflow-y: auto; scroll-snap-type: y mandatory; scrollbar-width: none; overscroll-behavior: contain; }
        .vp-feed::-webkit-scrollbar { display: none; }
        .vp-slide { position: relative; height: 100%; scroll-snap-align: start; scroll-snap-stop: always; display: flex; align-items: center; justify-content: center; }
        .vp-media { position: relative; height: 100%; max-width: 100%; aspect-ratio: 9 / 16; background: #0E3B2A; }
        .vp-slide.is-landscape .vp-media { aspect-ratio: 16 / 9; width: min(100%, 1200px); height: auto; max-height: 100%; }
        .vp-media video, .vp-media iframe, .vp-media img { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; object-fit: contain; background: #000; }
        .vp-tap { position: absolute; inset: 0; z-index: 2; background: transparent; border: 0; cursor: pointer; }
        .vp-slide.is-embed .vp-tap { display: none; }
        .vp-bigplay { position: absolute; z-index: 3; top: 50%; left: 50%; width: 76px; height: 76px; margin: -38px 0 0 -38px; border: 0; border-radius: 999px; background: rgba(235,110,0,.92); color: #fff; font-size: 34px; display: none; align-items: center; justify-content: center; cursor: pointer; }
        .vp-slide.is-paused .vp-bigplay { display: inline-flex; }
        .vp-info { position: absolute; z-index: 4; left: 0; right: 76px; bottom: 0; padding: 70px 18px 22px; pointer-events: none; }
        .vp-info::before { content: ''; position: absolute; inset: 0; background: rgba(14,59,42,.42); }
        @media (min-width: 768px) { .vp-info { right: auto; width: min(620px, calc(100% - 120px)); left: 24px; bottom: 24px; padding: 18px 20px; border-radius: 22px; overflow: hidden; } }
        .vp-info > * { position: relative; pointer-events: auto; }
        .vp-info small { font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #EB6E00; }
        .vp-info h2 { margin: 6px 0 0; font-size: 19px; font-weight: 800; line-height: 1.3; color: #fff; }
        .vp-info p { margin: 6px 0 0; max-width: 560px; font-size: 14px; line-height: 1.5; color: #D8E9E0; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .vp-info p.is-open { -webkit-line-clamp: unset; line-clamp: unset; }
        .vp-links { display: flex; gap: 8px; margin-top: 12px; overflow-x: auto; scrollbar-width: none; }
        .vp-links::-webkit-scrollbar { display: none; }
        .vp-link { flex: none; display: flex; align-items: center; gap: 10px; max-width: 380px; padding: 6px 12px 6px 6px; border-radius: 16px; background: rgba(255,255,255,.96); color: #0E3B2A; text-decoration: none; }
        .vp-link img, .vp-link span.ph { flex: none; width: 46px; height: 46px; border-radius: 11px; object-fit: cover; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
        .vp-link b { display: block; font-size: 13.5px; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; }
        .vp-link small { display: block; font-size: 11.5px; font-weight: 600; letter-spacing: 0; text-transform: none; color: #557466; }
        .vp-link em { flex: none; margin-left: 4px; padding: 7px 12px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 12px; font-weight: 800; font-style: normal; white-space: nowrap; }
        .vp-rail { position: absolute; z-index: 5; right: 12px; bottom: 26px; display: flex; flex-direction: column; gap: 14px; align-items: center; }
        .vp-act { display: flex; flex-direction: column; align-items: center; gap: 4px; border: 0; background: none; color: #fff; font-size: 12px; font-weight: 700; cursor: pointer; }
        .vp-act span.b { width: 50px; height: 50px; border-radius: 999px; background: rgba(255,255,255,.14); display: inline-flex; align-items: center; justify-content: center; font-size: 24px; transition: background .15s ease; }
        .vp-act:hover span.b { background: rgba(255,255,255,.24); }
        .vp-act.is-on span.b { background: #EB6E00; }
        .vp-act:focus-visible span.b { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .vp-close { position: fixed; z-index: 210; top: 16px; right: 16px; width: 46px; height: 46px; border: 0; border-radius: 999px; background: rgba(255,255,255,.16); color: #fff; font-size: 22px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .vp-close:hover { background: #EB6E00; }
        .vp-nav { position: fixed; z-index: 210; left: 24px; top: 50%; display: flex; flex-direction: column; gap: 10px; transform: translateY(-50%); }
        .vp-nav button { width: 48px; height: 48px; border: 0; border-radius: 999px; background: rgba(255,255,255,.16); color: #fff; font-size: 22px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .vp-nav button:hover { background: #EB6E00; }
        .vp-toast { position: fixed; z-index: 220; left: 50%; bottom: 30px; transform: translateX(-50%); padding: 10px 16px; border-radius: 999px; background: #fff; color: #0E3B2A; font-size: 14px; font-weight: 700; }
        .vp-toast[hidden] { display: none; }

        @media (max-width: 1023px) {
            .vp-nav { display: none; }
            .vd-bento { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: auto; }
            .vd-intro { grid-column: 1 / -1; }
            .vd-feature { grid-column: 1 / -1; grid-row: auto; min-height: 0; aspect-ratio: 16 / 10; }
            .vd-mini { aspect-ratio: 4 / 5; }
            .vd-arrow { display: none; }
        }
        @media (max-width: 767px) {
            .vd-wrap { padding: 0 16px; }
            .vd { padding-bottom: 110px; }
            .vd-stats { display: none; }
            .vd-intro { padding: 22px; }
            .vd-feature { aspect-ratio: 4 / 5; }
            .vd-tile__txt { left: 10px; right: 10px; bottom: 10px; padding: 10px 12px; }
            .vd-feature .vd-tile__txt b { font-size: 19px; }
            .vd-mini .vd-tile__txt b { font-size: 13.5px; }
            .vd-mini .vd-play { width: 42px; height: 42px; font-size: 19px; top: 10px; right: 10px; }
            .vd-bar { top: 60px; }
            .vd-rail { grid-auto-columns: 46%; gap: 10px; }
            .vd-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .vd-card { border-radius: 18px; padding: 6px 6px 12px; }
            .vd-card__media { border-radius: 14px; }
            .vd-card__title { font-size: 13.5px; }
            .vd-card__play { opacity: 1; transform: none; width: 44px; height: 44px; margin: -22px 0 0 -22px; font-size: 19px; }
            .vp-media { width: 100%; height: 100%; aspect-ratio: auto; }
            .vp-slide:not(.is-landscape) .vp-media video, .vp-slide:not(.is-landscape) .vp-media img { object-fit: cover; }
            .vp-info { right: 68px; padding-bottom: 28px; }
        }
        @media (prefers-reduced-motion: reduce) { .vd-card, .vd-card video, .vd-play, .vd-card__play, .vd-tile video { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.public-top-nav')

@php
    $totalViews = $videos->sum('views_count');
    $players = $videos->map->toPlayer()->values();
@endphp

<main class="vd">
    @php
        $minis = $videos->reject(fn ($v) => $featured && $v->id === $featured->id)->take(2)->values();
        $rows = $category ? collect() : $videos->groupBy('category');
        $card = fn ($v, $i) => view('videos._card', ['v' => $v, 'i' => $i])->render();
    @endphp
    <div class="vd-wrap">
        <div class="vd-top">
            <nav class="vd-tabs" aria-label="{{ __('Médiathèque') }}">
                <a href="{{ route('gallery.public') }}"><x-icon name="camera" />{{ __('Galerie photo') }}</a>
                <a href="{{ route('videos.index') }}" class="is-on" aria-current="page"><x-icon name="clapperboard-play" />{{ __('Vidéos') }}</a>
            </nav>
            <ul class="vd-stats">
                <li><x-icon name="video-frame-play-vertical" /><b>{{ $videos->count() }}</b> {{ trans_choice('vidéo|vidéos', $videos->count()) }}</li>
                @if($totalViews > 0)<li><x-icon name="eye" /><b>{{ number_format($totalViews, 0, ',', ' ') }}</b> {{ __('vues') }}</li>@endif
                <li><x-icon name="bag" />{{ __('Adresses à réserver depuis chaque vidéo') }}</li>
            </ul>
        </div>

        {{-- Mosaïque : présentation, vidéo à la une, deux vidéos récentes --}}
        <section class="vd-bento" aria-label="{{ __('À la une') }}">
            <div class="vd-tile vd-intro">
                <div>
                    <p class="vd-kicker"><x-icon name="clapperboard-play" />{{ __('Médiathèque') }}</p>
                    <h1 class="vd-title">{{ __('La Côte d\'Ivoire en vidéo') }}</h1>
                    <p class="vd-lead">{{ __('Destinations, saveurs, culture et aventure filmées sur place. Regardez, puis réservez l\'adresse en un geste.') }}</p>
                </div>
                @if($featured)
                    <button type="button" class="vd-cta" data-vd-open="{{ $featured->slug }}">{{ __('Lancer la sélection') }}<span><x-icon name="play" /></span></button>
                @endif
            </div>
            @if($featured)
                <div class="vd-tile vd-feature" data-vd-open="{{ $featured->slug }}" role="button" tabindex="0" aria-label="{{ __('Regarder : :t', ['t' => $featured->title]) }}">
                    @if($featured->poster())<img src="{{ $featured->poster() }}" alt="" decoding="async">@else<span class="vd-card__empty"><x-icon name="clapperboard-play" /></span>@endif
                    @if($featured->source === 'upload')<video data-preview data-src="{{ $featured->video_url }}" muted loop playsinline preload="none"></video>@endif
                    <span class="vd-badge"><x-icon name="star" />{{ __('À la une') }}</span>
                    <span class="vd-play" aria-hidden="true"><x-icon name="play" /></span>
                    <div class="vd-tile__txt">
                        <small>{{ $featured->categoryLabel() }}@if($featured->city) · {{ $featured->city->name }}@endif</small>
                        <b>{{ $featured->title }}</b>
                        @if($featured->durationLabel())<em><x-icon name="clock-circle" />{{ $featured->durationLabel() }}</em>@endif
                    </div>
                </div>
            @endif
            @foreach($minis as $m)
                <div class="vd-tile vd-mini" data-vd-open="{{ $m->slug }}" role="button" tabindex="0" aria-label="{{ __('Regarder : :t', ['t' => $m->title]) }}">
                    @if($m->poster())<img src="{{ $m->poster() }}" alt="" loading="lazy" decoding="async">@else<span class="vd-card__empty"><x-icon name="clapperboard-play" /></span>@endif
                    @if($m->source === 'upload')<video data-preview data-src="{{ $m->video_url }}" muted loop playsinline preload="none"></video>@endif
                    <span class="vd-play" aria-hidden="true"><x-icon name="play" /></span>
                    <div class="vd-tile__txt"><small>{{ $m->categoryLabel() }}</small><b>{{ \Illuminate\Support\Str::limit($m->title, 48) }}</b></div>
                </div>
            @endforeach
        </section>
    </div>

    <div class="vd-bar">
        <div class="vd-wrap">
            <nav class="vd-chips" aria-label="{{ __('Rubriques') }}">
                <a href="{{ route('videos.index') }}" class="vd-chip {{ $category ? '' : 'is-on' }}"><x-icon name="widget" />{{ __('Tout') }} <small>{{ $counts->sum() }}</small></a>
                @foreach(\App\Models\Video::CATEGORIES as $key => $def)
                    @continue(! ($counts[$key] ?? 0))
                    <a href="{{ route('videos.index', ['rubrique' => $key]) }}" class="vd-chip {{ $category === $key ? 'is-on' : '' }}"><x-icon :name="$def['icon']" />{{ __($def['label']) }} <small>{{ $counts[$key] }}</small></a>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="vd-wrap">
        @if($videos->isEmpty())
            <div class="vd-empty">
                <x-icon name="clapperboard-play" />
                <h2>{{ __('Les premières vidéos arrivent') }}</h2>
                <p>{{ __('Revenez bientôt : nos équipes et les établissements partenaires préparent des vidéos tournées sur place.') }}</p>
            </div>
        @elseif($category)
            {{-- Une rubrique : grille complète --}}
            <div class="vd-grid">
                @foreach($videos as $i => $v){!! $card($v, $i) !!}@endforeach
            </div>
        @else
            {{-- Toutes les rubriques : une rangée chacune, les premières vignettes portent la rangée --}}
            @foreach(\App\Models\Video::CATEGORIES as $key => $def)
                @php $items = $rows->get($key); @endphp
                @continue(! $items)
                <section class="vd-row" aria-labelledby="row-{{ $key }}">
                    <div class="vd-row__head">
                        <h2 class="vd-row__title" id="row-{{ $key }}"><x-icon :name="$def['icon']" />{{ __($def['label']) }}</h2>
                        <div class="vd-row__tools">
                            @if($items->count() > 4)<a href="{{ route('videos.index', ['rubrique' => $key]) }}" class="vd-row__all">{{ __('Tout voir') }}</a>@endif
                            <button type="button" class="vd-arrow" data-rail-step="-1" aria-label="{{ __('Précédent') }}"><x-icon name="alt-arrow-left" /></button>
                            <button type="button" class="vd-arrow" data-rail-step="1" aria-label="{{ __('Suivant') }}"><x-icon name="alt-arrow-right" /></button>
                        </div>
                    </div>
                    <div class="vd-rail" data-rail>
                        @foreach($items->take(12) as $i => $v){!! $card($v, $i) !!}@endforeach
                    </div>
                </section>
            @endforeach
        @endif
    </div>
</main>

{{-- Lecteur plein écran --}}
<div class="vp" id="vp" role="dialog" aria-modal="true" aria-label="{{ __('Lecteur vidéo') }}" hidden>
    <button type="button" class="vp-close" data-vp-close aria-label="{{ __('Fermer') }}"><x-icon name="close-circle" /></button>
    <div class="vp-nav"><button type="button" data-vp-step="-1" aria-label="{{ __('Vidéo précédente') }}"><x-icon name="alt-arrow-up" /></button><button type="button" data-vp-step="1" aria-label="{{ __('Vidéo suivante') }}"><x-icon name="alt-arrow-down" /></button></div>
    <div class="vp-feed" id="vp-feed"></div>
    <p class="vp-toast" id="vp-toast" role="status" hidden></p>
</div>

<template id="vp-ic">
    <span data-ic="heart"><x-icon name="heart" /></span>
    <span data-ic="share"><x-icon name="share" /></span>
    <span data-ic="on"><x-icon name="volume-loud" /></span>
    <span data-ic="off"><x-icon name="volume-cross" /></span>
    <span data-ic="play"><x-icon name="play" /></span>
    <span data-ic="eye"><x-icon name="eye" /></span>
    <span data-ic="bag"><x-icon name="bag" /></span>
</template>

@include('partials.homepage-footer')

<script>
(function () {
    const VIDEOS = @json($players);
    // Vidéos déjà aimées par ce visiteur (par uuid).
    const LIKED = new Set(@json($videos->filter(fn ($v) => in_array($v->id, $liked, true))->pluck('uuid')->values()));
    const OPEN = @json($openSlug);
    const BASE = @json(route('videos.index'));
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const T = {{ \Illuminate\Support\Js::from([
        'book' => __('Voir et réserver'), 'like' => __('J\'aime'), 'share' => __('Partager'), 'sound' => __('Son'),
        'copied' => __('Lien copié'), 'more' => __('Plus'), 'views' => __('vues'),
    ]) }};
    const IC = {};
    document.getElementById('vp-ic').content.querySelectorAll('[data-ic]').forEach(s => IC[s.dataset.ic] = s.innerHTML);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmt = n => Number(n || 0).toLocaleString('fr-FR');

    // Économie de données : pas de lecture automatique sur connexion lente ou « économiseur de données ».
    const conn = navigator.connection || {};
    const saveData = !!conn.saveData || ['slow-2g', '2g', '3g'].includes(conn.effectiveType);
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let muted = true;

    const post = (slug, action) => fetch(BASE + '/' + encodeURIComponent(slug) + '/' + action, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' } }).then(r => r.ok ? r.json() : null).catch(() => null);

    // ── Aperçu au survol des vignettes (fichiers envoyés uniquement) ──
    if (!saveData && !reduce && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.vd-card, .vd-tile[data-vd-open]').forEach(card => {
            const v = card.querySelector('video[data-preview]');
            if (!v) return;
            // Aperçu au survol : seulement avec une souris et hors mode « économie de données ».
            if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches || navigator.connection?.saveData) return;
            card.addEventListener('mouseenter', () => { if (!v.src && v.dataset.src) v.src = v.dataset.src; v.play().then(() => card.classList.add('is-preview')).catch(() => {}); });
            card.addEventListener('mouseleave', () => { v.pause(); card.classList.remove('is-preview'); });
        });
    }

    // ── Rangées : flèches (ordinateur), désactivées aux extrémités ──
    document.querySelectorAll('.vd-row').forEach(row => {
        const rail = row.querySelector('[data-rail]'), btns = row.querySelectorAll('[data-rail-step]');
        const sync = () => { const fits = rail.scrollWidth <= rail.clientWidth + 8; btns.forEach(x => x.hidden = fits); btns[0].disabled = rail.scrollLeft < 8; btns[1].disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 8; };
        btns.forEach(b => b.addEventListener('click', () => rail.scrollBy({ left: Number(b.dataset.railStep) * rail.clientWidth * .85, behavior: reduce ? 'auto' : 'smooth' })));
        rail.addEventListener('scroll', sync, { passive: true }); window.addEventListener('resize', sync); sync();
    });

    // ── Lecteur plein écran ──
    const vp = document.getElementById('vp'), feed = document.getElementById('vp-feed'), toast = document.getElementById('vp-toast');
    let built = false, current = -1, viewTimer = null, lastFocus = null;

    function slideHtml(v, i) {
        const links = v.links.map(l => `<a class="vp-link" href="${esc(l.url)}" data-vp-book="${esc(v.slug)}">${l.image ? `<img src="${esc(l.image)}" alt="" loading="lazy">` : `<span class="ph">${IC.bag}</span>`}<span><b>${esc(l.title)}</b><small>${esc(l.type)}${l.price ? ' · ' + fmt(l.price) + ' FCFA' : ''}</small></span><em>${T.book}</em></a>`).join('');
        const liked = LIKED.has(v.uuid);
        return `<section class="vp-slide${v.portrait ? '' : ' is-landscape'}${v.embed ? ' is-embed' : ''}" data-i="${i}" aria-label="${esc(v.title)}">
            <div class="vp-media" data-media>${v.poster ? `<img src="${esc(v.poster)}" alt="" loading="lazy">` : ''}</div>
            <button type="button" class="vp-tap" data-vp-tap aria-label="${esc(v.title)}"></button>
            <button type="button" class="vp-bigplay" data-vp-tap aria-hidden="true" tabindex="-1">${IC.play}</button>
            <div class="vp-info">
                <small>${esc(v.category)}${v.city ? ' · ' + esc(v.city) : ''}</small>
                <h2>${esc(v.title)}</h2>
                ${v.description ? `<p data-vp-desc>${esc(v.description)}</p>` : ''}
                ${links ? `<div class="vp-links">${links}</div>` : ''}
            </div>
            <div class="vp-rail">
                <button type="button" class="vp-act${liked ? ' is-on' : ''}" data-vp-like="${esc(v.slug)}" data-uuid="${esc(v.uuid)}" aria-pressed="${liked}" aria-label="${T.like}"><span class="b">${IC.heart}</span><span data-count>${fmt(v.likes)}</span></button>
                <button type="button" class="vp-act" data-vp-share="${esc(v.slug)}" aria-label="${T.share}"><span class="b">${IC.share}</span>${T.share}</button>
                ${v.embed ? '' : `<button type="button" class="vp-act" data-vp-sound aria-label="${T.sound}"><span class="b">${muted ? IC.off : IC.on}</span>${T.sound}</button>`}
                <span class="vp-act" aria-hidden="true"><span class="b">${IC.eye}</span>${fmt(v.views)}</span>
            </div>
        </section>`;
    }

    function build() {
        if (built) return;
        feed.innerHTML = VIDEOS.map(slideHtml).join('');
        built = true;
        const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting && e.intersectionRatio > .6) activate(+e.target.dataset.i); }), { root: feed, threshold: [.6] });
        feed.querySelectorAll('.vp-slide').forEach(s => io.observe(s));
    }

    function mount(i) {
        const v = VIDEOS[i], slide = feed.children[i];
        if (!slide || slide.dataset.mounted) return;
        const box = slide.querySelector('[data-media]');
        if (v.embed) {
            const f = document.createElement('iframe');
            f.src = saveData ? v.embed.replace('autoplay=1', 'autoplay=0') : v.embed;
            f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            f.allowFullscreen = true; f.title = v.title; f.loading = 'lazy';
            box.appendChild(f);
        } else if (v.src) {
            const el = document.createElement('video');
            el.src = v.src; el.muted = muted; el.loop = true; el.playsInline = true; el.preload = 'metadata';
            if (v.poster) el.poster = v.poster;
            box.appendChild(el);
        }
        slide.dataset.mounted = '1';
    }
    function unmount(i) {
        const slide = feed.children[i];
        if (!slide || !slide.dataset.mounted) return;
        slide.querySelectorAll('iframe, video').forEach(el => { if (el.tagName === 'VIDEO') el.pause(); el.remove(); });
        delete slide.dataset.mounted;
    }
    function activate(i) {
        if (i === current) return;
        current = i;
        // Garde en mémoire la vidéo active et ses voisines seulement (économie de données et de mémoire).
        [...feed.children].forEach((s, j) => { if (Math.abs(j - i) > 1) unmount(j); });
        mount(i); if (i + 1 < VIDEOS.length && !saveData) mount(i + 1);
        [...feed.children].forEach((s, j) => {
            const vid = s.querySelector('video');
            if (!vid) return;
            if (j === i && !saveData) { vid.muted = muted; vid.play().then(() => s.classList.remove('is-paused')).catch(() => s.classList.add('is-paused')); }
            else { vid.pause(); if (j === i) s.classList.add('is-paused'); }
        });
        const v = VIDEOS[i];
        history.replaceState(null, '', v.url);
        clearTimeout(viewTimer);
        viewTimer = setTimeout(() => post(v.slug, 'vue'), 2000);
    }

    function open(slug) {
        build();
        lastFocus = document.activeElement;
        vp.hidden = false; document.body.style.overflow = 'hidden';
        const i = Math.max(0, VIDEOS.findIndex(v => v.slug === slug));
        current = -1;
        feed.scrollTo({ top: i * feed.clientHeight, behavior: 'instant' });
        activate(i);
        vp.querySelector('.vp-close').focus();
    }
    function close() {
        [...feed.children].forEach((s, j) => unmount(j));
        current = -1; clearTimeout(viewTimer);
        vp.hidden = true; document.body.style.overflow = '';
        history.replaceState(null, '', BASE + location.search);
        if (lastFocus) lastFocus.focus();
    }
    function step(d) {
        const i = Math.min(VIDEOS.length - 1, Math.max(0, current + d));
        feed.scrollTo({ top: i * feed.clientHeight, behavior: reduce ? 'instant' : 'smooth' });
    }
    function say(msg) { toast.textContent = msg; toast.hidden = false; clearTimeout(say.t); say.t = setTimeout(() => toast.hidden = true, 1800); }

    document.querySelectorAll('[data-vd-open]').forEach(el => {
        el.addEventListener('click', () => open(el.dataset.vdOpen));
        el.addEventListener('keydown', e => { if ((e.key === 'Enter' || e.key === ' ') && el.tagName !== 'BUTTON') { e.preventDefault(); open(el.dataset.vdOpen); } });
    });

    vp.addEventListener('click', async e => {
        if (e.target.closest('[data-vp-close]')) return close();
        const st = e.target.closest('[data-vp-step]'); if (st) return step(+st.dataset.vpStep);
        const tap = e.target.closest('[data-vp-tap]');
        if (tap) {
            const s = tap.closest('.vp-slide'), vid = s.querySelector('video');
            if (!s.dataset.mounted) mount(+s.dataset.i);
            const v2 = s.querySelector('video');
            if (v2) { if (v2.paused) { v2.muted = muted; v2.play().then(() => s.classList.remove('is-paused')).catch(() => {}); } else { v2.pause(); s.classList.add('is-paused'); } }
            return;
        }
        const desc = e.target.closest('[data-vp-desc]'); if (desc) return desc.classList.toggle('is-open');
        const snd = e.target.closest('[data-vp-sound]');
        if (snd) {
            muted = !muted;
            feed.querySelectorAll('video').forEach(v => v.muted = muted);
            feed.querySelectorAll('[data-vp-sound] .b').forEach(b => b.innerHTML = muted ? IC.off : IC.on);
            return;
        }
        const like = e.target.closest('[data-vp-like]');
        if (like) {
            const r = await post(like.dataset.vpLike, 'jaime');
            if (r) {
                like.classList.toggle('is-on', r.liked); like.setAttribute('aria-pressed', r.liked);
                like.querySelector('[data-count]').textContent = fmt(r.likes);
                r.liked ? LIKED.add(like.dataset.uuid) : LIKED.delete(like.dataset.uuid);
            }
            return;
        }
        const sh = e.target.closest('[data-vp-share]');
        if (sh) {
            const v = VIDEOS.find(x => x.slug === sh.dataset.vpShare);
            post(v.slug, 'partage');
            if (navigator.share) { navigator.share({ title: v.title, url: v.url }).catch(() => {}); }
            else { navigator.clipboard?.writeText(v.url).then(() => say(T.copied)).catch(() => say(v.url)); }
            return;
        }
        const book = e.target.closest('[data-vp-book]');
        if (book) { navigator.sendBeacon?.(BASE + '/' + encodeURIComponent(book.dataset.vpBook) + '/clic', new Blob([new URLSearchParams({ _token: CSRF })], { type: 'application/x-www-form-urlencoded' })); }
    });
    document.addEventListener('keydown', e => {
        if (vp.hidden) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowDown' || e.key === 'PageDown') { e.preventDefault(); step(1); }
        if (e.key === 'ArrowUp' || e.key === 'PageUp') { e.preventDefault(); step(-1); }
        if (e.key === 'm') feed.querySelector('[data-vp-sound]')?.click();
    });

    if (OPEN) open(OPEN);
})();
</script>
</body>
</html>
