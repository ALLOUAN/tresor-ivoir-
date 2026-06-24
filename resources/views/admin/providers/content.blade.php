@extends('layouts.app')

@section('title', 'Contenus prestataire')
@section('page-title', 'Gestion des contenus prestataire')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-white text-lg font-semibold">{{ $provider->name }}</h2>
                <p class="text-slate-400 text-sm mt-1">
                    Gérez les contenus liés à ce prestataire (articles, événements, photos/médias).
                </p>
            </div>
            <a href="{{ route('admin.providers.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Retour prestataires
            </a>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-white font-semibold mb-4">Articles sponsorisés</h3>
        @if($articles->isEmpty())
            <p class="text-slate-500 text-sm">Aucun article lié.</p>
        @else
            <form method="POST" action="{{ route('admin.providers.content.articles.reassign-bulk', $provider) }}">
                @csrf
                @method('PATCH')
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <select name="target_provider_id" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" required>
                        @foreach($providers as $target)
                            <option value="{{ $target->id }}" @selected((int) $target->id === (int) $provider->id)>{{ $target->name }}</option>
                        @endforeach
                    </select>
                    <button class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-2 rounded">Réattribuer en masse</button>
                </div>
                <div class="space-y-3">
                @foreach($articles as $article)
                    <div class="rounded-lg border border-slate-800 bg-slate-800/30 p-4">
                        <div class="min-w-0 flex items-start gap-3">
                            <input type="checkbox" name="article_ids[]" value="{{ $article->id }}"
                                   class="mt-1 rounded border-slate-600 bg-slate-800 text-amber-500">
                            <div>
                                <p class="text-white font-medium truncate">{{ $article->title_fr }}</p>
                                <p class="text-slate-500 text-xs mt-1">Statut: {{ $article->status }} · {{ $article->published_at?->translatedFormat('d M Y H:i') ?? 'Non publié' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </form>
        @endif
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-white font-semibold mb-4">Événements</h3>
        @if($events->isEmpty())
            <p class="text-slate-500 text-sm">Aucun événement lié.</p>
        @else
            <form method="POST" action="{{ route('admin.providers.content.events.reassign-bulk', $provider) }}">
                @csrf
                @method('PATCH')
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <select name="target_provider_id" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" required>
                        @foreach($providers as $target)
                            <option value="{{ $target->id }}" @selected((int) $target->id === (int) $provider->id)>{{ $target->name }}</option>
                        @endforeach
                    </select>
                    <button class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-2 rounded">Réattribuer en masse</button>
                </div>
                <div class="space-y-3">
                @foreach($events as $event)
                    <div class="rounded-lg border border-slate-800 bg-slate-800/30 p-4">
                        <div class="min-w-0 flex items-start gap-3">
                            <input type="checkbox" name="event_ids[]" value="{{ $event->id }}"
                                   class="mt-1 rounded border-slate-600 bg-slate-800 text-amber-500">
                            <div>
                                <p class="text-white font-medium truncate">{{ $event->title_fr }}</p>
                                <p class="text-slate-500 text-xs mt-1">Statut: {{ $event->status }} · {{ $event->starts_at?->translatedFormat('d M Y H:i') ?? 'Date non définie' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </form>
        @endif
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-white font-semibold mb-4">Photos / Médias</h3>

        {{-- Erreurs de validation --}}
        @if($errors->any())
        <div class="mb-4 p-3 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p><i class="fas fa-circle-exclamation mr-1"></i>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Formulaire d'upload --}}
        <form method="POST"
              action="{{ route('admin.providers.content.media.store', $provider) }}"
              enctype="multipart/form-data"
              class="mb-6">
            @csrf
            <div class="flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <span class="text-slate-400 text-sm group-hover:text-slate-200 transition">
                        <i class="fas fa-cloud-arrow-up mr-1 text-amber-400/70"></i>Photos à ajouter :
                    </span>
                    <input type="file" name="media_files[]" multiple accept="image/*"
                           id="prov-media-upload"
                           onchange="previewProvMedia(this)"
                           class="text-slate-300 text-xs file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-amber-600 file:transition">
                </label>
                <button type="submit"
                        class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition">
                    <i class="fas fa-upload mr-1.5"></i>Enregistrer
                </button>
            </div>
            <div id="prov-media-previews" class="flex flex-wrap gap-2 mt-3"></div>
        </form>

        @if($mediaItems->isEmpty())
            <p class="text-slate-500 text-sm">Aucune photo pour ce prestataire.</p>
        @else
            {{-- Grille des photos existantes --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
                @foreach($mediaItems as $item)
                <div class="relative group rounded-xl overflow-hidden border border-slate-700 bg-slate-800 aspect-video">
                    @if(in_array($item->type, ['photo', 'image']))
                        <img src="{{ $item->url }}" alt="{{ $item->original_name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-500">
                            <i class="fas fa-file text-2xl"></i>
                        </div>
                    @endif
                    {{-- Bouton supprimer --}}
                    <form method="POST"
                          action="{{ route('admin.providers.content.media.destroy', [$provider, $item]) }}"
                          class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition"
                          onsubmit="return confirm('Supprimer cette photo ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-7 h-7 rounded-full bg-red-600/80 hover:bg-red-600 text-white flex items-center justify-center">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </form>
                    <div class="absolute bottom-0 left-0 right-0 px-2 py-1 bg-black/50 text-slate-300 text-[10px] truncate opacity-0 group-hover:opacity-100 transition">
                        {{ $item->original_name }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Réattribution en masse --}}
            <details class="text-sm">
                <summary class="text-slate-400 cursor-pointer hover:text-slate-200 transition select-none">Réattribuer des photos à un autre prestataire</summary>
                <form method="POST" action="{{ route('admin.providers.content.media.reassign-bulk', $provider) }}" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <select name="target_provider_id" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" required>
                            @foreach($providers as $target)
                                <option value="{{ $target->id }}" @selected((int) $target->id === (int) $provider->id)>{{ $target->name }}</option>
                            @endforeach
                        </select>
                        <button class="bg-slate-600 hover:bg-slate-500 text-white text-xs px-3 py-2 rounded">Réattribuer la sélection</button>
                    </div>
                    <div class="space-y-2">
                    @foreach($mediaItems as $item)
                        <label class="flex items-center gap-3 rounded-lg border border-slate-800 bg-slate-800/30 p-3 cursor-pointer hover:border-slate-600 transition">
                            <input type="checkbox" name="media_ids[]" value="{{ $item->id }}"
                                   class="rounded border-slate-600 bg-slate-800 text-amber-500">
                            @if(in_array($item->type, ['photo', 'image']))
                                <img src="{{ $item->url }}" alt="" class="w-14 h-10 rounded object-cover border border-slate-700">
                            @endif
                            <span class="text-slate-300 text-sm truncate">{{ $item->original_name ?: $item->url }}</span>
                        </label>
                    @endforeach
                    </div>
                </form>
            </details>
        @endif
    </div>
</div>

<script>
function previewProvMedia(input) {
    const preview = document.getElementById('prov-media-previews');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = 'w-20 h-14 object-cover rounded-lg border border-slate-600';
        preview.appendChild(img);
    });
}
</script>
@endsection

