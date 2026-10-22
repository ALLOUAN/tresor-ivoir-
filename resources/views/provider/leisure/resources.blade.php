@extends('layouts.app')

@section('title', 'Ressources partagées')
@section('page-title', 'Ressources partagées')

@section('content')
@include('provider.leisure._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100'; @endphp
<p class="text-slate-400 text-sm mb-4">Une ressource (salle, terrain, couloir, lot de matériel) peut servir à plusieurs activités : quand elle est occupée, toutes les activités qui l'utilisent affichent « complet » sur ce créneau. Une formule (anniversaire…) privatise sa ressource.</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-3">
        @forelse($resources as $r)
            <form method="POST" action="{{ route('provider.leisure-resources.update', $r) }}" class="bg-green-900 border border-slate-800 rounded-xl p-4 space-y-3">
                @csrf @method('PUT')
                <div class="flex flex-wrap items-end gap-2">
                    <label class="text-xs text-slate-400 flex-1 min-w-[10rem]">Nom<input name="name" value="{{ $r->name }}" required maxlength="120" class="{{ $f }} w-full mt-1"></label>
                    <label class="text-xs text-slate-400 w-28">Quantité<input type="number" name="units" value="{{ $r->units }}" min="1" required class="{{ $f }} w-full mt-1"></label>
                    <label class="text-xs text-slate-300 flex items-center gap-1.5 pb-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($r->is_active) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Active</label>
                </div>
                <div class="space-y-1.5">
                    <p class="text-xs text-slate-400">Activités qui l'utilisent</p>
                    @foreach($activities as $a)
                        @php $p = $r->activities->firstWhere('id', $a->id)?->pivot; @endphp
                        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-200">
                            <label class="flex items-center gap-2 min-w-[12rem] flex-1"><input type="checkbox" name="links[{{ $a->id }}][on]" value="1" @checked($p) class="rounded border-slate-600 bg-slate-800 text-orange-500"> {{ $a->name }}</label>
                            <input type="number" name="links[{{ $a->id }}][units]" min="1" value="{{ $p->units ?? 1 }}" class="{{ $f }} w-20" aria-label="Unités utilisées">
                            <select name="links[{{ $a->id }}][mode]" class="{{ $f }}" aria-label="Mode"><option value="booking" @selected($p && ! $p->per_participant)>par réservation</option><option value="participant" @selected($p?->per_participant)>par participant</option></select>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <button class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">Enregistrer</button>
                </div>
            </form>
            <form method="POST" action="{{ route('provider.leisure-resources.destroy', $r) }}" onsubmit="return confirm('Supprimer cette ressource ?');" class="-mt-1 text-right">@csrf @method('DELETE')<button class="text-xs text-rose-300">Supprimer « {{ $r->name }} »</button></form>
        @empty
            <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Aucune ressource. Exemple : « Salle de danse » (1), « Kayaks » (8, par participant).</div>
        @endforelse
    </div>
    <form method="POST" action="{{ route('provider.leisure-resources.store') }}" class="bg-green-900 border border-slate-800 rounded-xl p-4 space-y-3 self-start">
        @csrf
        <h2 class="text-white font-semibold text-sm"><i class="fas fa-plus text-orange-400 mr-1"></i>Ajouter une ressource</h2>
        <label class="block text-xs text-slate-400">Nom<input name="name" required maxlength="120" placeholder="Salle polyvalente" class="{{ $f }} w-full mt-1"></label>
        <label class="block text-xs text-slate-400">Quantité disponible<input type="number" name="units" min="1" value="1" required class="{{ $f }} w-full mt-1"></label>
        <button class="bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">Ajouter</button>
    </form>
</div>
@endsection
