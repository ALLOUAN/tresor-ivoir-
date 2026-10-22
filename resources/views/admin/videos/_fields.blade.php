{{--
    Champs communs du formulaire vidéo (administration et espace prestataire).
    Paramètres : $video, $cities ; $listings (prestataire : [clé => libellé]) ou $linked + $searchUrl (administration).
--}}
@php
    $isLink = old('source_type', $video->exists ? ($video->isEmbed() ? 'link' : 'upload') : 'link') === 'link';
    $extUrl = old('external_url', $video->source === 'youtube' ? 'https://youtu.be/'.$video->external_id : ($video->source === 'vimeo' ? 'https://vimeo.com/'.$video->external_id : ''));
    $inp = 'w-full bg-slate-800/50 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none';
@endphp

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="space-y-4">
        <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Titre</span>
            <input name="title" required maxlength="160" value="{{ old('title', $video->title) }}" class="{{ $inp }} mt-1.5" placeholder="Ex. : Coucher de soleil sur la lagune Ébrié"></label>
        <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Description</span>
            <textarea name="description" rows="3" maxlength="2000" class="{{ $inp }} mt-1.5" placeholder="Quelques phrases sur le lieu, le moment, ce qu'on y vit.">{{ old('description', $video->description) }}</textarea></label>
        <div class="grid grid-cols-2 gap-3">
            <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Rubrique</span>
                <select name="category" class="{{ $inp }} mt-1.5">@foreach(\App\Models\Video::CATEGORIES as $k => $c)<option value="{{ $k }}" @selected(old('category', $video->category) === $k)>{{ $c['label'] }}</option>@endforeach</select></label>
            <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Ville</span>
                <select name="city_id" class="{{ $inp }} mt-1.5"><option value="">—</option>@foreach($cities as $c)<option value="{{ $c->id }}" @selected((string) old('city_id', $video->city_id) === (string) $c->id)>{{ $c->name }}</option>@endforeach</select></label>
        </div>

        {{-- Fiches liées : « Voir et réserver » --}}
        <div>
            <span class="text-xs text-slate-400 uppercase tracking-wide">Fiches à réserver depuis la vidéo <span class="normal-case">(6 max)</span></span>
            @if(isset($listings))
                @if($listings)
                    <div class="mt-2 space-y-1.5">
                        @foreach($listings as $key => $label)
                            <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="links[]" value="{{ $key }}" @checked(in_array($key, (array) old('links', (array) $video->links), true)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> {{ $label }}</label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Sans choix, vos fiches sont proposées automatiquement.</p>
                @else
                    <p class="text-xs text-slate-500 mt-1">Votre fiche publique sera proposée automatiquement.</p>
                @endif
            @else
                <div class="relative mt-1.5" data-vf-links data-url="{{ $searchUrl }}">
                    <input type="search" class="{{ $inp }}" placeholder="Rechercher un hôtel, un restaurant, une activité…" data-vf-search autocomplete="off">
                    <ul class="absolute z-20 left-0 right-0 mt-1 rounded-xl border border-slate-700 bg-slate-900 max-h-64 overflow-auto hidden" data-vf-results></ul>
                    <div class="flex flex-wrap gap-2 mt-2" data-vf-chips>
                        @foreach($linked as $l)
                            <span class="inline-flex items-center gap-2 pl-3 pr-1 py-1 rounded-full bg-orange-500/15 border border-orange-500/40 text-orange-200 text-xs" data-key="{{ $l['key'] }}">{{ $l['title'] }} <small class="opacity-70">{{ $l['meta'] }}</small><input type="hidden" name="links[]" value="{{ $l['key'] }}"><button type="button" class="w-5 h-5 rounded-full hover:bg-orange-500/30" data-vf-remove aria-label="Retirer">×</button></span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <span class="text-xs text-slate-400 uppercase tracking-wide">Source</span>
            <div class="mt-1.5 grid grid-cols-2 gap-2">
                <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-700 cursor-pointer text-sm text-slate-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-500/10"><input type="radio" name="source_type" value="link" @checked($isLink) data-vf-source class="text-orange-500"> Lien YouTube / Vimeo</label>
                <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-700 cursor-pointer text-sm text-slate-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-500/10"><input type="radio" name="source_type" value="upload" @checked(! $isLink) data-vf-source class="text-orange-500"> Envoyer un fichier</label>
            </div>
        </div>
        <div data-vf-panel="link" @if(! $isLink) hidden @endif>
            <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Lien de la vidéo</span>
                <input name="external_url" value="{{ $extUrl }}" class="{{ $inp }} mt-1.5" placeholder="https://www.youtube.com/watch?v=… ou https://vimeo.com/…"></label>
            <p class="text-xs text-slate-500 mt-1">Idéal pour les vidéos longues : la lecture passe par YouTube ou Vimeo, sans charger le serveur.</p>
        </div>
        <div data-vf-panel="upload" @if($isLink) hidden @endif>
            <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-5 cursor-pointer text-center">
                <i class="fas fa-cloud-arrow-up text-2xl text-slate-500 mb-2"></i>
                <span class="text-slate-400 text-xs" data-vf-fname>{{ $video->source === 'upload' && $video->video_url ? 'Fichier actuel conservé — choisissez-en un autre pour le remplacer' : 'MP4, WebM ou MOV — 50 Mo max, format vertical conseillé (9:16), 15 à 60 s' }}</span>
                <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="hidden" data-vf-file>
            </label>
            <video class="w-full max-h-64 rounded-xl mt-2 bg-black {{ $video->source === 'upload' && $video->video_url ? '' : 'hidden' }}" data-vf-preview controls muted playsinline @if($video->source === 'upload') src="{{ $video->video_url }}" @endif></video>
        </div>
        <input type="hidden" name="duration_sec" value="{{ old('duration_sec', $video->duration_sec) }}" data-vf-duration>
        <div class="grid grid-cols-2 gap-3">
            <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Format</span>
                <select name="orientation" class="{{ $inp }} mt-1.5" data-vf-orientation>
                    <option value="portrait" @selected(old('orientation', $video->orientation) === 'portrait')>Vertical (9:16)</option>
                    <option value="landscape" @selected(old('orientation', $video->orientation) === 'landscape')>Horizontal (16:9)</option>
                </select></label>
            <label class="block"><span class="text-xs text-slate-400 uppercase tracking-wide">Image de couverture</span>
                <input type="file" name="poster_file" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-400 mt-2.5 file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-700 file:text-white"></label>
        </div>
        @if($video->poster())<img src="{{ $video->poster() }}" alt="" class="h-28 rounded-lg border border-slate-700">@endif
        <p class="text-xs text-slate-500">Couverture facultative pour YouTube (récupérée automatiquement), conseillée pour Vimeo et les fichiers envoyés.</p>
    </div>
</div>

<script>
(function () {
    const panels = document.querySelectorAll('[data-vf-panel]');
    document.querySelectorAll('[data-vf-source]').forEach(r => r.addEventListener('change', () => panels.forEach(p => p.hidden = p.dataset.vfPanel !== r.value)));
    // Fichier choisi : aperçu, durée et format détectés automatiquement.
    const file = document.querySelector('[data-vf-file]'), prev = document.querySelector('[data-vf-preview]');
    if (file) file.addEventListener('change', () => {
        const f = file.files[0]; if (!f) return;
        document.querySelector('[data-vf-fname]').textContent = f.name + ' — ' + (f.size / 1048576).toFixed(1) + ' Mo';
        prev.src = URL.createObjectURL(f); prev.classList.remove('hidden');
        prev.onloadedmetadata = () => {
            document.querySelector('[data-vf-duration]').value = Math.round(prev.duration) || '';
            document.querySelector('[data-vf-orientation]').value = prev.videoHeight >= prev.videoWidth ? 'portrait' : 'landscape';
            // Couverture automatique (image à ~1 s) si aucune n'est choisie : la médiathèque affiche
            // alors une image légère au lieu de télécharger la vidéo pour faire la vignette.
            const poster = document.querySelector('input[name="poster_file"]');
            if (!poster || poster.files.length || typeof DataTransfer === 'undefined') return;
            prev.addEventListener('seeked', function grab() {
                prev.removeEventListener('seeked', grab);
                const scale = Math.min(1, 1080 / Math.max(prev.videoWidth, prev.videoHeight));
                const c = document.createElement('canvas');
                c.width = Math.round(prev.videoWidth * scale); c.height = Math.round(prev.videoHeight * scale);
                try { c.getContext('2d').drawImage(prev, 0, 0, c.width, c.height); } catch (e) { return; }
                c.toBlob(b => {
                    if (!b || poster.files.length) return;
                    const dt = new DataTransfer();
                    dt.items.add(new File([b], 'couverture-auto.jpg', { type: 'image/jpeg' }));
                    poster.files = dt.files;
                }, 'image/jpeg', 0.82);
            });
            prev.currentTime = Math.min(1, (prev.duration || 2) / 2);
        };
    });
    // Fiches liées (administration) : recherche et pastilles.
    const box = document.querySelector('[data-vf-links]');
    if (!box) return;
    const input = box.querySelector('[data-vf-search]'), list = box.querySelector('[data-vf-results]'), chips = box.querySelector('[data-vf-chips]');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let t = null;
    input.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(async () => {
            const q = input.value.trim(); if (q.length < 2) { list.classList.add('hidden'); return; }
            const r = await fetch(box.dataset.url + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } }).then(x => x.json()).catch(() => []);
            list.innerHTML = r.length ? r.map(i => `<li><button type="button" class="w-full text-left px-3 py-2 text-sm text-slate-200 hover:bg-slate-800" data-key="${esc(i.key)}" data-title="${esc(i.title)}" data-meta="${esc(i.meta)}">${esc(i.title)} <small class="text-slate-500">${esc(i.meta)}</small></button></li>`).join('') : '<li class="px-3 py-2 text-sm text-slate-500">Aucune fiche trouvée</li>';
            list.classList.remove('hidden');
        }, 220);
    });
    list.addEventListener('click', e => {
        const b = e.target.closest('[data-key]'); if (!b) return;
        if (chips.querySelector(`[data-key="${CSS.escape(b.dataset.key)}"]`) || chips.children.length >= 6) { list.classList.add('hidden'); return; }
        chips.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center gap-2 pl-3 pr-1 py-1 rounded-full bg-orange-500/15 border border-orange-500/40 text-orange-200 text-xs" data-key="${esc(b.dataset.key)}">${esc(b.dataset.title)} <small class="opacity-70">${esc(b.dataset.meta)}</small><input type="hidden" name="links[]" value="${esc(b.dataset.key)}"><button type="button" class="w-5 h-5 rounded-full hover:bg-orange-500/30" data-vf-remove aria-label="Retirer">×</button></span>`);
        input.value = ''; list.classList.add('hidden');
    });
    chips.addEventListener('click', e => { if (e.target.closest('[data-vf-remove]')) e.target.closest('[data-key]').remove(); });
    document.addEventListener('click', e => { if (!box.contains(e.target)) list.classList.add('hidden'); });
})();
</script>
