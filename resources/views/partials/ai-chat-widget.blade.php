{{-- Assistant conversationnel (module IA) : bouton flottant + panneau de discussion. Aucun effet si l'IA ou l'assistant est coupé. --}}
@if(config('ai.enabled', true) && config('ai.chat.enabled', true))
<style>
    .ti-chat-fab { position: fixed; right: 20px; bottom: 24px; z-index: 55; display: inline-flex; align-items: center; gap: 8px; height: 52px; padding: 0 18px 0 16px; border: 0; border-radius: 999px; background: #14532d; color: #fff; font: 600 14px/1 Inter, system-ui, sans-serif; cursor: pointer; box-shadow: 0 10px 24px rgb(20 83 45 / .35); }
    .ti-chat-fab:hover { background: #1b6b3a; }
    .ti-chat-fab i { font-size: 17px; color: #f0c987; }
    .ti-chat-panel { position: fixed; right: 20px; bottom: 90px; z-index: 56; width: 380px; max-width: calc(100vw - 24px); height: 540px; max-height: calc(100vh - 120px); display: none; flex-direction: column; background: #fff; border: 1px solid #e8e1d3; border-radius: 16px; box-shadow: 0 20px 50px rgb(29 26 22 / .22); overflow: hidden; font-family: Inter, system-ui, sans-serif; }
    .ti-chat-panel.is-open { display: flex; }
    .ti-chat-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; background: #14532d; color: #fff; }
    .ti-chat-head b { font-size: 15px; }
    .ti-chat-head small { display: block; font-size: 11.5px; color: rgba(255,255,255,.75); margin-top: 1px; }
    .ti-chat-head button { background: none; border: 0; color: #fff; font-size: 18px; cursor: pointer; padding: 4px 6px; }
    .ti-chat-log { flex: 1; overflow-y: auto; padding: 14px; background: #faf8f3; display: flex; flex-direction: column; gap: 10px; }
    .ti-msg { max-width: 88%; padding: 10px 12px; border-radius: 12px; font-size: 14px; line-height: 1.5; color: #1d1a16; word-wrap: break-word; }
    .ti-msg--bot { align-self: flex-start; background: #fff; border: 1px solid #e8e1d3; border-top-left-radius: 4px; }
    .ti-msg--me { align-self: flex-end; background: #1b6b3a; color: #fff; border-top-right-radius: 4px; }
    .ti-msg sup a { color: #b9500a; font-weight: 700; text-decoration: none; padding: 0 1px; }
    .ti-src { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #ece4d4; font-size: 12.5px; }
    .ti-src a { display: block; color: #b9500a; font-weight: 600; text-decoration: none; margin-top: 2px; }
    .ti-src a:hover { text-decoration: underline; }
    .ti-rate { margin-top: 6px; display: flex; gap: 6px; align-items: center; font-size: 12px; color: #6b6458; }
    .ti-rate button { background: #fff; border: 1px solid #d8cdb8; border-radius: 8px; padding: 2px 8px; cursor: pointer; }
    .ti-rate button:hover { background: #f3eee4; }
    .ti-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .ti-chips button, .ti-contact { background: #fff; border: 1px solid #d8cdb8; color: #14532d; border-radius: 999px; padding: 5px 11px; font-size: 12.5px; cursor: pointer; }
    .ti-chips button:hover, .ti-contact:hover { background: #eef6f0; }
    .ti-chat-form { display: flex; gap: 8px; padding: 10px; border-top: 1px solid #e8e1d3; background: #fff; }
    .ti-chat-form input { flex: 1; height: 42px; border: 1px solid #d8cdb8; border-radius: 10px; padding: 0 12px; font-size: 14px; color: #1d1a16; background: #fff; }
    .ti-chat-form input:focus { outline: none; border-color: #b9500a; box-shadow: 0 0 0 3px rgb(185 80 10 / .18); }
    .ti-chat-form button { width: 42px; height: 42px; border: 0; border-radius: 10px; background: #b9500a; color: #fff; cursor: pointer; }
    .ti-chat-form button:disabled { opacity: .5; }
    .ti-chat-note { padding: 0 12px 8px; background: #fff; font-size: 11px; color: #8a7f6b; }
    @media (max-width: 767px) {
        .ti-chat-fab { right: 14px; bottom: calc(86px + env(safe-area-inset-bottom)); height: 46px; padding: 0 14px; }
        .ti-chat-fab span { display: none; }
        .ti-chat-panel { inset: 0; width: auto; max-width: none; height: auto; max-height: none; border-radius: 0; border: 0; }
    }
    @media print { .ti-chat-fab, .ti-chat-panel { display: none !important; } }
</style>
<button type="button" id="ti-chat-fab" class="ti-chat-fab" aria-label="{{ __('Ouvrir l\'assistant') }}" aria-expanded="false" aria-controls="ti-chat-panel"><i class="fas fa-comments"></i><span>{{ __('Assistant') }}</span></button>
<section id="ti-chat-panel" class="ti-chat-panel" role="dialog" aria-label="{{ __('Assistant Trésors d\'Ivoire') }}">
    <header class="ti-chat-head">
        <div><b>{{ __('Assistant Trésors d\'Ivoire') }}</b><small>{{ __('Réponses tirées du contenu du site') }}</small></div>
        <button type="button" id="ti-chat-close" aria-label="{{ __('Fermer') }}">&times;</button>
    </header>
    <div id="ti-chat-log" class="ti-chat-log" aria-live="polite"></div>
    <form id="ti-chat-form" class="ti-chat-form" autocomplete="off">
        <input id="ti-chat-input" type="text" maxlength="{{ (int) config('ai.chat.max_question', 300) }}" placeholder="{{ __('Votre question…') }}" aria-label="{{ __('Votre question') }}">
        <button type="submit" aria-label="{{ __('Envoyer') }}"><i class="fas fa-paper-plane"></i></button>
    </form>
    <p class="ti-chat-note">{{ __('Assistant automatique : il cite ses sources et peut se tromper. Pour une demande personnelle, contactez l\'équipe.') }}</p>
</section>
<script>
(function () {
    var URL_ASK = @json(route('ai.chat.ask'));
    var URL_FB = @json(route('ai.chat.feedback'));
    var TOKEN = @json(csrf_token());
    var fab = document.getElementById('ti-chat-fab'), panel = document.getElementById('ti-chat-panel');
    var log = document.getElementById('ti-chat-log'), form = document.getElementById('ti-chat-form'), input = document.getElementById('ti-chat-input');
    var history = [], started = false;
    try { history = JSON.parse(sessionStorage.getItem('ti_chat_hist') || '[]').slice(-4); } catch (e) {}

    function save() { try { sessionStorage.setItem('ti_chat_hist', JSON.stringify(history.slice(-4))); } catch (e) {} }
    function open() { panel.classList.add('is-open'); fab.setAttribute('aria-expanded', 'true'); if (!started) { start(); } setTimeout(function () { input.focus(); }, 50); }
    function close() { panel.classList.remove('is-open'); fab.setAttribute('aria-expanded', 'false'); }
    fab.addEventListener('click', function () { panel.classList.contains('is-open') ? close() : open(); });
    document.getElementById('ti-chat-close').addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    function bubble(who) { var d = document.createElement('div'); d.className = 'ti-msg ti-msg--' + who; log.appendChild(d); return d; }
    function scroll() { log.scrollTop = log.scrollHeight; }

    // Texte avec renvois [n] devenant des liens vers les sources (jamais de HTML injecté).
    function fill(el, text, sources) {
        var re = /\[(\d+)\]/g, last = 0, m;
        while ((m = re.exec(text))) {
            el.appendChild(document.createTextNode(text.slice(last, m.index)));
            var s = (sources || [])[parseInt(m[1], 10) - 1];
            var sup = document.createElement('sup');
            if (s) { var a = document.createElement('a'); a.href = s.url; a.textContent = '[' + m[1] + ']'; a.title = s.title; sup.appendChild(a); } else { sup.textContent = m[0]; }
            el.appendChild(sup);
            last = m.index + m[0].length;
        }
        el.appendChild(document.createTextNode(text.slice(last)));
    }

    function chips(el, list) {
        if (!list || !list.length) return;
        var box = document.createElement('div'); box.className = 'ti-chips';
        list.forEach(function (q) { var b = document.createElement('button'); b.type = 'button'; b.textContent = q; b.addEventListener('click', function () { send(q); }); box.appendChild(b); });
        el.appendChild(box);
    }

    function start() {
        started = true;
        var b = bubble('bot');
        b.textContent = {{ \Illuminate\Support\Js::from(__('Bonjour ! Je réponds à vos questions sur les destinations, hébergements, restaurants, événements et le fonctionnement du site.')) }};
        chips(b, [{{ \Illuminate\Support\Js::from(__('Où dormir à Grand-Bassam ?')) }}, {{ \Illuminate\Support\Js::from(__('Que visiter à Abidjan ?')) }}, 'Comment devenir prestataire ?']);
    }

    function render(r) {
        var b = bubble('bot');
        fill(b, r.answer, r.sources);
        if (r.sources && r.sources.length) {
            var box = document.createElement('div'); box.className = 'ti-src';
            box.appendChild(document.createTextNode('Sources :'));
            r.sources.forEach(function (s) {
                var a = document.createElement('a'); a.href = s.url; a.textContent = '[' + s.n + '] ' + s.title;
                if (s.maps) { a.target = '_blank'; a.rel = 'noopener'; a.appendChild(document.createTextNode(' · Google Maps')); }
                box.appendChild(a);
            });
            b.appendChild(box);
        }
        // Mini-carte Google (réponses ancrées sur Google Maps) : rendue par l'API Maps JavaScript.
        if (r.maps_widget && MAPS_KEY) { mapsWidget(b, r.maps_widget); }
        if (r.handoff && typeof window.openContactModal === 'function') {
            var c = document.createElement('button'); c.type = 'button'; c.className = 'ti-contact'; c.style.marginTop = '8px'; c.style.display = 'block';
            c.textContent = {{ \Illuminate\Support\Js::from(__('Contacter l\'équipe')) }}; c.addEventListener('click', function () { close(); window.openContactModal(); });
            b.appendChild(c);
        }
        if (r.id && r.status === 'answered') {
            var rate = document.createElement('div'); rate.className = 'ti-rate'; rate.appendChild(document.createTextNode({{ \Illuminate\Support\Js::from(__('Cette réponse vous aide ?')) }}));
            [[1, '👍'], [-1, '👎']].forEach(function (p) {
                var btn = document.createElement('button'); btn.type = 'button'; btn.textContent = p[1];
                btn.addEventListener('click', function () {
                    fetch(URL_FB, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN }, body: JSON.stringify({ id: r.id, rating: p[0] }) }).catch(function () {});
                    rate.textContent = {{ \Illuminate\Support\Js::from(__('Merci pour votre retour.')) }};
                });
                rate.appendChild(btn);
            });
            b.appendChild(rate);
        }
        if (r.status === 'no_source') chips(b, r.suggestions);
        scroll();
    }

    var MAPS_KEY = @json(config('services.google_maps.browser_key'));
    var mapsLoad = null;
    function mapsWidget(el, token) {
        mapsLoad = mapsLoad || new Promise(function (res) {
            if (window.google && google.maps && google.maps.importLibrary) return res();
            window.__tiChatMaps = res;
            var s = document.createElement('script');
            s.src = 'https://maps.googleapis.com/maps/api/js?' + new URLSearchParams({ key: MAPS_KEY, loading: 'async', callback: '__tiChatMaps', v: 'weekly' });
            s.async = true; document.head.appendChild(s);
        });
        mapsLoad.then(function () { return google.maps.importLibrary('places'); }).then(function () {
            var w = document.createElement('gmp-place-contextual');
            w.setAttribute('context-token', token);
            w.style.display = 'block'; w.style.marginTop = '8px';
            el.appendChild(w); scroll();
        }).catch(function () {});
    }
    // Position déjà autorisée sur le site : elle affine les lieux proposés par Google Maps.
    var pos = null;
    try { navigator.permissions && navigator.permissions.query({ name: 'geolocation' }).then(function (p) {
        if (p.state === 'granted') navigator.geolocation.getCurrentPosition(function (g) { pos = { lat: g.coords.latitude, lng: g.coords.longitude }; }, function () {}, { maximumAge: 300000 });
    }).catch(function () {}); } catch (e) {}

    function send(text) {
        text = (text || '').trim();
        if (!text) return;
        var me = bubble('me'); me.textContent = text; scroll();
        input.value = ''; var btn = form.querySelector('button'); btn.disabled = true;
        var wait = bubble('bot'); wait.textContent = '…'; scroll();
        fetch(URL_ASK, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN }, body: JSON.stringify(Object.assign({ message: text, history: history }, pos || {})) })
            .then(function (res) { if (!res.ok) throw new Error(res.status); return res.json(); })
            .then(function (r) {
                wait.remove(); render(r);
                history.push({ role: 'user', text: text }); history.push({ role: 'assistant', text: r.answer.slice(0, 500) }); history = history.slice(-4); save();
            })
            .catch(function () { wait.textContent = {{ \Illuminate\Support\Js::from(__('Service momentanément indisponible. Réessayez dans un instant.')) }}; })
            .then(function () { btn.disabled = false; input.focus(); });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); send(input.value); });
})();
</script>
@endif
