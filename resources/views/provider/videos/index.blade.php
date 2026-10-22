@extends('layouts.app')

@section('title', 'Mes vidéos')
@section('page-title', 'Mes vidéos')

@section('content')
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-5 flex flex-wrap items-center justify-between gap-4">
    <div class="max-w-2xl">
        <h2 class="text-white font-semibold text-lg">Vos vidéos dans la médiathèque</h2>
        <p class="text-slate-400 text-sm mt-1">Une courte vidéo verticale (15 à 60 s) filmée chez vous attire plus que des photos. Elle apparaît dans la rubrique Vidéos avec un bouton « Voir et réserver » vers votre fiche, après validation par notre équipe.</p>
    </div>
    <a href="{{ route('provider.videos.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm"><i class="fas fa-plus"></i> Proposer une vidéo</a>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    @forelse($videos as $v)
        <div class="flex flex-wrap items-center gap-4 p-4 border-b border-slate-800 last:border-0">
            <div class="w-16 h-24 rounded-lg overflow-hidden bg-slate-800 shrink-0 flex items-center justify-center text-slate-500">
                @if($v->poster())<img src="{{ $v->poster() }}" alt="" class="w-full h-full object-cover">@else<i class="fas fa-film"></i>@endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-white">{{ $v->title }}</p>
                <p class="text-xs mt-1 {{ ['pending' => 'text-amber-300', 'published' => 'text-emerald-300', 'rejected' => 'text-red-300'][$v->status] ?? 'text-slate-400' }}">
                    {{ ['pending' => 'En attente de validation', 'published' => 'Publiée', 'rejected' => 'Refusée'][$v->status] ?? $v->status }}
                    @if($v->status === 'rejected' && $v->rejection_reason) — {{ $v->rejection_reason }} @endif
                </p>
                @if($v->status === 'published')
                    <p class="text-xs text-slate-500 mt-1"><i class="fas fa-eye"></i> {{ $v->views_count }} vues · <i class="fas fa-heart"></i> {{ $v->likes_count }} · <i class="fas fa-bag-shopping"></i> {{ $v->booking_clicks_count }} clics vers votre fiche</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($v->status === 'published')<a href="{{ route('videos.show', $v) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 text-xs font-semibold"><i class="fas fa-play"></i> Voir</a>@endif
                <form method="POST" action="{{ route('provider.videos.destroy', $v) }}" onsubmit="return confirm('Supprimer cette vidéo ?');">@csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg bg-red-700/90 hover:bg-red-600 text-white text-xs font-semibold"><i class="fas fa-trash-can"></i> Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-slate-400"><i class="fas fa-film text-3xl mb-3"></i><p>Vous n'avez encore proposé aucune vidéo.</p></div>
    @endforelse
</div>
@endsection
