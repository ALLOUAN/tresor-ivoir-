@extends('layouts.app')

@section('title', 'Nouvel article')
@section('page-title', 'Nouvel article')

@section('header-actions')
<div class="flex items-center gap-2">
    <button type="button" onclick="openArticlePreviewModal()"
            class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-eye"></i> Prévisualiser
    </button>
    <a href="{{ route('editor.articles.index') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>
@endsection

@section('content')

<form method="POST" action="{{ route('editor.articles.store') }}" id="articleForm" enctype="multipart/form-data">
@csrf

{{-- Lang tabs --}}
<div class="flex items-center gap-1 mb-6 bg-slate-900/50 border border-slate-800 rounded-xl p-1 w-fit">
    <button type="button" onclick="switchLang('fr')" id="tab-fr"
        class="px-4 py-2 rounded-lg text-xs font-medium transition lang-tab bg-slate-700 text-white">
        🇫🇷 Français
    </button>
    <button type="button" onclick="switchLang('en')" id="tab-en"
        class="px-4 py-2 rounded-lg text-xs font-medium transition lang-tab text-slate-400 hover:text-white hover:bg-slate-800">
        🇬🇧 English
    </button>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6">

    {{-- ── Main content ────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- FR panel --}}
        <div id="panel-fr" class="space-y-5">
            {{-- Title FR --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Titre (FR) <span class="text-red-400">*</span></label>
                <input type="text" name="title_fr" id="title_fr" value="{{ old('title_fr') }}"
                    placeholder="Ex: Les palais royaux d'Abomey…"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-white text-base outline-none transition placeholder-slate-600"
                    oninput="autoSlug(this.value, 'slug_fr')">
                @error('title_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Slug FR --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Slug FR</label>
                <div class="flex items-center gap-2">
                    <span class="text-slate-600 text-xs shrink-0">/articles/</span>
                    <input type="text" name="slug_fr" id="slug_fr" value="{{ old('slug_fr') }}"
                        placeholder="titre-de-l-article"
                        class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600 font-mono">
                </div>
                @error('slug_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Excerpt FR --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Résumé (FR)</label>
                <textarea name="excerpt_fr" id="excerpt_fr" rows="3" placeholder="Un court résumé accrocheur…"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition placeholder-slate-600 resize-y">{{ old('excerpt_fr') }}</textarea>
                @error('excerpt_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Content FR --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs text-slate-400 font-medium uppercase tracking-wider">Contenu (FR)</label>
                    <span id="wordCount-fr" class="text-slate-600 text-xs">0 mots</span>
                </div>
                <textarea name="content_fr" id="content_fr" rows="20"
                    placeholder="Rédigez votre article ici…"
                    class="rich-editor w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition placeholder-slate-600 resize-y leading-relaxed">{{ old('content_fr') }}</textarea>
                @error('content_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- SEO FR --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-magnifying-glass text-amber-500/60"></i> SEO (FR)
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">Titre SEO</label>
                        <input type="text" name="meta_title_fr" value="{{ old('meta_title_fr') }}"
                            placeholder="Laissez vide pour utiliser le titre principal"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">Méta description</label>
                        <textarea name="meta_desc_fr" rows="2" placeholder="Description pour les moteurs de recherche (max 160 car.)…"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600 resize-none">{{ old('meta_desc_fr') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- EN panel --}}
        <div id="panel-en" class="space-y-5 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Title (EN)</label>
                <input type="text" name="title_en" value="{{ old('title_en') }}"
                    placeholder="e.g. The Royal Palaces of Abomey…"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-white text-base outline-none transition placeholder-slate-600"
                    oninput="autoSlug(this.value, 'slug_en')">
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Slug EN</label>
                <div class="flex items-center gap-2">
                    <span class="text-slate-600 text-xs shrink-0">/articles/</span>
                    <input type="text" name="slug_en" id="slug_en" value="{{ old('slug_en') }}"
                        placeholder="article-title"
                        class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600 font-mono">
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Excerpt (EN)</label>
                <textarea name="excerpt_en" rows="3" placeholder="A short catchy summary…"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition placeholder-slate-600 resize-y">{{ old('excerpt_en') }}</textarea>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs text-slate-400 font-medium uppercase tracking-wider">Content (EN)</label>
                    <span id="wordCount-en" class="text-slate-600 text-xs">0 words</span>
                </div>
                <textarea name="content_en" id="content_en" rows="20"
                    placeholder="Write your article here…"
                    class="rich-editor w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition placeholder-slate-600 resize-y leading-relaxed">{{ old('content_en') }}</textarea>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-magnifying-glass text-amber-500/60"></i> SEO (EN)
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">SEO Title</label>
                        <input type="text" name="meta_title_en" value="{{ old('meta_title_en') }}"
                            placeholder="Leave empty to use main title"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">Meta description</label>
                        <textarea name="meta_desc_en" rows="2" placeholder="Description for search engines (max 160 chars)…"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600 resize-none">{{ old('meta_desc_en') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /main --}}

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Publish --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-paper-plane text-amber-500/60"></i> Publication
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Statut</label>
                    <select name="status"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                        <option value="draft" {{ old('status','draft')==='draft' ? 'selected':'' }}>Brouillon</option>
                        <option value="review" {{ old('status')==='review' ? 'selected':'' }}>Soumettre pour révision</option>
                        @if(auth()->user()->isAdmin())
                        <option value="published" {{ old('status')==='published' ? 'selected':'' }}>Publier directement</option>
                        <option value="archived" {{ old('status')==='archived' ? 'selected':'' }}>Archiver</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Mode de publication</label>
                    <select name="publication_mode" id="publication_mode"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition"
                        onchange="togglePublicationMode()">
                        <option value="now" {{ old('publication_mode', 'now') === 'now' ? 'selected' : '' }}>Publier / soumettre maintenant</option>
                        <option value="schedule" {{ old('publication_mode') === 'schedule' ? 'selected' : '' }}>Planifier la publication</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Date de publication</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                </div>
                <div id="scheduled_at_group">
                    <label class="block text-xs text-slate-500 mb-1.5">Publication planifiée</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                    @error('scheduled_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-2 mt-5">
                <button type="submit" name="status" value="draft"
                    class="flex-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium rounded-lg transition">
                    <i class="fas fa-floppy-disk mr-1"></i> Brouillon
                </button>
                <button type="submit" name="status" value="review"
                    class="flex-1 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-black text-xs font-semibold rounded-lg transition">
                    <i class="fas fa-paper-plane mr-1"></i> Soumettre
                </button>
            </div>
            <p id="autosaveStatus" class="text-xs text-slate-600 mt-2 min-h-[1.25rem]" aria-live="polite"></p>
            <button type="button" class="text-[11px] text-slate-500 hover:text-amber-400/90 mt-1 underline"
                    onclick="try { localStorage.removeItem('tresor_editor_article_draft_v1'); document.getElementById('autosaveStatus').textContent='Brouillon local effacé'; } catch(e) {}">
                Effacer le brouillon local
            </button>
        </div>

        {{-- Category --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-folder text-amber-500/60"></i> Rubrique <span class="text-red-400">*</span>
            </h3>
            <select name="category_id"
                class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                <option value="">— Choisir une rubrique —</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name_fr }}
                </option>
                @endforeach
            </select>
            @error('category_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Cover --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-image text-amber-500/60"></i> Image de couverture
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">URL de l'image</label>
                    <input type="url" name="cover_url" id="cover_url" value="{{ old('cover_url') }}"
                        placeholder="https://…"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600"
                        oninput="previewCover(this.value)">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Ou importer un fichier</label>
                    <input type="file" name="cover_image" id="cover_image" accept="image/jpeg,image/png,image/webp"
                        class="w-full bg-slate-800 border border-slate-700 file:border-0 file:bg-slate-700 file:text-slate-300 file:px-3 file:py-2 file:mr-3 rounded-lg px-3 py-2 text-slate-400 text-xs outline-none transition">
                    <p class="text-[11px] text-slate-600 mt-1">JPG, PNG ou WEBP (max 4 Mo). Le fichier remplace l'URL si les deux sont remplis.</p>
                    @error('cover_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div id="coverPreview" class="hidden rounded-lg overflow-hidden h-32 bg-slate-800">
                    <img id="coverImg" src="" alt="" class="w-full h-full object-cover">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Texte alternatif</label>
                    <input type="text" name="cover_alt" value="{{ old('cover_alt') }}"
                        placeholder="Description de l'image…"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600">
                </div>
            </div>
        </div>

        {{-- Tags --}}
        @if($tags->isNotEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-tags text-amber-500/60"></i> Tags
            </h3>
            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto pr-1">
                @foreach($tags as $tag)
                <label class="flex items-center gap-1.5 cursor-pointer group">
                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                        {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                        class="w-3.5 h-3.5 accent-amber-500 rounded">
                    <span class="text-slate-400 text-xs group-hover:text-white transition">{{ $tag->name_fr }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Badges --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-certificate text-amber-500/60"></i> Badges
            </h3>
            <div class="space-y-3">
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-slate-300 text-sm">À la une</p>
                        <p class="text-slate-600 text-xs">Mis en avant sur la page d'accueil</p>
                    </div>
                    <div class="relative">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked':'' }}
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-700 peer-checked:bg-amber-500 rounded-full transition peer cursor-pointer"
                             onclick="this.previousElementSibling.click()"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-4 pointer-events-none"></div>
                    </div>
                </label>
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-slate-300 text-sm">Destination</p>
                        <p class="text-slate-600 text-xs">Article de type destination touristique</p>
                    </div>
                    <div class="relative">
                        <input type="hidden" name="is_destination" value="0">
                        <input type="checkbox" name="is_destination" value="1" {{ old('is_destination') ? 'checked':'' }}
                            class="sr-only peer" id="toggle-dest">
                        <div class="w-9 h-5 bg-slate-700 peer-checked:bg-blue-500 rounded-full transition cursor-pointer"
                             onclick="document.getElementById('toggle-dest').click()"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-4 pointer-events-none"></div>
                    </div>
                </label>
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-slate-300 text-sm">Sponsorisé</p>
                        <p class="text-slate-600 text-xs">Contenu partenaire</p>
                    </div>
                    <div class="relative">
                        <input type="hidden" name="is_sponsored" value="0">
                        <input type="checkbox" name="is_sponsored" id="is_sponsored" value="1" {{ old('is_sponsored') ? 'checked':'' }}
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-700 peer-checked:bg-purple-500 rounded-full transition cursor-pointer"
                             onclick="document.getElementById('is_sponsored').click()"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-4 pointer-events-none"></div>
                    </div>
                </label>
            </div>
            <div id="sponsor_group" class="mt-4 {{ old('is_sponsored') ? '' : 'hidden' }}">
                <label class="block text-xs text-slate-500 mb-1.5">Sponsor <span class="text-red-400">*</span></label>
                <select name="sponsor_id"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-purple-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                    <option value="">— Choisir un sponsor —</option>
                    @foreach(($sponsors ?? collect()) as $sponsor)
                    <option value="{{ $sponsor->id }}" {{ (string) old('sponsor_id') === (string) $sponsor->id ? 'selected' : '' }}>
                        {{ $sponsor->name }}
                    </option>
                    @endforeach
                </select>
                @error('sponsor_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Reading time --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-amber-500/60"></i> Temps de lecture
            </h3>
            <div class="flex items-center gap-2">
                <input type="number" name="reading_time" id="reading_time" value="{{ old('reading_time') }}"
                    min="1" max="120" placeholder="auto"
                    class="w-20 bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition text-center">
                <span class="text-slate-500 text-xs">minutes (laissez vide pour auto)</span>
            </div>
        </div>

    </div>{{-- /sidebar --}}

</div>{{-- /grid --}}

</form>

@push('scripts')
<script>
function switchLang(lang) {
    ['fr','en'].forEach(l => {
        document.getElementById('panel-'+l).classList.toggle('hidden', l !== lang);
        const tab = document.getElementById('tab-'+l);
        tab.classList.toggle('bg-slate-700', l === lang);
        tab.classList.toggle('text-white', l === lang);
        tab.classList.toggle('text-slate-400', l !== lang);
        tab.classList.toggle('hover:text-white', l !== lang);
        tab.classList.toggle('hover:bg-slate-800', l !== lang);
    });
}

function autoSlug(value, targetId) {
    const slug = value.toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim().replace(/\s+/g, '-').replace(/-+/g, '-');
    document.getElementById(targetId).value = slug;
}

function countWords(el, countId) {
    const words = el.value.trim().split(/\s+/).filter(w => w.length > 0);
    document.getElementById(countId).textContent = words.length + ' mots';
}

function previewCover(url) {
    const preview = document.getElementById('coverPreview');
    const img = document.getElementById('coverImg');
    if (url && url.startsWith('http')) {
        img.src = url;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

function previewCoverFile(fileInputId) {
    const input = document.getElementById(fileInputId);
    const preview = document.getElementById('coverPreview');
    const img = document.getElementById('coverImg');
    if (!input || !input.files || !input.files[0]) return;

    const file = input.files[0];
    img.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
}

function toggleSponsoredFields() {
    const sponsorToggle = document.getElementById('is_sponsored');
    const sponsorGroup = document.getElementById('sponsor_group');
    if (!sponsorToggle || !sponsorGroup) return;
    sponsorGroup.classList.toggle('hidden', !sponsorToggle.checked);
}

function togglePublicationMode() {
    const publicationMode = document.getElementById('publication_mode');
    const scheduledGroup = document.getElementById('scheduled_at_group');
    if (!publicationMode || !scheduledGroup) return;
    scheduledGroup.classList.toggle('hidden', publicationMode.value !== 'schedule');
}

// Init word count if content already present
document.addEventListener('DOMContentLoaded', () => {
    const fr = document.getElementById('content_fr');
    if (fr && fr.value) countWords(fr, 'wordCount-fr');
    const en = document.getElementById('content_en');
    if (en && en.value) countWords(en, 'wordCount-en');
    const url = document.getElementById('cover_url');
    if (url && url.value) previewCover(url.value);
    const file = document.getElementById('cover_image');
    if (file) {
        file.addEventListener('change', () => previewCoverFile('cover_image'));
    }
    const sponsorToggle = document.getElementById('is_sponsored');
    if (sponsorToggle) {
        sponsorToggle.addEventListener('change', toggleSponsoredFields);
        toggleSponsoredFields();
    }
    togglePublicationMode();
});
</script>
@include('editor.partials.article-rich-tools', ['article' => null, 'errorsPresent' => $errors->any()])
@endpush

@endsection
