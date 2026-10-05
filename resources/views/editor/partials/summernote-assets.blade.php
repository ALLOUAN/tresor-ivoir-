{{-- Summernote (lite) — éditeur riche partagé par les écrans articles et événements --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css">
<style>
    .note-editor.note-frame { border-radius: .75rem; border-color: #d6cfba; overflow: hidden; }
    .note-editor.note-frame .note-toolbar { background: #f6f3ed; border-bottom-color: #d6cfba; }
    .note-editor.fullscreen { z-index: 9999 !important; }

    /* Le reset de Tailwind aplatit titres, listes et citations : on les restaure dans la zone d'édition. */
    .note-editable { font-family: 'Inter', sans-serif; font-size: 15px; line-height: 1.65; color: #1c1915; background: #ffffff; }
    .note-editable p { margin: 0 0 .75rem; }
    .note-editable h1, .note-editable h2, .note-editable h3,
    .note-editable h4, .note-editable h5, .note-editable h6 { font-weight: 700; line-height: 1.25; margin: 1rem 0 .5rem; }
    .note-editable h1 { font-size: 2em; }
    .note-editable h2 { font-size: 1.5em; }
    .note-editable h3 { font-size: 1.25em; }
    .note-editable h4 { font-size: 1.1em; }
    .note-editable h5 { font-size: 1em; }
    .note-editable h6 { font-size: .9em; }
    .note-editable ul { list-style: disc; padding-left: 1.5rem; margin: 0 0 .75rem; }
    .note-editable ol { list-style: decimal; padding-left: 1.5rem; margin: 0 0 .75rem; }
    .note-editable li { display: list-item; }
    .note-editable blockquote { border-left: 4px solid #d6cfba; padding: .25rem 0 .25rem 1rem; margin: 0 0 .75rem; color: #544f47; }
    .note-editable pre { background: #1c1915; color: #f6f3ed; padding: .75rem 1rem; border-radius: .5rem; overflow: auto; margin: 0 0 .75rem; }
    .note-editable a { color: #a54a0b; text-decoration: underline; }
    .note-editable img { max-width: 100%; height: auto; }
    .note-editable table { border-collapse: collapse; margin: 0 0 .75rem; }
    .note-editable td, .note-editable th { border: 1px solid #cbd5e1; padding: .35rem .6rem; }
    .note-editable hr { border: 0; border-top: 1px solid #d6cfba; margin: 1rem 0; }
</style>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/lang/summernote-fr-FR.min.js"></script>
<script>
window.RichEditor = (function ($) {
    'use strict';

    const FONT_NAMES = ['Arial', 'Georgia', 'Inter', 'Oswald', 'Times New Roman', 'Verdana'];
    const registry = [];

    function available() {
        return !!($ && $.fn && $.fn.summernote);
    }

    function uploadImage(file, uploadUrl, csrf) {
        const fd = new FormData();
        fd.append('file', file, file.name);
        return fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        }).then(function (r) {
            if (!r.ok) throw new Error('upload');
            return r.json();
        }).then(function (json) {
            return json.location;
        });
    }

    /**
     * opts : { height, uploadUrl, csrf, onChange($note) }
     * Le placeholder vient de l'attribut placeholder du textarea.
     */
    function init(selector, opts) {
        if (!available()) return;
        opts = opts || {};

        $(selector).each(function () {
            const $note = $(this);
            if ($note.data('rich-init')) return;
            $note.data('rich-init', true);
            registry.push($note);

            const changed = function () { if (opts.onChange) opts.onChange($note); };

            $note.summernote({
                lang: 'fr-FR',
                height: opts.height || 600,
                placeholder: $note.attr('placeholder') || '',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']],
                ],
                styleTags: ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                addDefaultFonts: false,
                fontNames: FONT_NAMES,
                fontNamesIgnoreCheck: FONT_NAMES,
                fontSizes: ['8', '9', '10', '11', '12', '14', '18', '24', '36'],
                callbacks: {
                    onInit: changed,
                    onChange: changed,
                    onKeyup: changed,
                    onImageUpload: function (files) {
                        if (!opts.uploadUrl) return;
                        Array.prototype.forEach.call(files, function (file) {
                            uploadImage(file, opts.uploadUrl, opts.csrf || '').then(function (url) {
                                $note.summernote('insertImage', url, function ($img) { $img.css('max-width', '100%'); });
                            }).catch(function () {
                                alert("Échec du téléversement de l'image (JPG, PNG ou WebP, 10 Mo maximum).");
                            });
                        });
                    },
                },
            });
        });
    }

    /** Recopie le HTML des éditeurs dans leurs textareas (avant envoi, auto-sauvegarde, aperçu). */
    function sync() {
        registry.forEach(function ($n) { $n.val($n.summernote('code')); });
    }

    function setHtml(id, html) {
        const $n = $('#' + id);
        if ($n.data('rich-init')) $n.summernote('code', html || '');
        else $n.val(html || '');
    }

    function text(id) {
        const $n = $('#' + id);
        const html = $n.data('rich-init') ? $n.summernote('code') : ($n.val() || '');
        return $('<div>').html(html).text();
    }

    return { available: available, init: init, sync: sync, setHtml: setHtml, text: text };
})(window.jQuery);
</script>
