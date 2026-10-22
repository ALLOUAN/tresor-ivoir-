@extends('layouts.app')

@section('title', 'Flotte et chauffeurs')
@section('page-title', 'Flotte et chauffeurs')

@section('content')
@include('provider.transport._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-2 text-sm text-slate-100 w-full mt-1'; @endphp
<p class="text-slate-400 text-sm mb-4">Chaque réservation en ligne bloque automatiquement un véhicule libre de la bonne catégorie : un véhicule déjà réservé sur le créneau, ou en entretien, n'est jamais proposé deux fois.</p>
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
    <div class="space-y-3">
        <h2 class="text-white font-semibold"><i class="fas fa-car text-orange-400 mr-1"></i>Véhicules</h2>
        @foreach($vehicles->push(new \App\Models\TransportVehicle(['category' => 'berline', 'seats' => 4, 'luggage' => 2, 'has_ac' => true, 'is_active' => true])) as $v)
            <form method="POST" action="{{ route('provider.transport-fleet.vehicle', $v->exists ? $v : null) }}" class="bg-green-900 border border-slate-800 rounded-xl p-3 grid grid-cols-2 sm:grid-cols-4 gap-2">
                @csrf
                <p class="col-span-2 sm:col-span-4 text-sm text-white font-semibold">{{ $v->exists ? $v->name.' '.$v->plate : '+ Ajouter un véhicule' }}</p>
                <label class="text-[11px] text-slate-400 col-span-2">Modèle<input name="name" required maxlength="120" value="{{ $v->name }}" placeholder="Toyota Prado" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">Catégorie<select name="category" class="{{ $f }}">@foreach(\App\Models\TransportOffer::VEHICLE_CATEGORIES as $k => $l)<option value="{{ $k }}" @selected($v->category === $k)>{{ $l }}</option>@endforeach</select></label>
                <label class="text-[11px] text-slate-400">Plaque<input name="plate" maxlength="20" value="{{ $v->plate }}" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">Places<input type="number" name="seats" required min="1" value="{{ $v->seats }}" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">Bagages<input type="number" name="luggage" required min="0" value="{{ $v->luggage }}" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">Entretien du<input type="date" name="unavailable_from" value="{{ $v->unavailable_from?->toDateString() }}" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">au<input type="date" name="unavailable_to" value="{{ $v->unavailable_to?->toDateString() }}" class="{{ $f }}"></label>
                <label class="text-xs text-slate-300 flex items-center gap-1.5"><input type="hidden" name="has_ac" value="0"><input type="checkbox" name="has_ac" value="1" @checked($v->has_ac) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Climatisé</label>
                <label class="text-xs text-slate-300 flex items-center gap-1.5"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($v->is_active) class="rounded border-slate-600 bg-slate-800 text-orange-500"> En service</label>
                <div class="col-span-2 text-right"><button class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">{{ $v->exists ? 'Enregistrer' : 'Ajouter' }}</button></div>
            </form>
        @endforeach
    </div>
    <div class="space-y-3">
        <h2 class="text-white font-semibold"><i class="fas fa-id-badge text-orange-400 mr-1"></i>Chauffeurs</h2>
        @foreach($drivers->push(new \App\Models\TransportDriver(['is_active' => true])) as $d)
            <form method="POST" action="{{ route('provider.transport-fleet.driver', $d->exists ? $d : null) }}" class="bg-green-900 border border-slate-800 rounded-xl p-3 grid grid-cols-2 sm:grid-cols-4 gap-2 items-end">
                @csrf
                <label class="text-[11px] text-slate-400 col-span-2 sm:col-span-1">Nom<input name="name" required maxlength="120" value="{{ $d->name }}" placeholder="{{ $d->exists ? '' : '+ Nouveau chauffeur' }}" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">Téléphone<input name="phone" required maxlength="30" value="{{ $d->phone }}" class="{{ $f }}"></label>
                <label class="text-[11px] text-slate-400">Langues<input name="languages" maxlength="120" value="{{ $d->languages }}" placeholder="Français, English" class="{{ $f }}"></label>
                <div class="flex items-center gap-2"><label class="text-xs text-slate-300 flex items-center gap-1"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($d->is_active) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Actif</label><button class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">{{ $d->exists ? 'OK' : 'Ajouter' }}</button></div>
            </form>
        @endforeach
    </div>
</div>
@endsection
