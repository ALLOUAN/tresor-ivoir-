{{--
    Carte Google Maps des fiches recommandées / trouvées.
    Paramètre : $items (liste de CatalogItem). Silencieuse sans clé ou sans coordonnées.
    Le script Google n'est chargé qu'à l'approche de la carte.
--}}
@php
    $__mapsKey = config('services.google_maps.browser_key');
    $__pins = [];
    $__seen = [];
    foreach ($items as $__n => $__it) {
        if ($__it->lat === null || $__it->lng === null) continue;
        $__k = round($__it->lat, 4).'|'.round($__it->lng, 4);
        $__dup = $__seen[$__k] = ($__seen[$__k] ?? -1) + 1;   // fiches au même endroit (ville) : léger décalage pour les distinguer
        $__pins[] = [
            'n' => count($__pins) + 1,
            'image' => $__it->image, 'price' => $__it->price === null ? null : ($__it->price > 0 ? __('dès ').number_format($__it->price, 0, ',', ' ').' FCFA' : __('Gratuit')),
            'title' => $__it->title, 'type' => $__it->typeLabel, 'city' => $__it->city, 'url' => $__it->url,
            'lat' => $__it->lat + $__dup * 0.0012, 'lng' => $__it->lng + ($__dup % 2 ? 1 : -1) * $__dup * 0.0012,
        ];
    }
@endphp
@if($__mapsKey && $__pins)
@if(!empty($collapsed))
<div class="ai-search" data-reco-search>
    <label class="ai-search__field">
        <span class="ai-search__ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.500a2.5 2.5 0 0 1 0 5z"/></svg></span>
        <input type="search" class="ai-search__input" data-reco-input placeholder="{{ __('Où voulez-vous aller ?') }}" autocomplete="off" autocapitalize="none" spellcheck="false" role="combobox" aria-expanded="false" aria-autocomplete="list" aria-label="{{ __('Rechercher un lieu sur Google Maps') }}">
        <button type="button" class="ai-search__clear" data-reco-clear hidden aria-label="{{ __('Effacer') }}">×</button>
        <span class="ai-search__go" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.500" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.500-3.500"/></svg></span>
    </label>
    <ul class="ai-search__list" data-reco-list role="listbox" hidden></ul>
</div>
@endif
<div class="ai-mapbox" style="position:relative;margin-top:1.25rem;" data-reco-map data-pins='@json($__pins)'>
    <div data-reco-canvas class="ai-mapbox__canvas"></div>
    <p class="ai-mapbox__legend">{{ trans_choice(':count lieu sur la carte|:count lieux sur la carte', count($__pins)) }} · {{ __('touchez un repère pour ouvrir la fiche') }}</p>
</div>
@once
<style>
    .ai-search { position: relative; margin-top: 1.1rem; z-index: 20; }
    .ai-search__field { display: flex; align-items: center; gap: 10px; padding: 8px 8px 8px 10px; border-radius: 999px; background: #fff; border: 1.5px solid #e3d9c6; box-shadow: 0 6px 18px rgba(31,27,22,.10); transition: border-color .15s, box-shadow .15s; cursor: text; }
    .ai-search__field:focus-within { border-color: #c25e0a; box-shadow: 0 8px 26px rgba(194,94,10,.18); }
    .ai-search__ico { flex: none; width: 38px; height: 38px; border-radius: 50%; background: #c25e0a; color: #fff; display: flex; align-items: center; justify-content: center; }
    .ai-search__input { flex: 1; min-width: 0; border: 0 !important; outline: 0 !important; background: transparent !important; box-shadow: none !important; font: 600 16px system-ui, -apple-system, 'Segoe UI', sans-serif; color: #1f1b16; padding: 6px 0; }
    .ai-search__input::placeholder { color: #8d8372; font-weight: 500; }
    .ai-search__input::-webkit-search-cancel-button { display: none; }
    .ai-search__clear { flex: none; width: 28px; height: 28px; border-radius: 50%; border: 0; background: #f0e9da; color: #4a4336; font-size: 18px; line-height: 1; cursor: pointer; }
    .ai-search__go { flex: none; width: 38px; height: 38px; border-radius: 50%; background: #f3efe6; color: #1f1b16; display: flex; align-items: center; justify-content: center; }
    .ai-search__list { position: absolute; left: 0; right: 0; top: calc(100% + 8px); margin: 0; padding: 6px; list-style: none; max-height: min(60vh, 380px); overflow-y: auto; background: #fff; border: 1px solid #e3d9c6; border-radius: 18px; box-shadow: 0 16px 40px rgba(31,27,22,.22); }
    .ai-search__head { padding: 8px 10px 4px; font: 700 10.5px system-ui, sans-serif; letter-spacing: .06em; text-transform: uppercase; color: #8d8372; }
    .ai-search__opt { display: flex; align-items: center; gap: 11px; padding: 9px 10px; border-radius: 12px; cursor: pointer; }
    .ai-search__opt:hover, .ai-search__opt[aria-selected="true"] { background: #faf3e7; }
    .ai-search__badge { flex: none; width: 30px; height: 30px; border-radius: 50%; background: #fbeee0; color: #a14d08; display: flex; align-items: center; justify-content: center; font: 700 12px system-ui, sans-serif; }
    .ai-search__badge--g { background: #eef2f7; color: #4a5a72; }
    .ai-search__txt { min-width: 0; line-height: 1.2; }
    .ai-search__txt b { display: block; font: 600 14px system-ui, sans-serif; color: #1f1b16; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ai-search__txt small { display: block; font: 12px system-ui, sans-serif; color: #6b6354; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ai-search__empty { padding: 14px; font: 13px system-ui, sans-serif; color: #6b6354; }
    .ai-dest { width: 18px; height: 18px; border-radius: 50%; background: #1a73e8; border: 3px solid #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.3), 0 2px 6px rgba(0,0,0,.4); }
    .ai-mapbox { border: 1px solid rgba(0,0,0,.08); border-radius: 1rem; overflow: hidden; background: #fff; }
    .ai-mapbox__canvas { width: 100%; height: clamp(320px, 52vh, 520px); background: #f3efe6; }
    .ai-mapbox__legend { margin: 0; padding: .6rem .9rem; font-size: .75rem; color: #5c5548; border-top: 1px solid rgba(0,0,0,.06); }
    .ai-mapbox__legend i { color: #c25e0a; margin-right: .35rem; }
    .ai-mapbox__all { z-index: 5; padding: .45rem .8rem; border-radius: .6rem; border: 0; background: #fff; color: #1f1b16; font: 700 12px system-ui, sans-serif; box-shadow: 0 2px 8px rgba(0,0,0,.3); cursor: pointer; }
    .ai-pin { position: relative; display: flex; align-items: center; gap: 6px; max-width: 190px; padding: 3px 10px 3px 3px; border-radius: 999px; background: #fff; border: 2px solid #c25e0a; box-shadow: 0 2px 8px rgba(0,0,0,.3); font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; cursor: pointer; transform: translateY(-4px); transition: transform .12s; }
    .ai-pin::after { content: ''; position: absolute; left: 50%; bottom: -7px; width: 10px; height: 10px; margin-left: -5px; background: #fff; border-right: 2px solid #c25e0a; border-bottom: 2px solid #c25e0a; transform: rotate(45deg); }
    .ai-pin:hover { transform: translateY(-6px) scale(1.04); }
    .ai-pin__n { flex: none; width: 22px; height: 22px; border-radius: 50%; background: #c25e0a; color: #fff; font: 700 12px/22px system-ui, sans-serif; text-align: center; }
    .ai-pin__t { min-width: 0; line-height: 1.15; }
    .ai-pin__t b { display: block; font-size: 12px; font-weight: 700; color: #1f1b16; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
    .ai-pin__t small { display: block; font-size: 10.5px; color: #8a4208; font-weight: 600; white-space: nowrap; }
    .ai-iw { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; width: 250px; }
    .ai-iw__img { height: 120px; background: #ede6d7; display: flex; align-items: center; justify-content: center; color: #b9ab8c; font-size: 28px; overflow: hidden; }
    .ai-iw__img img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ai-iw__body { padding: 12px 14px 14px; }
    .ai-iw__type { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #fbeee0; color: #a14d08; font-size: 10.5px; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; }
    .ai-iw__title { margin: 7px 0 3px; font-size: 15px; line-height: 1.25; font-weight: 700; color: #1f1b16; }
    .ai-iw__meta { margin: 0 0 11px; font-size: 12px; color: #6b6354; }
    .ai-iw__meta b { color: #1f1b16; font-weight: 700; }
    .ai-iw__btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 12px; border-radius: 10px; background: #c25e0a; color: #fff !important; font-size: 13px; font-weight: 700; text-decoration: none !important; transition: background .15s; }
    .ai-iw__btn:hover { background: #a14d08; }
    .gm-style .gm-style-iw-c { padding: 0 !important; border-radius: 16px !important; overflow: hidden; box-shadow: 0 10px 30px rgba(31,27,22,.28) !important; }
    .gm-style .gm-style-iw-d { overflow: hidden !important; max-height: none !important; }
    .gm-style .gm-style-iw-chr { position: absolute; top: 4px; right: 4px; z-index: 2; height: auto; }
    .gm-style .gm-style-iw-chr button { background: rgba(255,255,255,.92) !important; border-radius: 50% !important; width: 28px !important; height: 28px !important; }
    .gm-style .gm-style-iw-chr button span { margin: 5px !important; width: 18px !important; height: 18px !important; }
</style>
<script>
(function () {
    const SRC = 'https://maps.googleapis.com/maps/api/js?' + new URLSearchParams({
        key: @json($__mapsKey), loading: 'async', callback: '__tiRecoMapsReady', language: 'fr', region: 'CI', v: 'weekly',
    });
    const MAP_ID = @json(config('services.google_maps.map_id'));
    let loading = null;
    function load() {
        if (window.google?.maps?.importLibrary) return Promise.resolve();
        if (!loading) loading = new Promise(res => {
            window.__tiRecoMapsReady = res;
            const s = document.createElement('script'); s.src = SRC; s.async = true; document.head.appendChild(s);
        });
        return loading;
    }
    async function init(root) {
        const pins = JSON.parse(root.dataset.pins || '[]');
        await load();
        const [{ Map, InfoWindow }, { LatLngBounds }, { AdvancedMarkerElement }] = await Promise.all([
            google.maps.importLibrary('maps'), google.maps.importLibrary('core'), google.maps.importLibrary('marker'),
        ]);
        const map = new Map(root.querySelector('[data-reco-canvas]'), {
            center: { lat: pins[0].lat, lng: pins[0].lng }, zoom: 9, mapId: MAP_ID,
            streetViewControl: false, mapTypeControl: false, fullscreenControl: true, gestureHandling: 'cooperative',
        });
        const info = new InfoWindow({ maxWidth: 250, minWidth: 250, disableAutoPan: true, shouldFocus: false });
        let hideT = null;
        info.addListener('domready', () => {
            const bubble = root.querySelector('.gm-style-iw-c');
            if (!bubble) return;
            bubble.addEventListener('mouseenter', () => clearTimeout(hideT));
            bubble.addEventListener('mouseleave', () => { hideT = setTimeout(() => info.close(), 350); });
        });
        const bounds = new LatLngBounds();
        const shows = [];
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        pins.forEach(p => {
            const el = document.createElement('div'); el.className = 'ai-pin'; el.innerHTML = '<span class="ai-pin__n">' + p.n + '</span><span class="ai-pin__t"><b>' + esc(p.title) + '</b>' + (p.price ? '<small>' + esc(p.price) + '</small>' : '') + '</span>';
            const m = new AdvancedMarkerElement({ map, position: { lat: p.lat, lng: p.lng }, content: el, title: p.title, zIndex: 100 - p.n });
            const show = () => {
                m.zIndex = 1000;
                info.setContent('<div class="ai-iw"><div class="ai-iw__img">' + (p.image ? '<img src="' + esc(p.image) + '" alt="">' : '<i class="fas fa-image"></i>') + '</div>'
                    + '<div class="ai-iw__body"><span class="ai-iw__type">' + esc(p.type) + '</span><h3 class="ai-iw__title">' + esc(p.title) + '</h3>'
                    + '<p class="ai-iw__meta">' + (p.city ? esc(p.city) : '') + (p.price ? (p.city ? ' · ' : '') + '<b>' + esc(p.price) + '</b>' : '') + '</p>'
                    + '<a class="ai-iw__btn" href="' + esc(p.url) + '">Voir la fiche →</a></div></div>');
                info.open({ map, anchor: m, shouldFocus: false });
            };
            // Au survol (ordinateur) la fiche s'affiche sans clic ; elle reste ouverte tant que la souris est sur le repère ou la bulle.
            // Au toucher (mobile), un appui sur le repère l'ouvre.
            [el, m].forEach(t => {
                t.addEventListener('pointerenter', () => { clearTimeout(hideT); show(); });
                t.addEventListener('pointerleave', () => { hideT = setTimeout(() => info.close(), 350); });
            });
            m.addEventListener('gmp-click', () => show());
            shows.push(show);
            bounds.extend({ lat: p.lat, lng: p.lng });
        });
        // Cadrage serré : on zoome sur le groupe de lieux le plus fourni (≈ 13 km autour du lieu le plus entouré),
        // les lieux éloignés restent accessibles via « Tout voir ».
        const near = (p, q) => Math.hypot(p.lat - q.lat, p.lng - q.lng) < 0.12;
        const hub = pins.reduce((best, p) => { const c = pins.filter(q => near(p, q)).length; return c > best.c ? { p, c } : best; }, { p: pins[0], c: 0 }).p;
        const group = pins.filter(q => near(hub, q));
        const fit = list => {
            const bb = new LatLngBounds(); list.forEach(q => bb.extend({ lat: q.lat, lng: q.lng }));
            if (list.length > 1) map.fitBounds(bb, 12); else { map.setCenter(bb.getCenter()); map.setZoom(15); }
            google.maps.event.addListenerOnce(map, 'idle', () => { if (map.getZoom() > 16) map.setZoom(16); });
        };
        fit(group);
        // La fiche du premier lieu du groupe est déjà affichée à l'ouverture : l'information est visible sans aucun geste.
        google.maps.event.addListenerOnce(map, 'idle', () => setTimeout(() => { if (root.dataset.picked) return; info.setOptions({ disableAutoPan: false }); shows[pins.indexOf(group[0])]?.(); setTimeout(() => info.setOptions({ disableAutoPan: true, shouldFocus: false }), 800); }, 150));
        const ctx = { map, info, shows, pins, esc, AdvancedMarkerElement, LatLngBounds, group, fit };
        if (group.length < pins.length) {
            const btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'ai-mapbox__all'; btn.textContent = 'Tout voir';
            let all = false;
            btn.addEventListener('click', () => { all = !all; btn.textContent = all ? {{ \Illuminate\Support\Js::from(__('Zoomer sur le groupe')) }} : 'Tout voir'; fit(all ? pins : group); });
            root.querySelector('[data-reco-canvas]').after(btn);
            btn.style.cssText = 'position:absolute;top:10px;left:10px;';
        }
        return ctx;
    }
    const ctxOf = root => (root.__ctx ||= init(root));
    const GPIN = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.500A2.500 2.500 0 1 1 12 6.500a2.500 2.500 0 0 1 0 5z"/></svg>';
    function initSearch(box) {
        const mapRoot = box.nextElementSibling, input = box.querySelector('[data-reco-input]'),
              list = box.querySelector('[data-reco-list]'), clear = box.querySelector('[data-reco-clear]');
        const pins = JSON.parse(mapRoot.dataset.pins || '[]');
        const norm = t => String(t ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        let places = null, token = null, items = [], active = -1, seq = 0, destMarker = null, timer = null;

        const placesLib = async () => {
            await load();
            places ||= await google.maps.importLibrary('places');
            token ||= new places.AutocompleteSessionToken();
            return places;
        };
        const close = () => { list.hidden = true; input.setAttribute('aria-expanded', 'false'); active = -1; };
        const asItem = p => ({ kind: 'p', group: 'Nos recommandations', n: p.n, main: p.title, sub: [p.type, p.city].filter(Boolean).join(' · '), pin: p });
        const paint = () => {
            list.innerHTML = '';
            if (!items.length) { list.innerHTML = '<li class="ai-search__empty">Aucun lieu trouvé.</li>'; return; }
            let last = '';
            items.forEach((it, i) => {
                if (it.group !== last) { last = it.group; const h = document.createElement('li'); h.className = 'ai-search__head'; h.textContent = last; h.setAttribute('role', 'presentation'); list.appendChild(h); }
                const li = document.createElement('li'); li.className = 'ai-search__opt'; li.setAttribute('role', 'option'); li.dataset.i = i;
                li.innerHTML = '<span class="ai-search__badge' + (it.kind === 'g' ? ' ai-search__badge--g' : '') + '">' + (it.kind === 'g' ? GPIN : esc(it.n)) + '</span><span class="ai-search__txt"><b>' + esc(it.main) + '</b><small>' + esc(it.sub) + '</small></span>';
                list.appendChild(li);
            });
        };
        const mark = () => list.querySelectorAll('.ai-search__opt').forEach(li => li.setAttribute('aria-selected', String(+li.dataset.i === active)));
        const open = () => { list.hidden = false; input.setAttribute('aria-expanded', 'true'); };

        async function search() {
            const q = input.value.trim(); clear.hidden = !q;
            const mine = ++seq;
            active = -1;
            if (q.length < 2) { items = pins.map(asItem); open(); paint(); return; }   // sans saisie : les lieux recommandés sont proposés d'emblée
            const nq = norm(q);
            items = pins.filter(p => norm(p.title + ' ' + p.city + ' ' + p.type).includes(nq)).map(asItem);
            open(); paint();
            try {
                const pl = await placesLib();
                const { suggestions } = await pl.AutocompleteSuggestion.fetchAutocompleteSuggestions({
                    input: q, sessionToken: token, includedRegionCodes: ['ci'], language: 'fr', region: 'ci',
                });
                if (mine !== seq) return;
                items = items.concat(suggestions.filter(x => x.placePrediction).slice(0, 5).map(x => ({
                    kind: 'g', group: 'Sur Google Maps',
                    main: x.placePrediction.mainText?.text ?? x.placePrediction.text.text,
                    sub: x.placePrediction.secondaryText?.text ?? '', pred: x.placePrediction,
                })));
                paint();
            } catch (e) { /* hors ligne ou API non autorisée : les recommandations restent proposées */ }
        }

        async function choose(it) {
            if (!it) return;
            close();
            mapRoot.dataset.picked = '1';   // le choix du visiteur prime sur l'ouverture automatique de la première fiche
            mapRoot.hidden = false;
            mapRoot.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const ctx = await ctxOf(mapRoot);
            if (it.kind === 'p') {
                input.value = it.main; clear.hidden = false;
                ctx.map.panTo({ lat: it.pin.lat, lng: it.pin.lng }); ctx.map.setZoom(15);
                ctx.shows[pins.indexOf(it.pin)]?.();
                return;
            }
            const place = it.pred.toPlace();
            await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location'] });
            token = null;
            input.value = place.displayName || it.main; clear.hidden = false;
            const pos = { lat: place.location.lat(), lng: place.location.lng() };
            if (destMarker) destMarker.map = null;
            const dot = document.createElement('div'); dot.className = 'ai-dest';
            destMarker = new ctx.AdvancedMarkerElement({ map: ctx.map, position: pos, content: dot, title: place.displayName, zIndex: 2000 });
            ctx.map.panTo(pos); ctx.map.setZoom(15);
            ctx.info.setContent('<div class="ai-iw"><div class="ai-iw__body"><span class="ai-iw__type">Google Maps</span><h3 class="ai-iw__title">' + ctx.esc(place.displayName) + '</h3>'
                + '<p class="ai-iw__meta">' + ctx.esc(place.formattedAddress) + '</p>'
                + '<a class="ai-iw__btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=' + pos.lat + ',' + pos.lng + '&query_place_id=' + encodeURIComponent(it.pred.placeId) + '">Ouvrir dans Google Maps</a></div></div>');
            ctx.info.open({ map: ctx.map, anchor: destMarker, shouldFocus: false });
        }

        input.addEventListener('focus', () => { search(); placesLib().catch(() => {}); });
        input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(search, 180); });
        input.addEventListener('keydown', e => {
            const n = items.length;
            if (e.key === 'ArrowDown' && n) { e.preventDefault(); active = (active + 1) % n; mark(); }
            else if (e.key === 'ArrowUp' && n) { e.preventDefault(); active = (active - 1 + n) % n; mark(); }
            else if (e.key === 'Enter') { e.preventDefault(); choose(items[active >= 0 ? active : 0]); }
            else if (e.key === 'Escape') close();
        });
        list.addEventListener('mousedown', e => { e.preventDefault(); const li = e.target.closest('.ai-search__opt'); if (li) choose(items[+li.dataset.i]); });
        clear.addEventListener('click', () => { input.value = ''; clear.hidden = true; input.focus(); search(); });
        document.addEventListener('click', e => { if (!box.contains(e.target)) close(); });
    }

    const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { io.unobserve(e.target); ctxOf(e.target).catch(() => {}); } }), { rootMargin: '300px' });
    const boot = () => {
        document.querySelectorAll('[data-reco-map]').forEach(el => io.observe(el));
        document.querySelectorAll('[data-reco-search]').forEach(initSearch);
    }
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
</script>
@endonce
@endif
