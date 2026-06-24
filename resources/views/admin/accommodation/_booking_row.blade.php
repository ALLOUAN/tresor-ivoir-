{{-- Partial: une ligne de lien de réservation (données existantes) --}}
<div class="booking-row bg-slate-800/60 border border-slate-700 rounded-xl p-4 space-y-3">
    <div class="flex gap-2 items-center">
        <input type="text" name="bl_provider[]"
               value="{{ $bl['provider_name'] ?? '' }}"
               placeholder="Booking.com"
               class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none font-medium">
        <button type="button" onclick="this.closest('.booking-row').remove()"
                class="w-8 h-9 rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">URL de réservation</span>
            <input type="url" name="bl_url[]"
                   value="{{ $bl['affiliate_url'] ?? '' }}"
                   placeholder="https://booking.com/…"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">URL du logo</span>
            <input type="url" name="bl_logo[]"
                   value="{{ $bl['logo_url'] ?? '' }}"
                   placeholder="https://…/logo.png"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div>
            <span class="text-[11px] text-slate-500 mb-1 block">Badge texte</span>
            <input type="text" name="bl_badge[]"
                   value="{{ $bl['badge_text'] ?? '' }}"
                   placeholder="Meilleur prix"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div class="flex items-center gap-2.5 pt-4">
            <input type="checkbox" name="bl_official[]"
                   value="{{ $i }}"
                   class="rounded border-slate-600 bg-slate-800 text-amber-500"
                   {{ !empty($bl['is_official']) ? 'checked' : '' }}>
            <span class="text-xs text-slate-400">Site officiel de l'établissement</span>
        </div>
    </div>
</div>
