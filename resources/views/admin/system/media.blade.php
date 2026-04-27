@extends('layouts.app')

@section('title', 'Gestionnaire de médias')
@section('page-title', 'Gestionnaire de médias')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'media'])

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-800/40">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center shrink-0">
                <i class="fas fa-folder-open text-indigo-300"></i>
            </div>
            <div>
                <h2 class="text-white font-semibold text-lg">Gestionnaire de médias</h2>
                <p class="text-slate-400 text-xs mt-0.5">Images, vidéos et documents pour le site et les contenus.</p>
            </div>
        </div>
    </div>

    <div class="px-5 py-4 border-b border-slate-800">
        <div class="rounded-lg border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-100">
            <p class="font-medium text-sky-200 mb-1 flex items-center gap-2">
                <i class="fas fa-circle-info text-sky-300"></i> À propos
            </p>
            <p class="text-sky-100/90 text-xs sm:text-sm leading-relaxed">
                Cette section permet de gérer vos médias (images, vidéos, documents). Vous pouvez les utiliser dans les paramètres et le contenu du site. Taille maximale par fichier&nbsp;: 50&nbsp;Mo.
            </p>
        </div>
    </div>

    <div class="px-5 py-5 border-b border-slate-800 bg-slate-900/40">
        <h3 class="text-white font-semibold text-sm mb-3 flex items-center gap-2">
            <i class="fas fa-images text-amber-400"></i>
            Formulaire de publication galerie (ultra)
        </h3>
        <form method="POST" action="{{ route('admin.administration.media.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Titre galerie</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex: Festival de Bassam 2026"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Section</label>
                    <select name="section" class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                        @foreach(['home_gallery' => 'Accueil - Galerie', 'hero' => 'Hero', 'discoveries' => 'Découvertes'] as $value => $label)
                        <option value="{{ $value }}" {{ old('section', 'home_gallery') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Légende</label>
                    <input type="text" name="caption" value="{{ old('caption') }}" placeholder="Texte court affichable sous les images"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Texte alternatif (accessibilité)</label>
                    <input type="text" name="alt_text" value="{{ old('alt_text') }}" placeholder="Description de l'image"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Crédit photo</label>
                    <input type="text" name="credit" value="{{ old('credit') }}" placeholder="Ex: Trésors d'Ivoire / JD"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Prix de l'image (FCFA)</label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" placeholder="Ex: 15000"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                    <p class="text-[11px] text-slate-500 mt-1">Optionnel. S'applique à chaque fichier du lot.</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Ordre d'affichage (départ)</label>
                    <input type="number" name="display_order" min="0" max="9999" value="{{ old('display_order', 0) }}"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Date de publication</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                           class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200">
                </div>
                <div class="md:col-span-2 flex flex-wrap items-center gap-4 pt-1">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-emerald-500">
                        Activer immédiatement
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-amber-500">
                        Marquer en vedette
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Fichiers images (multi-upload)</label>
                <input id="gallery-files-input" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="w-full bg-slate-950/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-300 file:mr-3 file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-black file:font-semibold">
                <p class="text-[11px] text-slate-500 mt-1">JPG, PNG, WEBP - jusqu'à 30 images par lot (8 Mo max / image).</p>
            </div>
            <div id="gallery-files-preview-wrap" class="hidden">
                <p class="text-[11px] text-slate-500 mb-2">Aperçu avant publication</p>
                <div id="gallery-files-preview" class="grid grid-cols-2 sm:grid-cols-4 gap-2"></div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-fuchsia-600 hover:from-amber-400 hover:to-fuchsia-500 text-white text-sm font-semibold px-4 py-2.5 transition">
                    <i class="fas fa-cloud-arrow-up"></i>
                    Publier les images
                </button>
            </div>
        </form>
    </div>

    <div class="p-5">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        @if($items->isEmpty())
            <div class="text-center py-16 rounded-xl border border-dashed border-slate-700 bg-slate-800/10">
                <i class="fas fa-folder-open text-5xl text-slate-600 mb-4"></i>
                <p class="text-slate-400 font-medium">Aucun média pour le moment</p>
                <p class="text-slate-600 text-sm mt-2">Utilisez le bouton « Télécharger un média » pour ajouter un fichier.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($items as $item)
                    <div class="rounded-xl border border-slate-800 bg-slate-800/20 p-4 flex flex-col gap-3">
                        <div class="aspect-video rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden">
                            @if($item->type === 'image')
                                <img src="{{ $item->url }}" alt="" class="max-h-full max-w-full object-contain">
                            @elseif($item->type === 'video')
                                <i class="fas fa-film text-4xl text-slate-500"></i>
                            @else
                                <i class="fas fa-file-lines text-4xl text-slate-500"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate" title="{{ $item->original_name }}">{{ $item->original_name }}</p>
                            @if(!empty($item->title))
                            <p class="text-amber-300 text-xs mt-1 truncate">{{ $item->title }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                @if(!empty($item->section))
                                <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-indigo-900/50 text-indigo-200 text-[10px]">{{ $item->section }}</span>
                                @endif
                                @if($item->is_featured ?? false)
                                <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-amber-900/50 text-amber-200 text-[10px]">Vedette</span>
                                @endif
                                @if(isset($item->price) && $item->price !== null && (float) $item->price > 0)
                                <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-emerald-900/50 text-emerald-200 text-[10px]">{{ number_format((float) $item->price, 0, ',', ' ') }} FCFA</span>
                                @endif
                                <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 {{ ($item->is_active ?? true) ? 'bg-emerald-900/50 text-emerald-200' : 'bg-slate-700 text-slate-300' }} text-[10px]">
                                    {{ ($item->is_active ?? true) ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            <p class="text-slate-500 text-xs mt-1">
                                <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-slate-700/80 text-slate-300 capitalize">{{ $item->type }}</span>
                                @php
                                    $bytes = (int) $item->size_bytes;
                                    $human = $bytes >= 1048576
                                        ? number_format($bytes / 1048576, 1).' Mo'
                                        : ($bytes >= 1024 ? number_format($bytes / 1024, 1).' Ko' : $bytes.' o');
                                @endphp
                                <span class="text-slate-600">·</span> {{ $human }}
                            </p>
                            <p class="text-slate-600 text-xs mt-0.5 truncate">{{ $item->created_at?->translatedFormat('d M Y, H:i') }}</p>
                            @if(!empty($item->caption))
                            <p class="text-slate-500 text-[11px] mt-1 line-clamp-2">{{ $item->caption }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-auto">
                            <input type="text" readonly value="{{ url($item->url) }}"
                                   class="flex-1 min-w-0 bg-slate-950/50 border border-slate-700 rounded px-2 py-1 text-[10px] text-slate-400 font-mono truncate"
                                   onclick="this.select()">
                            <form method="POST" action="{{ route('admin.administration.media.destroy', $item) }}" onsubmit="return confirm('Supprimer ce média ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 p-2 rounded-lg border border-slate-700 hover:border-rose-500/40 transition" title="Supprimer">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($items->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $items->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('gallery-files-input');
    const wrap = document.getElementById('gallery-files-preview-wrap');
    const grid = document.getElementById('gallery-files-preview');
    if (!input || !wrap || !grid) return;

    input.addEventListener('change', () => {
        grid.innerHTML = '';
        const files = Array.from(input.files || []);
        if (files.length === 0) {
            wrap.classList.add('hidden');
            return;
        }

        files.forEach((file) => {
            if (!file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            const card = document.createElement('figure');
            card.className = 'rounded-lg overflow-hidden border border-slate-800 bg-slate-900/70';
            card.innerHTML = `
                <img src="${url}" alt="" class="w-full h-24 object-cover">
                <figcaption class="px-2 py-1 text-[10px] text-slate-400 truncate">${file.name}</figcaption>
            `;
            grid.appendChild(card);
        });

        wrap.classList.toggle('hidden', grid.childElementCount === 0);
    });
});
</script>
@endpush
@endsection
