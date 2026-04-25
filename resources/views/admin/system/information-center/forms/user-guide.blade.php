{{-- Guide d'utilisation — timeline tech / cyan --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-8">
    @csrf
    @method('PUT')

    <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
        <aside class="lg:w-48 shrink-0 relative">
            <div class="absolute left-[15px] top-8 bottom-8 w-px bg-gradient-to-b from-cyan-400/60 via-sky-500/30 to-transparent hidden lg:block"></div>
            <ol class="space-y-8 lg:pl-10">
                @foreach(['Titres', 'Contenu FR', 'Contenu EN'] as $i => $label)
                    <li class="relative flex items-start gap-4 lg:block">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-800 border border-cyan-500/40 text-cyan-300 text-xs font-black z-10 lg:absolute lg:-left-1 lg:top-0">{{ $i + 1 }}</span>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider lg:hidden">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
            <div class="hidden lg:block mt-10 pl-6 pr-2 space-y-3">
                <p class="text-[10px] text-slate-600 uppercase tracking-widest leading-relaxed">Workflow éditorial</p>
                <ul class="text-[11px] text-slate-500 space-y-2 leading-relaxed border-l border-cyan-500/20 pl-3">
                    <li>Rédigez par <strong class="text-slate-400">objectif utilisateur</strong> (ex. « Publier un article »), pas par menu admin.</li>
                    <li>Numérotez les grandes étapes : <code class="text-cyan-500/70">&lt;ol&gt;</code> + captures d’écran entre les étapes.</li>
                    <li>Prévoyez un encart <strong class="text-slate-400">En cas de blocage</strong> avec contact support.</li>
                </ul>
            </div>
        </aside>

        <div class="flex-1 space-y-8">
            <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/60 p-6 sm:p-8 shadow-[0_0_40px_-10px_rgba(34,211,238,0.15)]">
                <div class="flex items-center gap-3 mb-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-400/30 text-cyan-300">
                        <i class="fas fa-heading"></i>
                    </span>
                    <div>
                        <h4 class="text-white font-semibold text-sm">Étape 1 — Titres</h4>
                        <p class="text-slate-500 text-xs">Visible dans l’en-tête de la page publique.</p>
                        <p class="text-slate-600 text-[11px] mt-2 leading-relaxed">Ex. « Guide de la rédaction » ou « Utiliser le tableau de bord » — restez factuel, sans emoji dans le titre si le ton du site est premium.</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-cyan-400/90 uppercase tracking-widest mb-2 block">Français *</label>
                        <input type="text" name="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}"
                            class="w-full bg-slate-950 border border-cyan-500/25 rounded-xl px-4 py-3 text-white text-sm focus:border-cyan-400 focus:shadow-[0_0_0_3px_rgba(34,211,238,0.12)] outline-none transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 block">English</label>
                        <input type="text" name="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 text-sm focus:border-sky-500/50 outline-none transition">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/80 bg-slate-900/40 p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-300">
                        <i class="fas fa-book"></i>
                    </span>
                    <div>
                        <h4 class="text-white font-semibold text-sm">Étape 2 — Guide (FR)</h4>
                        <p class="text-slate-500 text-xs">Captures, listes numérotées, encarts <code class="text-emerald-400/80">&lt;aside&gt;</code></p>
                        <p class="text-slate-600 text-[11px] mt-2 leading-relaxed">Longueur : visez des blocs courts ; utilisez <code class="text-emerald-400/70">&lt;h3&gt;</code> pour scinder. Pensez aux rôles <strong class="text-slate-500">admin / éditeur / prestataire</strong> si le comportement diffère.</p>
                    </div>
                </div>
                <textarea name="body_fr" rows="18" placeholder="Étapes, listes, alertes &lt;div class=&quot;alert&quot;&gt;…"
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 font-mono leading-relaxed focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 outline-none resize-y min-h-[280px]">{{ old('body_fr', $page->body_fr) }}</textarea>
            </div>

            <div class="rounded-2xl border border-indigo-500/25 bg-gradient-to-br from-indigo-950/40 to-slate-900/60 p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/15 border border-indigo-400/30 text-indigo-200">
                        <i class="fas fa-globe"></i>
                    </span>
                    <div>
                        <h4 class="text-white font-semibold text-sm">Étape 3 — Guide (EN)</h4>
                        <p class="text-slate-500 text-xs">Optionnel — même logique que la version française.</p>
                        <p class="text-slate-600 text-[11px] mt-2 leading-relaxed">Indiquez les termes UI en <strong class="text-slate-500">français</strong> dans le texte anglais si l’interface n’est pas localisée (ex. « Click <em>Enregistrer</em> »).</p>
                    </div>
                </div>
                <textarea name="body_en" rows="12" placeholder="Optional English guide…"
                    class="w-full bg-slate-950/80 border border-indigo-500/20 rounded-xl px-4 py-3 text-sm text-slate-300 font-mono focus:border-indigo-400/50 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
            </div>
        </div>
    </div>

    @include('admin.system.information-center.forms._submit')
</form>
