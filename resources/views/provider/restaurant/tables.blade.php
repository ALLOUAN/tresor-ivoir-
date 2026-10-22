@extends('layouts.app')

@section('title', 'Réservations de tables')
@section('page-title', 'Réservations de tables')

@section('content')
@php $field = 'w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none'; @endphp
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <form method="POST" action="{{ route('provider.restaurant.tables.update') }}" class="lg:col-span-2 space-y-5">
        @csrf @method('PUT')

        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 space-y-4">
            <h2 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-chair text-orange-400"></i> Places et créneaux</h2>
            <p class="text-slate-400 text-sm">Indiquez combien de couverts vous pouvez accueillir par créneau : le site refuse automatiquement les réservations quand c'est complet et propose les créneaux libres les plus proches. Laissez vide pour garder les demandes « sur confirmation ».</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Couverts par créneau</label>
                    <input type="number" name="table_capacity" min="1" value="{{ old('table_capacity', $restaurant->table_capacity) }}" placeholder="Ex. 40" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Durée d'un créneau</label>
                    <select name="slot_minutes" class="{{ $field }}">
                        @foreach([15, 30, 45, 60, 90, 120] as $m)
                            <option value="{{ $m }}" @selected((int) old('slot_minutes', $restaurant->slot_minutes ?: 30) === $m)>{{ $m }} min</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Table occupée pendant</label>
                    <select name="table_duration_minutes" class="{{ $field }}">
                        @foreach([60, 90, 120, 150, 180] as $m)
                            <option value="{{ $m }}" @selected((int) old('table_duration_minutes', $restaurant->table_duration_minutes ?: 90) === $m)>{{ intdiv($m, 60) }} h{{ $m % 60 ? ' '.($m % 60) : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Groupe max en ligne</label>
                    <input type="number" name="max_party_size" min="1" value="{{ old('max_party_size', $restaurant->max_party_size) }}" placeholder="Ex. 10" class="{{ $field }}">
                </div>
            </div>
            <label class="flex items-start gap-2 text-sm text-slate-300">
                <input type="checkbox" name="auto_confirm" value="1" class="mt-1" @checked(old('auto_confirm', $restaurant->auto_confirm))>
                <span><b>Confirmation instantanée</b> : quand il reste de la place, la table est confirmée tout de suite au client (vous êtes prévenu). Nécessite le nombre de couverts.</span>
            </label>
            <p class="text-slate-500 text-xs">Les créneaux suivent les horaires de votre fiche (dernière réservation une heure avant la fermeture). Sans horaires : 12 h – 14 h 30 et 19 h – 22 h 30.</p>
        </div>

        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 space-y-3">
            <h2 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-basket-shopping text-orange-400"></i> Commandes de plats</h2>
            <p class="text-slate-400 text-sm">Les clients choisissent des plats de votre carte (onglet « Commander » de votre fiche). Vous confirmez la commande et son prix, puis ils règlent en ligne ou au retrait.</p>
            <label class="flex items-start gap-2 text-sm text-slate-300">
                <input type="hidden" name="accepts_orders" value="0">
                <input type="checkbox" name="accepts_orders" value="1" class="mt-1" @checked(old('accepts_orders', $restaurant->accepts_orders))>
                <span><b>Accepter les commandes à emporter</b> (pendant vos horaires d'ouverture).</span>
            </label>
            <label class="flex items-start gap-2 text-sm text-slate-300">
                <input type="hidden" name="offers_delivery" value="0">
                <input type="checkbox" name="offers_delivery" value="1" class="mt-1" @checked(old('offers_delivery', $restaurant->offers_delivery))>
                <span><b>Proposer la livraison</b> : le client indique son adresse ; vous ajustez le prix (frais de livraison) en confirmant.</span>
            </label>
        </div>

        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 space-y-3">
            <h2 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-bowl-food text-orange-400"></i> Plat du jour</h2>
            <p class="text-slate-400 text-sm">Affiché en tête de votre fiche aujourd'hui seulement ; il disparaît automatiquement demain.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input name="daily_special" maxlength="200" value="{{ old('daily_special', $restaurant->daily_special_date?->isToday() ? $restaurant->daily_special : '') }}" placeholder="Ex. Kedjenou de poulet et attiéké" class="{{ $field }} sm:col-span-2">
                <input type="number" name="daily_special_price_xof" min="0" value="{{ old('daily_special_price_xof', $restaurant->daily_special_date?->isToday() ? $restaurant->daily_special_price_xof : '') }}" placeholder="Prix (FCFA)" class="{{ $field }}">
            </div>
        </div>

        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-5 py-2.5 rounded-lg">
            <i class="fas fa-floppy-disk"></i> Enregistrer
        </button>
    </form>

    <div class="space-y-5">
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold flex items-center gap-2 mb-2"><i class="fas fa-qrcode text-orange-400"></i> Menu QR</h2>
            <p class="text-slate-400 text-sm mb-3">Imprimez ce code et posez-le sur vos tables : vos clients consultent la carte sur leur téléphone, et découvrent votre fiche.</p>
            <a href="{{ route('provider.restaurant.menu-qr') }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-orange-300 hover:text-orange-200"><i class="fas fa-print"></i> Imprimer le menu QR</a>
        </div>

        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold flex items-center gap-2 mb-3"><i class="fas fa-calendar-day text-orange-400"></i> Tables du jour</h2>
            @forelse($today as $b)
                <div class="flex items-center justify-between text-sm py-2 border-b border-slate-800 last:border-0">
                    <span class="text-white font-semibold w-14">{{ $b->booking_time ?: '—' }}</span>
                    <span class="flex-1 text-slate-300 truncate">{{ $b->full_name }}@if($b->restaurantTable) <span class="ml-1 px-1.5 py-0.5 rounded bg-orange-500/15 text-orange-300 text-[11px] font-semibold">{{ $b->restaurantTable->label }}</span>@endif</span>
                    <span class="text-slate-400">{{ $b->party_size }} couv.</span>
                    <span class="ml-2 text-xs {{ $b->status === 'confirmed' ? 'text-emerald-300' : 'text-amber-300' }}">{{ $b->statusLabel() }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-sm">Aucune table réservée aujourd'hui.</p>
            @endforelse
            <a href="{{ route('provider.booking-requests.index') }}" class="inline-block mt-3 text-xs text-orange-300 hover:text-orange-200">Toutes les demandes →</a>
        </div>
    </div>
</div>

{{-- ══ Plan de salle : tables que le client choisit en réservant ══ --}}
<div class="mt-5 bg-green-900 border border-slate-800 rounded-xl p-5">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
        <div>
            <h2 class="text-white font-semibold flex items-center gap-2"><i class="fas fa-table-cells-large text-orange-400"></i> Plan de salle</h2>
            <p class="text-slate-400 text-sm mt-1">Vos clients choisissent leur table sur votre fiche : les tables déjà réservées à l'heure demandée apparaissent grisées. Une table reste occupée pendant la durée réglée ci-dessus.</p>
        </div>
        <span class="text-xs text-slate-400">{{ $tables->where('is_active', true)->count() }} table(s) en service · {{ $tables->where('is_active', true)->sum('seats') }} places</span>
    </div>

    <form method="POST" action="{{ route('provider.restaurant.floor.store') }}" class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end mb-5">
        @csrf
        <label class="block"><span class="block text-xs text-slate-400 mb-1">Numéro</span>
            <input name="label" required maxlength="30" value="{{ old('label') }}" placeholder="Ex. T1" class="{{ $field }}"></label>
        <label class="block"><span class="block text-xs text-slate-400 mb-1">Places</span>
            <input type="number" name="seats" required min="1" max="50" value="{{ old('seats', 4) }}" class="{{ $field }}"></label>
        <label class="block"><span class="block text-xs text-slate-400 mb-1">Zone</span>
            <input name="zone" required maxlength="40" list="zones-list" value="{{ old('zone', 'Salle') }}" class="{{ $field }}">
            <datalist id="zones-list">@foreach(\App\Models\RestaurantTable::ZONES as $z)<option value="{{ $z }}">@endforeach</datalist></label>
        <label class="block"><span class="block text-xs text-slate-400 mb-1">Forme</span>
            <select name="shape" class="{{ $field }}">@foreach(\App\Models\RestaurantTable::SHAPES as $k => $v)<option value="{{ $k }}" @selected(old('shape') === $k)>{{ $v }}</option>@endforeach</select></label>
        <label class="block"><span class="block text-xs text-slate-400 mb-1">Combien ?</span>
            <input type="number" name="count" min="1" max="30" value="{{ old('count', 1) }}" class="{{ $field }}" title="Plusieurs : « T » × 5 crée T1 à T5"></label>
        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg"><i class="fas fa-plus"></i> Ajouter</button>
    </form>

    @forelse($tables->groupBy('zone') as $zone => $zoneTables)
        <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2 mt-4">{{ $zone }} <span class="normal-case tracking-normal">· {{ $zoneTables->sum('seats') }} places</span></p>
        <div class="flex flex-wrap gap-3">
            @foreach($zoneTables as $t)
                <div class="relative w-28 rounded-xl border p-3 text-center {{ $t->is_active ? 'border-slate-700 bg-slate-800' : 'border-dashed border-slate-700 opacity-50' }}">
                    <span class="mx-auto mb-2 flex items-center justify-center bg-orange-500/15 text-orange-300 font-bold text-sm {{ $t->shape === 'round' ? 'w-12 h-12 rounded-full' : ($t->shape === 'long' ? 'w-20 h-10 rounded-lg' : 'w-12 h-12 rounded-lg') }}">{{ $t->label }}</span>
                    <span class="block text-xs text-slate-300">{{ $t->seats }} places</span>
                    <div class="mt-2 flex items-center justify-center gap-2">
                        <form method="POST" action="{{ route('provider.restaurant.floor.toggle', $t) }}">@csrf @method('PATCH')
                            <button class="text-[11px] text-slate-400 hover:text-white" title="{{ $t->is_active ? 'Mettre hors service' : 'Remettre en service' }}"><i class="fas {{ $t->is_active ? 'fa-pause' : 'fa-play' }}"></i></button></form>
                        <form method="POST" action="{{ route('provider.restaurant.floor.destroy', $t) }}" onsubmit="return confirm('Supprimer la table {{ $t->label }} ?')">@csrf @method('DELETE')
                            <button class="text-[11px] text-rose-400 hover:text-rose-300" aria-label="Supprimer la table {{ $t->label }}"><i class="fas fa-trash"></i></button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-slate-400 text-sm">Aucune table pour l'instant. Ajoutez vos tables (ex. « T » × 8 tables de 4 en Salle) : le plan apparaîtra aussitôt sur votre fiche.</p>
    @endforelse
</div>
@endsection
