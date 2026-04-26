@extends('layouts.app')

@section('title', 'Modifier — ' . $article->title_fr)
@section('page-title', 'Modifier l\'article')

@section('header-actions')
<div class="flex items-center gap-2">
    <button type="button" onclick="openArticlePreviewTab()"
            class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-eye"></i> Prévisualiser
    </button>
    @if($article->status === 'published')
    <a href="{{ route('articles.show', $article->slug_fr) }}" target="_blank"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-external-link-alt"></i> Voir en ligne
    </a>
    @endif
    <a href="{{ route('editor.articles.index') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>
@endsection

@section('content')

{{-- Status banner --}}
@php $statusMap = [
    'published' => ['bg-emerald-900/30 border-emerald-800 text-emerald-300', 'fas fa-circle-check', 'Publié'],
    'draft'     => ['bg-slate-800 border-slate-700 text-slate-400', 'fas fa-pen', 'Brouillon'],
    'review'    => ['bg-amber-900/30 border-amber-800 text-amber-300', 'fas fa-clock', 'En attente de révision'],
    'archived'  => ['bg-slate-800 border-slate-700 text-slate-500', 'fas fa-box-archive', 'Archivé'],
]; [$cls, $icon, $lbl] = $statusMap[$article->status] ?? ['bg-slate-800 border-slate-700 text-slate-400','fas fa-circle',ucfirst($article->status)]; @endphp
<div class="flex items-center gap-2 mb-5 px-4 py-3 border {{ $cls }} rounded-xl text-sm">
    <i class="{{ $icon }}"></i>
    <span>{{ $lbl }}</span>
    @if($article->published_at)
    <span class="text-slate-500 text-xs ml-1">— {{ $article->published_at->translatedFormat('d F Y à H:i') }}</span>
    @endif
</div>

<form method="POST" action="{{ route('editor.articles.update', $article) }}" id="articleForm" enctype="multipart/form-data">
@csrf
@method('PUT')

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
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Titre (FR) <span class="text-red-400">*</span></label>
                <input type="text" name="title_fr" id="title_fr" value="{{ old('title_fr', $article->title_fr) }}"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-white text-base outline-none transition placeholder-slate-600"
                    oninput="autoSlug(this.value, 'slug_fr')">
                @error('title_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Slug FR</label>
                <div class="flex items-center gap-2">
                    <span class="text-slate-600 text-xs shrink-0">/articles/</span>
                    <input type="text" name="slug_fr" id="slug_fr" value="{{ old('slug_fr', $article->slug_fr) }}"
                        class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition font-mono">
                </div>
                @error('slug_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Résumé (FR)</label>
                <textarea name="excerpt_fr" id="excerpt_fr" rows="3"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition resize-y">{{ old('excerpt_fr', $article->excerpt_fr) }}</textarea>
                @error('excerpt_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs text-slate-400 font-medium uppercase tracking-wider">Contenu (FR)</label>
                    <span id="wordCount-fr" class="text-slate-600 text-xs">0 mots</span>
                </div>
                <textarea name="content_fr" id="content_fr" rows="20"
                    class="rich-editor w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition resize-y leading-relaxed">{{ old('content_fr', $article->content_fr) }}</textarea>
                @error('content_fr') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-magnifying-glass text-amber-500/60"></i> SEO (FR)
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">Titre SEO</label>
                        <input type="text" name="meta_title_fr" value="{{ old('meta_title_fr', $article->meta_title_fr) }}"
                            placeholder="Laissez vide pour utiliser le titre principal"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">Méta description</label>
                        <textarea name="meta_desc_fr" rows="2"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition resize-none">{{ old('meta_desc_fr', $article->meta_desc_fr) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- EN panel --}}
        <div id="panel-en" class="space-y-5 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Title (EN)</label>
                <input type="text" name="title_en" value="{{ old('title_en', $article->title_en) }}"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-white text-base outline-none transition placeholder-slate-600"
                    oninput="autoSlug(this.value, 'slug_en')">
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Slug EN</label>
                <div class="flex items-center gap-2">
                    <span class="text-slate-600 text-xs shrink-0">/articles/</span>
                    <input type="text" name="slug_en" id="slug_en" value="{{ old('slug_en', $article->slug_en) }}"
                        class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition font-mono">
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <label class="block text-xs text-slate-400 font-medium mb-2 uppercase tracking-wider">Excerpt (EN)</label>
                <textarea name="excerpt_en" rows="3"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition resize-y">{{ old('excerpt_en', $article->excerpt_en) }}</textarea>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs text-slate-400 font-medium uppercase tracking-wider">Content (EN)</label>
                    <span id="wordCount-en" class="text-slate-600 text-xs">0 words</span>
                </div>
                <textarea name="content_en" id="content_en" rows="20"
                    class="rich-editor w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-3 text-slate-300 text-sm outline-none transition resize-y leading-relaxed">{{ old('content_en', $article->content_en) }}</textarea>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-magnifying-glass text-amber-500/60"></i> SEO (EN)
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">SEO Title</label>
                        <input type="text" name="meta_title_en" value="{{ old('meta_title_en', $article->meta_title_en) }}"
                            placeholder="Leave empty to use main title"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition placeholder-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5">Meta description</label>
                        <textarea name="meta_desc_en" rows="2"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-4 py-2.5 text-slate-300 text-sm outline-none transition resize-none">{{ old('meta_desc_en', $article->meta_desc_en) }}</textarea>
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
                        <option value="draft" {{ old('status', $article->status)==='draft' ? 'selected':'' }}>Brouillon</option>
                        <option value="review" {{ old('status', $article->status)==='review' ? 'selected':'' }}>En révision</option>
                        @if(auth()->user()->isAdmin())
                        <option value="published" {{ old('status', $article->status)==='published' ? 'selected':'' }}>Publié</option>
                        <option value="archived" {{ old('status', $article->status)==='archived' ? 'selected':'' }}>Archivé</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Date de publication</label>
                    <input type="datetime-local" name="published_at"
                        value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Publication planifiée</label>
                    <input type="datetime-local" name="scheduled_at"
                        value="{{ old('scheduled_at', $article->scheduled_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                </div>
            </div>
            <div class="flex gap-2 mt-5">
                <button type="submit"
                    class="w-full px-3 py-2.5 bg-amber-500 hover:bg-amber-600 text-black text-xs font-semibold rounded-lg transition">
                    <i class="fas fa-floppy-disk mr-1"></i> Enregistrer
                </button>
            </div>
            <p id="autosaveStatus" class="text-xs text-slate-600 mt-2 min-h-[1.25rem]" aria-live="polite"></p>
        </div>

        {{-- Quick status actions --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-2">
            <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-3">Actions rapides</h3>
            @if($article->status === 'draft')
            <form method="POST" action="{{ route('editor.articles.status', $article) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="review">
                <button class="w-full px-3 py-2 bg-amber-900/30 hover:bg-amber-900/50 border border-amber-800 text-amber-300 text-xs rounded-lg transition text-left flex items-center gap-2">
                    <i class="fas fa-paper-plane w-4 text-center"></i> Soumettre pour révision
                </button>
            </form>
            @endif
            @if(auth()->user()->isAdmin() && $article->status === 'review')
            <form method="POST" action="{{ route('editor.articles.status', $article) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="published">
                <button class="w-full px-3 py-2 bg-emerald-900/30 hover:bg-emerald-900/50 border border-emerald-800 text-emerald-300 text-xs rounded-lg transition text-left flex items-center gap-2">
                    <i class="fas fa-check w-4 text-center"></i> Publier maintenant
                </button>
            </form>
            @endif
            @if($article->status === 'published')
            <form method="POST" action="{{ route('editor.articles.status', $article) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="archived">
                <button class="w-full px-3 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-400 text-xs rounded-lg transition text-left flex items-center gap-2">
                    <i class="fas fa-box-archive w-4 text-center"></i> Archiver
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('editor.articles.destroy', $article) }}"
                  onsubmit="return confirm('Supprimer définitivement cet article ?')">
                @csrf @method('DELETE')
                <button class="w-full px-3 py-2 bg-red-900/20 hover:bg-red-900/40 border border-red-900/50 text-red-400 text-xs rounded-lg transition text-left flex items-center gap-2">
                    <i class="fas fa-trash w-4 text-center"></i> Supprimer
                </button>
            </form>
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
                <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>
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
            @if($article->cover_url)
            <div class="rounded-lg overflow-hidden h-32 bg-slate-800 mb-3">
                <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt }}" class="w-full h-full object-cover">
            </div>
            @endif
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">URL de l'image</label>
                    <input type="url" name="cover_url" id="cover_url" value="{{ old('cover_url', $article->cover_url) }}"
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
                <div id="coverPreview" class="{{ $article->cover_url ? '' : 'hidden' }} rounded-lg overflow-hidden h-32 bg-slate-800">
                    <img id="coverImg" src="{{ $article->cover_url }}" alt="" class="w-full h-full object-cover">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Texte alternatif</label>
                    <input type="text" name="cover_alt" value="{{ old('cover_alt', $article->cover_alt) }}"
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
            @php $selectedTags = old('tags', $article->tags->pluck('id')->toArray()); @endphp
            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto pr-1">
                @foreach($tags as $tag)
                <label class="flex items-center gap-1.5 cursor-pointer group">
                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                        {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}
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
                @foreach([
                    ['is_featured', 'toggle-feat', 'amber', 'À la une', 'Mis en avant sur la page d\'accueil'],
                    ['is_destination', 'toggle-dest', 'blue', 'Destination', 'Article de type destination touristique'],
                    ['is_sponsored', 'toggle-spons', 'purple', 'Sponsorisé', 'Contenu partenaire'],
                ] as [$field, $id, $color, $title, $desc])
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-slate-300 text-sm">{{ $title }}</p>
                        <p class="text-slate-600 text-xs">{{ $desc }}</p>
                    </div>
                    <div class="relative">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1"
                            {{ old($field, $article->$field) ? 'checked':'' }}
                            class="sr-only peer" id="{{ $id }}">
                        <div class="w-9 h-5 bg-slate-700 peer-checked:bg-{{ $color }}-500 rounded-full transition cursor-pointer"
                             onclick="document.getElementById('{{ $id }}').click()"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-4 pointer-events-none"></div>
                    </div>
                </label>
                @endforeach
            </div>
            <div id="sponsor_group" class="mt-4 {{ old('is_sponsored', $article->is_sponsored) ? '' : 'hidden' }}">
                <label class="block text-xs text-slate-500 mb-1.5">Sponsor <span class="text-red-400">*</span></label>
                <select name="sponsor_id"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-purple-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition">
                    <option value="">— Choisir un sponsor —</option>
                    @foreach(($sponsors ?? collect()) as $sponsor)
                    <option value="{{ $sponsor->id }}" {{ (string) old('sponsor_id', $article->sponsor_id) === (string) $sponsor->id ? 'selected' : '' }}>
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
                <input type="number" name="reading_time" id="reading_time" value="{{ old('reading_time', $article->reading_time) }}"
                    min="1" max="120" placeholder="auto"
                    class="w-20 bg-slate-800 border border-slate-700 focus:border-amber-500/60 rounded-lg px-3 py-2.5 text-slate-300 text-sm outline-none transition text-center">
                <span class="text-slate-500 text-xs">minutes (laissez vide pour auto)</span>
            </div>
        </div>

        {{-- Meta --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 text-xs text-slate-600 space-y-1">
            <p>UUID : <span class="font-mono text-slate-500">{{ $article->uuid }}</span></p>
            <p>Créé : {{ $article->created_at->diffForHumans() }}</p>
            <p>Modifié : {{ $article->updated_at->diffForHumans() }}</p>
            <p>Vues : {{ number_format($article->views_count ?? 0) }}</p>
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
    const sponsorToggle = document.getElementById('toggle-spons');
    const sponsorGroup = document.getElementById('sponsor_group');
    if (!sponsorToggle || !sponsorGroup) return;
    sponsorGroup.classList.toggle('hidden', !sponsorToggle.checked);
}

document.addEventListener('DOMContentLoaded', () => {
    const fr = document.getElementById('content_fr');
    if (fr && fr.value) countWords(fr, 'wordCount-fr');
    const en = document.getElementById('content_en');
    if (en && en.value) countWords(en, 'wordCount-en');
    const file = document.getElementById('cover_image');
    if (file) {
        file.addEventListener('change', () => previewCoverFile('cover_image'));
    }
    const sponsorToggle = document.getElementById('toggle-spons');
    if (sponsorToggle) {
        sponsorToggle.addEventListener('change', toggleSponsoredFields);
        toggleSponsoredFields();
    }
});
</script>
@include('editor.partials.article-rich-tools', ['article' => $article, 'errorsPresent' => $errors->any()])
@endpush

@endsection
