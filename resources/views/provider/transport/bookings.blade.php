@extends('layouts.app')

@section('title', 'Planning des transports')
@section('page-title', 'Planning des transports')

@section('content')
@include('provider.transport._nav')
@php $f = 'bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-100'; $tz = 'Africa/Abidjan'; @endphp
<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" aria-label="Date">
    @foreach($upcoming as $u)<a href="?date={{ $u->d }}" class="text-xs rounded-lg px-2.5 py-1.5 border {{ $u->d === $date ? 'border-orange-500 text-orange-300' : 'border-slate-700 text-slate-300' }}">{{ \Carbon\Carbon::parse($u->d)->locale('fr')->isoFormat('ddd D') }} · {{ $u->n }}</a>@endforeach
</form>

<div class="space-y-3">
    @forelse($bookings as $b)
        <div class="bg-green-900 border border-slate-800 rounded-xl p-4 {{ $b->status !== 'paid' ? 'opacity-50' : '' }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold"><span class="text-orange-300">{{ $b->starts_at->timezone($tz)->format('H:i') }}</span> · <i class="fas {{ \App\Models\TransportOffer::MODE_ICONS[$b->mode] ?? 'fa-car' }} text-xs text-slate-400"></i> {{ $b->offer_name }} <span class="text-xs text-slate-500">{{ $b->reference }}</span></p>
                    <p class="text-slate-300 text-sm">{{ $b->pickup_label }}@if($b->dropoff_label) → {{ $b->dropoff_label }}@endif @if($b->distance_km)<span class="text-slate-500">({{ number_format($b->distance_km, 1, ',', ' ') }} km)</span>@endif</p>
                    <p class="text-slate-300 text-sm">{{ $b->full_name }} · <a href="tel:{{ $b->phone }}" class="text-orange-300">{{ $b->phone }}</a> · {{ $b->passengers }} pers.@if($b->luggage) · {{ $b->luggage }} bagage(s)@endif @if($b->seats) · sièges {{ implode(', ', $b->seats) }}@endif</p>
                    @if($b->flight_number)
                        <p class="text-amber-300 text-sm"><i class="fas fa-plane mr-1"></i>Vol {{ $b->flight_number }} — pancarte « {{ $b->full_name }} »{{ $b->offer?->free_waiting_minutes ? ', '.$b->offer->free_waiting_minutes.' min d\'attente gratuite' : '' }}</p>
                    @endif
                    @if($b->round_trip && $b->return_at)<p class="text-slate-400 text-xs">Retour : {{ $b->return_at->timezone($tz)->format('d/m H:i') }}</p>@endif
                    @if($b->mode === 'rental')<p class="text-slate-400 text-xs">Retour du véhicule : {{ $b->ends_at->timezone($tz)->format('d/m H:i') }} · caution {{ number_format((int) $b->deposit_on_site_xof, 0, ',', ' ') }} XOF
                        · documents : @if(! empty($b->documents['license']))<a href="{{ route('provider.transport-bookings.file', [$b, 'license']) }}" target="_blank" class="text-orange-300">permis</a>@else<span class="text-rose-300">permis manquant</span>@endif,
                        @if(! empty($b->documents['id_card']))<a href="{{ route('provider.transport-bookings.file', [$b, 'id_card']) }}" target="_blank" class="text-orange-300">pièce</a>@else<span class="text-rose-300">pièce manquante</span>@endif
                        · contrat {{ $b->contract_accepted_at ? 'accepté' : 'non accepté' }}</p>@endif
                    @if($b->extras)<p class="text-slate-400 text-xs">+ {{ collect($b->extras)->map(fn ($e) => ($e['qty'] > 1 ? $e['qty'].' × ' : '').$e['label'])->join(', ') }}</p>@endif
                    @if($b->message)<p class="text-slate-400 text-xs italic">« {{ $b->message }} »</p>@endif
                </div>
                <div class="text-right text-xs space-y-1">
                    <p class="text-slate-200">{{ number_format($b->amount_total_xof, 0, ',', ' ') }} XOF · {{ $b->statusLabel() }}</p>
                    @if($b->tripStatusLabel())<p class="text-amber-300">{{ $b->tripStatusLabel() }}</p>@endif
                </div>
            </div>
            @if($b->status === 'paid')
                <div class="mt-3 flex flex-wrap items-end gap-2">
                    <form method="POST" action="{{ route('provider.transport-bookings.assign', $b) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <select name="transport_driver_id" class="{{ $f }}" aria-label="Chauffeur"><option value="">Chauffeur…</option>@foreach($drivers as $d)<option value="{{ $d->id }}" @selected($b->transport_driver_id === $d->id)>{{ $d->name }}</option>@endforeach</select>
                        <select name="transport_vehicle_id" class="{{ $f }}" aria-label="Véhicule"><option value="">Véhicule…</option>@foreach($vehicles as $v)<option value="{{ $v->id }}" @selected($b->transport_vehicle_id === $v->id)>{{ $v->name }} {{ $v->plate }}</option>@endforeach</select>
                        <button class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-1.5 rounded-lg">Affecter</button>
                    </form>
                    @if($b->mode !== 'rental' && $b->mode !== 'line')
                        <form method="POST" action="{{ route('provider.transport-bookings.status', $b) }}" class="flex flex-wrap gap-1">
                            @csrf
                            @foreach(['en_route' => 'En route', 'arrived' => 'Arrivé', 'in_progress' => 'Passager à bord', 'completed' => 'Terminé', 'no_show' => 'Client absent'] as $k => $l)
                                <button name="trip_status" value="{{ $k }}" class="text-xs border rounded-lg px-2.5 py-1.5 {{ $b->trip_status === $k ? 'border-emerald-500 text-emerald-300' : 'border-slate-700 text-slate-300' }}">{{ $l }}</button>
                            @endforeach
                        </form>
                    @endif
                </div>
                @if($b->mode === 'rental')
                    @foreach(['out' => 'État des lieux de départ', 'in' => 'État des lieux de retour'] as $stage => $title)
                        <details class="mt-2" @if($stage === 'out' && empty($b->inspection['out'])) open @endif>
                            <summary class="cursor-pointer text-xs text-slate-300">{{ $title }} @if(! empty($b->inspection[$stage]))<span class="text-emerald-300">✓ {{ $b->inspection[$stage]['km'] }} km, {{ $b->inspection[$stage]['fuel'] }} % carburant @if(isset($b->inspection[$stage]['extra_km_xof']) && $b->inspection[$stage]['extra_km_xof'] > 0)· {{ $b->inspection[$stage]['extra_km'] }} km en plus = {{ number_format($b->inspection[$stage]['extra_km_xof'], 0, ',', ' ') }} XOF @endif</span> @foreach($b->inspection[$stage]['photos'] ?? [] as $pi => $ph)<a href="{{ route('provider.transport-bookings.file', [$b, $stage, $pi]) }}" target="_blank" class="text-orange-300 ml-1">photo {{ $pi + 1 }}</a>@endforeach @endif</summary>
                            <form method="POST" action="{{ route('provider.transport-bookings.inspection', $b) }}" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-end gap-2">
                                @csrf <input type="hidden" name="stage" value="{{ $stage }}">
                                <label class="text-[11px] text-slate-400">Compteur (km)<input type="number" name="km" required min="0" class="{{ $f }} block mt-1 w-28"></label>
                                <label class="text-[11px] text-slate-400">Carburant<select name="fuel" class="{{ $f }} block mt-1">@foreach([100, 75, 50, 25, 0] as $fu)<option value="{{ $fu }}">{{ $fu }} %</option>@endforeach</select></label>
                                <label class="text-[11px] text-slate-400">Photos<input type="file" name="photos[]" multiple accept="image/*" class="{{ $f }} block mt-1"></label>
                                <label class="text-[11px] text-slate-400 flex-1 min-w-[10rem]">Remarques (rayures, chocs…)<input name="notes" maxlength="1000" class="{{ $f }} block mt-1 w-full"></label>
                                <button class="bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-1.5 rounded-lg">Enregistrer</button>
                            </form>
                        </details>
                    @endforeach
                @endif
            @endif
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Aucun transport ce jour-là.</div>
    @endforelse
</div>
@endsection
