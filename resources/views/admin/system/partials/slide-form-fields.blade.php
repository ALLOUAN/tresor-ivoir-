@php
    $isEdit = $isEdit ?? false;
    $prefix = $isEdit ? 'edit_' : '';
@endphp

<input type="hidden" name="_form_context" value="{{ $isEdit ? 'edit' : 'create' }}">
@if($isEdit)
    <input type="hidden" name="edit_slide_id" id="edit_slide_id_hidden" value="{{ old('edit_slide_id', '') }}">
@endif

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
    <textarea name="description" id="{{ $prefix }}description" rows="3"
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description') }}</textarea>
</div>

@if($isEdit)
    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="border border-slate-700 rounded-lg p-2 bg-slate-800/50">
            <p class="text-xs text-slate-400 mb-1">Aperçu Desktop</p>
            <img id="edit_preview_desktop" src="" alt="" class="w-full max-h-24 object-cover rounded hidden">
        </div>
        <div class="border border-slate-700 rounded-lg p-2 bg-slate-800/50">
            <p class="text-xs text-slate-400 mb-1">Aperçu Tablette</p>
            <img id="edit_preview_tablet" src="" alt="" class="w-full max-h-24 object-cover rounded hidden">
        </div>
        <div class="border border-slate-700 rounded-lg p-2 bg-slate-800/50">
            <p class="text-xs text-slate-400 mb-1">Aperçu Mobile</p>
            <img id="edit_preview_mobile" src="" alt="" class="w-full max-h-24 object-cover rounded hidden">
        </div>
    </div>
@endif

<div>
    <label class="block text-sm text-slate-300 mb-1">Image Desktop *</label>
    <input type="file" name="desktop_image" id="{{ $prefix }}desktop_image" accept="image/jpeg,image/png,image/webp"
           class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs"
           {{ $isEdit ? '' : 'required' }}>
    <p class="text-slate-500 text-xs mt-1">JPEG, PNG ou WebP — max 8&nbsp;Mo. Recommandé 1920×800&nbsp;px (min. env. 1600×600, max 4096×2000).</p>
</div>

<div>
    <label class="block text-sm text-slate-300 mb-1">Image Tablette</label>
    <input type="file" name="tablet_image" id="{{ $prefix }}tablet_image" accept="image/jpeg,image/png,image/webp"
           class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs">
    <p class="text-slate-500 text-xs mt-1">Optionnel — max 6&nbsp;Mo. Recommandé 1024×600 (min. env. 900×500).</p>
</div>

<div>
    <label class="block text-sm text-slate-300 mb-1">Image Mobile</label>
    <input type="file" name="mobile_image" id="{{ $prefix }}mobile_image" accept="image/jpeg,image/png,image/webp"
           class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs">
    <p class="text-slate-500 text-xs mt-1">Optionnel — max 4&nbsp;Mo. Recommandé 768×500 (min. env. 640×400).</p>
</div>

@if($isEdit)
    <p class="md:col-span-2 text-slate-400 text-xs">Laissez un champ fichier vide pour conserver l’image actuelle.</p>
@endif

<div>
    <label class="block text-sm text-slate-300 mb-1">Ordre *</label>
    <input type="number" name="display_order" id="{{ $prefix }}display_order" required min="1" max="9999"
           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
           value="{{ old('display_order', 1) }}">
</div>

<div class="md:col-span-2">
    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
        <input type="checkbox" name="is_active" id="{{ $prefix }}is_active" value="1" checked
               class="rounded border-slate-600 bg-slate-800 text-amber-500">
        Slide actif
    </label>
</div>
