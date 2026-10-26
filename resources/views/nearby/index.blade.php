<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Autour de moi') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => __('Autour de moi'), 'description' => __('Ce qui est ouvert près de vous en ce moment : restaurants, hôtels, sites, loisirs et transports vérifiés.')])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        /* « Autour de moi » : liste + carte côte à côte, façon application de cartes. Aplats, ombre au survol. */
        .nb { max-width: 1440px; margin: 0 auto; padding: 22px 20px 40px; }
        .nb-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px 24px; margin-bottom: 16px; }
        .nb-live { display: inline-flex; align-items: center; gap: 8px; margin: 0 0 8px; padding: 5px 12px 5px 8px; border-radius: 999px; background: #F3F8F5; color: #00874F; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .nb-live__dot { width: 8px; height: 8px; border-radius: 999px; background: #00874F; animation: nb-pulse 1.8s ease-out infinite; }
        @keyframes nb-pulse { 0% { box-shadow: 0 0 0 0 rgba(0,135,79,.5); } 100% { box-shadow: 0 0 0 9px rgba(0,135,79,0); } }
        .nb-title { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(30px, 2.4vw + 16px, 46px); font-weight: 700; line-height: 1.05; letter-spacing: -.01em; color: #0E3B2A; }
        .nb-sub { margin: 8px 0 0; font-size: 15px; color: #24503D; }

        /* Barre de recherche unifiée */
        .nb-bar { display: flex; align-items: center; gap: 6px; padding: 6px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; }
        .nb-locate { display: inline-flex; align-items: center; gap: 8px; height: 46px; padding: 0 18px; border: 0; border-radius: 999px; background: #0E3B2A; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: background .15s ease; }
        .nb-locate:hover { background: #EB6E00; }
        .nb-locate i { font-size: 18px; }
        .nb-city { position: relative; display: flex; align-items: center; gap: 8px; height: 46px; padding: 0 6px 0 14px; color: #557466; }
        .nb-city i { font-size: 17px; color: #EB6E00; }
        .nb-city select { height: 100%; min-width: 0; max-width: 220px; border: 0; background: transparent; font-size: 14px; font-weight: 600; color: #0E3B2A; cursor: pointer; }
        .nb-city select:focus { outline: 0; }
        .nb-bar:focus-within { border-color: #EB6E00; }

        /* Filtres */
        .nb-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; margin-bottom: 16px; }
        .nb-chips { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; padding: 2px; }
        .nb-chips::-webkit-scrollbar { display: none; }
        .nb-chip { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 15px 0 11px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; color: #24503D; font-size: 14px; font-weight: 600; cursor: pointer; transition: border-color .15s ease, background .15s ease, color .15s ease; }
        .nb-chip i { font-size: 18px; color: #EB6E00; }
        .nb-chip:hover { border-color: #EB6E00; }
        .nb-chip[aria-pressed="true"] { background: #0E3B2A; border-color: #0E3B2A; color: #fff; }
        .nb-chip[aria-pressed="true"] i { color: #EB6E00; }
        .nb-chip:focus-visible, .nb-seg button:focus-visible, .nb-locate:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .nb-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }
        .nb-seg { display: inline-flex; padding: 4px; border-radius: 999px; background: #D8E9E0; }
        .nb-seg button { height: 34px; padding: 0 12px; border: 0; border-radius: 999px; background: transparent; font-size: 13px; font-weight: 700; color: #24503D; cursor: pointer; }
        .nb-seg button[aria-pressed="true"] { background: #fff; color: #0E3B2A; }
        .nb-open { display: inline-flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: #0E3B2A; cursor: pointer; white-space: nowrap; }
        .nb-switch { width: 44px; height: 26px; border-radius: 999px; background: #8AA99B; position: relative; transition: background .2s; }
        .nb-switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform .2s; }
        input:checked + .nb-switch { background: #00874F; }
        input:checked + .nb-switch::after { transform: translateX(18px); }
        input:focus-visible + .nb-switch { outline: 3px solid #EB6E00; outline-offset: 2px; }

        /* Liste + carte */
        .nb-split { display: grid; grid-template-columns: minmax(0, 440px) minmax(0, 1fr); gap: 16px; align-items: start; }
        .nb-pane { min-width: 0; }
        #nb-map { height: calc(100vh - 200px); min-height: 520px; position: sticky; top: 92px; border-radius: 24px; overflow: hidden; background: #D8E9E0; border: 1px solid #D8E9E0; }
        .nb-status { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 0 4px 10px; font-size: 14px; color: #24503D; }
        .nb-status b { color: #0E3B2A; }

        /* État d'accueil */
        .nb-welcome { padding: 26px 24px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; }
        .nb-welcome__icon { width: 64px; height: 64px; border-radius: 20px; background: #F3F8F5; color: #00874F; font-size: 32px; }
        .nb-welcome h2 { margin: 16px 0 6px; font-family: 'Montserrat', serif; font-size: 24px; font-weight: 700; color: #0E3B2A; }
        .nb-welcome p { margin: 0; font-size: 14.5px; line-height: 1.6; color: #24503D; }
        .nb-quick { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .nb-quick button { display: inline-flex; align-items: center; gap: 7px; height: 38px; padding: 0 14px; border-radius: 999px; background: #F3F8F5; border: 0; font-size: 13.5px; font-weight: 600; color: #0E3B2A; cursor: pointer; }
        .nb-quick button:hover { background: #F3F8F5; color: #B85600; }
        .nb-quick i { color: #EB6E00; font-size: 15px; }
        .nb-msg { display: flex; gap: 12px; align-items: flex-start; padding: 16px 18px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; font-size: 14px; line-height: 1.55; color: #24503D; }
        .nb-msg i { flex: none; font-size: 22px; color: #EB6E00; }

        /* Lieux */
        #nb-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .nb-item { display: grid; grid-template-columns: 96px minmax(0, 1fr); gap: 14px; padding: 10px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; cursor: pointer; transition: border-color .15s ease, box-shadow .2s ease; }
        .nb-item:hover { border-color: #8AA99B; box-shadow: 0 14px 30px -24px rgba(14,59,42,.45); }
        .nb-item.is-active { border-color: #EB6E00; box-shadow: 0 0 0 3px rgba(235,110,0,.18); }
        .nb-item__img { position: relative; width: 96px; height: 96px; border-radius: 14px; overflow: hidden; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        .nb-item__img img { width: 100%; height: 100%; object-fit: cover; }
        .nb-item__n { position: absolute; top: 6px; left: 6px; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: #0E3B2A; color: #fff; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
        .nb-item__body { min-width: 0; display: flex; flex-direction: column; gap: 4px; padding-right: 4px; }
        .nb-item__type { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; font-size: 12px; color: #557466; }
        .nb-item__type .is-ok { display: inline-flex; align-items: center; gap: 4px; color: #00874F; font-weight: 700; }
        .nb-item__type .is-ok i { font-size: 13px; }
        .nb-item__rate { display: inline-flex; align-items: center; gap: 3px; font-weight: 700; color: #0E3B2A; }
        .nb-item__rate i { color: #EB6E00; font-size: 13px; }
        .nb-item__title { margin: 0; font-size: 15.5px; font-weight: 700; line-height: 1.3; color: #0E3B2A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .nb-open-pill { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; padding: 3px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .nb-open-pill.is-open { background: #F3F8F5; color: #00874F; }
        .nb-open-pill.is-open::before { content: ''; width: 7px; height: 7px; border-radius: 999px; background: #00874F; }
        .nb-open-pill.is-closed { background: #fbe9e5; color: #a3341f; }
        .nb-times { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 2px; }
        .nb-times span { display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 9px; border-radius: 999px; background: #F3F8F5; font-size: 12px; font-weight: 600; color: #24503D; }
        .nb-times i { font-size: 14px; color: #EB6E00; }
        .nb-actions { display: flex; gap: 6px; margin-top: 6px; }
        .nb-actions a { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 13px; border-radius: 999px; font-size: 12.5px; font-weight: 700; text-decoration: none; }
        .nb-actions .is-main { background: #EB6E00; color: #fff; }
        .nb-actions .is-main:hover { background: #B85600; }
        .nb-actions .is-line { background: #fff; color: #0E3B2A; border: 1.5px solid #D8E9E0; }
        .nb-actions .is-line:hover { border-color: #0E3B2A; }
        .nb-actions a:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .nb-skel { height: 118px; border-radius: 20px; background: #D8E9E0; animation: nb-skel 1.1s ease-in-out infinite alternate; }
        @keyframes nb-skel { to { background: #F3F8F5; } }

        /* Marqueurs */
        .nb-pin { display: inline-flex; align-items: center; gap: 4px; background: #fff; border: 2px solid #0E3B2A; color: #0E3B2A; border-radius: 999px; padding: 4px 9px; font: 800 11.5px/1.2 Montserrat, sans-serif; white-space: nowrap; transition: transform .15s ease; }
        .nb-pin.is-open { border-color: #00874F; color: #00874F; }
        .nb-pin.is-active { background: #EB6E00; border-color: #EB6E00; color: #fff; transform: scale(1.15); }
        .nb-me { width: 18px; height: 18px; border-radius: 50%; background: #1a73e8; border: 3px solid #fff; box-shadow: 0 0 0 7px rgba(26,115,232,.22); }

        /* Bascule liste / carte (mobile) */
        .nb-toggle { display: none; }
        @media (max-width: 1023px) {
            .nb-split { grid-template-columns: 1fr; }
            #nb-map { position: relative; top: 0; height: 62vh; min-height: 360px; }
            .nb-split:not(.show-map) .nb-mapwrap { display: none; }
            .nb-split.show-map .nb-pane { display: none; }
            .nb-toggle { position: fixed; left: 50%; bottom: 92px; z-index: 40; transform: translateX(-50%); display: inline-flex; align-items: center; gap: 8px; height: 48px; padding: 0 20px; border: 0; border-radius: 999px; background: #0E3B2A; color: #fff; font-size: 14.5px; font-weight: 700; cursor: pointer; }
            .nb-toggle i { font-size: 18px; color: #EB6E00; }
        }
        @media (min-width: 768px) and (max-width: 1023px) { .nb-toggle { bottom: 24px; } }
        @media (max-width: 767px) {
            .nb { padding: 16px 16px 120px; }
            .nb-bar { width: 100%; }
            .nb-city { flex: 1; min-width: 0; }
            .nb-city select { max-width: none; width: 100%; }
            .nb-locate span { display: none; }
            .nb-locate { padding: 0 14px; }
            .nb-right { margin-left: 0; width: 100%; flex-wrap: wrap; justify-content: space-between; gap: 10px; }
            .nb-seg { flex: 1 1 100%; justify-content: space-between; }
            .nb-seg button { flex: 1; padding: 0 4px; white-space: nowrap; }
            .nb-item { grid-template-columns: 84px minmax(0, 1fr); }
            .nb-item__img { width: 84px; height: 84px; }
        }
        @media (prefers-reduced-motion: reduce) { .nb-live__dot, .nb-skel { animation: none; } }
    
        /* Boutons aux couleurs de la marque (orange). */
        .nb-locate { background: #EB6E00; } .nb-locate:hover { background: #B85600; }
        .nb-chip[aria-pressed="true"] { background: #EB6E00; border-color: #EB6E00; color: #fff; }
        .nb-chip[aria-pressed="true"] i { color: #fff; }
        .nb-seg button[aria-pressed="true"] { background: #EB6E00; color: #fff; }
        .nb-item__n { background: #EB6E00; }
        .nb-toggle { background: #EB6E00; } .nb-toggle i { color: #fff; } .nb-toggle:hover { background: #B85600; }
        .nb-actions .is-line { border-color: #EB6E00; color: #B85600; } .nb-actions .is-line:hover { background: #F3F8F5; border-color: #B85600; }
        .nb-actions .is-line i { color: #EB6E00; }
</style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    @php
        $filters = ['all' => ['widget', 'Tout'], 'eat' => ['chef-hat', 'Manger'], 'sleep' => ['bed', 'Dormir'], 'visit' => ['masks', 'Visiter'], 'leisure' => ['ferris-wheel', 'Se divertir'], 'move' => ['bus', 'Se déplacer']];
        $quickCities = collect($cities)->take(6);
    @endphp

    <main class="nb">
        <header class="nb-head">
            <div>
                <p class="nb-live"><span class="nb-live__dot" aria-hidden="true"></span>{{ __('En direct') }}</p>
                <h1 class="nb-title">{{ __('Autour de moi, maintenant') }}</h1>
                <p class="nb-sub" id="nb-sub">{{ __('Ce qui est ouvert près de vous, trié par temps d\'accès.') }}</p>
            </div>
            <div class="nb-bar">
                <button type="button" id="nb-locate" class="nb-locate"><x-icon name="gps" /><span>{{ __('Me localiser') }}</span></button>
                <label class="nb-city">
                    <x-icon name="city" />
                    <select id="nb-city" aria-label="{{ __('Ou choisissez une ville') }}">
                        <option value="">{{ __('Ou choisissez une ville') }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c['lat'] }},{{ $c['lng'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </header>

        <div class="nb-tools">
            <div class="nb-chips" role="group" aria-label="{{ __('Filtrer') }}">
                @foreach($filters as $key => [$icon, $label])
                    <button type="button" class="nb-chip" data-filter="{{ $key }}" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}"><x-icon :name="$icon" />{{ __($label) }}</button>
                @endforeach
            </div>
            <div class="nb-right">
                <div class="nb-seg" role="group" aria-label="{{ __('Rayon') }}" data-radius>
                    @foreach([2, 5, 10, 25, 50] as $r)
                        <button type="button" data-r="{{ $r }}" aria-pressed="{{ $r === 10 ? 'true' : 'false' }}">{{ $r }} km</button>
                    @endforeach
                </div>
                <label class="nb-open">
                    <input type="checkbox" id="nb-open" class="sr-only" checked>
                    <span class="nb-switch" aria-hidden="true"></span>{{ __('Ouvert maintenant') }}
                </label>
            </div>
        </div>

        <div class="nb-split" id="nb-split">
            <div class="nb-pane">
                <div id="nb-status">
                    <div class="nb-welcome">
                        <x-icon name="point-on-map" class="nb-welcome__icon" />
                        <h2>{{ __('Où êtes-vous ?') }}</h2>
                        <p>{{ __('Autorisez la localisation ou choisissez une ville pour voir ce qui est autour.') }}</p>
                        <div class="nb-quick">
                            @foreach($quickCities as $c)
                                <button type="button" data-quick="{{ $c['lat'] }},{{ $c['lng'] }}"><x-icon name="map-point" />{{ $c['name'] }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <ul id="nb-list" aria-live="polite"></ul>
            </div>
            <div class="nb-mapwrap">
                <div id="nb-map"></div>
            </div>
        </div>
    </main>

    <button type="button" class="nb-toggle" id="nb-toggle" aria-controls="nb-split"><x-icon name="map" /><span>{{ __('Carte') }}</span></button>

    {{-- Icônes réutilisées par le script (rendu côté serveur, copiées dans la liste). --}}
    <template id="nb-ic">
        <span data-ic="walk"><x-icon name="walking" /></span>
        <span data-ic="drive"><x-icon name="car" /></span>
        <span data-ic="dist"><x-icon name="routing" /></span>
        <span data-ic="ok"><x-icon name="verified-check" /></span>
        <span data-ic="star"><x-icon name="star" /></span>
        <span data-ic="img"><x-icon name="gallery" /></span>
        <span data-ic="info"><x-icon name="info-circle" /></span>
        <span data-ic="go"><x-icon name="streets-navigation" /></span>
    </template>

    @include('partials.homepage-footer')

<script>
(function () {
    const API = @json(route('nearby.places'));
    const KEY = @json(config('services.google_maps.browser_key'));
    const MAP_ID = @json(config('services.google_maps.map_id'));
    const LANG = @json(app()->getLocale());
    const T = {
        loading: {{ \Illuminate\Support\Js::from(__('Recherche des lieux autour de vous…')) }},
        none: {{ \Illuminate\Support\Js::from(__('Rien de proche dans ce rayon. Élargissez le rayon ou désactivez « Ouvert maintenant ».')) }},
        error: {{ \Illuminate\Support\Js::from(__('Impossible de charger les lieux. Réessayez.')) }},
        denied: {{ \Illuminate\Support\Js::from(__('Localisation refusée : choisissez une ville dans la liste.')) }},
        open: {{ \Illuminate\Support\Js::from(__('Ouvert')) }},
        closed: {{ \Illuminate\Support\Js::from(__('Fermé')) }},
        until: {{ \Illuminate\Support\Js::from(__('jusqu\'à')) }},
        opensAt: {{ \Illuminate\Support\Js::from(__('ouvre à')) }},
        walk: {{ \Illuminate\Support\Js::from(__('à pied')) }},
        drive: {{ \Illuminate\Support\Js::from(__('en voiture')) }},
        go: {{ \Illuminate\Support\Js::from(__('Y aller')) }},
        see: {{ \Illuminate\Support\Js::from(__('Voir et réserver')) }},
        found: {{ \Illuminate\Support\Js::from(__('lieux trouvés')) }},
        verified: {{ \Illuminate\Support\Js::from(__('Vérifié')) }},
        you: {{ \Illuminate\Support\Js::from(__('Vous êtes ici')) }},
        at: {{ \Illuminate\Support\Js::from(__('Il est')) }},
        map: {{ \Illuminate\Support\Js::from(__('Carte')) }},
        list: {{ \Illuminate\Support\Js::from(__('Liste')) }},
    };
    const $ = (s) => document.querySelector(s);
    const state = { lat: null, lng: null, filter: 'all', open: true, radius: 10, places: [] };
    let map = null, markers = [], me = null, Adv = null, ctrl = null;

    // Icônes Solar rendues côté serveur.
    const IC = {};
    document.getElementById('nb-ic').content.querySelectorAll('[data-ic]').forEach(s => IC[s.dataset.ic] = s.innerHTML);

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmtMin = (m) => m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + String(m % 60).padStart(2, '0');

    function loadMaps() {
        if (!KEY) return Promise.resolve(false);
        if (window.google?.maps?.importLibrary) return Promise.resolve(true);
        return new Promise(res => {
            window.__tiNearbyReady = () => res(true);
            const s = document.createElement('script');
            s.src = 'https://maps.googleapis.com/maps/api/js?' + new URLSearchParams({ key: KEY, loading: 'async', callback: '__tiNearbyReady', language: LANG, region: 'CI', v: 'weekly' });
            s.async = true; s.onerror = () => res(false); document.head.appendChild(s);
        });
    }

    async function ensureMap() {
        if (map || !(await loadMaps())) return !!map;
        const [{ Map }, { AdvancedMarkerElement }] = await Promise.all([google.maps.importLibrary('maps'), google.maps.importLibrary('marker')]);
        Adv = AdvancedMarkerElement;
        map = new Map($('#nb-map'), { center: { lat: 5.345, lng: -4.024 }, zoom: 12, mapId: MAP_ID, gestureHandling: 'greedy', clickableIcons: false, streetViewControl: false, mapTypeControl: false, fullscreenControl: false });
        return true;
    }

    function status(p) {
        if (p.open === 'open') return '<span class="nb-open-pill is-open">' + T.open + (p.until ? ' · ' + T.until + ' ' + esc(p.until) : '') + '</span>';
        if (p.open === 'closed') return '<span class="nb-open-pill is-closed">' + T.closed + (p.until ? ' · ' + T.opensAt + ' ' + esc(p.until) : '') + '</span>';
        return '';
    }
    const msg = (text) => '<div class="nb-msg">' + IC.info + '<span>' + text + '</span></div>';

    function render() {
        const list = $('#nb-list');
        const places = state.places;
        $('#nb-status').innerHTML = places.length
            ? '<div class="nb-status"><span><b>' + places.length + '</b> ' + T.found + '</span><span>' + T.at + ' ' + esc(state.now || '') + '</span></div>'
            : msg(T.none);
        list.innerHTML = places.map((p, i) => `
            <li class="nb-item" data-i="${i}">
                <div class="nb-item__img">${p.image ? `<img src="${esc(p.image)}" alt="" loading="lazy">` : IC.img}<span class="nb-item__n">${i + 1}</span></div>
                <div class="nb-item__body">
                    <p class="nb-item__type">${esc(p.type_label)}${p.verified ? '<span class="is-ok">' + IC.ok + T.verified + '</span>' : ''}${p.rating ? '<span class="nb-item__rate">' + IC.star + p.rating + '</span>' : ''}</p>
                    <p class="nb-item__title">${esc(p.title)}</p>
                    ${status(p)}
                    <div class="nb-times">
                        <span>${IC.walk}${fmtMin(p.walk_min)} ${T.walk}</span>
                        <span>${IC.drive}${fmtMin(p.drive_min)}</span>
                        <span>${IC.dist}${p.km < 1 ? Math.round(p.km * 1000) + ' m' : p.km.toFixed(1) + ' km'}</span>
                    </div>
                    <div class="nb-actions">
                        <a href="${esc(p.url)}" class="is-main">${T.see}</a>
                        <a href="${esc(p.directions)}" target="_blank" rel="noopener" class="is-line">${IC.go}${T.go}</a>
                    </div>
                </div>
            </li>`).join('');
        list.querySelectorAll('.nb-item').forEach(li => {
            li.addEventListener('mouseenter', () => focus(+li.dataset.i, false));
            li.addEventListener('click', e => { if (!e.target.closest('a')) focusOnMap(+li.dataset.i); });
        });
        drawMarkers();
    }

    function drawMarkers() {
        if (!map || !Adv) return;
        markers.forEach(m => m.map = null); markers = [];
        const bounds = new google.maps.LatLngBounds();
        if (state.lat !== null) {
            const dot = document.createElement('div'); dot.className = 'nb-me'; dot.title = T.you;
            if (me) me.map = null;
            me = new Adv({ map, position: { lat: state.lat, lng: state.lng }, content: dot, zIndex: 9999 });
            bounds.extend({ lat: state.lat, lng: state.lng });
        }
        state.places.slice(0, 40).forEach((p, i) => {
            const el = document.createElement('div');
            el.className = 'nb-pin' + (p.open === 'open' ? ' is-open' : '');
            el.textContent = (i + 1) + ' · ' + fmtMin(p.walk_min <= 25 ? p.walk_min : p.drive_min);
            const m = new Adv({ map, position: { lat: p.lat, lng: p.lng }, content: el, title: p.title, zIndex: 100 - i });
            m.addListener('click', () => focus(i, true));
            markers.push(m);
            if (i < 12) bounds.extend({ lat: p.lat, lng: p.lng });
        });
        if (!bounds.isEmpty()) { map.fitBounds(bounds, 60); if (map.getZoom() > 16) map.setZoom(16); }
    }

    function focus(i, scroll) {
        document.querySelectorAll('.nb-item').forEach(li => li.classList.toggle('is-active', +li.dataset.i === i));
        markers.forEach((m, j) => m.content.classList.toggle('is-active', j === i));
        if (scroll) {
            if (split.classList.contains('show-map') && window.matchMedia('(max-width: 1023px)').matches) setView(false);
            document.querySelector(`.nb-item[data-i="${i}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    function focusOnMap(i) {
        focus(i, false);
        const p = state.places[i];
        if (map && p) map.panTo({ lat: p.lat, lng: p.lng });
    }

    async function load() {
        if (state.lat === null) return;
        ctrl?.abort(); ctrl = new AbortController();
        $('#nb-status').innerHTML = '<div class="nb-status"><span>' + T.loading + '</span></div>';
        $('#nb-list').innerHTML = '<li class="nb-skel"></li><li class="nb-skel"></li><li class="nb-skel"></li>';
        try {
            const q = new URLSearchParams({ lat: state.lat, lng: state.lng, radius: state.radius, filter: state.filter, open_now: state.open ? 1 : 0 });
            const r = await fetch(API + '?' + q, { headers: { Accept: 'application/json' }, signal: ctrl.signal });
            const data = await r.json();
            state.places = data.places || []; state.now = data.now;
            await ensureMap();
            render();
        } catch (e) {
            if (e.name !== 'AbortError') { $('#nb-status').innerHTML = msg(T.error); $('#nb-list').innerHTML = ''; }
        }
    }

    function setPosition(lat, lng) { state.lat = lat; state.lng = lng; load(); }

    function locate() {
        if (!navigator.geolocation) { $('#nb-status').innerHTML = msg(T.denied); return; }
        $('#nb-status').innerHTML = '<div class="nb-status"><span>' + T.loading + '</span></div>';
        navigator.geolocation.getCurrentPosition(
            p => setPosition(p.coords.latitude, p.coords.longitude),
            () => { $('#nb-status').innerHTML = msg(T.denied); $('#nb-city').focus(); },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
        );
    }

    // Bascule liste / carte (mobile et tablette).
    const split = document.getElementById('nb-split'), toggle = document.getElementById('nb-toggle');
    function setView(showMap) {
        split.classList.toggle('show-map', showMap);
        toggle.querySelector('span').textContent = showMap ? T.list : T.map;
        if (showMap) { window.scrollTo({ top: split.offsetTop - 80, behavior: 'smooth' }); setTimeout(drawMarkers, 60); }
    }
    toggle.addEventListener('click', () => setView(!split.classList.contains('show-map')));

    $('#nb-locate').addEventListener('click', locate);
    $('#nb-city').addEventListener('change', e => { if (!e.target.value) return; const [la, ln] = e.target.value.split(',').map(Number); setPosition(la, ln); });
    document.querySelectorAll('[data-quick]').forEach(b => b.addEventListener('click', () => {
        const sel = $('#nb-city'); sel.value = b.dataset.quick; sel.dispatchEvent(new Event('change'));
    }));
    document.querySelectorAll('[data-radius] [data-r]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('[data-radius] [data-r]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        state.radius = +b.dataset.r; load();
    }));
    $('#nb-open').addEventListener('change', e => { state.open = e.target.checked; load(); });
    document.querySelectorAll('[data-filter]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('[data-filter]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        state.filter = b.dataset.filter; load();
    }));

    ensureMap();
    // Localisation déjà autorisée : lancement direct, sans clic.
    navigator.permissions?.query({ name: 'geolocation' }).then(p => { if (p.state === 'granted') locate(); }).catch(() => {});
})();
</script>
</body>
</html>
