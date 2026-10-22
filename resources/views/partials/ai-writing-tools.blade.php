{{--
    Aide à la rédaction et à la traduction. Paramètre : $aiWriting = ['actions' => [...]] (voir les formulaires).
    Chaque action lit des champs du formulaire, appelle /ia/redaction et PROPOSE des valeurs : rien n'est
    enregistré ni modifié tant que l'utilisateur n'a pas cliqué sur « Appliquer ».
--}}
@if(config('ai.enabled', true) && config('ai.writing.enabled', true) && ! empty($aiWriting['actions']))
@php $__llm = app(\App\Services\Ai\WritingAssistant::class)->llmAvailable(); @endphp
<div id="ai-writing" class="mb-5 rounded-xl border border-slate-800 bg-green-900 p-4">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-wand-magic-sparkles text-orange-500/70"></i> Assistant de rédaction
        </h3>
        <p class="text-[11px] text-slate-500">Suggestions à relire : rien n'est appliqué sans votre accord.{{ $__llm ? '' : ' Traduction et titres : nécessitent un modèle de langage (non configuré).' }}</p>
    </div>
    <div class="flex flex-wrap gap-2" id="ai-writing-buttons">
        @foreach($aiWriting['actions'] as $a)
            <button type="button" data-ai-action="{{ $a['id'] }}"
                    @if(! $__llm && in_array($a['action'], ['translate', 'titles'], true)) title="Nécessite un modèle de langage (ANTHROPIC_API_KEY)" @endif
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition {{ (! $__llm && in_array($a['action'], ['translate', 'titles'], true)) ? 'opacity-60' : '' }}">
                <i class="fas {{ $a['icon'] ?? 'fa-wand-magic-sparkles' }} text-[10px] text-orange-400"></i> {{ $a['label'] }}
            </button>
        @endforeach
    </div>
    <div id="ai-writing-result" class="hidden mt-4 rounded-lg border border-slate-700 bg-slate-800/60 p-3 text-sm text-slate-300" role="status" aria-live="polite"></div>
</div>
<script>
(function () {
    var CFG = @json($aiWriting);
    var URL_RUN = @json(route('ai.writing.run'));
    var URL_APPLIED = @json(url('/ia/redaction'));
    var TOKEN = @json(csrf_token());
    var box = document.getElementById('ai-writing-result');
    var form = document.getElementById('ai-writing').closest('form') || document;

    function el(name) { return form.querySelector('[name="' + name + '"]') || document.querySelector('[name="' + name + '"]'); }
    function isRich(node) { return node && node.classList.contains('rich-editor') && window.RichEditor && window.RichEditor.available && window.RichEditor.available(); }
    function read(name) {
        var n = el(name); if (!n) return '';
        if (isRich(n) && window.RichEditor.sync) window.RichEditor.sync();
        return n.value || '';
    }
    function write(name, value) {
        var n = el(name); if (!n) return;
        if (isRich(n)) { window.RichEditor.setHtml(n.id, value); if (window.RichEditor.sync) window.RichEditor.sync(); }
        else { n.value = value; n.dispatchEvent(new Event('input', { bubbles: true })); n.dispatchEvent(new Event('change', { bubbles: true })); }
    }
    function clear() { while (box.firstChild) box.removeChild(box.firstChild); }
    function say(text, tone) { clear(); box.classList.remove('hidden'); var p = document.createElement('p'); p.textContent = text; p.style.color = tone === 'err' ? '#f87171' : ''; box.appendChild(p); }
    function plain(html) { var d = document.createElement('div'); d.innerHTML = html; return (d.textContent || '').trim(); }

    function row(label, value, targetName, logId, isHtml) {
        var r = document.createElement('div'); r.className = 'py-2 border-t border-slate-700/70 first:border-0';
        var h = document.createElement('p'); h.className = 'text-xs text-slate-500 mb-1'; h.textContent = label; r.appendChild(h);
        var v = document.createElement('p'); v.className = 'text-slate-200 whitespace-pre-line'; v.textContent = isHtml ? plain(value).slice(0, 600) + (plain(value).length > 600 ? '…' : '') : value; r.appendChild(v);
        var existing = read(targetName);
        var btn = document.createElement('button'); btn.type = 'button';
        btn.className = 'mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold';
        btn.textContent = existing.trim() ? 'Appliquer (remplace le texte actuel)' : 'Appliquer';
        btn.addEventListener('click', function () {
            write(targetName, value);
            btn.textContent = 'Appliqué ✓'; btn.disabled = true; btn.style.opacity = '.6';
            if (logId) fetch(URL_APPLIED + '/' + logId + '/appliquee', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } }).catch(function () {});
        });
        r.appendChild(btn);
        return r;
    }

    function show(action, res) {
        clear(); box.classList.remove('hidden');
        var head = document.createElement('p'); head.className = 'text-xs text-slate-500 mb-2';
        head.textContent = (res.engine === 'anthropic' ? 'Proposé par un modèle de langage' : 'Proposé par extraction du texte') + ' — à relire avant d\'appliquer.';
        box.appendChild(head);
        (res.warnings || []).forEach(function (w) { var p = document.createElement('p'); p.className = 'text-xs mb-1'; p.style.color = '#fbbf24'; p.textContent = '⚠ ' + w; box.appendChild(p); });
        var r = res.result || {};
        if (action.action === 'titles') {
            (r.titles || []).forEach(function (t, i) { box.appendChild(row('Proposition ' + (i + 1), t, action.apply.titles, res.log_id, false)); });
        } else if (action.action === 'translate') {
            Object.keys(action.map).forEach(function (k) { if (r[k]) box.appendChild(row(action.map[k][2] + ' (' + action.to.toUpperCase() + ')', r[k], action.map[k][1], res.log_id, k === 'content')); });
        } else {
            Object.keys(action.apply).forEach(function (k) { if (r[k]) box.appendChild(row(action.apply[k][1], r[k], action.apply[k][0], res.log_id, false)); });
        }
        var close = document.createElement('button'); close.type = 'button'; close.className = 'mt-3 text-xs text-slate-500 hover:text-white underline'; close.textContent = 'Fermer';
        close.addEventListener('click', function () { box.classList.add('hidden'); });
        box.appendChild(close);
    }

    document.getElementById('ai-writing-buttons').addEventListener('click', function (e) {
        var b = e.target.closest('[data-ai-action]'); if (!b) return;
        var action = CFG.actions.filter(function (a) { return a.id === b.getAttribute('data-ai-action'); })[0]; if (!action) return;
        var payload = { action: action.action, lang: action.lang || 'fr' };
        if (action.action === 'translate') {
            payload.from = action.from; payload.to = action.to; payload.texts = {};
            Object.keys(action.map).forEach(function (k) { var v = read(action.map[k][0]); if (v.trim()) payload.texts[k] = v; });
            if (!Object.keys(payload.texts).length) return say('Rien à traduire : le texte source est vide.', 'err');
        } else {
            payload.title = read(action.payload.title || ''); payload.content = read(action.payload.content || '');
        }
        say('Analyse en cours…'); b.disabled = true;
        fetch(URL_RUN, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN }, body: JSON.stringify(payload) })
            .then(function (res) { return res.json().then(function (j) { return { ok: res.ok, j: j }; }); })
            .then(function (x) { if (x.ok && x.j.ok) show(action, x.j); else say(x.j.message || (x.j.errors ? Object.values(x.j.errors)[0][0] : 'Suggestion indisponible.'), 'err'); })
            .catch(function () { say('Service momentanément indisponible. Réessayez dans un instant.', 'err'); })
            .then(function () { b.disabled = false; });
    });
})();
</script>
@endif
