@extends('layouts.app')

@section('title', 'Formules')
@section('page-title', 'Formules anniversaire, groupe et entreprise')

@section('content')
@include('provider.leisure._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 w-full mt-1'; @endphp

@foreach($packages->push(new \App\Models\LeisurePackage(['kind' => 'birthday', 'included_participants' => 10, 'min_participants' => 6, 'duration_minutes' => 120, 'free_cancellation_days' => 7, 'is_active' => true])) as $p)
    <details class="bg-green-900 border border-slate-800 rounded-xl mb-3" @if(! $p->exists && $packages->count() === 1) open @endif>
        <summary class="cursor-pointer px-4 py-3 flex flex-wrap items-center justify-between gap-2">
            <span class="text-white font-semibold">@if($p->exists)<i class="fas {{ \App\Models\LeisurePackage::KIND_ICONS[$p->kind] ?? 'fa-star' }} text-orange-400 mr-1.5"></i>{{ $p->name }} @unless($p->is_active)<span class="text-xs text-slate-400">(inactive)</span>@endunless @else<i class="fas fa-plus text-orange-400 mr-1.5"></i>Nouvelle formule @endif</span>
            @if($p->exists)<span class="text-xs text-slate-400">{{ number_format($p->weekday_price_xof, 0, ',', ' ') }} XOF · {{ $p->included_participants }} pers. · <a href="{{ route('leisure-package.show', $p->slug) }}" target="_blank" class="text-orange-300">voir la page</a></span>@endif
        </summary>
        <form method="POST" action="{{ $p->exists ? route('provider.leisure-packages.update', $p) : route('provider.leisure-packages.store') }}" class="px-4 pb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf @if($p->exists) @method('PUT') @endif
            <label class="text-xs text-slate-400 sm:col-span-2">Nom<input name="name" required maxlength="150" value="{{ $p->name }}" placeholder="Anniversaire Fun (10 enfants)" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Type<select name="kind" class="{{ $f }}">@foreach(\App\Models\LeisurePackage::KINDS as $k => $l)<option value="{{ $k }}" @selected($p->kind === $k)>{{ $l }}</option>@endforeach</select></label>
            <label class="text-xs text-slate-400 sm:col-span-3">Description<textarea name="description" rows="2" maxlength="2000" class="{{ $f }}">{{ $p->description }}</textarea></label>
            <label class="text-xs text-slate-400 sm:col-span-3">Ce qui est inclus (une ligne par élément)<textarea name="includes" rows="3" maxlength="2000" placeholder="Salle privée 2 h&#10;Animateur&#10;Goûter et boissons&#10;Gâteau d'anniversaire" class="{{ $f }}">{{ $p->includes }}</textarea></label>
            <label class="text-xs text-slate-400">Prix semaine (XOF)<input type="number" name="weekday_price_xof" required min="0" value="{{ $p->weekday_price_xof }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Prix week-end (XOF)<input type="number" name="weekend_price_xof" min="0" value="{{ $p->weekend_price_xof }}" placeholder="= semaine" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Acompte en ligne (XOF)<input type="number" name="deposit_xof" min="0" value="{{ $p->deposit_xof }}" placeholder="0 = paiement intégral" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Participants inclus<input type="number" name="included_participants" required min="1" value="{{ $p->included_participants }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Par participant en plus (XOF)<input type="number" name="extra_participant_xof" min="0" value="{{ $p->extra_participant_xof }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Durée (minutes)<input type="number" name="duration_minutes" required min="15" value="{{ $p->duration_minutes }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Minimum<input type="number" name="min_participants" required min="1" value="{{ $p->min_participants }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Maximum<input type="number" name="max_participants" min="1" value="{{ $p->max_participants }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Annulation gratuite (jours avant)<input type="number" name="free_cancellation_days" required min="0" max="60" value="{{ $p->free_cancellation_days }}" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400 sm:col-span-2">Horaires de début<input name="slot_times_text" required value="{{ collect($p->slot_times ?: [])->join(', ') }}" placeholder="10:00, 14:00, 16:30" class="{{ $f }}"></label>
            <label class="text-xs text-slate-400">Salle / ressource privatisée<select name="leisure_resource_id" class="{{ $f }}"><option value="">Aucune</option>@foreach($resources as $r)<option value="{{ $r->id }}" @selected($p->leisure_resource_id === $r->id)>{{ $r->name }}</option>@endforeach</select></label>
            <label class="text-xs text-slate-300 flex items-center gap-1.5"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($p->is_active) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Proposée aux clients</label>
            <div class="sm:col-span-3"><button class="bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">{{ $p->exists ? 'Enregistrer' : 'Créer la formule' }}</button></div>
        </form>
    </details>
@endforeach
@endsection
