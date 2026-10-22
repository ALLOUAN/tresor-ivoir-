{{-- Champs « marché » d'une activité (espace prestataire et administration). Paramètre : $activity. --}}
@php
    $in = 'w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100';
    $sec = 'rounded-xl border border-slate-800 p-4 space-y-3';
    $h = 'text-white text-sm font-semibold flex items-center gap-2';
    $hint = 'text-slate-500 text-[11px]';
    $tiers = old('tiers', $activity->price_tiers ?: []);
    $addons = old('addons', $activity->addons ?: []);
    $days = old('weekdays', $activity->weekdays ?: [1, 2, 3, 4, 5, 6, 7]);
    $del = '<button type="button" class="am-del text-slate-400 hover:text-rose-300 px-2" aria-label="'.e(__('Retirer')).'"><i class="fas fa-xmark"></i></button>';
@endphp

{{-- 1. Créneaux --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-clock text-orange-400 text-xs"></i> {{ __('Créneaux et réservation en ligne') }}</p>
    <label class="flex items-center gap-2.5 cursor-pointer">
        <input type="hidden" name="instant_booking" value="0">
        <input type="checkbox" name="instant_booking" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('instant_booking', $activity->exists ? $activity->instant_booking : true))>
        <span class="text-sm text-slate-300">{{ __('Réservation et paiement immédiats en ligne (billet à QR code)') }}</span>
    </label>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
            <label for="am-slots" class="block text-slate-400 text-xs mb-1.5">{{ __('Heures de début') }}</label>
            <input id="am-slots" type="text" name="slot_times_text" maxlength="600" value="{{ old('slot_times_text', collect($activity->slot_times ?: [])->join(', ')) }}" placeholder="09:00, 11:00, 15:00, 17:00" class="{{ $in }}">
        </div>
        <div>
            <label for="am-cap" class="block text-slate-400 text-xs mb-1.5">{{ __('Places par créneau') }}</label>
            <input id="am-cap" type="number" name="slot_capacity" min="1" max="5000" value="{{ old('slot_capacity', $activity->slot_capacity) }}" placeholder="{{ __('Sans limite') }}" class="{{ $in }}">
        </div>
    </div>
    <fieldset>
        <legend class="block text-slate-400 text-xs mb-1.5">{{ __('Jours d\'ouverture') }}</legend>
        <div class="flex flex-wrap gap-1.5">
            @foreach([1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 7 => 'Dim'] as $n => $l)
                <label class="inline-flex items-center gap-1.5 text-xs text-slate-300 border border-slate-700 rounded-lg px-2.5 py-1.5 cursor-pointer">
                    <input type="checkbox" name="weekdays[]" value="{{ $n }}" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(in_array($n, array_map('intval', $days)))> {{ __($l) }}
                </label>
            @endforeach
        </div>
    </fieldset>
    <p class="{{ $hint }}">{{ __('Sans heure de début, l\'activité reste « sur demande ». Les ressources partagées (salle, matériel) se règlent dans « Ressources ».') }}</p>
</div>

{{-- 4. Tarifs et options --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-tags text-orange-400 text-xs"></i> {{ __('Tarifs par catégorie et options') }}</p>
    <p class="{{ $hint }}">{{ __('ex : Adulte 5 000, Enfant 3 000, Étudiant 4 000. Sans grille, le prix ci-dessus s\'applique à chaque participant.') }}</p>
    <div id="am-tiers" class="space-y-2" data-next="{{ count($tiers) }}">
        @foreach($tiers as $i => $t)
            <div class="am-row grid grid-cols-[1fr_6rem_auto] sm:grid-cols-[1fr_8rem_auto] gap-2">
                <input type="text" name="tiers[{{ $i }}][label]" maxlength="60" value="{{ $t['label'] ?? '' }}" placeholder="{{ __('Catégorie') }}" class="{{ $in }}">
                <input type="number" name="tiers[{{ $i }}][price_xof]" min="0" value="{{ $t['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $in }}">{!! $del !!}
            </div>
        @endforeach
    </div>
    <button type="button" data-am-add="tiers" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter un tarif') }}</button>
    <div id="am-addons" class="space-y-2" data-next="{{ count($addons) }}">
        @foreach($addons as $i => $a)
            <div class="am-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2">
                <input type="text" name="addons[{{ $i }}][label]" maxlength="80" value="{{ $a['label'] ?? '' }}" placeholder="{{ __('Option (location de matériel, photos…)') }}" class="{{ $in }} col-span-3 sm:col-span-1">
                <input type="number" name="addons[{{ $i }}][price_xof]" min="0" value="{{ $a['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $in }}">
                <select name="addons[{{ $i }}][per]" class="{{ $in }}"><option value="person" @selected(($a['per'] ?? 'person') === 'person')>{{ __('/ personne') }}</option><option value="booking" @selected(($a['per'] ?? '') === 'booking')>{{ __('/ réservation') }}</option></select>{!! $del !!}
            </div>
        @endforeach
    </div>
    <button type="button" data-am-add="addons" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une option') }}</button>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div><label for="am-we" class="block text-slate-400 text-xs mb-1.5">{{ __('Week-end, %') }}</label><input id="am-we" type="number" name="weekend_adjust_percent" min="-50" max="100" value="{{ old('weekend_adjust_percent', $activity->weekend_adjust_percent) }}" placeholder="{{ __('ex : 20') }}" class="{{ $in }}"></div>
        <div><label for="am-opu" class="block text-slate-400 text-xs mb-1.5">{{ __('Heures creuses : avant') }}</label><input id="am-opu" type="time" name="offpeak_until" value="{{ old('offpeak_until', $activity->offpeak_until) }}" class="{{ $in }}"></div>
        <div><label for="am-op" class="block text-slate-400 text-xs mb-1.5">{{ __('Réduction heures creuses, %') }}</label><input id="am-op" type="number" name="offpeak_discount_percent" min="1" max="90" value="{{ old('offpeak_discount_percent', $activity->offpeak_discount_percent) }}" placeholder="{{ __('ex : 15') }}" class="{{ $in }}"></div>
    </div>
</div>

{{-- 8. Annulation --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-rotate-left text-orange-400 text-xs"></i> {{ __('Annulation et report') }}</p>
    @php $fch = old('free_cancellation_hours', $activity->free_cancellation_hours); @endphp
    <select name="free_cancellation_hours" class="{{ $in }}" aria-label="{{ __('Annulation gratuite') }}">
        <option value="" @selected($fch === null || $fch === '')>{{ __('Non remboursable, pas de report') }}</option>
        @foreach([0 => __('Jusqu\'au début de l\'activité'), 2 => __('Jusqu\'à 2 h avant'), 6 => __('Jusqu\'à 6 h avant'), 12 => __('Jusqu\'à 12 h avant'), 24 => __('Jusqu\'à 24 h avant (recommandé)'), 48 => __('Jusqu\'à 48 h avant'), 72 => __('Jusqu\'à 72 h avant')] as $hrs => $l)
            <option value="{{ $hrs }}" @selected($fch !== null && $fch !== '' && (int) $fch === $hrs)>{{ $l }}</option>
        @endforeach
    </select>
    <p class="{{ $hint }}">{{ __('Dans ce délai, le client annule (remboursé sur son solde) ou reporte sur un autre créneau libre, 2 fois maximum. La place libérée est proposée à la liste d\'attente.') }}</p>
</div>

{{-- 5. Conditions et décharge --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-shield-halved text-orange-400 text-xs"></i> {{ __('Conditions de participation et décharge') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div><label for="am-age" class="block text-slate-400 text-xs mb-1.5">{{ __('Âge minimum') }}</label><input id="am-age" type="number" name="min_age" min="1" max="99" value="{{ old('min_age', $activity->min_age) }}" class="{{ $in }}"></div>
        <div><label for="am-hgt" class="block text-slate-400 text-xs mb-1.5">{{ __('Taille minimum (cm)') }}</label><input id="am-hgt" type="number" name="min_height_cm" min="50" max="220" value="{{ old('min_height_cm', $activity->min_height_cm) }}" class="{{ $in }}"></div>
        <div><label for="am-lvl" class="block text-slate-400 text-xs mb-1.5">{{ __('Niveau') }}</label>
            <select id="am-lvl" name="level" class="{{ $in }}"><option value="">—</option>@foreach(\App\Models\Activity::LEVELS as $k => $l)<option value="{{ $k }}" @selected(old('level', $activity->level) === $k)>{{ __($l) }}</option>@endforeach</select></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div><label for="am-req" class="block text-slate-400 text-xs mb-1.5">{{ __('Conditions et contre-indications (une par ligne)') }}</label><textarea id="am-req" name="requirements" rows="3" maxlength="2000" placeholder="{{ __("Savoir nager\nDéconseillé aux femmes enceintes") }}" class="{{ $in }}">{{ old('requirements', $activity->requirements) }}</textarea></div>
        <div><label for="am-bring" class="block text-slate-400 text-xs mb-1.5">{{ __('À prévoir') }}</label><textarea id="am-bring" name="to_bring" rows="3" maxlength="2000" placeholder="{{ __("Maillot de bain\nChaussures fermées") }}" class="{{ $in }}">{{ old('to_bring', $activity->to_bring) }}</textarea></div>
    </div>
    <label class="flex items-center gap-2.5 cursor-pointer">
        <input type="hidden" name="requires_waiver" value="0">
        <input type="checkbox" name="requires_waiver" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('requires_waiver', $activity->requires_waiver))>
        <span class="text-sm text-slate-300">{{ __('Décharge de responsabilité obligatoire (nom de chaque participant + acceptation en ligne)') }}</span>
    </label>
    <textarea name="waiver_text" rows="4" maxlength="5000" placeholder="{{ __('Je reconnais avoir été informé(e) des risques liés à l\'activité…') }}" class="{{ $in }}" aria-label="{{ __('Texte de la décharge') }}">{{ old('waiver_text', $activity->waiver_text) }}</textarea>
</div>

<template id="am-tpl-tiers"><div class="am-row grid grid-cols-[1fr_6rem_auto] sm:grid-cols-[1fr_8rem_auto] gap-2"><input type="text" name="tiers[__i__][label]" maxlength="60" placeholder="{{ __('Catégorie') }}" class="{{ $in }}"><input type="number" name="tiers[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $in }}">{!! $del !!}</div></template>
<template id="am-tpl-addons"><div class="am-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2"><input type="text" name="addons[__i__][label]" maxlength="80" placeholder="{{ __('Option (location de matériel, photos…)') }}" class="{{ $in }} col-span-3 sm:col-span-1"><input type="number" name="addons[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $in }}"><select name="addons[__i__][per]" class="{{ $in }}"><option value="person">{{ __('/ personne') }}</option><option value="booking">{{ __('/ réservation') }}</option></select>{!! $del !!}</div></template>
<script>
(function () {
    document.querySelectorAll('[data-am-add]').forEach(b => b.addEventListener('click', () => {
        const k = b.dataset.amAdd, list = document.getElementById('am-' + k), tpl = document.getElementById('am-tpl-' + k);
        const i = parseInt(list.dataset.next || '0', 10); list.dataset.next = String(i + 1);
        const box = document.createElement('div'); box.innerHTML = tpl.innerHTML.replaceAll('__i__', String(i)).trim();
        list.appendChild(box.firstElementChild); list.lastElementChild.querySelector('input')?.focus();
    }));
    document.addEventListener('click', e => { const d = e.target.closest('.am-del'); if (d) d.closest('.am-row')?.remove(); });
})();
</script>
