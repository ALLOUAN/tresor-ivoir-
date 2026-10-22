@extends('layouts.app')

@section('title', 'Cartes et abonnements')
@section('page-title', 'Cartes multi-séances et abonnements')

@section('content')
@include('provider.leisure._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 w-full mt-1'; @endphp
<p class="text-slate-400 text-sm mb-4">Le client achète la carte en ligne puis réserve ses créneaux en payant « avec ma carte » : une séance est décomptée par participant (rien n'est décompté pour un abonnement illimité). Une annulation dans le délai gratuit recrédite les séances.</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-3">
        @foreach($passes->push(new \App\Models\LeisurePass(['validity_days' => 180, 'is_active' => true])) as $p)
            <form method="POST" action="{{ $p->exists ? route('provider.leisure-passes.update', $p) : route('provider.leisure-passes.store') }}" class="bg-green-900 border border-slate-800 rounded-xl p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                @csrf @if($p->exists) @method('PUT') @endif
                <p class="sm:col-span-4 text-white font-semibold text-sm">{{ $p->exists ? $p->name : '+ Nouvelle carte' }}</p>
                <label class="text-xs text-slate-400 sm:col-span-2">Nom<input name="name" required maxlength="150" value="{{ $p->name }}" placeholder="Carte 10 cours de danse" class="{{ $f }}"></label>
                <label class="text-xs text-slate-400">Séances<input type="number" name="sessions" min="1" value="{{ $p->sessions }}" placeholder="Illimité" class="{{ $f }}"></label>
                <label class="text-xs text-slate-400">Validité (jours)<input type="number" name="validity_days" required min="1" value="{{ $p->validity_days }}" class="{{ $f }}"></label>
                <label class="text-xs text-slate-400">Prix (XOF)<input type="number" name="price_xof" required min="0" value="{{ $p->price_xof }}" class="{{ $f }}"></label>
                <label class="text-xs text-slate-400 sm:col-span-3">Description<input name="description" maxlength="1000" value="{{ $p->description }}" class="{{ $f }}"></label>
                <fieldset class="sm:col-span-4"><legend class="text-xs text-slate-400 mb-1">Activités couvertes (aucune cochée = toutes)</legend>
                    <div class="flex flex-wrap gap-1.5">@foreach($activities as $a)<label class="inline-flex items-center gap-1.5 text-xs text-slate-300 border border-slate-700 rounded-lg px-2 py-1"><input type="checkbox" name="activity_ids[]" value="{{ $a->id }}" @checked(in_array($a->id, array_map('intval', $p->activity_ids ?? []))) class="rounded border-slate-600 bg-slate-800 text-orange-500">{{ $a->name }}</label>@endforeach</div></fieldset>
                <label class="text-xs text-slate-300 flex items-center gap-1.5"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($p->is_active) class="rounded border-slate-600 bg-slate-800 text-orange-500"> En vente</label>
                <div class="sm:col-span-3 text-right"><button class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">{{ $p->exists ? 'Enregistrer' : 'Créer la carte' }}</button></div>
            </form>
        @endforeach
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 self-start">
        <h2 class="text-white font-semibold text-sm mb-2">Cartes vendues</h2>
        @forelse($sold as $s)
            <p class="text-sm text-slate-300 border-b border-slate-800 py-1.5">{{ $s->user?->first_name }} {{ $s->user?->last_name }} · {{ $s->pass_name }}<br><span class="text-xs text-slate-500">{{ $s->remaining() === null ? 'illimitée' : $s->remaining().' séance(s) restante(s)' }} · jusqu'au {{ $s->expires_on?->format('d/m/Y') }}</span></p>
        @empty
            <p class="text-slate-400 text-sm">Aucune carte vendue.</p>
        @endforelse
    </div>
</div>
@endsection
