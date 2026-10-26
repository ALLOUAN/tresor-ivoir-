{{--
    Carte Google Maps d'un lieu + trajet depuis la position du visiteur + suivi
    en direct du déplacement (position, distance/temps restants, recalcul si le
    visiteur quitte la route, détection d'arrivée).
    Paramètres : $type (clé de DirectionsController), $slug, $lat, $lng, $name,
                 $height (défaut 260px), $accent (couleur du tracé), $frameClass.
    Sans clé GOOGLE_MAPS_BROWSER_KEY, retombe sur l'iframe Google Maps simple.
--}}
@php
    $mapsKey = config('services.google_maps.browser_key');
    $height = $height ?? '260px';
    $accent = $accent ?? '#EB6E00';
    $frameClass = $frameClass ?? 'bento-card rounded-2xl overflow-hidden mb-3';
@endphp

@if(! $mapsKey)
    <div class="{{ $frameClass }}">
        <iframe src="https://www.google.com/maps?q={{ $lat }},{{ $lng }}&output=embed"
                class="w-full" style="height:{{ $height }}; border:0;" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
@else
    <div class="{{ $frameClass }}" data-route-map
         data-lat="{{ $lat }}" data-lng="{{ $lng }}" data-name="{{ $name }}" data-accent="{{ $accent }}"
         data-route-url="{{ route('directions.route', [$type, $slug]) }}">
        <div class="relative">
            <div data-route-canvas class="w-full bg-[#F3F8F5]" style="height:{{ $height }};"></div>

            {{-- Bandeau de navigation, visible pendant le suivi --}}
            <div data-track-panel hidden
                 class="absolute top-2 left-2 right-2 rounded-xl bg-[#0E3B2A]/90 text-white px-3 py-2 text-xs shadow-lg backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <i class="fas fa-location-arrow" style="color: {{ $accent }};"></i>
                    <div class="min-w-0">
                        <p class="font-bold text-sm leading-tight" data-track-remaining>—</p>
                        <p class="text-white/70 truncate" data-track-detail>{{ __('Suivi en cours') }}</p>
                    </div>
                </div>
            </div>

            <button type="button" data-track-recenter hidden
                    class="absolute bottom-3 right-3 inline-flex items-center gap-2 px-3 py-2 rounded-full bg-white text-[#0E3B2A] text-xs font-bold shadow-lg">
                <i class="fas fa-crosshairs"></i> {{ __('Recentrer') }}
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 p-3 bg-white border-t border-black/5">
            <div class="inline-flex rounded-lg border border-black/10 overflow-hidden text-xs font-semibold" role="group" aria-label="{{ __('Mode de déplacement') }}">
                <button type="button" data-route-mode="driving" aria-pressed="true"
                        class="px-3 py-2 transition"><i class="fas fa-car mr-1"></i> {{ __('Voiture') }}</button>
                <button type="button" data-route-mode="walking" aria-pressed="false"
                        class="px-3 py-2 transition border-l border-black/10"><i class="fas fa-person-walking mr-1"></i> {{ __('À pied') }}</button>
            </div>
            <button type="button" data-route-locate
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-white text-xs font-bold transition hover:opacity-90"
                    style="background: {{ $accent }};">
                <i class="fas fa-route"></i> <span data-route-locate-label>{{ __('Afficher mon trajet') }}</span>
            </button>
            <button type="button" data-track-toggle hidden
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-[#1a73e8] text-white text-xs font-bold transition hover:opacity-90">
                <i class="fas fa-location-arrow"></i> <span data-track-toggle-label>{{ __('Suivre mon déplacement') }}</span>
            </button>
            <p data-route-status class="w-full sm:w-auto sm:ml-auto text-xs text-[#24503D]" aria-live="polite"></p>
        </div>
    </div>

    @once
    <style>
        [data-route-map] [data-route-mode][aria-pressed="true"] { background: #0E3B2A; color: #fff; }
        [data-route-map] [data-route-mode][aria-pressed="false"] { background: #fff; color: #24503D; }
        .ti-me { position: relative; width: 22px; height: 22px; }
        .ti-me__dot { position: absolute; inset: 3px; border-radius: 50%; background: #1a73e8; border: 3px solid #fff; box-shadow: 0 1px 4px rgba(14,59,42,.4); }
        .ti-me__pulse { position: absolute; inset: -8px; border-radius: 50%; background: rgba(26,115,232,.25); animation: ti-pulse 2s ease-out infinite; }
        .ti-me__cone { position: absolute; left: 50%; top: -14px; width: 0; height: 0; margin-left: -7px;
                       border-left: 7px solid transparent; border-right: 7px solid transparent; border-bottom: 14px solid #1a73e8;
                       transform-origin: 50% 25px; opacity: .85; display: none; }
        @keyframes ti-pulse { from { transform: scale(.6); opacity: 1; } to { transform: scale(1.6); opacity: 0; } }
        @media (prefers-reduced-motion: reduce) { .ti-me__pulse { animation: none; } }
    </style>
    <script>
    (function () {
        const MAPS_SRC = 'https://maps.googleapis.com/maps/api/js?' + new URLSearchParams({
            key: @json($mapsKey), loading: 'async', callback: '__tiRouteMapsReady',
            language: 'fr', region: 'CI', v: 'weekly',
        });
        const MAP_ID = @json(config('services.google_maps.map_id'));
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? @json(csrf_token());

        const ARRIVAL_RADIUS_M = 30;      // distance à la destination considérée comme « arrivé »
        const OFF_ROUTE_TOLERANCE = 8e-4; // ≈ 90 m, en degrés (isLocationOnEdge)
        const OFF_ROUTE_HITS = 3;         // positions hors route consécutives avant recalcul
        const REROUTE_MIN_GAP_MS = 30000; // pas plus d'un recalcul toutes les 30 s

        // Le script Google n'est chargé qu'à l'approche de la carte (chaque chargement est facturé).
        let loading = null;
        function loadMaps() {
            if (!loading) {
                loading = new Promise(resolve => {
                    window.__tiRouteMapsReady = resolve;
                    const s = document.createElement('script');
                    s.src = MAPS_SRC; s.async = true;
                    document.head.appendChild(s);
                });
            }
            return loading;
        }

        function formatDistance(m) {
            return m < 1000 ? `${Math.max(0, Math.round(m / 10) * 10)} m` : `${(m / 1000).toFixed(1).replace('.', ',')} km`;
        }
        function formatDuration(s) {
            const min = Math.max(1, Math.round(s / 60));
            return min < 60 ? `${min} min` : `${Math.floor(min / 60)} h ${String(min % 60).padStart(2, '0')}`;
        }

        async function initMap(root) {
            await loadMaps();
            const [{ Map, Polyline, Circle }, { LatLngBounds }, { AdvancedMarkerElement, PinElement }, { encoding, spherical, poly }] = await Promise.all([
                google.maps.importLibrary('maps'),
                google.maps.importLibrary('core'),
                google.maps.importLibrary('marker'),
                google.maps.importLibrary('geometry'),
            ]);

            const $ = sel => root.querySelector(sel);
            const accent = root.dataset.accent;
            const dest = new google.maps.LatLng(parseFloat(root.dataset.lat), parseFloat(root.dataset.lng));
            const status = $('[data-route-status]');
            const trackBtn = $('[data-track-toggle]');
            const panel = $('[data-track-panel]');
            const recenterBtn = $('[data-track-recenter]');

            const map = new Map($('[data-route-canvas]'), {
                center: dest, zoom: 14, mapId: MAP_ID,
                mapTypeControl: false, streetViewControl: false,
            });

            new AdvancedMarkerElement({
                map, position: dest, title: root.dataset.name,
                content: new PinElement({ background: accent, borderColor: '#0E3B2A', glyphColor: '#fff' }).element,
            });

            // ── État ───────────────────────────────────────────────────────
            let mode = 'driving';
            let origin = null;                 // dernière position connue {lat, lng}
            let route = null;                  // { path: LatLng[], cum: number[], total, duration_s, url }
            let travelled = null, remaining = null;
            let meEl = null, meMarker = null, accuracyCircle = null;
            let watchId = null, following = true, wakeLock = null;
            let offRouteHits = 0, lastRerouteAt = 0, rerouting = false;
            let countNext = false;             // le prochain calcul vient du bouton « Itinéraire » de la page : on compte le clic

            function say(text, link) {
                status.textContent = text;
                if (link) {
                    const a = document.createElement('a');
                    a.href = link; a.target = '_blank'; a.rel = 'noopener noreferrer';
                    a.className = 'ml-2 font-bold underline';
                    a.dataset.external = '1';
                    a.style.color = accent;
                    a.textContent = 'Ouvrir dans Google Maps';
                    status.appendChild(a);
                }
            }

            // ── Marqueur « vous êtes ici » (point bleu + cône d'orientation) ─
            function updateMe(pos) {
                const here = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                if (!meMarker) {
                    meEl = document.createElement('div');
                    meEl.className = 'ti-me';
                    meEl.innerHTML = '<span class="ti-me__pulse"></span><span class="ti-me__cone"></span><span class="ti-me__dot"></span>';
                    meMarker = new AdvancedMarkerElement({ map, title: 'Votre position', content: meEl, zIndex: 1000 });
                    accuracyCircle = new Circle({
                        map, clickable: false, strokeOpacity: 0,
                        fillColor: '#1a73e8', fillOpacity: 0.12,
                    });
                }
                meMarker.position = here;
                accuracyCircle.setCenter(here);
                accuracyCircle.setRadius(Math.min(pos.coords.accuracy || 0, 500));

                const cone = meEl.querySelector('.ti-me__cone');
                const heading = pos.coords.heading;
                if (heading !== null && !Number.isNaN(heading) && (pos.coords.speed ?? 0) > 0.5) {
                    cone.style.display = 'block';
                    cone.style.transform = `rotate(${heading}deg)`;
                } else {
                    cone.style.display = 'none';
                }
                return here;
            }

            // ── Calcul du trajet (serveur → Google Routes API) ──────────────
            async function fetchRoute() {
                const res = await fetch(root.dataset.routeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ lat: origin.lat, lng: origin.lng, mode, count: countNext }),
                });
                countNext = false;
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || {{ \Illuminate\Support\Js::from(__('Aucun trajet trouvé.')) }});

                const path = encoding.decodePath(data.polyline);
                const cum = [0];
                for (let i = 1; i < path.length; i++) cum.push(cum[i - 1] + spherical.computeDistanceBetween(path[i - 1], path[i]));
                route = { path, cum, total: cum[cum.length - 1] || 1, duration_s: data.duration_s, url: data.google_maps_url };

                travelled?.setMap(null); remaining?.setMap(null);
                travelled = new Polyline({ map, path: [], strokeColor: '#8AA99B', strokeOpacity: 0.8, strokeWeight: 5 });
                remaining = new Polyline({ map, path, strokeColor: accent, strokeOpacity: 0.95, strokeWeight: 6 });
                offRouteHits = 0;
                return data;
            }

            async function showRoute() {
                say({{ \Illuminate\Support\Js::from(__('Calcul du trajet…')) }});
                try {
                    const data = await fetchRoute();
                    const bounds = new LatLngBounds();
                    route.path.forEach(p => bounds.extend(p));
                    map.fitBounds(bounds, 40);
                    say(`${data.duration_text ?? formatDuration(data.duration_s)} · ${data.distance_text ?? formatDistance(data.distance_m)}`, route.url);
                    $('[data-route-locate-label]').textContent = 'Actualiser';
                    trackBtn.hidden = false;
                } catch (e) {
                    say(e.message || {{ \Illuminate\Support\Js::from(__('Impossible de calculer le trajet pour le moment.')) }});
                }
            }

            // ── Suivi en direct ─────────────────────────────────────────────
            function nearestIndex(here) {
                let best = 0, bestD = Infinity;
                for (let i = 0; i < route.path.length; i++) {
                    const d = spherical.computeDistanceBetween(here, route.path[i]);
                    if (d < bestD) { bestD = d; best = i; }
                }
                return best;
            }

            function onTrackPosition(pos) {
                origin = updateMe(pos);
                const here = new google.maps.LatLng(origin.lat, origin.lng);

                if (following) {
                    map.panTo(here);
                    if (map.getZoom() < 16) map.setZoom(17);
                }

                const toDest = spherical.computeDistanceBetween(here, dest);
                if (toDest <= ARRIVAL_RADIUS_M) {
                    $('[data-track-remaining]').textContent = {{ \Illuminate\Support\Js::from(__('Vous êtes arrivé 🎉')) }};
                    $('[data-track-detail]').textContent = root.dataset.name;
                    stopTracking(false);
                    say({{ \Illuminate\Support\Js::from(__('Vous êtes arrivé à destination.')) }});
                    navigator.vibrate?.([200, 100, 200]);
                    return;
                }

                if (!route) return;

                // Partie parcourue en gris, reste du trajet en couleur.
                const idx = nearestIndex(here);
                travelled.setPath(route.path.slice(0, idx + 1).concat([here]));
                remaining.setPath([here].concat(route.path.slice(idx + 1)));

                const left = Math.max(route.total - route.cum[idx], toDest);
                const eta = route.duration_s * (left / route.total);
                const arrival = new Date(Date.now() + eta * 1000).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                $('[data-track-remaining]').textContent = `${formatDuration(eta)} · ${formatDistance(left)}`;
                $('[data-track-detail]').textContent = `Arrivée vers ${arrival} — ${root.dataset.name}`;

                // Hors de la route ? Recalcul après plusieurs positions confirmées.
                const onRoute = poly.isLocationOnEdge(here, remaining, OFF_ROUTE_TOLERANCE);
                offRouteHits = onRoute ? 0 : offRouteHits + 1;
                if (offRouteHits >= OFF_ROUTE_HITS && !rerouting && Date.now() - lastRerouteAt > REROUTE_MIN_GAP_MS) {
                    rerouting = true; lastRerouteAt = Date.now();
                    $('[data-track-detail]').textContent = {{ \Illuminate\Support\Js::from(__('Nouvel itinéraire en cours de calcul…')) }};
                    fetchRoute().catch(() => {}).finally(() => { rerouting = false; });
                }
            }

            function onTrackError(err) {
                if (err.code === err.PERMISSION_DENIED) {
                    stopTracking(false);
                    say({{ \Illuminate\Support\Js::from(__('Autorisez la localisation pour suivre votre déplacement.')) }});
                } else {
                    $('[data-track-detail]').textContent = 'Signal GPS faible…';
                }
            }

            async function requestWakeLock() {
                try { wakeLock = await navigator.wakeLock?.request('screen'); } catch (e) { /* non supporté */ }
            }

            function startTracking() {
                if (!navigator.geolocation || !window.isSecureContext) return say({{ \Illuminate\Support\Js::from(__('Le suivi en direct exige une connexion sécurisée (https) et la géolocalisation.')) }});
                if (!navigator.geolocation) return say({{ \Illuminate\Support\Js::from(__('Votre navigateur ne permet pas la géolocalisation.')) }});
                following = true;
                recenterBtn.hidden = true;
                panel.hidden = false;
                $('[data-track-remaining]').textContent = 'Localisation…';
                $('[data-track-toggle-label]').textContent = {{ \Illuminate\Support\Js::from(__('Arrêter le suivi')) }};
                watchId = navigator.geolocation.watchPosition(onTrackPosition, onTrackError, {
                    enableHighAccuracy: true, maximumAge: 2000, timeout: 20000,
                });
                requestWakeLock();
                say({{ \Illuminate\Support\Js::from(__('Suivi en direct activé — gardez cette page ouverte.')) }});
            }

            function stopTracking(hidePanel = true) {
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                watchId = null;
                wakeLock?.release?.(); wakeLock = null;
                recenterBtn.hidden = true;
                if (hidePanel) { panel.hidden = true; say(''); }
                $('[data-track-toggle-label]').textContent = {{ \Illuminate\Support\Js::from(__('Suivre mon déplacement')) }};
            }

            // L'écran se rallume : le verrou d'écran doit être redemandé.
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && watchId !== null) requestWakeLock();
            });

            // Si le visiteur déplace la carte, on arrête de la recentrer automatiquement.
            map.addListener('dragstart', () => {
                if (watchId === null) return;
                following = false;
                recenterBtn.hidden = false;
            });
            recenterBtn.addEventListener('click', () => {
                following = true;
                recenterBtn.hidden = true;
                if (origin) { map.panTo(origin); map.setZoom(17); }
            });

            trackBtn.addEventListener('click', () => (watchId === null ? startTracking() : stopTracking()));

            // Repli : la position n'est pas disponible (page non sécurisée, refus, appareil sans GPS) →
            // le visiteur touche la carte pour indiquer son point de départ.
            let picking = null;
            // Sans GPS exploitable, on situe le visiteur par son adresse IP (position approximative, sans clic).
            async function ipStart(reason) {
                say(reason + {{ \Illuminate\Support\Js::from(__(' Recherche de votre position approximative…')) }});
                try {
                    const ctl = new AbortController(); setTimeout(() => ctl.abort(), 6000);
                    const r = await (await fetch('https://ipwho.is/', { signal: ctl.signal })).json();
                    if (!r.success || typeof r.latitude !== 'number') throw new Error('ip');
                    origin = updateMe({ coords: { latitude: r.latitude, longitude: r.longitude, accuracy: 5000, heading: null, speed: null } });
                    await showRoute();
                    return true;
                } catch (e) { return false; }
            }

            async function pickStart(reason) {
                if (await ipStart(reason)) return;
                say(reason + {{ \Illuminate\Support\Js::from(__(' Touchez la carte pour indiquer votre point de départ.')) }});
                map.setOptions({ draggableCursor: 'crosshair' });
                picking?.remove();
                picking = map.addListener('click', e => {
                    picking.remove(); picking = null;
                    map.setOptions({ draggableCursor: null });
                    origin = updateMe({ coords: { latitude: e.latLng.lat(), longitude: e.latLng.lng(), accuracy: 0, heading: null, speed: null } });
                    showRoute();
                });
                root.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            $('[data-route-locate]').addEventListener('click', () => {
                if (!navigator.geolocation || !window.isSecureContext) {
                    return pickStart(window.isSecureContext ? {{ \Illuminate\Support\Js::from(__('La géolocalisation n\'est pas disponible sur cet appareil.')) }} : {{ \Illuminate\Support\Js::from(__('La géolocalisation exige une connexion sécurisée (https).')) }});
                }
                say({{ \Illuminate\Support\Js::from(__('Localisation en cours…')) }});
                navigator.geolocation.getCurrentPosition(
                    pos => { origin = updateMe(pos); showRoute(); },
                    err => pickStart(err.code === err.PERMISSION_DENIED
                        ? {{ \Illuminate\Support\Js::from(__('La localisation est refusée pour ce site.')) }}
                        : {{ \Illuminate\Support\Js::from(__('Votre position est introuvable.')) }}),
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 },
                );
            });

            // Les boutons « Itinéraire » de la page demandent le trajet sur cette carte, sans quitter le site.
            root.addEventListener('ti:route', () => { countNext = true; $('[data-route-locate]').click(); });
            root.dataset.ready = '1';

            root.querySelectorAll('[data-route-mode]').forEach(btn => btn.addEventListener('click', () => {
                mode = btn.dataset.routeMode;
                root.querySelectorAll('[data-route-mode]').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
                if (origin) (watchId === null ? showRoute() : fetchRoute().catch(() => {}));
            }));
        }

        function boot() {
            const io = new IntersectionObserver(entries => entries.forEach(e => {
                if (e.isIntersecting) { io.unobserve(e.target); initMap(e.target); }
            }), { rootMargin: '300px' });
            document.querySelectorAll('[data-route-map]').forEach(el => io.observe(el));
        }
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();

        // Bouton « Itinéraire » de la page : fait défiler jusqu'à la carte et lance le trajet depuis la position du visiteur.
        document.addEventListener('click', e => {
            const a = e.target.closest('a[data-in-page-route]');
            const root = document.querySelector('[data-route-map]');
            if (!a || !root || e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
            e.preventDefault();
            root.scrollIntoView({ behavior: 'smooth', block: 'center' });
            let tries = 0;
            const go = () => {
                if (root.dataset.ready === '1') return root.dispatchEvent(new Event('ti:route'));
                if (++tries < 60) setTimeout(go, 250);   // la carte s'initialise à l'approche : on attend au plus 15 s
            };
            go();
        });
    })();
    </script>
    @endonce
@endif
