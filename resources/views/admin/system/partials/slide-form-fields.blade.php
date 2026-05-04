@php
    $isEdit  = $isEdit ?? false;
    $prefix  = $isEdit  ? 'edit_' : '';
    // Pour le modal d'édition, le JS re-bascule dynamiquement via data-media-type.
    // On part toujours d'image=false pour ne pas dépendre de $slide en dehors du foreach.
    $isVideo = false;
@endphp

<input type="hidden" name="_form_context" value="{{ $isEdit ? 'edit' : 'create' }}">
@if($isEdit)
    <input type="hidden" name="edit_slide_id" id="edit_slide_id_hidden" value="{{ old('edit_slide_id', '') }}">
@endif

{{-- ══════════ TYPE DE MÉDIA ══════════ --}}
<div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-2 font-semibold">Type de média *</label>
    <div class="flex gap-3" id="{{ $prefix }}media_type_group">
        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="radio" name="media_type" id="{{ $prefix }}type_image" value="image"
                   class="accent-violet-500 w-4 h-4"
                   {{ (!$isEdit || !$isVideo) ? 'checked' : '' }}>
            <span class="flex items-center gap-1.5 text-sm text-slate-300 group-has-[:checked]:text-white">
                <i class="fas fa-image text-violet-400 w-4 text-center"></i> Image
            </span>
        </label>
        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="radio" name="media_type" id="{{ $prefix }}type_video" value="video"
                   class="accent-amber-500 w-4 h-4"
                   {{ ($isEdit && $isVideo) ? 'checked' : '' }}>
            <span class="flex items-center gap-1.5 text-sm text-slate-300 group-has-[:checked]:text-white">
                <i class="fas fa-film text-amber-400 w-4 text-center"></i> Vidéo
            </span>
        </label>
    </div>
</div>

{{-- ══════════ TITRE / SOUS-TITRE / DESCRIPTION ══════════ --}}
<div>
    <label class="block text-sm text-slate-300 mb-1">Titre *</label>
    <input type="text" name="title" id="{{ $prefix }}title" required
           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
           value="{{ old('title') }}">
</div>

<div>
    <label class="block text-sm text-slate-300 mb-1">Sous-titre</label>
    <input type="text" name="subtitle" id="{{ $prefix }}subtitle"
           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
           value="{{ old('subtitle') }}">
</div>

<div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-1">Description</label>
    <textarea name="description" id="{{ $prefix }}description" rows="2"
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description') }}</textarea>
</div>

{{-- ══════════ APERÇUS (mode édition) ══════════ --}}
@if($isEdit)
    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3" id="{{ $prefix }}previews_container">
        {{-- Aperçus image --}}
        <div class="border border-slate-700 rounded-lg p-2 bg-slate-800/50 {{ $isVideo ? 'hidden' : '' }}" id="{{ $prefix }}preview_img_desktop_wrap">
            <p class="text-xs text-slate-400 mb-1">Aperçu Image — Desktop</p>
            <img id="{{ $prefix }}preview_desktop" src="" alt="" class="w-full max-h-24 object-cover rounded hidden">
        </div>
        <div class="border border-slate-700 rounded-lg p-2 bg-slate-800/50 {{ $isVideo ? 'hidden' : '' }}" id="{{ $prefix }}preview_img_tablet_wrap">
            <p class="text-xs text-slate-400 mb-1">Aperçu Image — Tablette</p>
            <img id="{{ $prefix }}preview_tablet" src="" alt="" class="w-full max-h-24 object-cover rounded hidden">
        </div>
        <div class="border border-slate-700 rounded-lg p-2 bg-slate-800/50 {{ $isVideo ? 'hidden' : '' }}" id="{{ $prefix }}preview_img_mobile_wrap">
            <p class="text-xs text-slate-400 mb-1">Aperçu Image — Mobile</p>
            <img id="{{ $prefix }}preview_mobile" src="" alt="" class="w-full max-h-24 object-cover rounded hidden">
        </div>
        {{-- Aperçus vidéo --}}
        <div class="border border-amber-500/20 rounded-lg p-2 bg-amber-500/5 {{ !$isVideo ? 'hidden' : '' }}" id="{{ $prefix }}preview_vid_desktop_wrap">
            <p class="text-xs text-slate-400 mb-1">Aperçu Vidéo — Desktop</p>
            <video id="{{ $prefix }}preview_video_desktop" src="" class="w-full max-h-24 object-cover rounded hidden" muted></video>
            <p id="{{ $prefix }}preview_vid_desktop_name" class="text-xs text-amber-300 mt-1 truncate hidden"></p>
        </div>
        <div class="border border-amber-500/20 rounded-lg p-2 bg-amber-500/5 {{ !$isVideo ? 'hidden' : '' }}" id="{{ $prefix }}preview_vid_tablet_wrap">
            <p class="text-xs text-slate-400 mb-1">Aperçu Vidéo — Tablette</p>
            <p id="{{ $prefix }}preview_vid_tablet_name" class="text-xs text-amber-300 truncate hidden"></p>
        </div>
        <div class="border border-amber-500/20 rounded-lg p-2 bg-amber-500/5 {{ !$isVideo ? 'hidden' : '' }}" id="{{ $prefix }}preview_vid_mobile_wrap">
            <p class="text-xs text-slate-400 mb-1">Aperçu Vidéo — Mobile</p>
            <p id="{{ $prefix }}preview_vid_mobile_name" class="text-xs text-amber-300 truncate hidden"></p>
        </div>
    </div>
@endif

{{-- ══════════ CHAMPS IMAGE ══════════ --}}
<div id="{{ $prefix }}image_fields" class="{{ $isVideo ? 'hidden' : '' }} md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-slate-300 mb-1">
            Image Desktop <span class="text-violet-400">{{ $isEdit ? '' : '*' }}</span>
        </label>
        <input type="file" name="desktop_image" id="{{ $prefix }}desktop_image"
               accept="image/jpeg,image/png,image/webp"
               class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs"
               {{ ($isEdit || $isVideo) ? '' : 'required' }}>
        <p class="text-slate-500 text-xs mt-1">JPEG, PNG, WebP — max 8 Mo. Cible 1920×800 px.</p>
    </div>
    <div>
        <label class="block text-sm text-slate-300 mb-1">Image Tablette</label>
        <input type="file" name="tablet_image" id="{{ $prefix }}tablet_image"
               accept="image/jpeg,image/png,image/webp"
               class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs">
        <p class="text-slate-500 text-xs mt-1">Optionnel — max 6 Mo. Cible 1024×600 px.</p>
    </div>
    <div>
        <label class="block text-sm text-slate-300 mb-1">Image Mobile</label>
        <input type="file" name="mobile_image" id="{{ $prefix }}mobile_image"
               accept="image/jpeg,image/png,image/webp"
               class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs">
        <p class="text-slate-500 text-xs mt-1">Optionnel — max 4 Mo. Cible 768×500 px.</p>
    </div>
    @if($isEdit)
        <p class="text-slate-400 text-xs self-end pb-2">Laissez vide pour conserver le fichier actuel.</p>
    @endif
</div>

{{-- ══════════ CHAMPS VIDÉO ══════════ --}}
<div id="{{ $prefix }}video_fields" class="{{ !$isVideo ? 'hidden' : '' }} md:col-span-2">
    <div class="rounded-lg border border-amber-500/25 bg-amber-500/5 px-4 py-3 mb-3 text-xs text-amber-200/80">
        <i class="fas fa-circle-info text-amber-400 mr-1"></i>
        Formats acceptés : <strong>MP4</strong> (H.264) ou <strong>WebM</strong>. Vidéos muettes, lecture en boucle automatique.
        Desktop : 1920×800 — Tablette : 1024×600 — Mobile : 768×500.
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm text-slate-300 mb-1">
                Vidéo Desktop <span class="text-amber-400">{{ $isEdit ? '' : '*' }}</span>
            </label>
            <input type="file" name="video_desktop" id="{{ $prefix }}video_desktop"
                   accept="video/mp4,video/webm"
                   class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-amber-600 file:px-3 file:py-2 file:text-white file:text-xs">
            <p class="text-slate-500 text-xs mt-1">Max 100 Mo — 1920×800 px recommandé.</p>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Vidéo Tablette</label>
            <input type="file" name="video_tablet" id="{{ $prefix }}video_tablet"
                   accept="video/mp4,video/webm"
                   class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-amber-600 file:px-3 file:py-2 file:text-white file:text-xs">
            <p class="text-slate-500 text-xs mt-1">Optionnel — max 50 Mo — 1024×600 px.</p>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Vidéo Mobile</label>
            <input type="file" name="video_mobile" id="{{ $prefix }}video_mobile"
                   accept="video/mp4,video/webm"
                   class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-amber-600 file:px-3 file:py-2 file:text-white file:text-xs">
            <p class="text-slate-500 text-xs mt-1">Optionnel — max 30 Mo — 768×500 px.</p>
        </div>
    </div>
    @if($isEdit)
        <p class="text-slate-400 text-xs mt-2">Laissez vide pour conserver la vidéo actuelle.</p>
    @endif
</div>

{{-- ══════════ ORDRE + STATUT ══════════ --}}
<div>
    <label class="block text-sm text-slate-300 mb-1">Ordre *</label>
    <input type="number" name="display_order" id="{{ $prefix }}display_order" required min="1" max="9999"
           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
           value="{{ old('display_order', 1) }}">
</div>

<div class="md:col-span-2">
    <label class="inline-flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
        <input type="checkbox" name="is_active" id="{{ $prefix }}is_active" value="1" checked
               class="rounded border-slate-600 bg-slate-800 text-amber-500">
        Slide actif (visible sur le site)
    </label>
</div>

{{-- ══════════ JS : bascule Image ↔ Vidéo dans le formulaire ══════════ --}}
<script>
(function() {
    var prefix  = '{{ $prefix }}';
    var isEdit  = {{ $isEdit ? 'true' : 'false' }};   // constante PHP → JS, sans directive Blade dans le corps

    function toggleFields(type) {
        var isVid = type === 'video';

        var imgFields = document.getElementById(prefix + 'image_fields');
        var vidFields = document.getElementById(prefix + 'video_fields');
        if (imgFields) imgFields.classList.toggle('hidden', isVid);
        if (vidFields) vidFields.classList.toggle('hidden', !isVid);

        if (isEdit) {
            ['img_desktop', 'img_tablet', 'img_mobile'].forEach(function(k) {
                var el = document.getElementById(prefix + 'preview_' + k + '_wrap');
                if (el) el.classList.toggle('hidden', isVid);
            });
            ['vid_desktop', 'vid_tablet', 'vid_mobile'].forEach(function(k) {
                var el = document.getElementById(prefix + 'preview_' + k + '_wrap');
                if (el) el.classList.toggle('hidden', !isVid);
            });
        }

        var desktopImg = document.getElementById(prefix + 'desktop_image');
        if (desktopImg) desktopImg.required = !isVid;
    }

    ['type_image', 'type_video'].forEach(function(id) {
        var radio = document.getElementById(prefix + id);
        if (radio) radio.addEventListener('change', function() {
            toggleFields(this.value);
        });
    });

    ['video_desktop', 'video_tablet', 'video_mobile'].forEach(function(field) {
        var input = document.getElementById(prefix + field);
        if (!input) return;
        input.addEventListener('change', function() {
            if (!isEdit) return;
            var nameEl = document.getElementById(prefix + 'preview_vid_' + field.replace('video_', '') + '_name');
            if (nameEl && this.files[0]) {
                nameEl.textContent = this.files[0].name;
                nameEl.classList.remove('hidden');
            }
        });
    });
})();
</script>
