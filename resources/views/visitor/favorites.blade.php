@extends('layouts.app')

@section('title', 'Mes favoris')
@section('page-title', 'Wishlist / Favoris')

@section('content')
<div class="max-w-5xl mx-auto">
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800">
        @forelse($favorites as $favorite)
            @php $item = $favorite->favoritable; @endphp
            <div class="px-5 py-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs uppercase text-slate-500 mb-1">
                        {{ class_basename($favorite->favoritable_type) }}
                    </p>
                    <p class="text-white text-sm font-medium truncate">
                        @if($item instanceof \App\Models\Provider)
                            {{ $item->name }}
                        @elseif($item instanceof \App\Models\Article)
                            {{ $item->title_fr }}
                        @elseif($item instanceof \App\Models\Event)
                            {{ $item->title_fr }}
                        @else
                            Élément
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Ajouté {{ $favorite->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if($item instanceof \App\Models\Provider)
                        <a href="{{ route('providers.show', $item->slug) }}" class="text-xs px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200">Voir</a>
                    @elseif($item instanceof \App\Models\Article)
                        <a href="{{ route('articles.show', $item->slug_fr) }}" class="text-xs px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200">Voir</a>
                    @elseif($item instanceof \App\Models\Event)
                        <a href="{{ route('events.show', $item->slug) }}" class="text-xs px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200">Voir</a>
                    @endif
                    <form method="POST" action="{{ route('visitor.favorites.destroy', $favorite) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs px-3 py-1.5 rounded bg-rose-900/30 hover:bg-rose-900/50 text-rose-300 border border-rose-700/40">
                            Retirer
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center text-slate-500 text-sm">
                Aucun favori pour le moment.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $favorites->links() }}
    </div>
</div>
@endsection
