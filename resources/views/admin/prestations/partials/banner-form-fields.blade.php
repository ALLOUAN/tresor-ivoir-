@php $isEdit = $isEdit ?? false; $prefix = $isEdit ? 'edit_' : ''; @endphp

<div>
    <label class="block text-xs text-slate-400 mb-2">Image de bannière</label>
    <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-4 cursor-pointer transition group">
        <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-orange-400/70 mb-1.5 transition"></i>
        <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez (JPG, PNG, WebP — 8 Mo max)</span>
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="hidden"
               onchange="const r=new FileReader();r.onload=e=>{document.getElementById('{{ $prefix }}banner_preview').src=e.target.result;document.getElementById('{{ $prefix }}banner_preview').classList.remove('hidden');};r.readAsDataURL(this.files[0])">
    </label>
    <img id="{{ $prefix }}banner_preview" src="" class="mt-2 w-full h-32 object-cover rounded-lg border border-slate-700 hidden">
</div>

<div>
    <label for="{{ $prefix }}banner_title" class="block text-xs text-slate-400 mb-1">Titre</label>
    <input type="text" name="title" id="{{ $prefix }}banner_title" maxlength="255"
           placeholder="Nos Prestations"
           class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
</div>

<div>
    <label for="{{ $prefix }}banner_content" class="block text-xs text-slate-400 mb-1">Contenu</label>
    <textarea name="content" id="{{ $prefix }}banner_content" rows="4"
              class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y"></textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}banner_link_url" class="block text-xs text-slate-400 mb-1">Bouton — lien</label>
        <input type="url" name="link_url" id="{{ $prefix }}banner_link_url" maxlength="500" placeholder="https://…"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
    <div>
        <label for="{{ $prefix }}banner_link_label" class="block text-xs text-slate-400 mb-1">Bouton — libellé</label>
        <input type="text" name="link_label" id="{{ $prefix }}banner_link_label" maxlength="150" placeholder="Demander un devis"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
</div>
<p class="text-[11px] text-slate-600 -mt-2">Laissez vide pour ne pas afficher de bouton d'action sur cette bannière.</p>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}banner_display_order" class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
        <input type="number" name="display_order" id="{{ $prefix }}banner_display_order" min="0" max="9999" value="0"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
    </div>
    <div class="flex items-end pb-2.5">
        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="{{ $prefix }}banner_is_active" value="1" checked
                   class="rounded border-slate-600 bg-slate-800 text-orange-500">
            <span class="text-sm text-slate-300 group-hover:text-white transition">Actif</span>
        </label>
    </div>
</div>
