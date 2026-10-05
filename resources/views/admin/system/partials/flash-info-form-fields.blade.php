@php $isEdit = $isEdit ?? false; $prefix = $isEdit ? 'edit_' : ''; @endphp

<div>
    <label for="{{ $prefix }}message" class="block text-xs text-slate-400 mb-1">Message <span class="text-red-400">*</span></label>
    <textarea name="message" id="{{ $prefix }}message" rows="2" maxlength="500" required
              class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition"
              placeholder="Ex : Nouveau ! Réservez votre hébergement directement en ligne."></textarea>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}link_url" class="block text-xs text-slate-400 mb-1">Lien (optionnel)</label>
        <input type="url" name="link_url" id="{{ $prefix }}link_url" maxlength="500" placeholder="https://…"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
    <div>
        <label for="{{ $prefix }}link_label" class="block text-xs text-slate-400 mb-1">Libellé du lien</label>
        <input type="text" name="link_label" id="{{ $prefix }}link_label" maxlength="120" placeholder="En savoir plus"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}type" class="block text-xs text-slate-400 mb-1">Type</label>
        <select name="type" id="{{ $prefix }}type"
                class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            <option value="info">Info</option>
            <option value="success">Succès</option>
            <option value="warning">Alerte</option>
            <option value="urgent">Urgent</option>
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}display_order" class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
        <input type="number" name="display_order" id="{{ $prefix }}display_order" min="0" max="9999" value="0"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}starts_at" class="block text-xs text-slate-400 mb-1">Début de diffusion</label>
        <input type="datetime-local" name="starts_at" id="{{ $prefix }}starts_at"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
    </div>
    <div>
        <label for="{{ $prefix }}ends_at" class="block text-xs text-slate-400 mb-1">Fin de diffusion</label>
        <input type="datetime-local" name="ends_at" id="{{ $prefix }}ends_at"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
    </div>
</div>
<p class="text-[11px] text-slate-600 -mt-2">Laissez vide pour une diffusion immédiate et sans date de fin.</p>

<div class="flex flex-wrap gap-5">
    <label class="flex items-center gap-2.5 cursor-pointer group">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="{{ $prefix }}is_active" value="1" checked
               class="rounded border-slate-600 bg-slate-800 text-orange-500">
        <span class="text-sm text-slate-300 group-hover:text-white transition">Actif</span>
    </label>
    <label class="flex items-center gap-2.5 cursor-pointer group">
        <input type="hidden" name="is_dismissible" value="0">
        <input type="checkbox" name="is_dismissible" id="{{ $prefix }}is_dismissible" value="1" checked
               class="rounded border-slate-600 bg-slate-800 text-orange-500">
        <span class="text-sm text-slate-300 group-hover:text-white transition">
            Fermable par le visiteur <span class="text-slate-500 text-xs">(croix de fermeture)</span>
        </span>
    </label>
</div>
