@extends('layouts.app')

@section('title', $type->exists ? __('Modifier le type de visite') : __('Créer un type de visite'))
@section('page-title', ($type->exists ? __('Modifier le type de visite') : __('Créer un type de visite')).' — '.$experience->name)

@section('header-actions')
    <a href="{{ route($routes['index'], $routes['params']) }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> {{ __('Retour') }}
    </a>
@endsection

@section('content')

@php
    $input = 'w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2.5 text-sm text-slate-100 outline-none';
    $currentIcon = old('icon', $type->icon ?: 'fa-ticket');
    $tierRows = old('tiers', $type->price_tiers ?: []);
    $addonRows = old('addons', $type->addons ?: []);
    $commonLanguages = ['Français', 'English', 'Español', 'Deutsch', 'Italiano', 'Português', '中文', 'Dioula', 'Baoulé'];
    $langs = old('languages', $type->languages ?: []);
    $otherLangs = old('languages_other', collect($type->languages ?: [])->diff($commonLanguages)->join(', '));
    $section = 'rounded-xl border border-slate-800 p-4 space-y-3';
    $h = 'text-white text-sm font-semibold flex items-center gap-2';
    $hint = 'text-slate-500 text-[11px]';
@endphp

<div class="max-w-3xl">
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $type->exists ? route($routes['update'], $routes['params'] + ['visitType' => $type->id]) : route($routes['store'], $routes['params']) }}"
          class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6 space-y-5">
        @csrf
        @if($type->exists) @method('PUT') @endif

        <div>
            <label for="vt-name" class="block text-slate-400 text-xs mb-1.5">{{ __('Nom du type de visite') }} <span class="text-rose-400">*</span></label>
            <input id="vt-name" type="text" name="name" required maxlength="120" placeholder="{{ __('ex : Visite nocturne, Visite VIP, Sortie scolaire') }}"
                   value="{{ old('name', $type->name) }}" class="{{ $input }}">
        </div>

        <div>
            <label for="vt-desc" class="block text-slate-400 text-xs mb-1.5">{{ __('Courte description') }}</label>
            <input id="vt-desc" type="text" name="description" maxlength="500" placeholder="{{ __('ex : Découverte du site à la lueur des lanternes') }}"
                   value="{{ old('description', $type->description) }}" class="{{ $input }}">
            <p class="text-slate-500 text-[11px] mt-1">{{ __('Affichée sous le nom, sur la fiche du site.') }}</p>
        </div>

        <fieldset>
            <legend class="block text-slate-400 text-xs mb-1.5">{{ __('Icône') }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\TouristVisitType::ICONS as $icon => $label)
                    <label class="vt-icon cursor-pointer" title="{{ __($label) }}">
                        <input type="radio" name="icon" value="{{ $icon }}" class="sr-only peer" @checked($currentIcon === $icon)>
                        <span class="vt-icon__box">
                            <i class="fas {{ $icon }}"></i>
                        </span>
                        <span class="sr-only">{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="vt-price" class="block text-slate-400 text-xs mb-1.5">{{ __('Prix par personne (XOF)') }}</label>
                <input id="vt-price" type="number" name="price_xof" min="0" placeholder="{{ __('Laisser vide = gratuit') }}"
                       value="{{ old('price_xof', $type->price_xof) }}" class="{{ $input }}">
            </div>
            <div>
                <label for="vt-duration" class="block text-slate-400 text-xs mb-1.5">{{ __('Durée (minutes)') }}</label>
                <input id="vt-duration" type="number" name="duration_minutes" min="1" placeholder="{{ __('ex : 90') }}"
                       value="{{ old('duration_minutes', $type->duration_minutes) }}" class="{{ $input }}">
            </div>
            <div>
                <label for="vt-max" class="block text-slate-400 text-xs mb-1.5">{{ __('Participants max. par réservation') }}</label>
                <input id="vt-max" type="number" name="max_participants" min="1" max="500" placeholder="{{ __('Sans limite') }}"
                       value="{{ old('max_participants', $type->max_participants) }}" class="{{ $input }}">
            </div>
            <div>
                <label for="vt-order" class="block text-slate-400 text-xs mb-1.5">{{ __('Ordre d\'affichage') }}</label>
                <input id="vt-order" type="number" name="sort_order" min="0" max="999" placeholder="{{ __('Automatique') }}"
                       value="{{ old('sort_order', $type->exists ? $type->sort_order : '') }}" class="{{ $input }}">
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 p-4 space-y-3">
            <label class="flex items-center gap-2.5 cursor-pointer group">
                <input type="hidden" name="includes_guide" value="0">
                <input type="checkbox" name="includes_guide" value="1" id="vt-guide"
                       class="rounded border-slate-600 bg-slate-800 text-orange-500"
                       {{ old('includes_guide', $type->includes_guide) ? 'checked' : '' }}>
                <span class="text-sm text-slate-300 group-hover:text-white transition">{{ __('Visite accompagnée d\'un guide') }}</span>
            </label>
            <div id="vt-guide-fee">
                <label for="vt-fee" class="block text-slate-400 text-xs mb-1.5">{{ __('Supplément guide (forfait par réservation, XOF)') }}</label>
                <input id="vt-fee" type="number" name="guide_supplement_xof" min="0" placeholder="0"
                       value="{{ old('guide_supplement_xof', $type->guide_supplement_xof) }}" class="{{ $input }}">
            </div>
        </div>

        {{-- ── 1. Tarifs par catégorie de visiteur ── --}}
        <div class="{{ $section }}">
            <p class="{{ $h }}"><i class="fas fa-tags text-orange-400 text-xs"></i> {{ __('Tarifs par catégorie de visiteur') }}</p>
            <p class="{{ $hint }}">{{ __('ex : Adulte 3 000, Enfant (4-12 ans) 1 000, Étudiant 1 500, Résident 2 000, Moins de 4 ans 0. Sans grille, le prix par personne ci-dessus s\'applique (avec les tarifs enfant et résident de la fiche du site).') }}</p>
            <div id="vt-tiers" class="space-y-2" data-next="{{ count($tierRows) }}">
                @foreach($tierRows as $i => $row)
                    <div class="vt-row grid grid-cols-[1fr_5.5rem_auto] sm:grid-cols-[1fr_8rem_auto] gap-2">
                        <input type="text" name="tiers[{{ $i }}][label]" maxlength="60" value="{{ $row['label'] ?? '' }}" placeholder="{{ __('Catégorie') }}" class="{{ $input }}">
                        <input type="number" name="tiers[{{ $i }}][price_xof]" min="0" value="{{ $row['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $input }}">
                        <button type="button" class="vt-row__del text-slate-400 hover:text-rose-300 px-2" aria-label="{{ __('Retirer') }}"><i class="fas fa-xmark"></i></button>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-add-row="tiers" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter un tarif') }}</button>
                <button type="button" data-preset-tiers class="text-xs text-slate-300 hover:text-white border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Grille type (adulte, enfant, étudiant, résident, gratuit)') }}</button>
            </div>
        </div>

        {{-- ── 2. Créneaux horaires et places ── --}}
        <div class="{{ $section }}">
            <p class="{{ $h }}"><i class="fas fa-clock text-orange-400 text-xs"></i> {{ __('Créneaux horaires et places') }}</p>
            <div>
                <label for="vt-slots" class="block text-slate-400 text-xs mb-1.5">{{ __('Heures de départ') }}</label>
                <input id="vt-slots" type="text" name="slot_times_text" maxlength="600" placeholder="09:00, 10:30, 14:00, 15:30"
                       value="{{ old('slot_times_text', collect($type->slot_times ?: [])->join(', ')) }}" class="{{ $input }}">
                <p class="{{ $hint }} mt-1">{{ __('Laisser vide : le visiteur choisit seulement une date (entrée libre dans la journée).') }}</p>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <div><label class="block text-slate-500 text-[11px] mb-1">{{ __('De') }}</label><input type="time" id="vt-gen-from" value="09:00" class="{{ $input }} w-28"></div>
                <div><label class="block text-slate-500 text-[11px] mb-1">{{ __('À') }}</label><input type="time" id="vt-gen-to" value="17:00" class="{{ $input }} w-28"></div>
                <div><label class="block text-slate-500 text-[11px] mb-1">{{ __('Toutes les') }}</label>
                    <select id="vt-gen-every" class="{{ $input }} w-28">
                        @foreach([30 => '30 min', 60 => '1 h', 90 => '1 h 30', 120 => '2 h', 180 => '3 h'] as $m => $l)<option value="{{ $m }}" @selected($m === 60)>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <button type="button" id="vt-gen" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-2.5">{{ __('Générer les créneaux') }}</button>
            </div>
            <div>
                <label for="vt-cap" class="block text-slate-400 text-xs mb-1.5">{{ __('Places par créneau (ou par jour sans créneau)') }}</label>
                <input id="vt-cap" type="number" name="slot_capacity" min="1" max="5000" placeholder="{{ __('Sans limite') }}"
                       value="{{ old('slot_capacity', $type->slot_capacity) }}" class="{{ $input }}">
                <p class="{{ $hint }} mt-1">{{ __('Un créneau complet est grisé sur la fiche ; les places restantes sont affichées.') }}</p>
            </div>
        </div>

        {{-- ── 3. Annulation et paiement ── --}}
        <div class="{{ $section }}">
            <p class="{{ $h }}"><i class="fas fa-rotate-left text-orange-400 text-xs"></i> {{ __('Annulation et paiement') }}</p>
            <div>
                <label for="vt-cancel" class="block text-slate-400 text-xs mb-1.5">{{ __('Annulation gratuite') }}</label>
                <select id="vt-cancel" name="free_cancellation_hours" class="{{ $input }}">
                    @php $fch = old('free_cancellation_hours', $type->free_cancellation_hours); @endphp
                    <option value="" @selected($fch === null || $fch === '')>{{ __('Non remboursable') }}</option>
                    @foreach([0 => __('Jusqu\'au début de la visite'), 2 => __('Jusqu\'à 2 h avant'), 6 => __('Jusqu\'à 6 h avant'), 12 => __('Jusqu\'à 12 h avant'), 24 => __('Jusqu\'à 24 h avant (recommandé)'), 48 => __('Jusqu\'à 48 h avant'), 72 => __('Jusqu\'à 72 h avant'), 168 => __('Jusqu\'à 7 jours avant')] as $hrs => $l)
                        <option value="{{ $hrs }}" @selected($fch !== null && $fch !== '' && (int) $fch === $hrs)>{{ $l }}</option>
                    @endforeach
                </select>
                <p class="{{ $hint }} mt-1">{{ __('Le visiteur annule depuis « Mes visites » : une visite payée est remboursée sur son solde.') }}</p>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer group">
                <input type="hidden" name="allow_pay_on_site" value="0">
                <input type="checkbox" name="allow_pay_on_site" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(old('allow_pay_on_site', $type->allow_pay_on_site))>
                <span class="text-sm text-slate-300 group-hover:text-white transition">{{ __('Autoriser « Réserver maintenant, payer sur place »') }}</span>
            </label>
            <p class="{{ $hint }}">{{ __('Le billet est émis sans paiement ; vous encaissez à l\'entrée, lors du scan du QR code.') }}</p>
        </div>

        {{-- ── 4. Fiche détaillée ── --}}
        <div class="{{ $section }}">
            <p class="{{ $h }}"><i class="fas fa-circle-info text-orange-400 text-xs"></i> {{ __('Fiche détaillée') }}</p>
            <fieldset>
                <legend class="block text-slate-400 text-xs mb-1.5">{{ __('Langues de la visite') }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach($commonLanguages as $l)
                        <label class="inline-flex items-center gap-1.5 text-sm text-slate-300 border border-slate-700 rounded-lg px-2.5 py-1.5 cursor-pointer">
                            <input type="checkbox" name="languages[]" value="{{ $l }}" class="rounded border-slate-600 bg-slate-800 text-orange-500" @checked(in_array($l, $langs, true))> {{ $l }}
                        </label>
                    @endforeach
                </div>
                <input type="text" name="languages_other" maxlength="200" value="{{ $otherLangs }}" placeholder="{{ __('Autres langues, séparées par des virgules (ex : Bété, Sénoufo)') }}" class="{{ $input }} mt-2">
            </fieldset>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="vt-incl" class="block text-slate-400 text-xs mb-1.5">{{ __('Inclus (une ligne par élément)') }}</label>
                    <textarea id="vt-incl" name="included" rows="4" maxlength="1500" placeholder="{{ __("Droit d'entrée\nGuide certifié\nEau minérale") }}" class="{{ $input }}">{{ old('included', $type->included) }}</textarea>
                </div>
                <div>
                    <label for="vt-notincl" class="block text-slate-400 text-xs mb-1.5">{{ __('Non inclus') }}</label>
                    <textarea id="vt-notincl" name="not_included" rows="4" maxlength="1500" placeholder="{{ __("Transport\nPourboires") }}" class="{{ $input }}">{{ old('not_included', $type->not_included) }}</textarea>
                </div>
            </div>
            <div>
                <label for="vt-meet" class="block text-slate-400 text-xs mb-1.5">{{ __('Point de rendez-vous') }}</label>
                <input id="vt-meet" type="text" name="meeting_point" maxlength="255" value="{{ old('meeting_point', $type->meeting_point) }}" placeholder="{{ __('ex : Accueil du site, devant la billetterie') }}" class="{{ $input }}">
            </div>
            <div>
                <label for="vt-access" class="block text-slate-400 text-xs mb-1.5">{{ __('Accessibilité') }}</label>
                <input id="vt-access" type="text" name="accessibility" maxlength="255" value="{{ old('accessibility', $type->accessibility) }}" placeholder="{{ __('ex : Accessible en fauteuil roulant, poussettes acceptées') }}" class="{{ $input }}">
            </div>
            <div>
                <label for="vt-bring" class="block text-slate-400 text-xs mb-1.5">{{ __('À prévoir') }}</label>
                <textarea id="vt-bring" name="to_bring" rows="3" maxlength="1500" placeholder="{{ __("Chaussures de marche\nChapeau et crème solaire\nPièce d'identité") }}" class="{{ $input }}">{{ old('to_bring', $type->to_bring) }}</textarea>
            </div>
        </div>

        {{-- ── 5. Week-end et heures creuses ── --}}
        <div class="{{ $section }}">
            <p class="{{ $h }}"><i class="fas fa-chart-line text-orange-400 text-xs"></i> {{ __('Tarif week-end et heures creuses') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="vt-we" class="block text-slate-400 text-xs mb-1.5">{{ __('Week-end (samedi, dimanche), %') }}</label>
                    <input id="vt-we" type="number" name="weekend_adjust_percent" min="-50" max="100" value="{{ old('weekend_adjust_percent', $type->weekend_adjust_percent) }}" placeholder="{{ __('ex : 20 ou -10') }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="vt-op-until" class="block text-slate-400 text-xs mb-1.5">{{ __('Heures creuses : avant') }}</label>
                    <input id="vt-op-until" type="time" name="offpeak_until" value="{{ old('offpeak_until', $type->offpeak_until) }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="vt-op" class="block text-slate-400 text-xs mb-1.5">{{ __('Réduction heures creuses, %') }}</label>
                    <input id="vt-op" type="number" name="offpeak_discount_percent" min="1" max="90" value="{{ old('offpeak_discount_percent', $type->offpeak_discount_percent) }}" placeholder="{{ __('ex : 15') }}" class="{{ $input }}">
                </div>
            </div>
            <p class="{{ $hint }}">{{ __('S\'applique aux tarifs par personne, selon la date et le créneau choisis. La réduction heures creuses vaut pour les créneaux qui commencent avant l\'heure indiquée.') }}</p>
        </div>

        {{-- ── 6. Options payantes ── --}}
        <div class="{{ $section }}">
            <p class="{{ $h }}"><i class="fas fa-square-plus text-orange-400 text-xs"></i> {{ __('Options payantes') }}</p>
            <p class="{{ $hint }}">{{ __('ex : Audioguide 1 000 / personne, Pack photos 5 000 / réservation, Déjeuner local 4 000 / personne.') }}</p>
            <div id="vt-addons" class="space-y-2" data-next="{{ count($addonRows) }}">
                @foreach($addonRows as $i => $row)
                    <div class="vt-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2">
                        <input type="text" name="addons[{{ $i }}][label]" maxlength="80" value="{{ $row['label'] ?? '' }}" placeholder="{{ __('Option') }}" class="{{ $input }} col-span-3 sm:col-span-1">
                        <input type="number" name="addons[{{ $i }}][price_xof]" min="0" value="{{ $row['price_xof'] ?? '' }}" placeholder="XOF" class="{{ $input }}">
                        <select name="addons[{{ $i }}][per]" class="{{ $input }}">
                            <option value="person" @selected(($row['per'] ?? 'person') === 'person')>{{ __('/ personne') }}</option>
                            <option value="booking" @selected(($row['per'] ?? '') === 'booking')>{{ __('/ réservation') }}</option>
                        </select>
                        <button type="button" class="vt-row__del text-slate-400 hover:text-rose-300 px-2" aria-label="{{ __('Retirer') }}"><i class="fas fa-xmark"></i></button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-add-row="addons" class="text-xs text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-1.5"><i class="fas fa-plus mr-1"></i>{{ __('Ajouter une option') }}</button>
        </div>

        <template id="vt-tpl-tiers">
            <div class="vt-row grid grid-cols-[1fr_5.5rem_auto] sm:grid-cols-[1fr_8rem_auto] gap-2">
                <input type="text" name="tiers[__i__][label]" maxlength="60" placeholder="{{ __('Catégorie') }}" class="{{ $input }}">
                <input type="number" name="tiers[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $input }}">
                <button type="button" class="vt-row__del text-slate-400 hover:text-rose-300 px-2" aria-label="{{ __('Retirer') }}"><i class="fas fa-xmark"></i></button>
            </div>
        </template>
        <template id="vt-tpl-addons">
            <div class="vt-row grid grid-cols-[1fr_1fr_auto] sm:grid-cols-[1fr_7rem_8.5rem_auto] gap-2">
                <input type="text" name="addons[__i__][label]" maxlength="80" placeholder="{{ __('Option') }}" class="{{ $input }} col-span-3 sm:col-span-1">
                <input type="number" name="addons[__i__][price_xof]" min="0" placeholder="XOF" class="{{ $input }}">
                <select name="addons[__i__][per]" class="{{ $input }}"><option value="person">{{ __('/ personne') }}</option><option value="booking">{{ __('/ réservation') }}</option></select>
                <button type="button" class="vt-row__del text-slate-400 hover:text-rose-300 px-2" aria-label="{{ __('Retirer') }}"><i class="fas fa-xmark"></i></button>
            </div>
        </template>

        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   class="rounded border-slate-600 bg-slate-800 text-orange-500"
                   {{ old('is_active', $type->exists ? $type->is_active : true) ? 'checked' : '' }}>
            <span class="text-sm text-slate-300 group-hover:text-white transition">
                {{ __('Type actif') }} <span class="text-slate-500 text-xs">({{ __('proposé aux visiteurs') }})</span>
            </span>
        </label>

        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <i class="fas fa-floppy-disk"></i> {{ $type->exists ? __('Enregistrer') : __('Créer le type de visite') }}
        </button>
    </form>
</div>

<style>
    /* Icône choisie : orange plein (le thème admin force les fonds ardoise). */
    .vt-icon__box { width: 44px; height: 44px; border-radius: 10px; border: 1.5px solid #D8E9E0; background: #fff; color: #B85600; display: flex; align-items: center; justify-content: center; transition: background .15s ease, border-color .15s ease; }
    .vt-icon:hover .vt-icon__box { border-color: #EB6E00; }
    .vt-icon input:checked + span { background: #EB6E00 !important; border-color: #EB6E00 !important; color: #fff !important; }
    .vt-icon input:focus-visible + span { outline: 3px solid #EB6E00; outline-offset: 2px; }
</style>
<script>
(function () {
    const box = document.getElementById('vt-guide');
    const fee = document.getElementById('vt-guide-fee');
    if (!box || !fee) return;
    const sync = () => { fee.hidden = !box.checked; };
    box.addEventListener('change', sync);
    sync();
})();
(function () {
    const addRow = (kind, values) => {
        const list = document.getElementById('vt-' + kind);
        const tpl = document.getElementById('vt-tpl-' + kind);
        if (!list || !tpl) return;
        const i = parseInt(list.dataset.next || '0', 10);
        list.dataset.next = String(i + 1);
        const wrap = document.createElement('div');
        wrap.innerHTML = tpl.innerHTML.replaceAll('__i__', String(i)).trim();
        const row = wrap.firstElementChild;
        if (values) row.querySelectorAll('input').forEach((el, k) => { if (values[k] !== undefined) el.value = values[k]; });
        list.appendChild(row);
        if (!values) row.querySelector('input')?.focus();
    };
    document.querySelectorAll('[data-add-row]').forEach(b => b.addEventListener('click', () => addRow(b.dataset.addRow)));
    document.addEventListener('click', e => { const d = e.target.closest('.vt-row__del'); if (d) d.closest('.vt-row')?.remove(); });

    const preset = document.querySelector('[data-preset-tiers]');
    preset?.addEventListener('click', () => {
        const base = parseInt(document.getElementById('vt-price')?.value || '0', 10) || 0;
        const r = p => Math.round(base * p / 10000) * 100;
        [[@json(__('Adulte')), base], [@json(__('Enfant (4-12 ans)')), r(50)], [@json(__('Étudiant')), r(60)], [@json(__('Résident ivoirien')), r(70)], [@json(__('Moins de 4 ans')), 0]]
            .forEach(v => addRow('tiers', v));
    });

    // Générateur de créneaux : de … à … toutes les … minutes.
    document.getElementById('vt-gen')?.addEventListener('click', () => {
        const toMin = v => { const [h, m] = (v || '').split(':').map(Number); return h * 60 + (m || 0); };
        const from = toMin(document.getElementById('vt-gen-from').value);
        const to = toMin(document.getElementById('vt-gen-to').value);
        const every = parseInt(document.getElementById('vt-gen-every').value, 10) || 60;
        if (!(to >= from)) return;
        const out = [];
        for (let t = from; t <= to && out.length < 48; t += every) out.push(String(Math.floor(t / 60)).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0'));
        document.getElementById('vt-slots').value = out.join(', ');
    });
})();
</script>

@endsection
