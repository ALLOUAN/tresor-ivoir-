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
        @if($mediaItems->isEmpty())
            <p class="text-slate-500 text-sm">Aucun média lié.</p>
        @else
            <form method="POST" action="{{ route('admin.providers.content.media.reassign-bulk', $provider) }}">
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
                @foreach($mediaItems as $item)
                    <div class="rounded-lg border border-slate-800 bg-slate-800/30 p-4">
                        <div class="min-w-0 flex items-start gap-3">
                            <input type="checkbox" name="media_ids[]" value="{{ $item->id }}"
                                   class="mt-1 rounded border-slate-600 bg-slate-800 text-amber-500">
                            @if($item->type === 'image')
                                <img src="{{ $item->url }}" alt="" class="w-16 h-12 rounded object-cover border border-slate-700">
                            @endif
                            <div>
                                <p class="text-white font-medium truncate">{{ $item->original_name }}</p>
                                <p class="text-slate-500 text-xs mt-1">Type: {{ $item->type }} · {{ $item->created_at?->translatedFormat('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

