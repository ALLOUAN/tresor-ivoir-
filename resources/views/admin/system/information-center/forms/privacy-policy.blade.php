{{-- Politique de confidentialité — bento responsive + halos émeraude --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border border-emerald-500/20 bg-emerald-950/10 px-5 py-4 text-sm text-slate-400 leading-relaxed">
        <p class="font-semibold text-emerald-200/90 mb-2 flex items-center gap-2"><i class="fas fa-scale-balanced text-emerald-400/80"></i> Contenu attendu</p>
        <p class="text-xs">Décrivez <strong class="text-slate-300">quelles données</strong> vous collectez (compte, newsletter, paiement, analytics), <strong class="text-slate-300">pourquoi</strong> (base légale : contrat, consentement, intérêt légitime), <strong class="text-slate-300">combien de temps</strong> vous les conservez, <strong class="text-slate-300">qui</strong> y accède (sous-traitants), et <strong class="text-slate-300">comment</strong> l’utilisateur exerce ses droits (email DPO, formulaire).</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
        <div class="lg:col-span-4 rounded-2xl p-[1px] bg-gradient-to-br from-emerald-400/40 via-teal-500/20 to-slate-700/50">
            <div class="rounded-2xl bg-slate-950/90 px-5 py-6 sm:px-8 sm:py-7">
                <div class="flex items-center gap-3 mb-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/15 border border-emerald-400/25 text-emerald-300">
                        <i class="fas fa-shield-halved"></i>
                    </span>
                    <div>
                        <p class="text-emerald-400/90 text-[10px] font-black uppercase tracking-[0.2em]">Confidentialité</p>
                        <p class="text-white font-semibold text-sm">Titres &amp; champs bilingues</p>
                        <p class="text-slate-500 text-[11px] mt-1 leading-relaxed max-w-xl">Le titre FR apparaît souvent comme titre de page ; évitez les formulations vagues (« Politique » seul). Ex. « Politique de confidentialité — Trésors d’Ivoire ».</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <input type="text" name="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}" placeholder="Titre FR *"
                        class="w-full bg-slate-900 border border-emerald-500/20 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-500/15 outline-none">
                    <input type="text" name="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}" placeholder="Title EN"
                        class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:border-teal-400/40 outline-none">
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3">
            <div class="rounded-2xl border border-teal-500/25 bg-teal-950/20 px-4 py-4 text-center flex flex-col justify-center">
                <i class="fas fa-lock text-teal-400/80 text-lg mb-1"></i>
                <p class="text-teal-200/80 text-[10px] font-bold uppercase tracking-wider">Sécurité</p>
                <p class="text-slate-600 text-[10px] mt-1 leading-tight">TLS, hébergement UE…</p>
            </div>
            <div class="rounded-2xl border border-slate-700/80 bg-slate-900/50 px-4 py-4 text-center flex flex-col justify-center">
                <i class="fas fa-user-shield text-slate-400 mb-1"></i>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Droits</p>
                <p class="text-slate-600 text-[10px] mt-1">RGPD, DPO</p>
            </div>
            <div class="rounded-2xl border border-amber-500/20 bg-amber-950/15 px-4 py-4 text-center flex flex-col justify-center">
                <i class="fas fa-cookie-bite text-amber-400/70 mb-1"></i>
                <p class="text-amber-200/80 text-[10px] font-bold uppercase tracking-wider">Cookies</p>
                <p class="text-slate-600 text-[10px] mt-1">Consentement</p>
            </div>
        </div>

        <div class="lg:col-span-6 rounded-2xl border border-white/10 bg-slate-900/40 p-4 sm:p-6">
            <label class="flex items-center justify-between text-xs font-bold text-emerald-200/90 uppercase tracking-widest mb-3">
                <span>Politique — contenu FR</span>
                <span class="text-[10px] font-normal text-slate-600 normal-case">HTML</span>
            </label>
            <textarea name="body_fr" rows="20" placeholder="Sections : finalités, base légale, durées, cookies, droits RGPD, réclamations…"
                class="w-full bg-slate-950/80 border border-emerald-500/15 rounded-xl px-4 py-3 text-sm text-slate-200 font-mono leading-relaxed focus:border-emerald-400/35 outline-none resize-y min-h-[300px]">{{ old('body_fr', $page->body_fr) }}</textarea>
            <details class="mt-3 rounded-lg border border-emerald-500/15 bg-slate-950/50 px-3 py-2">
                <summary class="text-[11px] font-medium text-emerald-300/80 cursor-pointer">Idée : tableau HTML des traitements</summary>
                <p class="text-[10px] text-slate-600 mt-2 leading-relaxed">Une table <code class="text-emerald-600/80">&lt;table&gt;</code> avec colonnes « Finalité / Données / Durée / Base légale » améliore la lisibilité pour les utilisateurs et les autorités.</p>
            </details>
        </div>

        <div class="lg:col-span-6 rounded-2xl border border-slate-700/60 bg-slate-900/30 p-4 sm:p-6">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 block">Politique — contenu EN</label>
            <p class="text-slate-600 text-[11px] mb-3 leading-relaxed">Alignez sur le RGPD ou sur les exigences du marché visé (ex. UK GDPR, CCPA si audience US). Laissez vide si vous n’avez pas de version anglaise validée.</p>
            <textarea name="body_en" rows="10" placeholder="Privacy policy in English…"
                class="w-full bg-slate-950/60 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-400 font-mono focus:border-slate-500 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
        </div>
    </div>

    @include('admin.system.information-center.forms._submit')
</form>
