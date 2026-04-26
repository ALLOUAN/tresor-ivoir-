{{-- TinyMCE + auto-sauvegarde (édition) / brouillon local (création) + prévisualisation --}}
@php
    $editorMode = isset($event) && $event ? 'edit' : 'create';
    $autosaveUrl = $editorMode === 'edit' ? route('editor.events.autosave', $event) : null;
    $previewUrl = $editorMode === 'edit' ? route('editor.events.preview', $event) : null;
    $localKey = 'tresor_editor_event_draft_v1';
    $errorsPresent = $errorsPresent ?? false;
@endphp

@if($editorMode === 'create')
<div id="event-preview-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="event-preview-modal-title">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
            <h2 id="event-preview-modal-title" class="text-sm font-semibold text-white">Prévisualisation</h2>
            <button type="button" onclick="closeEventPreviewModal()" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="event-preview-body" class="overflow-y-auto p-5 text-slate-300 prose prose-invert prose-sm max-w-none"></div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const AUTOSAVE_URL = @json($autosaveUrl);
    const PREVIEW_URL = @json($previewUrl);
    const LOCAL_KEY = @json($localKey);
    const ERRORS_PRESENT = @json($errorsPresent);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const PAYLOAD_KEYS = [
        'title_fr', 'title_en', 'slug', 'category_id', 'description_fr', 'description_en',
        'cover_url', 'cover_alt', 'ticket_url', 'starts_at', 'ends_at', 'location_name', 'city', 'address',
        'latitude', 'longitude', 'provider_id', 'organizer_name', 'organizer_phone', 'organizer_email',
        'status', 'capacity', 'registration_deadline', 'timezone', 'recurrence_rule',
        'price', 'meta_title_fr', 'meta_desc_fr', 'meta_title_en', 'meta_desc_en',
    ];

    function collectEventPayload(form) {
        const data = {};
        PAYLOAD_KEYS.forEach(function (k) {
            const el = form.querySelector('[name="' + k + '"]');
            if (!el) return;
            if (el.type === 'checkbox') return;
            data[k] = el.value;
        });
        const isFree = form.querySelector('input[name="is_free"]');
        data.is_free = !!(isFree && isFree.checked);
        const isRec = form.querySelector('#is_recurring');
        data.is_recurring = !!(isRec && isRec.checked);
        return data;
    }

    let autosaveTimer = null;
    let localTimer = null;

    function scheduleAutosave() {
        if (!AUTOSAVE_URL) return;
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(runAutosave, 2600);
    }

    function runAutosave() {
        const form = document.getElementById('eventEditorForm');
        if (!form || !AUTOSAVE_URL) return;
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        const payload = collectEventPayload(form);
        const statusEl = document.getElementById('autosaveStatus');
        fetch(AUTOSAVE_URL, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        }).then(function (r) {
            if (!r.ok) throw new Error('save');
            return r.json();
        }).then(function () {
            if (statusEl) {
                statusEl.textContent = 'Brouillon enregistré';
                statusEl.classList.remove('text-rose-400');
                statusEl.classList.add('text-emerald-400/90');
            }
        }).catch(function () {
            if (statusEl) {
                statusEl.textContent = 'Erreur auto-sauvegarde';
                statusEl.classList.add('text-rose-400');
                statusEl.classList.remove('text-emerald-400/90');
            }
        });
    }

    function scheduleLocalDraft() {
        if (AUTOSAVE_URL) return;
        clearTimeout(localTimer);
        localTimer = setTimeout(saveLocalDraft, 2000);
    }

    function saveLocalDraft() {
        const form = document.getElementById('eventEditorForm');
        if (!form || !LOCAL_KEY) return;
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        try {
            const data = collectEventPayload(form);
            data._savedAt = new Date().toISOString();
            localStorage.setItem(LOCAL_KEY, JSON.stringify(data));
            const st = document.getElementById('autosaveStatus');
            if (st) {
                st.textContent = 'Brouillon local enregistré';
                st.classList.add('text-sky-400/90');
            }
        } catch (e) {}
    }

    function restoreLocalDraft() {
        if (AUTOSAVE_URL || !LOCAL_KEY || ERRORS_PRESENT) return;
        var raw;
        try { raw = localStorage.getItem(LOCAL_KEY); } catch (e) { return; }
        if (!raw) return;
        if (!confirm('Un brouillon local a été trouvé. Voulez-vous le restaurer ?')) return;
        var parsed;
        try { parsed = JSON.parse(raw); } catch (e) { return; }
        const form = document.getElementById('eventEditorForm');
        if (!form) return;
        Object.keys(parsed).forEach(function (k) {
            if (k === '_savedAt' || k === 'is_free' || k === 'is_recurring') return;
            const el = form.querySelector('[name="' + k + '"]');
            if (el && typeof parsed[k] === 'string') el.value = parsed[k];
        });
        if (typeof parsed.is_free === 'boolean') {
            const cb = form.querySelector('input[name="is_free"]');
            if (cb) cb.checked = parsed.is_free;
        }
        if (typeof parsed.is_recurring === 'boolean') {
            const cb = form.querySelector('#is_recurring');
            if (cb) cb.checked = parsed.is_recurring;
            const grp = document.getElementById('recurrence_group');
            if (grp) grp.classList.toggle('hidden', !parsed.is_recurring);
        }
        if (typeof tinymce !== 'undefined') {
            ['description_fr', 'description_en'].forEach(function (id) {
                const ed = tinymce.get(id);
                const ta = document.getElementById(id);
                if (ed && ta) ed.setContent(ta.value || '');
            });
        }
    }

    window.openEventPreviewModal = function () {
        const modal = document.getElementById('event-preview-modal');
        const body = document.getElementById('event-preview-body');
        if (!modal || !body) return;
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        const form = document.getElementById('eventEditorForm');
        const title = (form && form.querySelector('[name="title_fr"]')) ? form.querySelector('[name="title_fr"]').value : 'Sans titre';
        const starts = (form && form.querySelector('[name="starts_at"]')) ? form.querySelector('[name="starts_at"]').value : '';
        const loc = (form && form.querySelector('[name="location_name"]')) ? form.querySelector('[name="location_name"]').value : '';
        const city = (form && form.querySelector('[name="city"]')) ? form.querySelector('[name="city"]').value : '';
        const html = document.getElementById('description_fr') ? document.getElementById('description_fr').value : '';
        const esc = function (s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
        body.innerHTML = '<h1 class="text-2xl font-bold text-white mb-2 font-serif">' + esc(title) + '</h1>'
            + '<p class="text-slate-500 text-sm mb-4">' + esc(starts) + (loc || city ? ' · ' + esc([loc, city].filter(Boolean).join(' · ')) : '') + '</p>'
            + '<iframe class="w-full min-h-[280px] rounded-lg border border-slate-800 bg-slate-950" sandbox="" referrerpolicy="no-referrer" title="Aperçu contenu"></iframe>';
        const iframe = body.querySelector('iframe');
        if (iframe) {
            iframe.srcdoc = '<!DOCTYPE html><meta charset="utf-8"><style>body{font-family:system-ui,sans-serif;padding:1rem;background:#0f172a;color:#e2e8f0;line-height:1.65;} a{color:#fbbf24;}</style><div class="c">' + html + '</div>';
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeEventPreviewModal = function () {
        const modal = document.getElementById('event-preview-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.openEventPreviewTab = function () {
        if (PREVIEW_URL) window.open(PREVIEW_URL, '_blank', 'noopener');
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce === 'undefined') return;
        if (!document.getElementById('description_fr') && !document.getElementById('description_en')) return;

        tinymce.init({
            selector: '#description_fr,#description_en',
            height: 280,
            menubar: false,
            branding: false,
            promotion: false,
            plugins: 'lists link autoresize code',
            toolbar: 'undo redo | bold italic | bullist numlist | link | removeformat | code',
            skin: 'oxide-dark',
            content_css: 'dark',
            setup: function (editor) {
                editor.on('init change keyup Undo Redo', function () {
                    scheduleAutosave();
                    scheduleLocalDraft();
                });
            },
        });

        const form = document.getElementById('eventEditorForm');
        if (form) {
            form.addEventListener('submit', function () {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            });
            PAYLOAD_KEYS.forEach(function (k) {
                const el = form.querySelector('[name="' + k + '"]');
                if (el && el.type !== 'file') {
                    el.addEventListener('input', function () { scheduleAutosave(); scheduleLocalDraft(); });
                    el.addEventListener('change', function () { scheduleAutosave(); scheduleLocalDraft(); });
                }
            });
            const isFree = form.querySelector('input[name="is_free"]');
            if (isFree) {
                isFree.addEventListener('change', function () { scheduleAutosave(); scheduleLocalDraft(); });
            }
            const isRec = form.querySelector('#is_recurring');
            if (isRec) {
                isRec.addEventListener('change', function () { scheduleAutosave(); scheduleLocalDraft(); });
            }
        }

        if (!AUTOSAVE_URL) {
            restoreLocalDraft();
        }
    });
})();
</script>
