{{-- Summernote + auto-sauvegarde (édition) / brouillon local (création) + prévisualisation --}}
@php
    $editorMode = isset($article) ? 'edit' : 'create';
    $autosaveUrl = $editorMode === 'edit' ? route('editor.articles.autosave', $article) : null;
    $previewUrl = $editorMode === 'edit' ? route('editor.articles.preview', $article) : null;
    $localKey = 'tresor_editor_article_draft_v1';
    $errorsPresent = $errorsPresent ?? false;
@endphp

@include('editor.partials.summernote-assets')

@if($editorMode === 'create')
<div id="article-preview-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4 bg-green-950/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="preview-modal-title">
    <div class="bg-green-900 border border-slate-700 rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
            <h2 id="preview-modal-title" class="text-sm font-semibold text-white">Prévisualisation</h2>
            <button type="button" onclick="closeArticlePreviewModal()" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="article-preview-body" class="overflow-y-auto p-5 text-slate-300 prose prose-invert prose-sm max-w-none"></div>
    </div>
</div>
@endif

<script>
(function () {
    const AUTOSAVE_URL = @json($autosaveUrl);
    const PREVIEW_URL = @json($previewUrl);
    const LOCAL_KEY = @json($localKey);
    const ERRORS_PRESENT = @json($errorsPresent);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const IMAGE_UPLOAD_URL = @json(route('editor.events.description-image'));

    function updateWordCountFromText(text, countId) {
        const el = document.getElementById(countId);
        if (!el) return;
        const words = String(text).trim().split(/\s+/).filter(w => w.length > 0);
        el.textContent = words.length + (countId.includes('en') ? ' words' : ' mots');
    }

    function collectArticlePayload(form) {
        const keys = ['title_fr','title_en','slug_fr','slug_en','category_id','excerpt_fr','excerpt_en','content_fr','content_en',
            'cover_url','cover_alt','reading_time','meta_title_fr','meta_desc_fr','meta_title_en','meta_desc_en'];
        const data = {};
        keys.forEach(k => {
            const el = form.querySelector('[name="' + k + '"]');
            if (el) data[k] = el.value;
        });
        const tags = [];
        form.querySelectorAll('input[name="tags[]"]:checked').forEach(cb => tags.push(parseInt(cb.value, 10)));
        if (tags.length) data.tags = tags;
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
        const form = document.getElementById('articleForm');
        if (!form || !AUTOSAVE_URL) return;
        window.RichEditor.sync();
        const payload = collectArticlePayload(form);
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
        }).then(r => {
            if (!r.ok) throw new Error('save');
            return r.json();
        }).then(() => {
            if (statusEl) {
                statusEl.textContent = 'Brouillon enregistré';
                statusEl.classList.remove('text-rose-400');
                statusEl.classList.add('text-emerald-400/90');
            }
        }).catch(() => {
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
        const form = document.getElementById('articleForm');
        if (!form || !LOCAL_KEY) return;
        window.RichEditor.sync();
        try {
            const data = collectArticlePayload(form);
            data._savedAt = new Date().toISOString();
            localStorage.setItem(LOCAL_KEY, JSON.stringify(data));
            const st = document.getElementById('autosaveStatus');
            if (st) {
                st.textContent = 'Brouillon local enregistré';
                st.classList.add('text-green-400/90');
            }
        } catch (e) {}
    }

    function restoreLocalDraft() {
        if (AUTOSAVE_URL || !LOCAL_KEY || ERRORS_PRESENT) return;
        let raw;
        try { raw = localStorage.getItem(LOCAL_KEY); } catch (e) { return; }
        if (!raw) return;
        if (!confirm('Un brouillon local a été trouvé. Voulez-vous le restaurer ?')) return;
        let data;
        try { data = JSON.parse(raw); } catch (e) { return; }
        const form = document.getElementById('articleForm');
        if (!form) return;
        Object.keys(data).forEach(k => {
            if (k === 'tags' || k === '_savedAt' || k === 'content_fr' || k === 'content_en') return;
            const el = form.querySelector('[name="' + k + '"]');
            if (el && typeof data[k] === 'string') el.value = data[k];
        });
        if (Array.isArray(data.tags)) {
            form.querySelectorAll('input[name="tags[]"]').forEach(cb => {
                cb.checked = data.tags.includes(parseInt(cb.value, 10));
            });
        }
        if (typeof data.content_fr === 'string') window.RichEditor.setHtml('content_fr', data.content_fr);
        if (typeof data.content_en === 'string') window.RichEditor.setHtml('content_en', data.content_en);
    }

    window.openArticlePreviewModal = function () {
        const modal = document.getElementById('article-preview-modal');
        const body = document.getElementById('article-preview-body');
        if (!modal || !body) return;
        window.RichEditor.sync();
        const title = (document.getElementById('title_fr') || document.querySelector('[name="title_fr"]'))?.value || 'Sans titre';
        const ex = (document.getElementById('excerpt_fr') || document.querySelector('[name="excerpt_fr"]'))?.value || '';
        const html = document.getElementById('content_fr')?.value || '';
        const esc = (s) => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        body.innerHTML = '<h1 class="text-2xl font-bold text-white mb-4 font-serif">' + esc(title) + '</h1>'
            + (ex ? '<p class="text-slate-400 mb-4 border-l-2 border-orange-500/40 pl-3">' + esc(ex) + '</p>' : '')
            + '<iframe class="w-full min-h-[320px] rounded-lg border border-slate-800 bg-green-950" sandbox="" referrerpolicy="no-referrer" title="Aperçu contenu"></iframe>';
        const iframe = body.querySelector('iframe');
        if (iframe) {
            iframe.srcdoc = '<!DOCTYPE html><meta charset="utf-8"><style>body{font-family:system-ui,sans-serif;padding:1rem;background:#e9e5d9;color:#1c1915;line-height:1.65;} a{color:#a54a0b;}</style><div class="c">' + html + '</div>';
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeArticlePreviewModal = function () {
        const modal = document.getElementById('article-preview-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.openArticlePreviewTab = function () {
        if (PREVIEW_URL) window.open(PREVIEW_URL, '_blank', 'noopener');
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.RichEditor || !window.RichEditor.available()) return;

        window.RichEditor.init('textarea.rich-editor', {
            height: 440,
            uploadUrl: IMAGE_UPLOAD_URL,
            csrf: CSRF,
            onChange: function ($note) {
                const id = $note.attr('id');
                if (id === 'content_fr') updateWordCountFromText(window.RichEditor.text('content_fr'), 'wordCount-fr');
                if (id === 'content_en') updateWordCountFromText(window.RichEditor.text('content_en'), 'wordCount-en');
                scheduleAutosave();
                scheduleLocalDraft();
            },
        });

        const form = document.getElementById('articleForm');
        if (form) {
            form.addEventListener('submit', function () {
                window.RichEditor.sync();
            });
        }

        if (!AUTOSAVE_URL) {
            restoreLocalDraft();
        }
    });
})();
</script>
