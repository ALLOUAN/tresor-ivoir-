@php $isEdit = $isEdit ?? false; $prefix = $isEdit ? 'edit_' : ''; @endphp

<div>
    <label for="{{ $prefix }}title" class="block text-xs text-slate-400 mb-1">Titre <span class="text-red-400">*</span></label>
    <input type="text" name="title" id="{{ $prefix }}title" maxlength="255" required
           class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition"
           placeholder="Ex : Organisation de circuits sur mesure">
</div>

<div>
    <label for="{{ $prefix }}description" class="block text-xs text-slate-400 mb-1">Description</label>
    <textarea name="description" id="{{ $prefix }}description" rows="3"
              class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y"></textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs text-slate-400 mb-2">Image</label>
        <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-3 cursor-pointer transition group">
            <i class="fas fa-cloud-arrow-up text-lg text-slate-600 group-hover:text-orange-400/70 mb-1 transition"></i>
            <span class="text-slate-500 text-[11px] group-hover:text-slate-300 transition">JPG, PNG, WebP — 5 Mo max</span>
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="hidden"
                   onchange="const r=new FileReader();r.onload=e=>{document.getElementById('{{ $prefix }}image_preview').src=e.target.result;document.getElementById('{{ $prefix }}image_preview').classList.remove('hidden');};r.readAsDataURL(this.files[0])">
        </label>
        <img id="{{ $prefix }}image_preview" src="" class="mt-2 w-full h-20 object-cover rounded-lg border border-slate-700 hidden">
    </div>
    <div>
        <label for="{{ $prefix }}icon" class="block text-xs text-slate-400 mb-1">
            Icône <span class="text-slate-600">(si pas d'image)</span>
        </label>
        <input type="text" name="icon" id="{{ $prefix }}icon" maxlength="100"
               placeholder="fas fa-route"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition font-mono">
        <p class="text-[11px] text-slate-600 mt-1">Classe FontAwesome, ex. <code class="text-orange-400/70">fas fa-route</code></p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}link_url" class="block text-xs text-slate-400 mb-1">Lien / bouton d'action</label>
        <input type="url" name="link_url" id="{{ $prefix }}link_url" maxlength="500" placeholder="https://…"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
    <div>
        <label for="{{ $prefix }}link_label" class="block text-xs text-slate-400 mb-1">Libellé du bouton</label>
        <input type="text" name="link_label" id="{{ $prefix }}link_label" maxlength="150" placeholder="En savoir plus"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}display_order" class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
        <input type="number" name="display_order" id="{{ $prefix }}display_order" min="0" max="9999" value="0"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
    </div>
    <div class="flex items-end pb-2.5">
        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="{{ $prefix }}is_active" value="1" checked
                   class="rounded border-slate-600 bg-slate-800 text-orange-500">
            <span class="text-sm text-slate-300 group-hover:text-white transition">Actif</span>
        </label>
    </div>
</div>
