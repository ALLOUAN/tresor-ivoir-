@extends('layouts.app')

@section('title', 'Proposer une vidéo')
@section('page-title', 'Proposer une vidéo')

@section('content')
<a href="{{ route('provider.videos.index') }}" class="inline-flex items-center gap-2 mb-4 text-sm text-slate-400 hover:text-orange-400"><i class="fas fa-arrow-left text-xs"></i> Mes vidéos</a>

<form method="POST" action="{{ route('provider.videos.store') }}" enctype="multipart/form-data" class="bg-green-900 border border-slate-800 rounded-xl p-6 space-y-6">
    @csrf
    <div class="px-4 py-3 rounded-xl border border-slate-700 text-slate-300 text-sm">
        <i class="fas fa-circle-info text-orange-400"></i> Conseils : filmez à la verticale, en lumière naturelle, et montrez ce que vit le client (le plat qui arrive, la vue de la chambre, l'activité). Pas de texte publicitaire ni de musique protégée.
    </div>

    @include('admin.videos._fields', ['listings' => $listings])

    <div class="flex items-center gap-4 pt-4 border-t border-slate-800">
        <p class="text-xs text-slate-500">La vidéo sera publiée après validation par l'équipe Trésors d'Ivoire.</p>
        <button type="submit" class="ml-auto px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl flex items-center gap-2"><i class="fas fa-paper-plane"></i> Envoyer pour validation</button>
    </div>
</form>
@endsection
