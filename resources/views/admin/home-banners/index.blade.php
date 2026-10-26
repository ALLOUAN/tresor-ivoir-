@extends('layouts.app')

@section('title', 'Bannières publicitaires — Accueil')
@section('page-title', 'Bannières publicitaires — Accueil')

@section('content')
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <p class="text-sm text-slate-400 max-w-2xl">Deux emplacements sur la page d'accueil : après « Les meilleures adresses » et après « Cultures ivoiriennes ». Pour chaque emplacement, la première bannière active (par ordre) est affichée. Visuel conseillé : PNG détouré (fond transparent), 800 × 500 px environ.</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-sm font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-up-right-from-square text-xs"></i> Voir l'accueil</a>
        <a href="{{ route('admin.home-banners.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 font-bold text-sm"><i class="fas fa-plus"></i> Ajouter une bannière</a>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    @forelse($banners as $b)
        <div class="flex flex-wrap items-center gap-4 p-4 border-b border-slate-800 last:border-0">
            <div class="w-24 h-16 rounded-lg overflow-hidden bg-slate-800 shrink-0 flex items-center justify-center text-slate-400">
                @if($b->imageList())<img src="{{ $b->imageList()[0] }}" alt="" class="w-full h-full object-contain">@else<i class="fas {{ $b->icon ?: 'fa-rectangle-ad' }} text-xl"></i>@endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-white truncate">{{ $b->title_strong }} <span class="font-normal">{{ $b->title_rest }}</span></p>
                    @if($b->is_active)
                        <span class="px-2 py-0.5 rounded-full text-xs border bg-emerald-500/20 text-emerald-300 border-emerald-500/30">Affichée</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs border bg-slate-500/20 text-slate-300 border-slate-500/30">Masquée</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $b->placementLabel() }} · {{ count($b->imageList()) }} visuel(s) · ordre {{ $b->display_order }} · <i class="fas fa-link"></i> {{ \Illuminate\Support\Str::limit($b->link_url, 60) }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('admin.home-banners.toggle', $b) }}">@csrf @method('PATCH')
                    <button type="submit" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas {{ $b->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i> {{ $b->is_active ? 'Masquer' : 'Afficher' }}</button>
                </form>
                <a href="{{ route('admin.home-banners.edit', $b) }}" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-pen"></i> Modifier</a>
                <form method="POST" action="{{ route('admin.home-banners.destroy', $b) }}" onsubmit="return confirm('Supprimer cette bannière ?')">@csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-lg border border-red-800/60 text-xs font-semibold text-red-300 hover:bg-red-900/30" aria-label="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-slate-400 text-sm">
            <i class="fas fa-rectangle-ad text-3xl mb-3 block text-slate-600"></i>
            Aucune bannière. Ajoutez-en une pour l'afficher sur la page d'accueil.
        </div>
    @endforelse
</div>
@endsection
