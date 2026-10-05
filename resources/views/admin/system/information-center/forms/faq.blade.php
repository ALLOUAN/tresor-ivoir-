{{-- FAQ --}}
<form method="POST" action="{{ route('admin.administration.info-center.update', $page) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3 text-xs text-slate-400 leading-relaxed">
        <p class="font-semibold text-slate-300 mb-1"><i class="fas fa-circle-info text-orange-400/80 mr-1"></i> FAQ dynamique</p>
        <p>Chaque entrée doit être autonome (une question, une réponse complète). Utilisez <code class="text-slate-300">&lt;details&gt;&lt;summary&gt;</code> ou des <code class="text-slate-300">&lt;h3&gt;</code> pour chaque question ; 2–6 phrases par réponse en général.</p>
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
        <label for="body_fr" class="block text-sm text-slate-300 mb-1">Bloc FAQ (français)</label>
        <textarea name="body_fr" id="body_fr" rows="18" placeholder="Questions / réponses en HTML…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_fr', $page->body_fr) }}</textarea>
        <details class="mt-2 rounded-lg border border-slate-700 bg-slate-800/40 px-3 py-2">
            <summary class="text-[11px] font-medium text-slate-400 cursor-pointer">Exemple <code class="text-slate-300">&lt;details&gt;</code> (natif navigateur)</summary>
            <pre class="mt-2 text-[10px] text-slate-500 font-mono whitespace-pre-wrap leading-relaxed">&lt;details&gt;
  &lt;summary&gt;Comment réinitialiser mon mot de passe ?&lt;/summary&gt;
  &lt;p&gt;Sur la page de connexion, cliquez sur « Mot de passe oublié »…&lt;/p&gt;
&lt;/details&gt;</pre>
        </details>
    </div>
    <div>
        <label for="body_en" class="block text-sm text-slate-300 mb-1">Bloc FAQ (anglais)</label>
        <p class="text-slate-500 text-[11px] mb-2 leading-relaxed">Même ordre de questions que le FR pour faciliter la maintenance.</p>
        <textarea name="body_en" id="body_en" rows="10" placeholder="English FAQ HTML…"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 font-mono focus:ring-2 focus:ring-orange-500/30 outline-none resize-y">{{ old('body_en', $page->body_en) }}</textarea>
    </div>
    @include('admin.system.information-center.forms._submit')
</form>
