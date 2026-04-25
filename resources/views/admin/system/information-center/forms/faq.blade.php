{{-- FAQ — cartes type « accordéon » + halo violet --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-8">
    @csrf
    @method('PUT')

    <div class="rounded-3xl p-[1px] bg-gradient-to-br from-violet-500/50 via-fuchsia-500/30 to-amber-500/40 shadow-2xl shadow-violet-900/30">
        <div class="rounded-[1.4rem] bg-slate-950 px-6 sm:px-10 py-8 sm:py-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-8">
                <div>
                    <p class="text-violet-300 text-[10px] font-black tracking-[0.25em] uppercase mb-2">FAQ dynamique</p>
                    <h3 class="text-2xl font-bold text-white tracking-tight">Questions &amp; réponses</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1.5 rounded-lg bg-violet-500/15 border border-violet-400/25 text-violet-200 text-xs font-medium">HTML</span>
                    <span class="px-3 py-1.5 rounded-lg bg-fuchsia-500/10 border border-fuchsia-400/20 text-fuchsia-200 text-xs font-medium">Schema.org</span>
                    <span class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-400/20 text-amber-200 text-xs font-medium">Accessibilité</span>
                </div>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed max-w-3xl mb-6">
                Chaque entrée FAQ doit être <strong class="text-slate-400">autonome</strong> : une question, une réponse complète. Évitez les renvois du type « voir ci-dessus » sans ancre. Pensez aux mots-clés naturels pour le référencement (longue traîne).
            </p>

            <div class="space-y-3 mb-8">
                <div class="rounded-xl border border-white/8 bg-white/[0.03] px-4 py-3 flex gap-3 items-start hover:border-violet-500/30 transition cursor-default">
                    <i class="fas fa-chevron-right text-violet-400/60 text-xs mt-1"></i>
                    <div>
                        <p class="text-slate-200 text-sm font-medium">Structure Q / R</p>
                        <p class="text-slate-500 text-xs mt-0.5">Utilisez <code class="text-violet-300/80">&lt;details&gt;&lt;summary&gt;</code> ou des <code class="text-violet-300/80">&lt;h3&gt;</code> pour chaque question.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-white/8 bg-white/[0.03] px-4 py-3 flex gap-3 items-start hover:border-violet-500/30 transition cursor-default">
                    <i class="fas fa-chevron-right text-violet-400/60 text-xs mt-1"></i>
                    <div>
                        <p class="text-slate-200 text-sm font-medium">Rich snippets</p>
                        <p class="text-slate-500 text-xs mt-0.5">Listes <code class="text-violet-300/80">&lt;ul&gt;</code> et ancres <code class="text-violet-300/80">#</code> pour un sommaire cliquable.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-white/8 bg-white/[0.03] px-4 py-3 flex gap-3 items-start hover:border-violet-500/30 transition cursor-default">
                    <i class="fas fa-chevron-right text-violet-400/60 text-xs mt-1"></i>
                    <div>
                        <p class="text-slate-200 text-sm font-medium">Longueur des réponses</p>
                        <p class="text-slate-500 text-xs mt-0.5">2–6 phrases par réponse en général ; ajoutez un lien « En savoir plus » vers un article du magazine si la réponse dépasse 10 lignes.</p>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 mb-8">
                <div class="relative">
                    <label class="absolute -top-2.5 left-3 px-2 bg-slate-950 text-[10px] font-bold text-violet-300 uppercase tracking-wider">Titre FR *</label>
                    <input type="text" name="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}"
                        class="w-full mt-2 bg-transparent border border-violet-500/35 rounded-2xl px-4 py-3.5 text-white text-sm focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-500/20 outline-none transition">
                </div>
                <div class="relative">
                    <label class="absolute -top-2.5 left-3 px-2 bg-slate-950 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Titre EN</label>
                    <input type="text" name="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}"
                        class="w-full mt-2 bg-transparent border border-white/10 rounded-2xl px-4 py-3.5 text-slate-200 text-sm focus:border-violet-400/50 outline-none transition">
                </div>
            </div>

            <div class="relative rounded-2xl border border-fuchsia-500/20 bg-gradient-to-b from-fuchsia-950/20 to-transparent p-1">
                <label class="block text-xs font-bold text-fuchsia-200/90 uppercase tracking-widest px-4 pt-4 pb-2">Bloc FAQ — français</label>
                <textarea name="body_fr" rows="18" placeholder="Questions / réponses en HTML…"
                    class="w-full rounded-xl bg-slate-950/90 border-0 px-5 py-4 text-sm text-slate-200 font-mono leading-relaxed focus:ring-0 outline-none resize-y min-h-[300px]">{{ old('body_fr', $page->body_fr) }}</textarea>
                <details class="mx-4 mb-4 rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2">
                    <summary class="text-[11px] font-medium text-slate-500 cursor-pointer">Exemple <code class="text-fuchsia-300/80">&lt;details&gt;</code> (natif navigateur)</summary>
                    <pre class="mt-2 text-[10px] text-slate-600 font-mono whitespace-pre-wrap leading-relaxed">&lt;details&gt;
  &lt;summary&gt;Comment réinitialiser mon mot de passe ?&lt;/summary&gt;
  &lt;p&gt;Sur la page de connexion, cliquez sur « Mot de passe oublié »…&lt;/p&gt;
&lt;/details&gt;</pre>
                </details>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-500/20 bg-amber-950/10 px-6 py-6">
        <label class="flex items-center gap-2 text-amber-200/90 text-xs font-bold uppercase tracking-widest mb-3">
            <i class="fas fa-language"></i> FAQ — anglais
        </label>
        <p class="text-amber-200/40 text-[11px] mb-3 leading-relaxed">Même ordre de questions que le FR pour faciliter la maintenance. Si une question n’a pas d’équivalent culturel, adaptez l’intitulé plutôt que traduire mot à mot.</p>
        <textarea name="body_en" rows="12" placeholder="English FAQ HTML…"
            class="w-full bg-slate-950/70 border border-amber-500/15 rounded-xl px-4 py-3 text-sm text-slate-300 font-mono focus:border-amber-400/40 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
    </div>

    @include('admin.system.information-center.forms._submit')
</form>
