{{-- Champs « marché » d'une œuvre (espace artiste). Paramètre : $artwork. --}}
@php
    $in = 'w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100';
    $sec = 'rounded-xl border border-slate-800 p-4 space-y-3';
    $h = 'text-white text-sm font-semibold flex items-center gap-2';
    $hint = 'text-slate-500 text-[11px]';
    $eds = old('editions', $artwork->exists ? $artwork->editions()->where('is_active', true)->get()->map(fn ($e) => $e->only(['id', 'label', 'width_cm', 'height_cm', 'price_xof', 'edition_size']) + ['sold' => $e->sold_count])->all() : []);
    $del = '<button type="button" class="ae-del text-slate-400 hover:text-rose-300 px-2" aria-label="Retirer"><i class="fas fa-xmark"></i></button>';
@endphp

<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-ruler-combined text-orange-400 text-xs"></i> Dimensions exactes (pour « Voir sur mon mur »)</p>
    <div class="grid grid-cols-3 gap-3">
        <label class="text-xs text-slate-400">Largeur (cm)<input type="number" step="0.1" name="width_cm" min="1" value="{{ old('width_cm', $artwork->width_cm) }}" class="{{ $in }} mt-1"></label>
        <label class="text-xs text-slate-400">Hauteur (cm)<input type="number" step="0.1" name="height_cm" min="1" value="{{ old('height_cm', $artwork->height_cm) }}" class="{{ $in }} mt-1"></label>
        <label class="text-xs text-slate-400">Profondeur (cm)<input type="number" step="0.1" name="depth_cm" min="0" value="{{ old('depth_cm', $artwork->depth_cm) }}" class="{{ $in }} mt-1"></label>
    </div>
    <p class="{{ $hint }}">Au-delà de 100 cm, le supplément « grand format » de votre page artiste s'ajoute à la livraison.</p>
</div>

<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-handshake text-orange-400 text-xs"></i> Offres et paiement en plusieurs fois</p>
    <label class="flex items-center gap-2.5 cursor-pointer"><input type="hidden" name="accepts_offers" value="0"><input type="checkbox" name="accepts_offers" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('accepts_offers', $artwork->accepts_offers))><span class="text-sm text-slate-300">Accepter les offres (« Faire une offre »)</span></label>
    <label class="text-xs text-slate-400 block">Prix plancher privé (XOF) — en dessous, l'offre est refusée automatiquement
        <input type="number" name="min_offer_xof" min="0" value="{{ old('min_offer_xof', $artwork->min_offer_xof) }}" placeholder="Facultatif" class="{{ $in }} mt-1"></label>
    <label class="flex items-center gap-2.5 cursor-pointer"><input type="hidden" name="layaway_allowed" value="0"><input type="checkbox" name="layaway_allowed" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('layaway_allowed', $artwork->layaway_allowed))><span class="text-sm text-slate-300">Proposer la réservation avec paiement en 3 fois</span></label>
    <p class="{{ $hint }}">Acompte de 30 % qui réserve l'original, puis deux échéances à 30 et 60 jours. Vous remettez l'œuvre une fois soldée. En cas d'impayé, l'acompte vous revient en dédommagement.</p>
</div>

<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-layer-group text-orange-400 text-xs"></i> Éditions limitées (tirages numérotés)</p>
    <p class="{{ $hint }}">ex : « Tirage d'art A3 », 30 × 42 cm, 45 000 FCFA, 25 exemplaires. Chaque exemplaire vendu reçoit son numéro (7/25) et son certificat.</p>
    <div id="ae-list" class="space-y-2" data-next="{{ count($eds) }}">
        @foreach($eds as $i => $e)
            <div class="ae-row grid grid-cols-2 sm:grid-cols-[1fr_5rem_5rem_7rem_5rem_auto] gap-2 items-center">
                <input type="hidden" name="editions[{{ $i }}][id]" value="{{ $e['id'] ?? '' }}">
                <input name="editions[{{ $i }}][label]" maxlength="120" value="{{ $e['label'] ?? '' }}" placeholder="Format" class="{{ $in }} col-span-2 sm:col-span-1">
                <input type="number" step="0.1" name="editions[{{ $i }}][width_cm]" value="{{ $e['width_cm'] ?? '' }}" placeholder="L cm" class="{{ $in }}">
                <input type="number" step="0.1" name="editions[{{ $i }}][height_cm]" value="{{ $e['height_cm'] ?? '' }}" placeholder="H cm" class="{{ $in }}">
                <input type="number" name="editions[{{ $i }}][price_xof]" min="1000" value="{{ $e['price_xof'] ?? '' }}" placeholder="Prix XOF" class="{{ $in }}">
                <input type="number" name="editions[{{ $i }}][edition_size]" min="1" value="{{ $e['edition_size'] ?? '' }}" placeholder="Tirage" class="{{ $in }}" title="{{ isset($e['sold']) ? $e['sold'].' vendu(s)' : '' }}">
                {!! $del !!}
            </div>
        @endforeach
    </div>
    <button type="button" id="ae-add" class="text-xs text-orange-300 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>Ajouter une édition</button>
</div>
<template id="ae-tpl"><div class="ae-row grid grid-cols-2 sm:grid-cols-[1fr_5rem_5rem_7rem_5rem_auto] gap-2 items-center"><input type="hidden" name="editions[__i__][id]"><input name="editions[__i__][label]" maxlength="120" placeholder="Format" class="{{ $in }} col-span-2 sm:col-span-1"><input type="number" step="0.1" name="editions[__i__][width_cm]" placeholder="L cm" class="{{ $in }}"><input type="number" step="0.1" name="editions[__i__][height_cm]" placeholder="H cm" class="{{ $in }}"><input type="number" name="editions[__i__][price_xof]" min="1000" placeholder="Prix XOF" class="{{ $in }}"><input type="number" name="editions[__i__][edition_size]" min="1" placeholder="Tirage" class="{{ $in }}">{!! $del !!}</div></template>
<script>
(function () {
    const list = document.getElementById('ae-list'), tpl = document.getElementById('ae-tpl');
    document.getElementById('ae-add').addEventListener('click', () => { const i = parseInt(list.dataset.next || '0', 10); list.dataset.next = String(i + 1); const b = document.createElement('div'); b.innerHTML = tpl.innerHTML.replaceAll('__i__', String(i)).trim(); list.appendChild(b.firstElementChild); });
    document.addEventListener('click', e => { const d = e.target.closest('.ae-del'); if (d) d.closest('.ae-row')?.remove(); });
})();
</script>
