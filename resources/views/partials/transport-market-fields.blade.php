{{-- Champs « marché » d'une offre de transport (espace compagnie et administration). Paramètre : $offer. --}}
@php
    $in = 'w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100';
    $sec = 'rounded-xl border border-slate-800 p-4 space-y-3';
    $h = 'text-white text-sm font-semibold flex items-center gap-2';
    $hint = 'text-slate-500 text-[11px]';
    $mode = old('mode', $offer->mode);
    $zones = old('zones', $offer->zone_prices ?: []);
    $extras = old('extras', $offer->extras ?: []);
    $del = '<button type="button" class="tx-del text-slate-400 hover:text-rose-300 px-2" aria-label="'.e(__('Retirer')).'"><i class="fas fa-xmark"></i></button>';
    $num = fn (string $name, string $label, $placeholder = '') => '<label class="text-xs text-slate-400">'.e($label).'<input type="number" name="'.$name.'" min="0" value="'.e(old($name, $offer->{$name})).'" placeholder="'.e($placeholder).'" class="'.$in.' mt-1"></label>';
@endphp

<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-bolt text-orange-400 text-xs"></i> {{ __('Type d\'offre et réservation en ligne') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <label class="text-xs text-slate-400 sm:col-span-1">{{ __('Type') }}
            <select name="mode" id="tx-mode" class="{{ $in }} mt-1">
                <option value="">{{ __('Sur demande (devis par la compagnie)') }}</option>
                @foreach(\App\Models\TransportOffer::MODES as $k => $l)<option value="{{ $k }}" @selected($mode === $k)>{{ __($l) }}</option>@endforeach
            </select></label>
        <label class="text-xs text-slate-400">{{ __('Catégorie de véhicule') }}
            <select name="vehicle_category" class="{{ $in }} mt-1"><option value="">{{ __('Toutes') }}</option>@foreach(\App\Models\TransportOffer::VEHICLE_CATEGORIES as $k => $l)<option value="{{ $k }}" @selected(old('vehicle_category', $offer->vehicle_category) === $k)>{{ __($l) }}</option>@endforeach</select></label>
        <label class="text-xs text-slate-400">{{ __('Bagages max.') }}<input type="number" name="max_luggage" min="0" value="{{ old('max_luggage', $offer->max_luggage) }}" class="{{ $in }} mt-1"></label>
    </div>
    <label class="flex items-center gap-2.5 cursor-pointer"><input type="hidden" name="instant_booking" value="0"><input type="checkbox" name="instant_booking" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('instant_booking', $offer->exists ? $offer->instant_booking : true))><span class="text-sm text-slate-300">{{ __('Réservation et paiement immédiats') }}</span></label>
    <p class="{{ $hint }}">{{ __('Un véhicule de votre flotte (catégorie ci-dessus) est réservé automatiquement : pas de double réservation. Sans véhicule déclaré, aucune limite n\'est appliquée.') }}</p>
</div>

<div class="{{ $sec }} tx-pane" data-mode="transfer">
    <p class="{{ $h }}"><i class="fas fa-plane-arrival text-orange-400 text-xs"></i> {{ __('Transfert à prix fixe') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <label class="text-xs text-slate-400 sm:col-span-1">{{ __('Point d\'origine') }}<input name="origin_label" maxlength="150" value="{{ old('origin_label', $offer->origin_label) }}" placeholder="{{ __('Aéroport FHB (Port-Bouët)') }}" class="{{ $in }} mt-1"></label>
        {!! $num('free_waiting_minutes', __('Attente gratuite (min)'), '60') !!}
        {!! $num('round_trip_discount_percent', __('Remise aller-retour (%)'), '10') !!}
        {!! $num('trip_minutes', __('Durée moyenne (min)'), '45') !!}
    </div>
    <p class="text-xs text-slate-400">{{ __('Destinations et prix fixes') }}</p>
    <div id="tx-zones" class="space-y-2" data-next="{{ count($zones) }}">
        @foreach($zones as $i => $z)
            <div class="tx-row grid grid-cols-[1fr_7rem_auto] gap-2"><input name="zones[{{ $i }}][label]" maxlength="120" value="{{ $z['label'] ?? '' }}" placeholder="{{ __('Cocody') }}" class="{{ $in }}"><input type="number" name="zones[{{ $i }}][price_xof]" min="0" value="{{ $z['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $in }}">{!! $del !!}</div>
        @endforeach
    </div>
    <button type="button" data-tx-add="zones" class="text-xs text-orange-300 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une destination') }}</button>
</div>

<div class="{{ $sec }} tx-pane" data-mode="chauffeur">
    <p class="{{ $h }}"><i class="fas fa-user-tie text-orange-400 text-xs"></i> {{ __('Chauffeur privé : devis instantané') }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {!! $num('base_fare_xof', __('Prise en charge'), '2000') !!}
        {!! $num('per_km_xof', __('Prix par km'), '350') !!}
        {!! $num('per_hour_xof', __('Prix par heure'), '3000') !!}
        {!! $num('minimum_xof', __('Minimum'), '8000') !!}
        {!! $num('half_day_xof', __('Forfait demi-journée (5 h)'), '35000') !!}
        {!! $num('full_day_xof', __('Forfait journée (10 h)'), '60000') !!}
        {!! $num('included_km', __('Km inclus (forfaits / location par jour)'), '150') !!}
    </div>
    <p class="{{ $hint }}">{{ __('Trajet A → B : prise en charge + km × prix + durée × prix horaire, jamais moins que le minimum. Distance estimée sur la route depuis les points choisis sur la carte.') }}</p>
</div>

<div class="{{ $sec }} tx-pane" data-mode="rental">
    <p class="{{ $h }}"><i class="fas fa-car text-orange-400 text-xs"></i> {{ __('Location (le prix ci-dessus est le prix par jour)') }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {!! $num('weekly_daily_xof', __('Prix / jour dès 7 jours'), '32000') !!}
        {!! $num('deposit_xof', __('Caution au retrait'), '200000') !!}
        {!! $num('extra_km_xof', __('Prix du km en plus'), '150') !!}
        <label class="text-xs text-slate-400">{{ __('Âge minimum du conducteur') }}<input type="number" name="min_driver_age" min="18" max="30" value="{{ old('min_driver_age', $offer->min_driver_age) }}" class="{{ $in }} mt-1"></label>
    </div>
    <p class="{{ $hint }}">{{ __('Le client dépose permis et pièce d\'identité et accepte le contrat en ligne ; vous faites l\'état des lieux (km, carburant, photos) au départ et au retour.') }}</p>
</div>

<div class="{{ $sec }} tx-pane" data-mode="line">
    <p class="{{ $h }}"><i class="fas fa-bus text-orange-400 text-xs"></i> {{ __('Ligne régulière') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <label class="text-xs text-slate-400">{{ __('Départ') }}<input name="route_from" maxlength="120" value="{{ old('route_from', $offer->route_from) }}" placeholder="Abidjan (Adjamé)" class="{{ $in }} mt-1"></label>
        <label class="text-xs text-slate-400">{{ __('Arrivée') }}<input name="route_to" maxlength="120" value="{{ old('route_to', $offer->route_to) }}" placeholder="Yamoussoukro" class="{{ $in }} mt-1"></label>
        {!! $num('trip_minutes', __('Durée du trajet (min)'), '180') !!}
    </div>
    <p class="{{ $hint }}">{{ __('Les départs (date, heures, sièges, prix) se programment dans « Lignes ».') }}</p>
</div>

<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-square-plus text-orange-400 text-xs"></i> {{ __('Options, suppléments et annulation') }}</p>
    <div id="tx-extras" class="space-y-2" data-next="{{ count($extras) }}">
        @foreach($extras as $i => $e)
            <div class="tx-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2"><input name="extras[{{ $i }}][label]" maxlength="80" value="{{ $e['label'] ?? '' }}" placeholder="{{ __('Siège enfant') }}" class="{{ $in }} col-span-3 sm:col-span-1"><input type="number" name="extras[{{ $i }}][price_xof]" min="0" value="{{ $e['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $in }}"><select name="extras[{{ $i }}][per]" class="{{ $in }}">@foreach(['booking' => __('/ réservation'), 'day' => __('/ jour'), 'passenger' => __('/ passager')] as $k => $l)<option value="{{ $k }}" @selected(($e['per'] ?? 'booking') === $k)>{{ $l }}</option>@endforeach</select>{!! $del !!}</div>
        @endforeach
    </div>
    <button type="button" data-tx-add="extras" class="text-xs text-orange-300 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une option') }}</button>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {!! $num('night_surcharge_percent', __('Supplément de nuit 22 h – 6 h (%)'), '20') !!}
        <label class="text-xs text-slate-400">{{ __('Annulation et modification gratuites') }}
            @php $fch = old('free_cancellation_hours', $offer->free_cancellation_hours); @endphp
            <select name="free_cancellation_hours" class="{{ $in }} mt-1"><option value="" @selected($fch === null || $fch === '')>{{ __('Non remboursable') }}</option>@foreach([0 => __('Jusqu\'au départ'), 2 => '2 h', 6 => '6 h', 12 => '12 h', 24 => __('24 h (recommandé)'), 48 => '48 h', 72 => '72 h'] as $hrs => $l)<option value="{{ $hrs }}" @selected($fch !== null && $fch !== '' && (int) $fch === $hrs)>{{ $hrs ? __('Jusqu\'à :l avant', ['l' => $l]) : $l }}</option>@endforeach</select></label>
    </div>
</div>

<template id="tx-tpl-zones"><div class="tx-row grid grid-cols-[1fr_7rem_auto] gap-2"><input name="zones[__i__][label]" maxlength="120" placeholder="{{ __('Destination') }}" class="{{ $in }}"><input type="number" name="zones[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $in }}">{!! $del !!}</div></template>
<template id="tx-tpl-extras"><div class="tx-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2"><input name="extras[__i__][label]" maxlength="80" placeholder="{{ __('Option') }}" class="{{ $in }} col-span-3 sm:col-span-1"><input type="number" name="extras[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $in }}"><select name="extras[__i__][per]" class="{{ $in }}"><option value="booking">{{ __('/ réservation') }}</option><option value="day">{{ __('/ jour') }}</option><option value="passenger">{{ __('/ passager') }}</option></select>{!! $del !!}</div></template>
<script>
(function () {
    const mode = document.getElementById('tx-mode');
    const sync = () => document.querySelectorAll('.tx-pane').forEach(p => { p.hidden = p.dataset.mode !== mode.value; p.querySelectorAll('input').forEach(i => i.disabled = p.hidden); });
    mode.addEventListener('change', sync); sync();
    document.querySelectorAll('[data-tx-add]').forEach(b => b.addEventListener('click', () => {
        const k = b.dataset.txAdd, list = document.getElementById('tx-' + k), tpl = document.getElementById('tx-tpl-' + k);
        const i = parseInt(list.dataset.next || '0', 10); list.dataset.next = String(i + 1);
        const box = document.createElement('div'); box.innerHTML = tpl.innerHTML.replaceAll('__i__', String(i)).trim(); list.appendChild(box.firstElementChild);
    }));
    document.addEventListener('click', e => { const d = e.target.closest('.tx-del'); if (d) d.closest('.tx-row')?.remove(); });
})();
</script>
