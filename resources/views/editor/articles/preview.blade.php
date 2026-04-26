@extends('layouts.app')

@section('title', 'Prévisualisation')
@section('page-title', 'Prévisualisation article')

@section('header-actions')
<div class="flex items-center gap-2 flex-wrap">
    <span class="text-xs px-2 py-1 rounded-lg bg-amber-900/40 border border-amber-700/50 text-amber-200">
        <i class="fas fa-eye mr-1"></i> Aperçu — statut : {{ $article->status }}
    </span>
    <a href="{{ route('editor.articles.edit', $article) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black text-xs font-semibold rounded-lg transition">
        <i class="fas fa-pen"></i> Continuer l'édition
    </a>
    @if($article->status === 'published')
        <a href="{{ route('articles.show', $article->slug_fr) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
            <i class="fas fa-external-link-alt"></i> Version publique
        </a>
    @endif
</div>
@endsection

@section('content')
<article class="max-w-3xl mx-auto space-y-6">
    <div class="text-xs text-slate-500 uppercase tracking-wider">
        {{ $article->category?->name_fr ?? 'Rubrique' }}
    </div>
    <h1 class="text-3xl sm:text-4xl font-bold text-white font-serif leading-tight">{{ $article->title_fr }}</h1>
    @if($article->excerpt_fr)
        <p class="text-slate-300 text-lg leading-relaxed border-l-2 border-amber-500/40 pl-4">{{ $article->excerpt_fr }}</p>
    @endif
    @if($article->cover_url)
        <figure class="rounded-xl overflow-hidden border border-slate-800">
            <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?? '' }}" class="w-full max-h-[420px] object-cover">
        </figure>
    @endif
    <div class="prose prose-invert prose-amber max-w-none text-slate-300 leading-relaxed [&_a]:text-amber-400 [&_blockquote]:border-amber-500/40">
        {!! \App\Support\HtmlSanitizer::articleBody($article->content_fr) !!}
    </div>
</article>
@endsection
