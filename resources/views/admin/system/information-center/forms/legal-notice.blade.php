{{-- Mentions légales — document institutionnel + colonne latérale --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="lg:grid lg:grid-cols-12 gap-8 items-start">
    @csrf
    @method('PUT')

    <aside class="lg:col-span-3 order-2 lg:order-1 space-y-4">
        <div class="sticky top-24 rounded-2xl border border-stone-600/40 bg-stone-950/80 p-5">
            <p class="text-stone-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-4">Sommaire visuel</p>
            <nav class="space-y-2 text-xs text-stone-400">
                <span class="block py-2 px-3 rounded-lg bg-stone-900/80 border border-stone-700/50 text-stone-300">Éditeur &amp; hébergeur</span>
                <span class="block py-2 px-3 rounded-lg hover:bg-stone-900/50 transition">Propriété intellectuelle</span>
                <span class="block py-2 px-3 rounded-lg hover:bg-stone-900/50 transition">Responsabilité</span>
                <span class="block py-2 px-3 rounded-lg hover:bg-stone-900/50 transition">Données &amp; cookies</span>
            </nav>
            <p class="text-stone-600 text-[10px] leading-relaxed mt-4 border-t border-stone-800 pt-4">Structurez le HTML du corps avec des <code class="text-stone-500">&lt;h2 id="..."&gt;</code> pour ancrer ces entrées plus tard sur le site public.</p>
            <div class="mt-4 rounded-lg border border-amber-900/40 bg-amber-950/20 p-3">
                <p class="text-amber-200/90 text-[10px] font-bold uppercase tracking-wider mb-2">Rappel juridique</p>
                <p class="text-stone-500 text-[10px] leading-relaxed">Ce texte engage la responsabilité de l’éditeur du site. Vérifiez l’exactitude des mentions (raison sociale, RCS, capital, siège, directeur de publication, hébergeur, TVA le cas échéant).</p>
            </div>
            <ul class="mt-4 text-[10px] text-stone-500 space-y-1.5 list-disc list-inside leading-relaxed">
                <li>Éditeur &amp; coordonnées complètes</li>
                <li>Hébergeur (nom, adresse, téléphone)</li>
                <li>Propriété intellectuelle &amp; marques</li>
                <li>Limitation de responsabilité sur les contenus tiers</li>
                <li>Médiation / litiges consommateurs (si applicable)</li>
            </ul>
        </div>
    </aside>

    <div class="lg:col-span-9 order-1 lg:order-2">
        <div class="rounded-sm border-[3px] border-double border-stone-600/50 bg-[#0c0a09] px-6 sm:px-12 py-10 sm:py-14 shadow-inner">
            <div class="max-w-2xl mx-auto text-center mb-10 pb-8 border-b border-stone-800">
                <p class="font-serif text-stone-500 text-xs tracking-[0.35em] uppercase mb-3">Document officiel</p>
                <h3 class="font-serif text-2xl sm:text-3xl text-stone-100 tracking-tight">Mentions légales</h3>
            </div>

            <div class="max-w-2xl mx-auto space-y-8">
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label class="font-serif text-stone-500 text-xs tracking-widest uppercase block mb-2">Intitulé (FR) *</label>
                        <input type="text" name="title_fr" required maxlength="200" value="{{ old('title_fr', $page->title_fr) }}"
                            class="w-full bg-stone-950 border-b-2 border-stone-700 focus:border-amber-600/70 px-0 py-2 text-stone-100 font-serif text-lg outline-none transition placeholder-stone-700">
                    </div>
                    <div>
                        <label class="font-serif text-stone-500 text-xs tracking-widest uppercase block mb-2">Title (EN)</label>
                        <input type="text" name="title_en" maxlength="200" value="{{ old('title_en', $page->title_en) }}"
                            class="w-full bg-stone-950 border-b-2 border-stone-800 focus:border-stone-500 px-0 py-2 text-stone-300 font-serif text-sm outline-none transition">
                    </div>
                </div>

                <div class="rounded-lg border border-stone-800 bg-stone-950/40 px-4 py-3 mb-4 text-left">
                    <p class="text-stone-500 text-[11px] leading-relaxed"><strong class="text-stone-400">Conseil de rédaction :</strong> une section par <code class="text-stone-600">&lt;h2&gt;</code> ; paragraphes courts ; pas de jargon inutile. Les dates de dernière mise à jour peuvent figurer en tête dans un <code class="text-stone-600">&lt;p class=&quot;text-sm opacity-70&quot;&gt;</code> si votre gabarit public le prévoit.</p>
                </div>
                <div>
                    <label class="font-serif text-stone-500 text-xs tracking-widest uppercase block mb-3">Texte juridique — français</label>
                    <textarea name="body_fr" rows="22" placeholder="Mentions complètes en HTML…"
                        class="w-full bg-stone-950/50 border border-stone-800 rounded-sm px-5 py-4 text-sm text-stone-300 leading-relaxed font-mono tracking-tight focus:border-stone-600 focus:ring-1 focus:ring-stone-700 outline-none resize-y min-h-[360px]">{{ old('body_fr', $page->body_fr) }}</textarea>
                </div>

                <div class="border-t border-stone-800 pt-8">
                    <label class="font-serif text-stone-500 text-xs tracking-widest uppercase block mb-3">Legal text — English</label>
                    <p class="text-stone-600 text-[11px] mb-3 leading-relaxed">Si vous ciblez un public international, cette version peut reprendre les mêmes sections que le FR. Sinon laissez vide.</p>
                    <textarea name="body_en" rows="14" placeholder="Legal notice in English (optional)…"
                        class="w-full bg-stone-950/50 border border-stone-800 rounded-sm px-5 py-4 text-sm text-stone-400 leading-relaxed font-mono focus:border-stone-600 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            @include('admin.system.information-center.forms._submit')
        </div>
    </div>
</form>
