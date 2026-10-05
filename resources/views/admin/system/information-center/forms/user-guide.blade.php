{{-- Guide d'utilisation --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3 text-xs text-slate-400 leading-relaxed">
        <p class="font-semibold text-slate-300 mb-1"><i class="fas fa-circle-info text-orange-400/80 mr-1"></i> Workflow éditorial</p>
        <p>Rédigez par objectif utilisateur (ex. « Publier un article »), pas par menu admin. Numérotez les grandes étapes (<code class="text-slate-300">&lt;ol&gt;</code>) et prévoyez un encart « En cas de blocage » avec contact support.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="title_fr" class="block text-sm text-slate-300 mb-1">Titre (français) <span class="text-red-400">*</span></label>
            <input type="text" name="title_fr" id="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}"
                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500/50 outline-none transition">
        </div>
        <div>
            <label for="title_en" class="block text-sm text-slate-300 mb-1">Titre (anglais)</label>
            <input type="text" name="title_en" id="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}"
                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 focus:ring-2 focus:ring-orange-500/30 outline-none transition" placeholder="Optionnel">
        </div>
    </div>
    <div>
        <label for="body_fr" class="block text-sm text-slate-300 mb-1">Guide (français)</label>
        <p class="text-slate-500 text-[11px] mb-2 leading-relaxed">Blocs courts, <code class="text-slate-300">&lt;h3&gt;</code> pour scinder les étapes. Pensez aux rôles admin / éditeur / prestataire si le comportement diffère.</p>
        <textarea name="body_fr" id="body_fr" rows="18" placeholder="Étapes, listes, alertes &lt;div class=&quot;alert&quot;&gt;…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono leading-relaxed focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_fr', $page->body_fr) }}</textarea>
    </div>
    <div>
        <label for="body_en" class="block text-sm text-slate-300 mb-1">Guide (anglais)</label>
        <p class="text-slate-500 text-[11px] mb-2 leading-relaxed">Optionnel — même logique que la version française.</p>
        <textarea name="body_en" id="body_en" rows="12" placeholder="Optional English guide…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
    </div>
    @include('admin.system.information-center.forms._submit')
</form>
