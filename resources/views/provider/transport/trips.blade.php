@extends('layouts.app')

@section('title', 'Lignes régulières')
@section('page-title', 'Lignes régulières et navettes')

@section('content')
@include('provider.transport._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-2 text-sm text-slate-100 w-full mt-1'; $tz = 'Africa/Abidjan'; @endphp
@if($lines->isEmpty())
    <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Créez d'abord une offre de type « Ligne régulière / navette » dans <a href="{{ route('provider.transport.create') }}" class="text-orange-300">Offres et tarifs</a>.</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-4">
        @foreach($lines as $line)
            <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
                <p class="text-white font-semibold">{{ $line->name }} <span class="text-slate-400 font-normal text-sm">— {{ $line->route_from }} → {{ $line->route_to }}</span></p>
                <div class="mt-2 divide-y divide-slate-800">
                    @forelse($line->trips as $t)
                        <div class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm {{ $t->is_active ? 'text-slate-200' : 'text-slate-500' }}">
                            <span>{{ ucfirst($t->departs_at->timezone($tz)->locale('fr')->isoFormat('ddd D MMM · HH:mm')) }}</span>
                            <span>{{ $t->seats_total - $t->seatsLeft() }} / {{ $t->seats_total }} sièges · {{ number_format($t->price(), 0, ',', ' ') }} XOF</span>
                            <form method="POST" action="{{ route('provider.transport-trips.toggle', $t) }}">@csrf @method('PATCH')<button class="text-xs border border-slate-700 rounded-lg px-2.5 py-1">{{ $t->is_active ? 'Fermer à la vente' : 'Rouvrir' }}</button></form>
                        </div>
                    @empty
                        <p class="text-slate-400 text-sm py-2">Aucun départ programmé.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    <form method="POST" action="{{ route('provider.transport-trips.store') }}" class="bg-green-900 border border-slate-800 rounded-xl p-4 space-y-2 self-start">
        @csrf
        <h2 class="text-white font-semibold text-sm"><i class="fas fa-plus text-orange-400 mr-1"></i>Programmer des départs</h2>
        <label class="block text-[11px] text-slate-400">Ligne<select name="transport_offer_id" class="{{ $f }}">@foreach($lines as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></label>
        <label class="block text-[11px] text-slate-400">À partir du<input type="date" name="date" required min="{{ now()->toDateString() }}" value="{{ now()->addDay()->toDateString() }}" class="{{ $f }}"></label>
        <label class="block text-[11px] text-slate-400">Heures de départ<input name="times" required placeholder="07:00, 14:00" class="{{ $f }}"></label>
        <label class="block text-[11px] text-slate-400">Répéter chaque jour pendant<select name="repeat_days" class="{{ $f }}"><option value="0">Ce jour seulement</option>@foreach([6, 13, 29, 59] as $d)<option value="{{ $d }}">{{ $d + 1 }} jours</option>@endforeach</select></label>
        <div class="grid grid-cols-3 gap-2">
            <label class="text-[11px] text-slate-400">Sièges<input type="number" name="seats_total" required min="1" value="30" class="{{ $f }}"></label>
            <label class="text-[11px] text-slate-400">Par rangée<input type="number" name="seats_per_row" required min="2" max="6" value="4" class="{{ $f }}"></label>
            <label class="text-[11px] text-slate-400">Prix<input type="number" name="price_xof" min="0" placeholder="offre" class="{{ $f }}"></label>
        </div>
        <label class="block text-[11px] text-slate-400">Véhicule<select name="transport_vehicle_id" class="{{ $f }}"><option value="">—</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->name }} {{ $v->plate }}</option>@endforeach</select></label>
        <button class="bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">Programmer</button>
    </form>
</div>
@endif
@endsection
