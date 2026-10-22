@extends('layouts.app')

@section('title', 'Médiathèque — Vidéos')
@section('page-title', 'Médiathèque — Vidéos')

@section('content')
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap gap-2">
        @php $tabs = ['' => 'Toutes', 'pending' => 'En attente', 'published' => 'Publiées', 'rejected' => 'Refusées']; @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.videos.index', $key ? ['statut' => $key] : []) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold border {{ (string) $status === (string) $key ? 'bg-orange-500 border-orange-500 text-white' : 'border-slate-700 text-slate-300 hover:border-orange-500/60' }}">
                {{ $label }}
                <span class="text-xs opacity-80">{{ $key ? ($counts[$key] ?? 0) : $counts->sum() }}</span>
            </a>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('videos.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-sm font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-up-right-from-square text-xs"></i> Voir la page</a>
        <a href="{{ route('admin.videos.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm"><i class="fas fa-plus"></i> Ajouter une vidéo</a>
    </div>
</div>

@if(($counts['pending'] ?? 0) > 0 && $status !== 'pending')
<a href="{{ route('admin.videos.index', ['statut' => 'pending']) }}" class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl border border-amber-700/50 bg-amber-900/20 text-amber-200 text-sm">
    <i class="fas fa-hourglass-half"></i> {{ $counts['pending'] }} vidéo(s) proposée(s) par des prestataires attendent votre validation.
</a>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    @forelse($videos as $v)
        <div class="flex flex-wrap items-center gap-4 p-4 border-b border-slate-800 last:border-0">
            <div class="w-16 h-24 rounded-lg overflow-hidden bg-slate-800 shrink-0 flex items-center justify-center text-slate-500">
                @if($v->poster())<img src="{{ $v->poster() }}" alt="" class="w-full h-full object-cover">@else<i class="fas fa-film"></i>@endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-white truncate">{{ $v->title }}</p>
                    @php $badge = ['pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30', 'published' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30', 'rejected' => 'bg-red-500/20 text-red-300 border-red-500/30'][$v->status] ?? ''; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs border {{ $badge }}">{{ ['pending' => 'En attente', 'published' => 'Publiée', 'rejected' => 'Refusée'][$v->status] ?? $v->status }}</span>
                    @if($v->is_featured)<span class="px-2 py-0.5 rounded-full text-xs border border-orange-500/40 text-orange-300">À la une</span>@endif
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $v->categoryLabel() }}
                    · {{ $v->source === 'upload' ? 'Fichier envoyé' : ucfirst($v->source) }}
                    @if($v->durationLabel()) · {{ $v->durationLabel() }} @endif
                    @if($v->city) · {{ $v->city->name }} @endif
                    @if($v->provider) · <span class="text-slate-300">Proposée par {{ $v->provider->name }}</span> @endif
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    <i class="fas fa-eye"></i> {{ $v->views_count }} vues · <i class="fas fa-heart"></i> {{ $v->likes_count }} · <i class="fas fa-share-nodes"></i> {{ $v->shares_count }} · <i class="fas fa-bag-shopping"></i> {{ $v->booking_clicks_count }} clics « réserver »
                </p>
                @if($v->status === 'rejected' && $v->rejection_reason)<p class="text-xs text-red-300 mt-1">Motif : {{ $v->rejection_reason }}</p>@endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($v->status !== 'published')
                    <form method="POST" action="{{ route('admin.videos.approve', $v) }}">@csrf @method('PATCH')
                        <button class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold"><i class="fas fa-check"></i> Publier</button>
                    </form>
                @endif
                @if($v->status === 'pending')
                    <form method="POST" action="{{ route('admin.videos.reject', $v) }}" class="flex items-center gap-1" onsubmit="const r = prompt('Motif du refus (visible par le prestataire) :', ''); if (r === null) return false; this.reason.value = r; return true;">@csrf @method('PATCH')
                        <input type="hidden" name="reason">
                        <button class="px-3 py-1.5 rounded-lg border border-red-600/70 text-red-300 hover:bg-red-900/30 text-xs font-semibold"><i class="fas fa-xmark"></i> Refuser</button>
                    </form>
                @endif
                @if($v->status === 'published')
                    <a href="{{ route('videos.show', $v) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 text-xs font-semibold"><i class="fas fa-play"></i> Voir</a>
                @endif
                <a href="{{ route('admin.videos.edit', $v) }}" class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:border-orange-500/60 text-xs font-semibold"><i class="fas fa-pen"></i> Modifier</a>
                <form method="POST" action="{{ route('admin.videos.destroy', $v) }}" onsubmit="return confirm('Supprimer cette vidéo ?');">@csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg bg-red-700/90 hover:bg-red-600 text-white text-xs font-semibold"><i class="fas fa-trash-can"></i></button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-slate-400">
            <i class="fas fa-film text-3xl mb-3"></i>
            <p>Aucune vidéo pour le moment.</p>
            <a href="{{ route('admin.videos.create') }}" class="inline-flex mt-4 items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 text-black font-bold text-sm"><i class="fas fa-plus"></i> Ajouter la première vidéo</a>
        </div>
    @endforelse
</div>
<div class="mt-5">{{ $videos->links() }}</div>
@endsection
