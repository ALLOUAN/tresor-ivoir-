@extends('layouts.app')

@section('title', 'Départs — '.$tour->name)
@section('page-title', 'Départs programmés')

@section('header-actions')
    <a href="{{ route('provider.tours.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2"><i class="fas fa-arrow-left text-xs"></i> Mes circuits</a>
@endsection

@section('content')
@php $field = 'w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none'; @endphp
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

<p class="text-slate-400 text-sm mb-4"><b class="text-white">{{ $tour->name }}</b> — les voyageurs choisissent un départ et voient les places restantes{{ $tour->instant_booking ? ', réservent et paient en ligne immédiatement' : '' }}.
    @if($tour->min_participants) Minimum par défaut : <b class="text-white">{{ $tour->min_participants }} voyageurs</b>, décision {{ $tour->guarantee_days }} jours avant le départ.@endif
    @if($url = $tour->publicUrl()) <a href="{{ $url }}" target="_blank" class="text-orange-300 hover:text-orange-200 whitespace-nowrap">Voir la page du circuit <i class="fas fa-arrow-up-right-from-square text-[10px]"></i></a>@endif
</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
    <div class="lg:col-span-3 space-y-3">
        @forelse($departures as $d)
            @php
                $taken = $d->seatsTaken();
                $online = $d->onlineTravelers();
                $past = $d->departure_date->isPast() && ! $d->departure_date->isToday();
                $active = $d->bookings->whereIn('status', [\App\Models\TourBooking::STATUS_DEPOSIT_PAID, \App\Models\TourBooking::STATUS_PAID]);
            @endphp
            <div class="bg-green-900 border border-slate-800 rounded-xl p-4 {{ $past || $d->cancelled_at ? 'opacity-60' : '' }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-white font-semibold">{{ $d->departure_date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                        <p class="text-slate-400 text-xs mt-0.5">{{ $taken }} / {{ $d->seats_total }} places prises · {{ number_format($d->pricePerPerson(), 0, ',', ' ') }} XOF / pers.@if($d->minimum()) · minimum {{ $d->minimum() }}@endif</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                        @if($d->cancelled_at)
                            <span class="px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-300">Annulé — {{ $d->cancel_reason }}</span>
                        @elseif($d->isGuaranteed())
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300"><i class="fas fa-circle-check mr-1"></i>Départ garanti</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-300">{{ $d->missingForGuarantee() }} voyageur(s) manquant(s) · décision le {{ $d->decisionDate()->format('d/m') }}</span>
                        @endif
                        @if(! $d->is_active && ! $d->cancelled_at)<span class="px-2 py-0.5 rounded-full bg-slate-700 text-slate-300">Inscriptions fermées</span>@endif
                        @if($taken >= $d->seats_total)<span class="px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-300">Complet</span>@endif
                    </div>
                </div>

                @if($active->isNotEmpty() || $d->bookings->isNotEmpty())
                    <details class="mt-3 group">
                        <summary class="cursor-pointer text-sm text-orange-300 hover:text-orange-200"><i class="fas fa-users mr-1"></i>Liste des voyageurs ({{ $online }})</summary>
                        <div class="mt-2 overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead><tr class="text-left text-slate-400 border-b border-slate-800"><th class="py-2 pr-3">Réservation</th><th class="py-2 pr-3">Voyageurs</th><th class="py-2 pr-3">Prise en charge</th><th class="py-2 pr-3">Paiement</th></tr></thead>
                                <tbody class="divide-y divide-slate-800 text-slate-200">
                                    @foreach($d->bookings as $b)
                                        <tr class="{{ $b->status === 'cancelled' ? 'opacity-50' : '' }}">
                                            <td class="py-2 pr-3 align-top"><b>{{ $b->full_name }}</b><br><span class="text-slate-400">{{ $b->reference }} · {{ $b->phone }}</span></td>
                                            <td class="py-2 pr-3 align-top">
                                                {{ $b->adults }} adulte(s){{ $b->children ? ', '.$b->children.' enfant(s)' : '' }}{{ $b->single_rooms ? ' · '.$b->single_rooms.' ch. indiv.' : '' }}
                                                @foreach(collect($b->travelers ?? [])->filter(fn ($t) => $t['name'] ?? null) as $t)
                                                    <br><span class="text-slate-400">{{ $t['name'] }}@if($t['id_number'] ?? null) · {{ $t['id_number'] }}@endif</span>
                                                @endforeach
                                                @unless($b->travelersComplete())<br><span class="text-amber-300">Liste nominative incomplète</span>@endunless
                                                @if($b->extras)<br><span class="text-slate-400">+ {{ collect($b->extras)->pluck('label')->join(', ') }}</span>@endif
                                            </td>
                                            <td class="py-2 pr-3 align-top">{{ $b->pickup_point ?: '—' }}</td>
                                            <td class="py-2 pr-3 align-top">{{ number_format($b->amount_paid_xof, 0, ',', ' ') }} / {{ number_format($b->amount_total_xof, 0, ',', ' ') }} XOF<br><span class="text-slate-400">{{ $b->statusLabel() }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif

                @if(! $past && ! $d->cancelled_at)
                    <div class="mt-3 flex flex-wrap items-start gap-2">
                        <details class="w-full sm:w-auto">
                            <summary class="cursor-pointer inline-flex text-xs text-slate-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-pen mr-1.5"></i>Modifier</summary>
                            <form method="POST" action="{{ route('provider.tours.departures.update', [$tour, $d]) }}" class="mt-2 grid grid-cols-3 gap-2 items-end">
                                @csrf @method('PATCH')
                                <label class="text-[11px] text-slate-400">Places<input type="number" name="seats_total" min="1" value="{{ $d->seats_total }}" class="{{ $field }} mt-1"></label>
                                <label class="text-[11px] text-slate-400">Minimum<input type="number" name="min_participants" min="1" value="{{ $d->min_participants }}" placeholder="{{ $tour->min_participants }}" class="{{ $field }} mt-1"></label>
                                <label class="text-[11px] text-slate-400">Prix / pers.<input type="number" name="price_xof" min="0" value="{{ $d->price_xof }}" placeholder="{{ $tour->price_xof }}" class="{{ $field }} mt-1"></label>
                                <label class="col-span-3 flex items-center gap-2 text-xs text-slate-300"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($d->is_active) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Inscriptions ouvertes</label>
                                <button class="col-span-3 sm:col-span-1 bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-3 py-2 rounded-lg">Enregistrer</button>
                            </form>
                        </details>
                        @unless($d->isGuaranteed())
                            <form method="POST" action="{{ route('provider.tours.departures.guarantee', [$tour, $d]) }}" onsubmit="return confirm('Confirmer ce départ même sans le minimum ? Les voyageurs inscrits seront prévenus.');">
                                @csrf
                                <button class="text-xs text-emerald-300 border border-emerald-700/50 hover:border-emerald-500 rounded-lg px-3 py-1.5"><i class="fas fa-circle-check mr-1.5"></i>Garantir le départ</button>
                            </form>
                        @endunless
                        @if($active->isNotEmpty())
                            <details class="w-full sm:w-auto">
                                <summary class="cursor-pointer inline-flex text-xs text-rose-300 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-ban mr-1.5"></i>Annuler le départ</summary>
                                <form method="POST" action="{{ route('provider.tours.departures.cancel', [$tour, $d]) }}" class="mt-2 space-y-2" onsubmit="return confirm('Annuler ce départ ? Tous les voyageurs seront remboursés à 100 % sur leur solde.');">
                                    @csrf
                                    <input type="text" name="reason" required maxlength="255" placeholder="Motif transmis aux voyageurs" class="{{ $field }}">
                                    <button class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold px-3 py-2 rounded-lg">Annuler et rembourser {{ $active->count() }} réservation(s)</button>
                                </form>
                            </details>
                        @elseif($d->is_active)
                            <form method="POST" action="{{ route('provider.tours.departures.destroy', [$tour, $d]) }}" onsubmit="return confirm('Retirer ce départ ?');">
                                @csrf @method('DELETE')
                                <button class="text-xs text-rose-300 border border-slate-700 rounded-lg px-3 py-1.5">{{ $taken > 0 ? 'Fermer' : 'Supprimer' }}</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-green-900 border border-slate-800 rounded-xl px-4 py-8 text-center text-slate-400 text-sm">Aucun départ programmé.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('provider.tours.departures.store', $tour) }}" class="lg:col-span-2 bg-green-900 border border-slate-800 rounded-xl p-5 space-y-3 self-start">
        @csrf
        <h2 class="text-white font-semibold"><i class="fas fa-plus text-orange-400 mr-1"></i> Ajouter un départ</h2>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Date de départ</label>
            <input type="date" name="departure_date" required min="{{ now()->toDateString() }}" value="{{ old('departure_date') }}" class="{{ $field }}">
        </div>
        <div class="grid grid-cols-3 gap-2">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Places</label>
                <input type="number" name="seats_total" required min="1" value="{{ old('seats_total', $tour->max_participants ?: 12) }}" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Minimum</label>
                <input type="number" name="min_participants" min="1" value="{{ old('min_participants') }}" placeholder="{{ $tour->min_participants ?: '—' }}" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Prix / pers.</label>
                <input type="number" name="price_xof" min="0" value="{{ old('price_xof') }}" placeholder="{{ (int) $tour->price_xof }}" class="{{ $field }}">
            </div>
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Répéter chaque semaine pendant</label>
            <select name="repeat_weeks" class="{{ $field }}">
                <option value="0">Départ unique</option>
                @foreach([3, 7, 11, 25, 51] as $w)
                    <option value="{{ $w }}">{{ $w + 1 }} semaines</option>
                @endforeach
            </select>
        </div>
        <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2.5 rounded-lg"><i class="fas fa-floppy-disk"></i> Ajouter</button>
    </form>
</div>
@endsection
