{{-- À propos --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3 text-xs text-slate-400 leading-relaxed">
        <p class="font-semibold text-slate-300 mb-1"><i class="fas fa-circle-info text-orange-400/80 mr-1"></i> Page « À propos »</p>
        <p>Titres bilingues puis un contenu riche (HTML) pour mission, équipe et valeurs. Ton institutionnel mais chaleureux, une seule <code class="text-slate-300">&lt;h1&gt;</code> côté public (le titre en sert souvent de H1). Images via URL absolues <code class="text-slate-300">&lt;img src="https://…"&gt;</code>.</p>
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
        <textarea name="body_fr" id="body_fr" rows="18" placeholder="Collez ou rédigez votre HTML ici…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_fr', $page->body_fr) }}</textarea>
        <details class="mt-2 rounded-lg border border-slate-700 bg-slate-800/40 px-3 py-2">
            <summary class="text-[11px] font-medium text-slate-400 cursor-pointer list-none flex items-center gap-2 [&::-webkit-details-marker]:hidden">
                <i class="fas fa-code text-orange-400/70"></i> Exemple de squelette HTML
            </summary>
            <pre class="mt-2 text-[11px] text-slate-500 overflow-x-auto leading-relaxed font-mono whitespace-pre-wrap">&lt;section&gt;
  &lt;h2 id="mission"&gt;Notre mission&lt;/h2&gt;
  &lt;p&gt;…&lt;/p&gt;
  &lt;h2 id="equipe"&gt;L’équipe&lt;/h2&gt;
  &lt;ul&gt;&lt;li&gt;…&lt;/li&gt;&lt;/ul&gt;
&lt;/section&gt;</pre>
        </details>
    </div>
    <div>
        <label for="body_en" class="block text-sm text-slate-300 mb-1">Contenu (anglais)</label>
        <textarea name="body_en" id="body_en" rows="10"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
    </div>
    @include('admin.system.information-center.forms._submit')
</form>
