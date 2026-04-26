@extends('layouts.app')

@section('title', 'Prévisualisation événement')
@section('page-title', 'Prévisualisation événement')

@section('header-actions')
<div class="flex items-center gap-2 flex-wrap">
    <span class="text-xs px-2 py-1 rounded-lg bg-amber-900/40 border border-amber-700/50 text-amber-200">
        <i class="fas fa-eye mr-1"></i> Aperçu — statut : {{ $event->status }}
    </span>
    <a href="{{ route('editor.events.edit', $event) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black text-xs font-semibold rounded-lg transition">
        <i class="fas fa-pen"></i> Continuer l'édition
    </a>
    @if($event->status === 'published')
        <a href="{{ route('events.show', $event->slug) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
            <i class="fas fa-external-link-alt"></i> Version publique
        </a>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <p class="text-xs text-slate-500 uppercase tracking-wider">
        {{ $event->category?->name_fr ?? 'Événement' }}
    </p>
    <h1 class="text-3xl sm:text-4xl font-bold text-white font-serif leading-tight">{{ $event->title_fr }}</h1>
    <p class="text-slate-400 text-sm">
        {{ $event->starts_at?->translatedFormat('d F Y à H:i') }}
        @if($event->ends_at)
            <span class="text-slate-600"> — </span>{{ $event->ends_at->translatedFormat('d F Y à H:i') }}
        @endif
    </p>
    <p class="text-slate-500 text-sm">
        {{ $event->location_name ?: 'Lieu non précisé' }}
        @if($event->city)
            <span class="text-slate-600"> · </span>{{ $event->city }}
        @endif
    </p>
    @if($event->cover_url)
        <figure class="rounded-xl overflow-hidden border border-slate-800">
            <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?? '' }}" class="w-full max-h-[360px] object-cover">
        </figure>
    @endif
    <div class="prose prose-invert prose-amber max-w-none text-slate-300 leading-relaxed [&_a]:text-amber-400">
        {!! \App\Support\HtmlSanitizer::articleBody($event->description_fr ?? '') !!}
    </div>
    @if($event->ticket_url)
        <p>
            <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black text-sm font-semibold rounded-lg transition">
                Billetterie
            </a>
        </p>
    @endif
</div>
@endsection
