<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3 text-xs text-slate-400 leading-relaxed">
        <p class="font-semibold text-slate-300 mb-1"><i class="fas fa-circle-info text-amber-400/80 mr-1"></i> Édition générique</p>
        <p>Cette page utilise le gabarit standard. Les champs <strong class="text-slate-300">corps FR / EN</strong> acceptent du HTML (balises sûres : paragraphes, titres, listes, liens). Limite technique d’environ <strong class="text-slate-300">100&nbsp;000 caractères</strong> par zone. Pensez à prévoir une relecture avant publication sur le site public.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="title_fr" class="block text-sm text-slate-300 mb-1">Titre (français) <span class="text-red-400">*</span></label>
            <input type="text" name="title_fr" id="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}"
                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500/50 outline-none transition">
        </div>
        <div>
            <label for="title_en" class="block text-sm text-slate-300 mb-1">Titre (anglais)</label>
            <input type="text" name="title_en" id="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}"
                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 focus:ring-2 focus:ring-amber-500/30 outline-none transition" placeholder="Optionnel">
        </div>
    </div>
    <div>
        <label for="body_fr" class="block text-sm text-slate-300 mb-1">Contenu (français)</label>
        <textarea name="body_fr" id="body_fr" rows="16"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-amber-500/30 outline-none resize-y">{{ old('body_fr', $page->body_fr) }}</textarea>
    </div>
    <div>
        <label for="body_en" class="block text-sm text-slate-300 mb-1">Contenu (anglais)</label>
        <textarea name="body_en" id="body_en" rows="10"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-amber-500/30 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
    </div>
    @include('admin.system.information-center.forms._submit')
</form>
