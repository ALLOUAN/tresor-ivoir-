@extends('layouts.app')

@section('title', "Cinéma d'Ivoire — Films")
@section('page-title', "Cinéma d'Ivoire — Films")

@section('content')
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-200 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-triangle-exclamation shrink-0"></i> {{ session('error') }}
</div>
@endif

@php
    $badge = [
        'draft' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
        'published' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
        'rejected' => 'bg-red-500/20 text-red-300 border-red-500/30',
        'withdrawn' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <p class="text-sm text-slate-400 max-w-2xl">Rubrique de la Médiathèque : films documentaires et de fiction. Les propositions envoyées depuis le site arrivent « À valider » ; aucune fiche n'est publiée sans confirmation des droits.</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('films.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-sm font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-up-right-from-square text-xs"></i> Voir la rubrique</a>
        <a href="{{ route('admin.films.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm"><i class="fas fa-plus"></i> Ajouter un film</a>
    </div>
</div>

<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    <a href="{{ route('admin.films.index') }}" class="px-3 py-2 rounded-lg border text-xs font-semibold {{ ! $status ? 'border-orange-500 text-orange-300' : 'border-slate-700 text-slate-300 hover:border-orange-500/60' }}">Tous <span class="text-slate-500">{{ $counts->sum() }}</span></a>
    @foreach(\App\Models\Film::STATUSES as $key => $label)
        <a href="{{ route('admin.films.index', ['statut' => $key]) }}" class="px-3 py-2 rounded-lg border text-xs font-semibold {{ $status === $key ? 'border-orange-500 text-orange-300' : 'border-slate-700 text-slate-300 hover:border-orange-500/60' }}">{{ $label }} <span class="text-slate-500">{{ $counts[$key] ?? 0 }}</span></a>
    @endforeach
    @if($status)<input type="hidden" name="statut" value="{{ $status }}">@endif
    <input type="search" name="q" value="{{ $q }}" placeholder="Titre ou réalisateur" class="sm:ml-auto w-full sm:w-64 bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
</form>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    @forelse($films as $f)
        <div class="flex flex-wrap items-center gap-4 p-4 border-b border-slate-800 last:border-0">
            <div class="w-16 h-24 rounded-lg overflow-hidden bg-slate-800 shrink-0 flex items-center justify-center text-slate-500">
                @if($f->posterUrl())<img src="{{ $f->posterUrl() }}" alt="" class="w-full h-full object-cover">@else<i class="fas {{ $f->kindIcon() }}"></i>@endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-white truncate">{{ $f->title }}</p>
                    <span class="px-2 py-0.5 rounded-full text-xs border {{ $badge[$f->status] ?? '' }}">{{ $f->statusLabel() }}</span>
                    @if($f->is_featured)<span class="px-2 py-0.5 rounded-full text-xs border border-orange-500/40 text-orange-300">À l'affiche</span>@endif
                    @if($f->is_sponsored)<span class="px-2 py-0.5 rounded-full text-xs border border-sky-500/40 text-sky-300">Sponsorisé</span>@endif
                    @unless($f->rights_confirmed)<span class="px-2 py-0.5 rounded-full text-xs border border-red-500/40 text-red-300"><i class="fas fa-shield-halved"></i> Droits à confirmer</span>@endunless
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    <i class="fas {{ $f->kindIcon() }}"></i> {{ $f->kindLabel() }}
                    @if($f->genre) · {{ $f->genre }} @endif
                    @if($f->director) · {{ $f->director }} @endif
                    @if($f->year) · {{ $f->year }} @endif
                    · <i class="fas fa-eye"></i> {{ $f->views_count }} vue(s) · <i class="fas fa-arrow-pointer"></i> {{ $f->clicks_count }} clic(s)
                </p>
                @if($f->submitter_email)
                    <p class="text-xs text-slate-500 mt-1"><i class="fas fa-inbox"></i> Proposé par {{ $f->submitter_name }} — <a href="mailto:{{ $f->submitter_email }}" class="hover:text-orange-300">{{ $f->submitter_email }}</a>@if($f->submitter_phone) · {{ $f->submitter_phone }}@endif @if($f->submitted_at) · {{ $f->submitted_at->format('d/m/Y') }}@endif</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($f->status === 'pending')
                    @if($f->rights_confirmed)
                        <form method="POST" action="{{ route('admin.films.status', $f) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="published">
                            <button type="submit" class="px-3 py-2 rounded-lg bg-emerald-600/80 hover:bg-emerald-600 text-xs font-semibold text-white"><i class="fas fa-check"></i> Publier</button></form>
                    @endif
                    <form method="POST" action="{{ route('admin.films.status', $f) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected">
                        <button type="submit" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-red-500/60"><i class="fas fa-xmark"></i> Refuser</button></form>
                @elseif($f->status === 'published')
                    <a href="{{ route('films.show', $f) }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-eye"></i> Voir</a>
                    <form method="POST" action="{{ route('admin.films.status', $f) }}" onsubmit="return confirm('Retirer ce film du site ?')">@csrf @method('PATCH')<input type="hidden" name="status" value="withdrawn">
                        <button type="submit" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-red-500/60"><i class="fas fa-eye-slash"></i> Retirer</button></form>
                @endif
                <a href="{{ route('admin.films.edit', $f) }}" class="px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60"><i class="fas fa-pen"></i> Modifier</a>
                <form method="POST" action="{{ route('admin.films.destroy', $f) }}" onsubmit="return confirm('Supprimer définitivement cette fiche et ses images ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-lg border border-red-800/60 text-xs font-semibold text-red-300 hover:bg-red-900/30" aria-label="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-slate-400 text-sm">
            <i class="fas fa-film text-3xl mb-3 block text-slate-600"></i>
            Aucun film {{ $status ? '« '.mb_strtolower(\App\Models\Film::STATUSES[$status]).' »' : '' }} pour le moment.
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $films->links() }}</div>
@endsection
