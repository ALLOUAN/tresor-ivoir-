@extends('layouts.app')

@section('title', 'Mes publications')
@section('page-title', 'Publications de ma fiche')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <p class="text-slate-500 text-xs">Total publications</p>
            <p class="text-white text-2xl font-bold mt-1">{{ number_format($totalPublications) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <p class="text-slate-500 text-xs">Publiées</p>
            <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($publishedCount) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <p class="text-slate-500 text-xs">Articles / Événements</p>
            <p class="text-white text-2xl font-bold mt-1">{{ number_format($articles->count()) }} / {{ number_format($events->count()) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 md:col-span-3">
            <p class="text-slate-500 text-xs">Photos publiées visibles</p>
            <p class="text-amber-300 text-2xl font-bold mt-1">{{ number_format($publishedPhotos->count()) }}</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold mb-1">Photos publiées</h2>
        <p class="text-slate-400 text-sm mb-4">Photos de votre fiche + couvertures de vos articles et événements.</p>

        @if($publishedPhotos->isEmpty())
            <p class="text-slate-500 text-sm">Aucune photo publiée trouvée.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($publishedPhotos as $photo)
                    <div class="rounded-xl border border-slate-800 bg-slate-800/20 p-3">
                        <div class="aspect-video rounded-lg bg-slate-800 border border-slate-700 overflow-hidden">
                            <img src="{{ $photo['url'] }}" alt="{{ $photo['title'] }}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-white text-sm font-medium mt-2 truncate" title="{{ $photo['title'] }}">{{ $photo['title'] }}</p>
                        <p class="text-slate-500 text-xs mt-1">
                            {{ $photo['source'] }}
                            @if(!empty($photo['date']))
                                · {{ $photo['date']->translatedFormat('d M Y') }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold">Articles publiés / en brouillon</h2>
        <p class="text-slate-400 text-sm mt-1">Toutes vos publications sponsorisées liées à votre fiche.</p>

        @if($articles->isEmpty())
            <p class="text-slate-500 text-sm mt-4">Aucun article trouvé.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach($articles as $article)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-800 bg-slate-800/30 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ $article->title_fr }}</p>
                            <p class="text-slate-500 text-xs mt-1">
                                {{ $article->published_at?->translatedFormat('d M Y H:i') ?? $article->created_at?->translatedFormat('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-1 rounded-full text-xs {{ $article->status === 'published' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                {{ $article->status }}
                            </span>
                            @if($article->slug_fr)
                                <a href="{{ route('articles.show', $article->slug_fr) }}" target="_blank"
                                   class="text-xs text-amber-300 hover:text-amber-200">Voir</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold mb-1">Événements publiés / en brouillon</h2>
        <p class="text-slate-400 text-sm mb-4">Événements liés à votre établissement.</p>

        @if($events->isEmpty())
            <p class="text-slate-500 text-sm">Aucun événement trouvé.</p>
        @else
            <div class="space-y-3">
                @foreach($events as $event)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-800 bg-slate-800/30 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ $event->title_fr }}</p>
                            <p class="text-slate-500 text-xs mt-1">
                                {{ $event->starts_at?->translatedFormat('d M Y H:i') ?? $event->created_at?->translatedFormat('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-1 rounded-full text-xs {{ $event->status === 'published' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                {{ $event->status }}
                            </span>
                            @if($event->slug)
                                <a href="{{ route('events.show', $event->slug) }}" target="_blank"
                                   class="text-xs text-amber-300 hover:text-amber-200">Voir</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

