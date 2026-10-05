{{-- Politique de confidentialité --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3 text-xs text-slate-400 leading-relaxed">
        <p class="font-semibold text-slate-300 mb-1"><i class="fas fa-circle-info text-orange-400/80 mr-1"></i> Contenu attendu</p>
        <p>Décrivez <strong class="text-slate-300">quelles données</strong> vous collectez (compte, newsletter, paiement, analytics), <strong class="text-slate-300">pourquoi</strong> (base légale : contrat, consentement, intérêt légitime), <strong class="text-slate-300">combien de temps</strong> vous les conservez, <strong class="text-slate-300">qui</strong> y accède (sous-traitants), et <strong class="text-slate-300">comment</strong> l'utilisateur exerce ses droits (email DPO, formulaire).</p>
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
        <label for="body_fr" class="block text-sm text-slate-300 mb-1">Contenu (français)</label>
        <textarea name="body_fr" id="body_fr" rows="20" placeholder="Sections : finalités, base légale, durées, cookies, droits RGPD, réclamations…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono leading-relaxed focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_fr', $page->body_fr) }}</textarea>
        <details class="mt-2 rounded-lg border border-slate-700 bg-slate-800/40 px-3 py-2">
            <summary class="text-[11px] font-medium text-slate-400 cursor-pointer">Idée : tableau HTML des traitements</summary>
            <p class="text-[10px] text-slate-500 mt-2 leading-relaxed">Une table <code class="text-slate-300">&lt;table&gt;</code> avec colonnes « Finalité / Données / Durée / Base légale » améliore la lisibilité pour les utilisateurs et les autorités.</p>
        </details>
    </div>
    <div>
        <label for="body_en" class="block text-sm text-slate-300 mb-1">Contenu (anglais)</label>
        <p class="text-slate-500 text-[11px] mb-2 leading-relaxed">Alignez sur le RGPD ou sur les exigences du marché visé (ex. UK GDPR, CCPA si audience US). Laissez vide si vous n'avez pas de version anglaise validée.</p>
        <textarea name="body_en" id="body_en" rows="12" placeholder="Privacy policy in English…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
    </div>
    @include('admin.system.information-center.forms._submit')
</form>
