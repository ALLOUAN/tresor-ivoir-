{{-- À propos — split hero + glass editorial --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="relative">
    @csrf
    @method('PUT')

    <div class="relative rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl shadow-fuchsia-900/20 mb-8">
        <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-600/30 via-violet-600/20 to-amber-500/10"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent"></div>
        <div class="relative px-6 sm:px-10 py-10 sm:py-14 grid lg:grid-cols-2 gap-10 items-center">
            <div class="space-y-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-fuchsia-200 text-[10px] font-bold tracking-[0.2em] uppercase backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-400 animate-pulse"></span> Identité
                </span>
                <h3 class="text-2xl sm:text-3xl font-light text-white tracking-tight leading-tight">
                    Racontez <span class="font-semibold bg-gradient-to-r from-fuchsia-200 to-amber-200 bg-clip-text text-transparent">l’histoire</span> du magazine
                </h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-md">
                    Ce bloc met en scène votre page « À propos » : titres bilingues, puis un contenu riche (HTML) pour mission, équipe et valeurs.
                </p>
                <ul class="text-slate-500 text-xs space-y-2 max-w-md border-l-2 border-fuchsia-500/30 pl-4">
                    <li><span class="text-fuchsia-200/90 font-medium">Ton</span> — institutionnel mais chaleureux ; évitez le jargon marketing creux.</li>
                    <li><span class="text-fuchsia-200/90 font-medium">Titres</span> — 200 caractères max ; privilégiez un titre court + sous-titre dans le corps en <code class="text-fuchsia-300/70">&lt;p class="lead"&gt;</code> si besoin.</li>
                    <li><span class="text-fuchsia-200/90 font-medium">Visuels</span> — intégrez des images via URL absolues <code class="text-fuchsia-300/70">&lt;img src="https://…"&gt;</code> (hébergées sur votre média ou CDN).</li>
                    <li><span class="text-fuchsia-200/90 font-medium">SEO</span> — une seule <code class="text-fuchsia-300/70">&lt;h1&gt;</code> côté public ; ici le « titre » sert souvent de H1 de page.</li>
                </ul>
            </div>
            <div class="rounded-2xl bg-slate-950/60 backdrop-blur-xl border border-white/10 p-6 shadow-inner">
                <div class="space-y-5">
                    <div class="group">
                        <label class="block text-[11px] font-semibold text-fuchsia-300/90 uppercase tracking-widest mb-2">Titre · français</label>
                        <input type="text" name="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}"
                            class="w-full bg-slate-900/80 border border-white/10 rounded-xl px-4 py-3 text-lg text-white placeholder-slate-600 focus:border-fuchsia-400/50 focus:ring-2 focus:ring-fuchsia-500/20 outline-none transition group-hover:border-white/20">
                    </div>
                    <div class="group">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-widest mb-2">Titre · english</label>
                        <input type="text" name="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}"
                            class="w-full bg-slate-900/80 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:border-violet-400/40 focus:ring-2 focus:ring-violet-500/20 outline-none transition">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3 rounded-2xl border border-white/8 bg-gradient-to-b from-slate-900/90 to-slate-950 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-fuchsia-500/40 to-transparent"></div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Corps FR</span>
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-fuchsia-500/40 to-transparent"></div>
            </div>
            <textarea name="body_fr" rows="20" placeholder="Collez ou rédigez votre HTML ici…"
                class="w-full bg-slate-950/50 border border-white/10 rounded-2xl px-5 py-4 text-sm text-slate-200 leading-relaxed font-mono focus:border-fuchsia-500/40 focus:ring-1 focus:ring-fuchsia-500/30 outline-none resize-y min-h-[320px]">{{ old('body_fr', $page->body_fr) }}</textarea>
            <details class="mt-4 rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 group">
                <summary class="text-xs font-semibold text-slate-400 cursor-pointer list-none flex items-center gap-2 [&::-webkit-details-marker]:hidden">
                    <i class="fas fa-code text-fuchsia-400/70"></i> Exemple de squelette HTML (cliquer pour afficher)
                </summary>
                <pre class="mt-3 text-[11px] text-slate-500 overflow-x-auto leading-relaxed font-mono whitespace-pre-wrap">&lt;section&gt;
  &lt;h2 id="mission"&gt;Notre mission&lt;/h2&gt;
  &lt;p&gt;…&lt;/p&gt;
  &lt;h2 id="equipe"&gt;L’équipe&lt;/h2&gt;
  &lt;ul&gt;&lt;li&gt;…&lt;/li&gt;&lt;/ul&gt;
&lt;/section&gt;</pre>
            </details>
        </div>
        <div class="lg:col-span-2 rounded-2xl border border-cyan-500/15 bg-slate-900/40 p-6 sm:p-8 flex flex-col">
            <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-language text-cyan-400/80"></i>
                <span class="text-xs font-bold text-cyan-200/80 uppercase tracking-widest">English body</span>
            </div>
            <textarea name="body_en" rows="14"
                class="flex-1 w-full bg-slate-950/60 border border-cyan-500/20 rounded-2xl px-4 py-3 text-sm text-slate-300 font-mono focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/20 outline-none resize-y min-h-[200px]">{{ old('body_en', $page->body_en) }}</textarea>
            <div class="mt-4 rounded-xl border border-cyan-500/20 bg-cyan-950/20 p-4 text-[11px] text-slate-400 leading-relaxed space-y-2">
                <p class="font-semibold text-cyan-200/90 flex items-center gap-2"><i class="fas fa-earth-europe"></i> Version anglaise</p>
                <p>Reproduisez la <strong class="text-slate-300">même hiérarchie</strong> de titres (<code class="text-cyan-300/80">id</code> identiques sur les sections miroir) pour le référencement multilingue et les liens profonds.</p>
                <p class="text-slate-600">Si la page n’a pas de version EN publique, laissez ce champ vide.</p>
            </div>
        </div>
    </div>

    @include('admin.system.information-center.forms._submit')
</form>
