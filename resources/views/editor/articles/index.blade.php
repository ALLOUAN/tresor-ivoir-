@extends('layouts.app')

@section('title', 'Mes articles')
@section('page-title', 'Articles')

@section('header-actions')
<a href="{{ route('editor.articles.create') }}"
   class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvel article
</a>
@endsection

@section('content')

{{-- Status tabs --}}
<div class="flex items-center gap-1 overflow-x-auto mb-6 bg-slate-900/50 border border-slate-800 rounded-xl p-1">
    @php $tabs = [''=>'Tous','draft'=>'Brouillons','review'=>'En révision','published'=>'Publiés','archived'=>'Archivés']; @endphp
    @foreach($tabs as $val => $label)
    <a href="{{ route('editor.articles.index', $val ? ['status' => $val] : []) }}"
       class="shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap
              {{ $status === $val || ($status === null && $val === '') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
        {{ $label }}
        <span class="bg-slate-600/50 text-slate-300 text-[10px] px-1.5 py-0.5 rounded-full">
            {{ $counts[$val ?: 'all'] }}
        </span>
    </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Article</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Rubrique</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Auteur</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Date</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($articles as $article)
                <tr class="hover:bg-slate-800/30 transition group">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($article->cover_url)
                            <img src="{{ $article->cover_url }}" class="w-12 h-10 rounded-lg object-cover shrink-0 hidden sm:block">
                            @else
                            <div class="w-12 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 hidden sm:block">
                                <i class="fas fa-image text-slate-600 text-sm"></i>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-white font-medium text-sm line-clamp-1">{{ $article->title_fr }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($article->is_featured)
                                    <span class="text-amber-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>À la une</span>
                                    @endif
                                    @if($article->reading_time)
                                    <span class="text-slate-600 text-[10px]">{{ $article->reading_time }} min</span>
                                    @endif
                                    <span class="text-slate-600 text-[10px]">
                                        <i class="fas fa-eye mr-0.5"></i>{{ number_format($article->views_count) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-amber-400/80 text-xs">{{ $article->category->name_fr ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell text-slate-400 text-xs">
                        {{ $article->author->full_name ?? 'N/A' }}
                    </td>
                    <td class="px-5 py-4">
                        @php $statusMap = [
                            'published' => ['bg-emerald-900/40 text-emerald-300 border-emerald-800', 'Publié'],
                            'draft'     => ['bg-slate-800 text-slate-400 border-slate-700', 'Brouillon'],
                            'review'    => ['bg-amber-900/40 text-amber-300 border-amber-800', 'En révision'],
                            'archived'  => ['bg-slate-800 text-slate-500 border-slate-700', 'Archivé'],
                        ]; [$cls, $lbl] = $statusMap[$article->status] ?? ['bg-slate-800 text-slate-400', $article->status]; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium border {{ $cls }}">{{ $lbl }}</span>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell text-slate-500 text-xs">
                        {{ $article->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                            @if($article->status === 'published')
                            <a href="{{ route('articles.show', $article->slug_fr) }}" target="_blank"
                               class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-blue-900/50 flex items-center justify-center text-slate-400 hover:text-blue-300 transition" title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @endif
                            <a href="{{ route('editor.articles.edit', $article) }}"
                               class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/50 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Modifier">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            {{-- Quick status change --}}
                            @if($article->status === 'draft')
                            <form method="POST" action="{{ route('editor.articles.status', $article) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="review">
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/50 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Soumettre">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->isAdmin() && $article->status === 'review')
                            <form method="POST" action="{{ route('editor.articles.status', $article) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-emerald-900/50 flex items-center justify-center text-slate-400 hover:text-emerald-300 transition" title="Publier">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('editor.articles.destroy', $article) }}"
                                onsubmit="return confirm('Supprimer cet article ?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center text-slate-500">
                        <i class="fas fa-newspaper text-3xl mb-3 block text-slate-700"></i>
                        Aucun article
                        @if($status) avec le statut "{{ $tabs[$status] ?? $status }}"@endif.
                        <a href="{{ route('editor.articles.create') }}" class="text-amber-400 hover:underline ml-1">Créer le premier</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($articles->hasPages())
    <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $articles->firstItem() }}–{{ $articles->lastItem() }} sur {{ $articles->total() }}</span>
        <div class="flex gap-1">
            @if(!$articles->onFirstPage())
            <a href="{{ $articles->previousPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
            @endif
            @if($articles->hasMorePages())
            <a href="{{ $articles->nextPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
