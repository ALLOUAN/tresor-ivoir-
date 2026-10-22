@extends('layouts.app')

@section('title', 'Les Astuces de Mian — Bandes dessinées')
@section('page-title', 'Les Astuces de Mian — Bandes dessinées')

@section('content')
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <p class="text-sm text-slate-400">Planches en images (JPG, PNG, WebP). La fenêtre de présentation s'affiche à la première visite dès qu'une BD est publiée.</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('comics.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-sm font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-up-right-from-square text-xs"></i> Voir la rubrique</a>
        <a href="{{ route('admin.comics.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm"><i class="fas fa-plus"></i> Ajouter une BD</a>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    @forelse($comics as $c)
        <div class="flex flex-wrap items-center gap-4 p-4 border-b border-slate-800 last:border-0">
            <div class="w-16 h-24 rounded-lg overflow-hidden bg-slate-800 shrink-0 flex items-center justify-center text-slate-500">
                @if($c->coverUrl())<img src="{{ $c->coverUrl() }}" alt="" class="w-full h-full object-cover">@else<i class="fas fa-book-open"></i>@endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-white truncate">{{ $c->title }}</p>
                    @if($c->is_published)
                        <span class="px-2 py-0.5 rounded-full text-xs border bg-emerald-500/20 text-emerald-300 border-emerald-500/30">Publiée</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs border bg-amber-500/20 text-amber-300 border-amber-500/30">Brouillon</span>
                    @endif
                    @if($c->is_featured)<span class="px-2 py-0.5 rounded-full text-xs border border-orange-500/40 text-orange-300">À la une</span>@endif
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $c->categoryLabel() }} · {{ $c->pageCount() }} planche(s)
                    @if($c->age_label) · {{ $c->age_label }} @endif
                    · <i class="fas fa-eye"></i> {{ $c->views_count }} lecture(s)
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($c->is_published)
                    <a href="{{ route('comics.show', $c) }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-eye"></i> Lire</a>
                @endif
                <a href="{{ route('admin.comics.edit', $c) }}" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-pen"></i> Modifier</a>
                <form method="POST" action="{{ route('admin.comics.destroy', $c) }}" onsubmit="return confirm('Supprimer cette bande dessinée et toutes ses planches ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-lg border border-red-800/60 text-xs font-semibold text-red-300 hover:bg-red-900/30"><i class="fas fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-slate-400 text-sm">
            <i class="fas fa-book-open text-3xl mb-3 block text-slate-600"></i>
            Aucune bande dessinée pour le moment.
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $comics->links() }}</div>
@endsection
