{{--
    Carte d'une journée du séjour : tracé routier réel, étapes numérotées dans l'ordre optimisé,
    temps de route entre chaque étape. Calculée à l'ouverture seulement (aucun appel Google tant que le visiteur ne la demande pas).
    Paramètres : url (route trip.day-route), day (numéro du jour).
--}}
<div class="ti-dayroute" data-url="{{ $url }}" data-day="{{ $day }}">
    <button type="button" class="ti-dayroute__toggle">
        <i class="fas fa-route"></i> <span>{{ __('Voir la journée sur la carte') }}</span>
        <i class="fas fa-chevron-down ti-dayroute__chev"></i>
    </button>
    <div class="ti-dayroute__panel" hidden>
        <div class="ti-dayroute__map"></div>
        <div class="ti-dayroute__info">
            <p class="ti-dayroute__summary">{{ __('Calcul du trajet…') }}</p>
            <ol class="ti-dayroute__steps"></ol>
            <a class="ti-dayroute__gmaps" href="#" target="_blank" rel="noopener" hidden><i class="fas fa-diamond-turn-right"></i> {{ __('Ouvrir la journée dans Google Maps') }}</a>
        </div>
    </div>
</div>

@once
<style>
    .ti-dayroute { border-top: 1px solid #ece4d3; }
    .ti-dayroute__toggle { width: 100%; display: flex; align-items: center; gap: 8px; padding: 12px 20px; font-weight: 600; font-size: 14px; color: #31621c; background: #f7f3ea; }
    .ti-dayroute__toggle:hover { background: #f0eadb; }
    .ti-dayroute__chev { margin-left: auto; transition: transform .2s; }
    .ti-dayroute.is-open .ti-dayroute__chev { transform: rotate(180deg); }
    .ti-dayroute__panel { display: grid; grid-template-columns: 1fr; }
    @media (min-width: 768px) { .ti-dayroute__panel { grid-template-columns: 3fr 2fr; } }
    .ti-dayroute__map { min-height: 280px; background: #e8e1d3; }
    .ti-dayroute__info { padding: 14px 18px; font-size: 14px; color: #3d372c; }
    .ti-dayroute__summary { font-weight: 600; margin-bottom: 8px; }
    .ti-dayroute__steps { list-style: none; margin: 0; padding: 0; }
    .ti-dayroute__steps li { display: flex; gap: 10px; align-items: flex-start; padding: 6px 0; }
    .ti-dayroute__no { width: 22px; height: 22px; border-radius: 50%; background: #31621c; color: #fff; font: 700 11px/22px Inter, sans-serif; text-align: center; flex-shrink: 0; }
    .ti-dayroute__no.is-home { background: #ef7c2b; }
    .ti-dayroute__leg { font-size: 12px; color: #8a7f6b; padding-left: 32px; }
    .ti-dayroute__gmaps { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-weight: 600; color: #ef7c2b; }
    .ti-dayroute__pin { width: 26px; height: 26px; border-radius: 50%; background: #31621c; color: #fff; border: 2px solid #fff; font: 700 12px/22px Inter, sans-serif; text-align: center; box-shadow: 0 3px 8px rgba(0,0,0,.25); }
    .ti-dayroute__pin.is-home { background: #ef7c2b; }
</style>
<script>
(function () {
    const KEY = @json(config('services.google_maps.browser_key'));
    const MAP_ID = @json(config('services.google_maps.map_id'));
    const LANG = @json(app()->getLocale());
    const T = {
        start: {{ \Illuminate\Support\Js::from(__('Départ et retour')) }},
        route: {{ \Illuminate\Support\Js::from(__('de route')) }},
        total: {{ \Illuminate\Support\Js::from(__('Total de la journée')) }},
        optimized: {{ \Illuminate\Support\Js::from(__('ordre optimisé par Google Maps')) }},
        estimated: {{ \Illuminate\Support\Js::from(__('temps estimés')) }},
        error: {{ \Illuminate\Support\Js::from(__('Impossible de calculer le trajet pour le moment.')) }},
    };
    let mapsReady = null;
    const loadMaps = () => {
        if (!KEY) return Promise.resolve(false);
        if (window.google?.maps?.importLibrary) return Promise.resolve(true);
        return mapsReady ||= new Promise(res => {
            window.__tiDayRouteReady = () => res(true);
            const s = document.createElement('script');
            s.src = 'https://maps.googleapis.com/maps/api/js?' + new URLSearchParams({ key: KEY, loading: 'async', callback: '__tiDayRouteReady', language: LANG, region: 'CI', v: 'weekly' });
            s.async = true; s.onerror = () => res(false); document.head.appendChild(s);
        });
    };
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const dur = (m) => m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + String(m % 60).padStart(2, '0');

    async function open(box) {
        if (box.dataset.loaded) return;
        box.dataset.loaded = '1';
        const summary = box.querySelector('.ti-dayroute__summary');
        let data;
        try {
            data = await (await fetch(box.dataset.url, { headers: { Accept: 'application/json' } })).json();
        } catch (e) { summary.textContent = T.error; return; }
        if (!data.stops || !data.stops.length) { box.hidden = true; return; }

        const steps = box.querySelector('.ti-dayroute__steps');
        let html = `<li><span class="ti-dayroute__no is-home">⌂</span><span><b>${esc(data.home.title)}</b><br><small>${T.start}</small></span></li>`;
        data.stops.forEach((s, i) => {
            const leg = data.legs[i];
            if (leg) html += `<li class="ti-dayroute__leg"><i class="fas fa-car-side mr-1"></i>${dur(leg.minutes)} ${T.route} · ${leg.km} km</li>`;
            html += `<li><span class="ti-dayroute__no">${i + 1}</span><span>${s.url ? `<a href="${esc(s.url)}" class="ai-link">${esc(s.title)}</a>` : esc(s.title)}<br><small>${esc(s.slot)}</small></span></li>`;
        });
        const last = data.legs[data.stops.length];
        if (last) html += `<li class="ti-dayroute__leg"><i class="fas fa-car-side mr-1"></i>${dur(last.minutes)} ${T.route} · ${last.km} km → ⌂</li>`;
        steps.innerHTML = html;
        summary.innerHTML = `${T.total} : ${dur(data.total.minutes)} · ${data.total.km} km <small style="font-weight:400;color:#8a7f6b">(${data.engine === 'google' ? T.optimized : T.estimated})</small>`;
        const gm = box.querySelector('.ti-dayroute__gmaps');
        if (data.maps_url) { gm.href = data.maps_url; gm.hidden = false; }

        const mapEl = box.querySelector('.ti-dayroute__map');
        if (!(await loadMaps())) { mapEl.style.display = 'none'; return; }
        const [{ Map, Polyline }, { AdvancedMarkerElement }, { LatLngBounds }, geometry] = await Promise.all([
            google.maps.importLibrary('maps'), google.maps.importLibrary('marker'), google.maps.importLibrary('core'), google.maps.importLibrary('geometry'),
        ]);
        const map = new Map(mapEl, { center: { lat: data.home.lat, lng: data.home.lng }, zoom: 12, mapId: MAP_ID, streetViewControl: false, mapTypeControl: false, fullscreenControl: true });
        const bounds = new LatLngBounds();
        const pin = (lat, lng, label, home, title) => {
            const el = document.createElement('div'); el.className = 'ti-dayroute__pin' + (home ? ' is-home' : ''); el.textContent = label;
            new AdvancedMarkerElement({ map, position: { lat, lng }, content: el, title });
            bounds.extend({ lat, lng });
        };
        pin(data.home.lat, data.home.lng, '⌂', true, data.home.title);
        data.stops.forEach((s, i) => pin(s.lat, s.lng, String(i + 1), false, s.title));
        const path = data.polyline
            ? geometry.encoding.decodePath(data.polyline)
            : [data.home, ...data.stops, data.home].map(p => ({ lat: p.lat, lng: p.lng }));
        new Polyline({ map, path, strokeColor: '#31621c', strokeOpacity: data.polyline ? 0.9 : 0.6, strokeWeight: data.polyline ? 5 : 3, geodesic: true,
            icons: data.polyline ? [] : [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 }, offset: '0', repeat: '12px' }] });
        map.fitBounds(bounds, 50);
    }

    document.querySelectorAll('.ti-dayroute').forEach(box => {
        const panel = box.querySelector('.ti-dayroute__panel');
        box.querySelector('.ti-dayroute__toggle').addEventListener('click', () => {
            const show = panel.hidden;
            panel.hidden = !show; box.classList.toggle('is-open', show);
            if (show) open(box);
        });
    });
})();
</script>
@endonce
