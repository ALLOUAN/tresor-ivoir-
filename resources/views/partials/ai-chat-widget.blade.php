{{-- Assistant conversationnel (module IA) : bouton flottant + panneau de discussion. Aucun effet si l'IA ou l'assistant est coupé.
     Design : pastille flottante vert forêt, en-tête à motifs discrets, bulles de messagerie, suggestions en lignes cliquables,
     saisie en pilule. Aplats (aucun dégradé). Identifiants et script inchangés. --}}
@if(config('ai.enabled', true) && config('ai.chat.enabled', true))
<style>
    /* ── Bouton flottant ── */
    .ti-chat-fab { position: fixed; right: 20px; bottom: 24px; z-index: 55; display: inline-flex; align-items: center; gap: 10px; height: 54px; padding: 0 20px 0 7px; border: 0; border-radius: 999px; background: #00874F; color: #fff; font: 600 14px/1 Montserrat, system-ui, sans-serif; cursor: pointer; box-shadow: 0 14px 30px -12px rgb(0 135 79 / .55); transition: transform .2s ease, background .2s ease; }
    .ti-chat-fab:hover { background: #00874F; transform: translateY(-2px); }
    .ti-chat-fab:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
    .ti-chat-fab__avatar { position: relative; width: 40px; height: 40px; border-radius: 999px; background: #EB6E00; display: inline-flex; align-items: center; justify-content: center; }
    .ti-chat-fab__avatar i { font-size: 16px; color: #fff; }
    .ti-chat-fab__avatar::after { content: ''; position: absolute; right: -1px; bottom: -1px; width: 11px; height: 11px; border-radius: 999px; background: #00874F; border: 2px solid #00874F; }
    .ti-chat-fab[aria-expanded="true"] .ti-chat-fab__avatar i::before { content: '\f00d'; }

    /* ── Panneau ── */
    .ti-chat-panel { position: fixed; right: 20px; bottom: 92px; z-index: 56; width: 400px; max-width: calc(100vw - 24px); height: 600px; max-height: calc(100vh - 120px); display: none; flex-direction: column; background: #F3F8F5; border: 1px solid #D8E9E0; border-radius: 24px; box-shadow: 0 30px 70px -24px rgb(14 59 42 / .45); overflow: hidden; font-family: Montserrat, system-ui, sans-serif; }
    .ti-chat-panel.is-open { display: flex; animation: ti-chat-in .22s ease-out; }
    @keyframes ti-chat-in { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }

    .ti-chat-head { position: relative; overflow: hidden; display: flex; align-items: center; gap: 12px; padding: 16px 16px 16px 18px; background: #00874F; color: #fff; }
    .ti-chat-head > .ivm .ivm-g { opacity: .08 !important; }
    .ti-chat-head > .ivm .ivm-g > * { stroke: #D8E9E0 !important; }
    .ti-chat-head > :not(.ivm) { position: relative; z-index: 1; }
    .ti-chat-head__avatar { position: relative; flex: none; width: 44px; height: 44px; border-radius: 14px; background: #EB6E00; display: inline-flex; align-items: center; justify-content: center; }
    .ti-chat-head__avatar i { font-size: 18px; color: #fff; }
    .ti-chat-head__txt { flex: 1; min-width: 0; }
    .ti-chat-head b { display: block; font-family: 'Montserrat', Georgia, serif; font-size: 17px; font-weight: 700; line-height: 1.2; }
    .ti-chat-head small { display: inline-flex; align-items: center; gap: 6px; margin-top: 3px; font-size: 12px; color: rgba(255,255,255,.78); }
    .ti-chat-head small::before { content: ''; width: 7px; height: 7px; border-radius: 999px; background: #00874F; }
    .ti-chat-head button { flex: none; width: 36px; height: 36px; border: 0; border-radius: 999px; background: rgba(255,255,255,.12); color: #fff; font-size: 20px; line-height: 1; cursor: pointer; transition: background .15s ease; }
    .ti-chat-head button:hover { background: rgba(255,255,255,.22); }
    .ti-chat-head button:focus-visible { outline: 2px solid #F7C396; outline-offset: 2px; }

    /* ── Fil de discussion ── */
    .ti-chat-log { flex: 1; overflow-y: auto; padding: 18px 16px; background: #F3F8F5; display: flex; flex-direction: column; gap: 12px; scrollbar-width: thin; }
    .ti-msg { max-width: 86%; padding: 11px 14px; border-radius: 18px; font-size: 14px; line-height: 1.55; color: #0E3B2A; word-wrap: break-word; }
    .ti-msg--bot { align-self: flex-start; background: #fff; border: 1px solid #D8E9E0; border-top-left-radius: 6px; }
    .ti-msg--me { align-self: flex-end; background: #EB6E00; color: #fff; border-top-right-radius: 6px; }
    .ti-msg sup a { color: #EB6E00; font-weight: 700; text-decoration: none; padding: 0 1px; }
    .ti-src { margin-top: 10px; padding-top: 10px; border-top: 1px solid #F3F8F5; font-size: 12.5px; color: #557466; }
    .ti-src a { display: flex; align-items: center; gap: 6px; margin-top: 6px; padding: 7px 10px; border-radius: 10px; background: #F3F8F5; color: #B85600; font-weight: 600; text-decoration: none; }
    .ti-src a:hover { background: #F3F8F5; }
    .ti-rate { margin-top: 10px; display: flex; gap: 6px; align-items: center; font-size: 12px; color: #557466; }
    .ti-rate button { width: 32px; height: 28px; background: #fff; border: 1px solid #D8E9E0; border-radius: 999px; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .ti-rate button:hover { background: #F3F8F5; border-color: #EB6E00; }

    /* Suggestions : lignes cliquables avec flèche */
    .ti-chips { display: flex; flex-direction: column; gap: 6px; margin-top: 12px; }
    .ti-chips button { display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; padding: 10px 12px; border: 1px solid #D8E9E0; border-radius: 12px; background: #F3F8F5; color: #0E3B2A; font: 600 13px/1.3 Montserrat, system-ui, sans-serif; text-align: left; cursor: pointer; transition: border-color .15s ease, background .15s ease, color .15s ease; }
    .ti-chips button::after { content: '\2192'; flex: none; color: #EB6E00; font-weight: 700; transition: transform .15s ease; }
    .ti-chips button:hover { border-color: #EB6E00; background: #fff; color: #B85600; }
    .ti-chips button:hover::after { transform: translateX(3px); }
    .ti-contact { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border: 0; border-radius: 999px; background: #00874F; color: #fff; font: 600 12.5px/1 Montserrat, system-ui, sans-serif; cursor: pointer; }
    .ti-contact:hover { background: #00874F; }

    /* Indicateur « en train d'écrire » */
    .ti-msg.is-typing { display: inline-flex; gap: 5px; padding: 14px 16px; }
    .ti-msg.is-typing span { width: 7px; height: 7px; border-radius: 999px; background: #8AA99B; animation: ti-dot 1.1s infinite ease-in-out; }
    .ti-msg.is-typing span:nth-child(2) { animation-delay: .15s; }
    .ti-msg.is-typing span:nth-child(3) { animation-delay: .3s; }
    @keyframes ti-dot { 0%, 80%, 100% { opacity: .35; transform: translateY(0); } 40% { opacity: 1; transform: translateY(-3px); } }

    /* ── Saisie ── */
    .ti-chat-form { display: flex; align-items: center; gap: 6px; margin: 0 12px; padding: 6px 6px 6px 16px; border: 1px solid #D8E9E0; border-radius: 999px; background: #fff; transition: border-color .15s ease, box-shadow .15s ease; }
    .ti-chat-form:focus-within { border-color: #EB6E00; box-shadow: 0 0 0 4px rgb(235 110 0 / .12); }
    .ti-chat-form input { flex: 1; min-width: 0; height: 40px; border: 0; padding: 0; font-size: 14px; color: #0E3B2A; background: transparent; }
    .ti-chat-form input:focus { outline: none; }
    #ti-chat-form #ti-chat-input { border: 0 !important; box-shadow: none !important; background: transparent !important; border-radius: 0 !important; padding: 0 !important; }
    .ti-chat-form button { flex: none; width: 40px; height: 40px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; cursor: pointer; transition: background .15s ease, transform .15s ease; }
    .ti-chat-form button:hover { background: #B85600; }
    .ti-chat-form button:disabled { opacity: .45; cursor: default; }
    .ti-chat-foot { padding: 10px 0 12px; background: #F3F8F5; border-top: 1px solid #D8E9E0; }
    .ti-chat-note { margin: 8px 16px 0; font-size: 11px; line-height: 1.4; text-align: center; color: #557466; }

    @media (max-width: 767px) {
        .ti-chat-fab { right: 14px; bottom: calc(86px + env(safe-area-inset-bottom)); height: 50px; padding: 0 5px; }
        .ti-chat-fab span:not(.ti-chat-fab__avatar) { display: none; }
        .ti-chat-panel { inset: 0; width: auto; max-width: none; height: auto; max-height: none; border-radius: 0; border: 0; }
        .ti-chat-head { padding-top: calc(14px + env(safe-area-inset-top)); }
        .ti-chat-foot { padding-bottom: calc(12px + env(safe-area-inset-bottom)); }
        .ti-msg { max-width: 90%; }
    }
    @media (prefers-reduced-motion: reduce) { .ti-chat-panel.is-open, .ti-msg.is-typing span { animation: none; } .ti-chat-fab, .ti-chips button::after { transition: none; } }
    @media print { .ti-chat-fab, .ti-chat-panel { display: none !important; } }
</style>
<button type="button" id="ti-chat-fab" class="ti-chat-fab" aria-label="{{ __('Ouvrir l\'assistant') }}" aria-expanded="false" aria-controls="ti-chat-panel"><span class="ti-chat-fab__avatar" aria-hidden="true"><i class="fas fa-wand-magic-sparkles"></i></span><span>{{ __('Assistant') }}</span></button>
<section id="ti-chat-panel" class="ti-chat-panel" role="dialog" aria-label="{{ __('Assistant Trésors d\'Ivoire') }}">
    <header class="ti-chat-head">
        @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
        <span class="ti-chat-head__avatar" aria-hidden="true"><i class="fas fa-wand-magic-sparkles"></i></span>
        <div class="ti-chat-head__txt"><b>{{ __('Assistant Trésors d\'Ivoire') }}</b><small>{{ __('Répond à partir du contenu du site') }}</small></div>
        <button type="button" id="ti-chat-close" aria-label="{{ __('Fermer') }}">&times;</button>
    </header>
    <div id="ti-chat-log" class="ti-chat-log" aria-live="polite"></div>
    <div class="ti-chat-foot">
        <form id="ti-chat-form" class="ti-chat-form" autocomplete="off">
            <input id="ti-chat-input" type="text" maxlength="{{ (int) config('ai.chat.max_question', 300) }}" placeholder="{{ __('Posez votre question…') }}" aria-label="{{ __('Votre question') }}">
            <button type="submit" aria-label="{{ __('Envoyer') }}"><i class="fas fa-arrow-up"></i></button>
        </form>
        <p class="ti-chat-note">{{ __('Assistant automatique : il cite ses sources et peut se tromper. Pour une demande personnelle, contactez l\'équipe.') }}</p>
    </div>
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
        var wait = bubble('bot'); wait.classList.add('is-typing'); wait.setAttribute('aria-label', 'Rédaction en cours'); for (var i = 0; i < 3; i++) wait.appendChild(document.createElement('span')); scroll();
        fetch(URL_ASK, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN }, body: JSON.stringify(Object.assign({ message: text, history: history }, pos || {})) })
            .then(function (res) { if (!res.ok) throw new Error(res.status); return res.json(); })
            .then(function (r) {
                wait.remove(); render(r);
                history.push({ role: 'user', text: text }); history.push({ role: 'assistant', text: r.answer.slice(0, 500) }); history = history.slice(-4); save();
            })
            .catch(function () { wait.classList.remove('is-typing'); wait.textContent = {{ \Illuminate\Support\Js::from(__('Service momentanément indisponible. Réessayez dans un instant.')) }}; })
            .then(function () { btn.disabled = false; input.focus(); });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); send(input.value); });
})();
</script>
@endif
