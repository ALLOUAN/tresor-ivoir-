{{-- Champs « marché » d'un circuit (espace agence et administration). Paramètre : $tour. --}}
@php
    $in = 'w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100';
    $sec = 'rounded-xl border border-slate-800 p-4 space-y-3';
    $h = 'text-white text-sm font-semibold flex items-center gap-2';
    $hint = 'text-slate-500 text-[11px]';
    $days = old('itinerary', $tour->itinerary ?: []);
    $pickups = old('pickup_points', $tour->pickup_points ?: []);
    $tiers = old('group_prices', $tour->group_prices ?: []);
    $extras = old('extras', $tour->extras ?: []);
    $policy = old('cancellation_policy', $tour->cancellation_policy ?: ($tour->exists ? [] : [['days' => 30, 'percent' => 90], ['days' => 15, 'percent' => 50], ['days' => 0, 'percent' => 0]]));
    $del = '<button type="button" class="tm-del text-slate-400 hover:text-rose-300 px-2" aria-label="'.e(__('Retirer')).'"><i class="fas fa-xmark"></i></button>';
@endphp

<div>
    <label for="tm-summary" class="block text-slate-400 text-xs mb-1.5">{{ __('Accroche (affichée en haut de la page du circuit)') }}</label>
    <input id="tm-summary" type="text" name="summary" maxlength="300" value="{{ old('summary', $tour->summary) }}" placeholder="{{ __('ex : 3 jours entre plages, forêt sacrée et villages de pêcheurs') }}" class="{{ $in }}">
</div>

{{-- 1. Programme jour par jour --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-route text-orange-400 text-xs"></i> {{ __('Programme jour par jour') }}</p>
    <div id="tm-itinerary" class="space-y-3" data-next="{{ count($days) }}">
        @foreach($days as $i => $d)
            <div class="tm-row rounded-lg border border-slate-700/70 p-3 space-y-2">
                <div class="flex items-center gap-2"><span class="tm-day text-orange-300 text-xs font-bold shrink-0">{{ __('Jour') }} {{ $loop->iteration }}</span>
                    <input type="text" name="itinerary[{{ $i }}][title]" maxlength="150" value="{{ $d['title'] ?? '' }}" placeholder="{{ __('Titre de la journée') }}" class="{{ $in }}">{!! $del !!}</div>
                <textarea name="itinerary[{{ $i }}][description]" rows="2" maxlength="2000" placeholder="{{ __('Étapes, visites, temps forts…') }}" class="{{ $in }}">{{ $d['description'] ?? '' }}</textarea>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <input type="text" name="itinerary[{{ $i }}][meals]" maxlength="120" value="{{ $d['meals'] ?? '' }}" placeholder="{{ __('Repas inclus (ex : déjeuner, dîner)') }}" class="{{ $in }}">
                    <input type="text" name="itinerary[{{ $i }}][accommodation]" maxlength="150" value="{{ $d['accommodation'] ?? '' }}" placeholder="{{ __('Hébergement (ex : hôtel 3★ à Assinie)') }}" class="{{ $in }}">
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" data-tm-add="itinerary" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une journée') }}</button>
</div>

{{-- Inclus / non inclus, niveau, langues --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-list-check text-orange-400 text-xs"></i> {{ __('Ce qui est inclus') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="tm-incl" class="block text-slate-400 text-xs mb-1.5">{{ __('Inclus (une ligne par élément)') }}</label>
            <textarea id="tm-incl" name="included" rows="4" maxlength="2000" placeholder="{{ __("Transport en minibus climatisé\nHébergement 2 nuits\nGuide francophone") }}" class="{{ $in }}">{{ old('included', $tour->included) }}</textarea>
        </div>
        <div>
            <label for="tm-excl" class="block text-slate-400 text-xs mb-1.5">{{ __('Non inclus') }}</label>
            <textarea id="tm-excl" name="not_included" rows="4" maxlength="2000" placeholder="{{ __("Boissons\nPourboires\nAssurance voyage") }}" class="{{ $in }}">{{ old('not_included', $tour->not_included) }}</textarea>
        </div>
        <div>
            <label for="tm-diff" class="block text-slate-400 text-xs mb-1.5">{{ __('Niveau') }}</label>
            <select id="tm-diff" name="difficulty" class="{{ $in }}">
                <option value="">—</option>
                @foreach(\App\Models\TourPackage::DIFFICULTIES as $k => $l)<option value="{{ $k }}" @selected(old('difficulty', $tour->difficulty) === $k)>{{ __($l) }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="tm-lang" class="block text-slate-400 text-xs mb-1.5">{{ __('Langues du guide') }}</label>
            <input id="tm-lang" type="text" name="languages_text" maxlength="200" value="{{ old('languages_text', collect($tour->languages ?: [])->join(', ')) }}" placeholder="Français, English" class="{{ $in }}">
        </div>
    </div>
</div>

{{-- 5. Prise en charge --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-van-shuttle text-orange-400 text-xs"></i> {{ __('Lieux de prise en charge') }}</p>
    <p class="{{ $hint }}">{{ __('Le voyageur choisit le sien en réservant ; il est rappelé sur sa feuille de route.') }}</p>
    <div id="tm-pickup_points" class="space-y-2" data-next="{{ count($pickups) }}">
        @foreach($pickups as $i => $p)
            <div class="tm-row grid grid-cols-[1fr_5.5rem_auto] sm:grid-cols-[1fr_6rem_1fr_auto] gap-2">
                <input type="text" name="pickup_points[{{ $i }}][label]" maxlength="150" value="{{ $p['label'] ?? '' }}" placeholder="{{ __('Lieu') }}" class="{{ $in }}">
                <input type="time" name="pickup_points[{{ $i }}][time]" value="{{ $p['time'] ?? '' }}" class="{{ $in }}">
                <input type="text" name="pickup_points[{{ $i }}][note]" maxlength="200" value="{{ $p['note'] ?? '' }}" placeholder="{{ __('Repère (ex : devant la pharmacie)') }}" class="{{ $in }} col-span-2 sm:col-span-1 order-last sm:order-none">
                {!! $del !!}
            </div>
        @endforeach
    </div>
    <button type="button" data-tm-add="pickup_points" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter un lieu') }}</button>
</div>

{{-- 4. Tarifs --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-tags text-orange-400 text-xs"></i> {{ __('Tarifs') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label for="tm-child" class="block text-slate-400 text-xs mb-1.5">{{ __('Prix enfant (XOF)') }}</label>
            <input id="tm-child" type="number" name="child_price_xof" min="0" value="{{ old('child_price_xof', $tour->child_price_xof) }}" placeholder="{{ __('Vide = tarif adulte') }}" class="{{ $in }}">
        </div>
        <div>
            <label for="tm-childage" class="block text-slate-400 text-xs mb-1.5">{{ __('Enfant jusqu\'à (ans)') }}</label>
            <input id="tm-childage" type="number" name="child_age_max" min="1" max="17" value="{{ old('child_age_max', $tour->child_age_max) }}" placeholder="11" class="{{ $in }}">
        </div>
        <div>
            <label for="tm-single" class="block text-slate-400 text-xs mb-1.5">{{ __('Supplément chambre individuelle') }}</label>
            <input id="tm-single" type="number" name="single_supplement_xof" min="0" value="{{ old('single_supplement_xof', $tour->single_supplement_xof) }}" placeholder="{{ __('Vide = aucun') }}" class="{{ $in }}">
        </div>
    </div>
    <div>
        <p class="block text-slate-400 text-xs mb-1.5">{{ __('Tarif dégressif selon la taille du groupe (circuit privatif)') }}</p>
        <p class="{{ $hint }} mb-2">{{ __('ex : à partir de 2 voyageurs 85 000 / pers., à partir de 4 voyageurs 65 000 / pers. Le prix d\'un départ, s\'il est fixé, reste prioritaire.') }}</p>
        <div id="tm-group_prices" class="space-y-2" data-next="{{ count($tiers) }}">
            @foreach($tiers as $i => $g)
                <div class="tm-row grid grid-cols-[1fr_1fr_auto] gap-2">
                    <input type="number" name="group_prices[{{ $i }}][min]" min="1" value="{{ $g['min'] ?? '' }}" placeholder="{{ __('À partir de … voyageurs') }}" class="{{ $in }}">
                    <input type="number" name="group_prices[{{ $i }}][price_xof]" min="0" value="{{ $g['price_xof'] ?? '' }}" placeholder="{{ __('XOF / pers.') }}" class="{{ $in }}">
                    {!! $del !!}
                </div>
            @endforeach
        </div>
        <button type="button" data-tm-add="group_prices" class="mt-2 text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter un palier') }}</button>
    </div>
    <div>
        <p class="block text-slate-400 text-xs mb-1.5">{{ __('Options facultatives') }}</p>
        <div id="tm-extras" class="space-y-2" data-next="{{ count($extras) }}">
            @foreach($extras as $i => $e)
                <div class="tm-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2">
                    <input type="text" name="extras[{{ $i }}][label]" maxlength="100" value="{{ $e['label'] ?? '' }}" placeholder="{{ __('Option') }}" class="{{ $in }} col-span-3 sm:col-span-1">
                    <input type="number" name="extras[{{ $i }}][price_xof]" min="0" value="{{ $e['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $in }}">
                    <select name="extras[{{ $i }}][per]" class="{{ $in }}"><option value="person" @selected(($e['per'] ?? 'person') === 'person')>{{ __('/ personne') }}</option><option value="booking" @selected(($e['per'] ?? '') === 'booking')>{{ __('/ réservation') }}</option></select>
                    {!! $del !!}
                </div>
            @endforeach
        </div>
        <button type="button" data-tm-add="extras" class="mt-2 text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une option') }}</button>
    </div>
</div>

{{-- 2, 3, 6. Réservation, acompte, minimum --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-credit-card text-orange-400 text-xs"></i> {{ __('Réservation et paiement') }}</p>
    <label class="flex items-center gap-2.5 cursor-pointer group">
        <input type="hidden" name="instant_booking" value="0">
        <input type="checkbox" name="instant_booking" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('instant_booking', $tour->exists ? $tour->instant_booking : true))>
        <span class="text-sm text-slate-300 group-hover:text-white transition">{{ __('Réservation immédiate sur les départs programmés') }}</span>
    </label>
    <p class="{{ $hint }}">{{ __('Le voyageur réserve et paie en ligne, sans attendre votre réponse, tant qu\'il reste des places. Décoché : il vous envoie une demande.') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="tm-dep" class="block text-slate-400 text-xs mb-1.5">{{ __('Acompte à la réservation (%)') }}</label>
            <select id="tm-dep" name="deposit_percent" class="{{ $in }}">
                @php $dp = old('deposit_percent', $tour->deposit_percent); @endphp
                <option value="">{{ __('Aucun : paiement intégral') }}</option>
                @foreach([20, 25, 30, 40, 50] as $p)<option value="{{ $p }}" @selected((int) $dp === $p)>{{ $p }} %</option>@endforeach
            </select>
        </div>
        <div>
            <label for="tm-bal" class="block text-slate-400 text-xs mb-1.5">{{ __('Solde à régler … jours avant le départ') }}</label>
            <input id="tm-bal" type="number" name="balance_due_days" min="1" max="120" value="{{ old('balance_due_days', $tour->balance_due_days ?? 15) }}" class="{{ $in }}">
        </div>
        <div>
            <label for="tm-min" class="block text-slate-400 text-xs mb-1.5">{{ __('Minimum de voyageurs pour garantir un départ') }}</label>
            <input id="tm-min" type="number" name="min_participants" min="1" max="500" value="{{ old('min_participants', $tour->min_participants) }}" placeholder="{{ __('Vide = départ toujours garanti') }}" class="{{ $in }}">
        </div>
        <div>
            <label for="tm-gd" class="block text-slate-400 text-xs mb-1.5">{{ __('Décision … jours avant le départ') }}</label>
            <input id="tm-gd" type="number" name="guarantee_days" min="1" max="60" value="{{ old('guarantee_days', $tour->guarantee_days ?? 7) }}" class="{{ $in }}">
        </div>
    </div>
    <p class="{{ $hint }}">{{ __('Une réservation faite après l\'échéance du solde est payée en totalité. Solde non réglé 2 jours après l\'échéance : réservation annulée selon le barème. Minimum non atteint à la date de décision : départ annulé et voyageurs remboursés à 100 % sur leur solde.') }}</p>
</div>

{{-- 7. Annulation --}}
<div class="{{ $sec }}">
    <p class="{{ $h }}"><i class="fas fa-rotate-left text-orange-400 text-xs"></i> {{ __('Conditions d\'annulation') }}</p>
    <p class="{{ $hint }}">{{ __('« Plus de X jours avant le départ : Y % du montant versé remboursé sur le solde du voyageur. » Sans barème : non remboursable (sauf départ annulé).') }}</p>
    <div id="tm-cancellation_policy" class="space-y-2" data-next="{{ count($policy) }}">
        @foreach($policy as $i => $r)
            <div class="tm-row grid grid-cols-[1fr_1fr_auto] gap-2 items-center">
                <label class="text-xs text-slate-400">{{ __('Jusqu\'à … jours avant') }}<input type="number" name="cancellation_policy[{{ $i }}][days]" min="0" max="365" value="{{ $r['days'] ?? '' }}" class="{{ $in }} mt-1"></label>
                <label class="text-xs text-slate-400">{{ __('% remboursé') }}<input type="number" name="cancellation_policy[{{ $i }}][percent]" min="0" max="100" value="{{ $r['percent'] ?? '' }}" class="{{ $in }} mt-1"></label>
                {!! $del !!}
            </div>
        @endforeach
    </div>
    <button type="button" data-tm-add="cancellation_policy" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une ligne') }}</button>
</div>

<template id="tm-tpl-itinerary">
    <div class="tm-row rounded-lg border border-slate-700/70 p-3 space-y-2">
        <div class="flex items-center gap-2"><span class="tm-day text-orange-300 text-xs font-bold shrink-0">{{ __('Jour') }}</span>
            <input type="text" name="itinerary[__i__][title]" maxlength="150" placeholder="{{ __('Titre de la journée') }}" class="{{ $in }}">{!! $del !!}</div>
        <textarea name="itinerary[__i__][description]" rows="2" maxlength="2000" placeholder="{{ __('Étapes, visites, temps forts…') }}" class="{{ $in }}"></textarea>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <input type="text" name="itinerary[__i__][meals]" maxlength="120" placeholder="{{ __('Repas inclus (ex : déjeuner, dîner)') }}" class="{{ $in }}">
            <input type="text" name="itinerary[__i__][accommodation]" maxlength="150" placeholder="{{ __('Hébergement (ex : hôtel 3★ à Assinie)') }}" class="{{ $in }}">
        </div>
    </div>
</template>
<template id="tm-tpl-pickup_points">
    <div class="tm-row grid grid-cols-[1fr_5.5rem_auto] sm:grid-cols-[1fr_6rem_1fr_auto] gap-2">
        <input type="text" name="pickup_points[__i__][label]" maxlength="150" placeholder="{{ __('Lieu') }}" class="{{ $in }}">
        <input type="time" name="pickup_points[__i__][time]" class="{{ $in }}">
        <input type="text" name="pickup_points[__i__][note]" maxlength="200" placeholder="{{ __('Repère (ex : devant la pharmacie)') }}" class="{{ $in }} col-span-2 sm:col-span-1 order-last sm:order-none">
        {!! $del !!}
    </div>
</template>
<template id="tm-tpl-group_prices">
    <div class="tm-row grid grid-cols-[1fr_1fr_auto] gap-2">
        <input type="number" name="group_prices[__i__][min]" min="1" placeholder="{{ __('À partir de … voyageurs') }}" class="{{ $in }}">
        <input type="number" name="group_prices[__i__][price_xof]" min="0" placeholder="{{ __('XOF / pers.') }}" class="{{ $in }}">
        {!! $del !!}
    </div>
</template>
<template id="tm-tpl-extras">
    <div class="tm-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2">
        <input type="text" name="extras[__i__][label]" maxlength="100" placeholder="{{ __('Option') }}" class="{{ $in }} col-span-3 sm:col-span-1">
        <input type="number" name="extras[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $in }}">
        <select name="extras[__i__][per]" class="{{ $in }}"><option value="person">{{ __('/ personne') }}</option><option value="booking">{{ __('/ réservation') }}</option></select>
        {!! $del !!}
    </div>
</template>
<template id="tm-tpl-cancellation_policy">
    <div class="tm-row grid grid-cols-[1fr_1fr_auto] gap-2 items-center">
        <label class="text-xs text-slate-400">{{ __('Jusqu\'à … jours avant') }}<input type="number" name="cancellation_policy[__i__][days]" min="0" max="365" class="{{ $in }} mt-1"></label>
        <label class="text-xs text-slate-400">{{ __('% remboursé') }}<input type="number" name="cancellation_policy[__i__][percent]" min="0" max="100" class="{{ $in }} mt-1"></label>
        {!! $del !!}
    </div>
</template>
<script>
(function () {
    const dayLabel = @json(__('Jour'));
    const renumber = () => document.querySelectorAll('#tm-itinerary .tm-day').forEach((el, i) => { el.textContent = dayLabel + ' ' + (i + 1); });
    document.querySelectorAll('[data-tm-add]').forEach(btn => btn.addEventListener('click', () => {
        const kind = btn.dataset.tmAdd, list = document.getElementById('tm-' + kind), tpl = document.getElementById('tm-tpl-' + kind);
        const i = parseInt(list.dataset.next || '0', 10); list.dataset.next = String(i + 1);
        const box = document.createElement('div'); box.innerHTML = tpl.innerHTML.replaceAll('__i__', String(i)).trim();
        const row = box.firstElementChild; list.appendChild(row); row.querySelector('input')?.focus(); renumber();
    }));
    document.addEventListener('click', e => { const d = e.target.closest('.tm-del'); if (d) { d.closest('.tm-row')?.remove(); renumber(); } });
})();
</script>
