{{--
    Carte « Réserver » des fiches restaurant / loisir / agence (demande sans paiement).
    Paramètres : type (restaurant|leisure|tour|bundle|transport), target (modèle avec id, name, provider_id),
    tours (collection, type tour), offers (collection, type transport).
--}}
@php
    $brType = $type;
    $brUnit = \App\Models\BookingRequest::TYPES[$brType]['unit'] ?? 'personnes';
    $brTitle = match ($brType) {
        'restaurant' => __('Réserver une table'),
        'leisure' => __('Réserver une activité'),
        'bundle' => __('Réserver cette offre'),
        'transport' => __('Réserver un transport'),
        'art' => __('Commander une œuvre sur mesure'),
        default => __('Réserver un circuit'),
    };
    $brTours = collect($tours ?? []);
    $brOffers = collect($offers ?? []);
    $brActivities = collect($activities ?? []);
    $brUser = auth()->user();
    $brErrors = $errors->getBag('booking');
    $brProviderId = $brType === 'art' ? $target->id : $target->provider_id;
    $brHasPromo = $brProviderId && \App\Models\ProviderPromoCode::where('provider_id', $brProviderId)->where('is_active', true)->exists();
    // Restaurant : réserver une table OU commander des plats (à emporter / en livraison).
    $brDishes = $brType === 'restaurant' && $target->provider_id && $target->accepts_orders
        ? \App\Models\MenuItem::query()->available()->where('provider_id', $target->provider_id)->with('category')->get()
            ->sortBy([fn ($a, $b) => ($a->category?->sort_order ?? 999) <=> ($b->category?->sort_order ?? 999), fn ($a, $b) => strcasecmp($a->name, $b->name)])->values()
        : collect();
    $brCanOrder = $brDishes->isNotEmpty();
    $brTab = $brCanOrder && (old('bookable_type') === 'restaurant_order' || request()->has('commander')) ? 'order' : 'table';
    if ($brCanOrder) {
        $brTitle = __('Réserver ou commander');
    }
@endphp
@if(($brType === 'art' || $target->provider_id) && ($brType !== 'tour' || $brTours->isNotEmpty()) && ($brType !== 'transport' || $brOffers->isNotEmpty()))
@once
<style>.contact-card { border: 1px solid rgba(14,59,42,0.08); background: #FFFFFF; box-shadow: 0 20px 50px rgba(14,59,42,0.1); }</style>
@endonce
<div class="contact-card rounded-3xl p-5" id="reserver">
    <h2 class="font-serif text-xl font-bold mb-1">{{ $brTitle }}</h2>
    <p class="text-[#557466] text-xs mb-4">{{ __('Sans engagement : l\'établissement confirme votre demande par e-mail, puis vous réglez en ligne ou sur place.') }}</p>
    @if($brCanOrder && auth()->check())
        <div class="br-tabs" role="tablist" aria-label="{{ __('Réserver ou commander') }}">
            <button type="button" role="tab" data-br-tab="table" aria-selected="{{ $brTab === 'table' ? 'true' : 'false' }}" class="{{ $brTab === 'table' ? 'is-active' : '' }}"><i class="fas fa-chair" aria-hidden="true"></i>{{ __('Réserver une table') }}</button>
            <button type="button" role="tab" data-br-tab="order" aria-selected="{{ $brTab === 'order' ? 'true' : 'false' }}" class="{{ $brTab === 'order' ? 'is-active' : '' }}"><i class="fas fa-basket-shopping" aria-hidden="true"></i>{{ __('Commander') }}</button>
        </div>
    @endif

    @if(session('booking_success'))
        <div class="mb-3 rounded-xl bg-[#00874F]/10 text-[#00874F] text-sm font-semibold px-3.5 py-3">
            <i class="fas fa-circle-check mr-1"></i> {{ session('booking_success') }}
        </div>
    @endif
    @if($brErrors->any())
        <div class="mb-3 rounded-xl bg-red-50 text-red-700 text-sm px-3.5 py-3">{{ $brErrors->first() }}</div>
    @endif

    @auth
        <div data-br-panel="table" @if($brTab !== 'table') hidden @endif>
        <form method="POST" action="{{ route('booking-requests.store') }}" class="space-y-2.5">
            @csrf
            <input type="hidden" name="bookable_type" value="{{ $brType }}">
            <input type="hidden" name="bookable_id" value="{{ $target->id }}">
            @if($brType === 'tour')
                <label class="flex items-center gap-2 text-xs font-semibold text-[#24503D] cursor-pointer">
                    <input type="checkbox" name="custom_quote" value="1" class="br-custom" @checked(old('custom_quote'))>
                    {{ __('Je veux un voyage sur mesure (devis personnalisé)') }}
                </label>
                <label class="br-tour-field block text-xs font-semibold text-[#24503D]">{{ __('Circuit') }}
                    <select name="tour_package_id" required class="br-tour mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                        @foreach($brTours as $t)
                            @php $deps = $t->upcomingDepartures()->get()->map(fn ($d) => ['d' => $d->departure_date->toDateString(), 'left' => $d->seatsLeft(), 'price' => $d->pricePerPerson(), 'label' => $d->departure_date->locale(app()->getLocale())->isoFormat('ddd D MMM')])->values(); @endphp
                            <option value="{{ $t->id }}" data-departures='@json($deps)' @selected(old('tour_package_id') == $t->id)>{{ $t->name }}{{ $t->price_xof ? ' — '.number_format((int) $t->price_xof, 0, ',', ' ').' FCFA' : '' }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            @if($brType === 'leisure' && $brActivities->isNotEmpty())
                <label class="block text-xs font-semibold text-[#24503D]">{{ __('Activité') }}
                    <select name="activity_id" class="mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                        <option value="">{{ __('— Au choix sur place —') }}</option>
                        @foreach($brActivities as $a)
                            <option value="{{ $a->id }}" @selected(old('activity_id', request('activite')) == $a->id)>{{ $a->name }}{{ $a->price_xof ? ' — '.number_format((int) $a->price_xof, 0, ',', ' ').' FCFA' : '' }}{{ $a->duration_minutes ? ' · '.$a->duration_minutes.' min' : '' }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            @if($brType === 'transport')
                <label class="block text-xs font-semibold text-[#24503D]">{{ __('Offre') }}
                    <select name="transport_offer_id" required class="mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                        @foreach($brOffers as $o)
                            <option value="{{ $o->id }}" @selected(old('transport_offer_id') == $o->id)>{{ $o->name }}{{ $o->price_xof ? ' — '.number_format((int) $o->price_xof, 0, ',', ' ').' FCFA' : '' }}{{ $o->max_passengers ? ' · '.$o->max_passengers.' '.__('places') : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <input type="text" name="pickup_location" required maxlength="255" value="{{ old('pickup_location') }}" placeholder="{{ __('Prise en charge (ex. aéroport FHB, hôtel…)') }}"
                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                <input type="text" name="dropoff_location" required maxlength="255" value="{{ old('dropoff_location') }}" placeholder="{{ __('Destination') }}"
                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
            @endif
            <div class="grid grid-cols-2 gap-2">
                <label class="block text-xs font-semibold text-[#24503D]">{{ $brType === 'art' ? __('Livraison souhaitée') : (in_array($brType, ['tour', 'bundle'], true) ? __('Départ') : __('Date')) }}
                    <input type="date" name="booking_date" required min="{{ now()->toDateString() }}" value="{{ old('booking_date') }}"
                           class="br-date mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                    @if($brType === 'tour')
                        <select name="booking_date" disabled hidden class="br-departures mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm"></select>
                    @endif
                </label>
                @if(! in_array($brType, ['tour', 'bundle', 'art'], true))
                <label class="block text-xs font-semibold text-[#24503D]">{{ __('Heure') }}
                    @if($brType === 'restaurant' && (int) ($target->table_capacity ?? 0) > 0)
                    <select name="booking_time" required data-slots-url="{{ route('restaurant.slots', $target->id) }}" class="br-slots mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                        <option value="">{{ __('Choisissez d\'abord une date') }}</option>
                    </select>
                @else
                <input type="time" name="booking_time" value="{{ old('booking_time', $brType === 'restaurant' ? '20:00' : '') }}" @required($brType === 'transport')
                           class="mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                @endif
                </label>
                @else
                <label class="block text-xs font-semibold text-[#24503D]">{{ ucfirst(__($brUnit)) }}
                    <input type="number" name="party_size" required min="1" max="200" value="{{ old('party_size', 2) }}"
                           class="mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                </label>
                @endif
            </div>
            @if(! in_array($brType, ['tour', 'bundle', 'art'], true))
            <label class="block text-xs font-semibold text-[#24503D]">{{ ucfirst(__($brUnit)) }}
                <input type="number" name="party_size" required min="1" max="200" value="{{ old('party_size', 2) }}"
                       class="mt-1 w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
            </label>
            @endif
            @if($brType === 'restaurant' && $target->tables()->active()->exists())
                {{-- Plan de salle : tables libres, réservées (grisées) ou trop petites pour l'heure choisie --}}
                <div class="br-plan" data-plan-url="{{ route('restaurant.table-plan', $target->id) }}">
                    <input type="hidden" name="restaurant_table_id" value="{{ old('restaurant_table_id') }}">
                    <div class="br-plan__head">
                        <span class="br-plan__title"><i class="fas fa-table-cells-large" aria-hidden="true"></i>{{ __('Choisissez votre table') }}</span>
                        <span class="br-plan__legend">
                            <span><i class="is-free"></i>{{ __('Libre') }}</span>
                            <span><i class="is-booked"></i>{{ __('Réservée') }}</span>
                        </span>
                    </div>
                    <div class="br-plan__zones" role="tablist" aria-label="{{ __('Zones') }}"></div>
                    <div class="br-plan__room" aria-live="polite"><p class="br-plan__hint">{{ __('Choisissez une date et une heure pour voir les tables libres.') }}</p></div>
                    <button type="button" class="br-plan__any is-on" data-table-any>{{ __('Peu importe — le restaurant choisit') }}</button>
                </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input type="text" name="full_name" required maxlength="255" value="{{ old('full_name', $brUser?->full_name) }}" placeholder="{{ __('Nom complet') }}"
                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                <input type="tel" name="phone" maxlength="50" value="{{ old('phone', $brUser?->phone) }}" placeholder="{{ __('Téléphone') }}"
                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
            </div>
            <input type="email" name="email" required maxlength="255" value="{{ old('email', $brUser?->email) }}" placeholder="{{ __('E-mail') }}"
                   class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
            @if($brHasPromo)
                <input type="text" name="promo_code" maxlength="30" value="{{ old('promo_code') }}" placeholder="{{ __('Code promo (facultatif)') }}" autocapitalize="characters"
                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm uppercase placeholder:normal-case">
            @endif
            <textarea name="message" rows="{{ $brType === 'art' ? 4 : 2 }}" maxlength="2000" @required($brType === 'art') placeholder="{{ $brType === 'art' ? __('Décrivez l\'œuvre souhaitée : sujet, format, technique, couleurs, budget…') : __('Précisions (allergies, occasion, besoins…)') }}"
                      class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">{{ old('message') }}</textarea>
            <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                <i class="fas fa-calendar-check text-sm"></i> {{ __('Envoyer ma demande') }}
            </button>
        </form>
        </div>
        @if($brCanOrder)
        {{-- ── Commande de plats : les quantités alimentent le panier du restaurant (partials.restaurant-cart) ── --}}
        <div data-br-panel="order" @if($brTab !== 'order') hidden @endif>
            <div class="br-dishes" role="group" aria-label="{{ __('Plats de la carte') }}">
                @foreach($brDishes as $dish)
                    @php $dishImg = collect((array) $dish->images)->first(fn ($u) => is_string($u) && $u !== ''); @endphp
                    <div class="br-dish">
                        <span class="br-dish__img">@if($dishImg)<img src="{{ $dishImg }}" alt="" loading="lazy">@else<i class="fas fa-utensils" aria-hidden="true"></i>@endif</span>
                        <span class="br-dish__txt">
                            <strong>{{ $dish->name }}</strong>
                            <small>{{ number_format((int) $dish->price_xof, 0, ',', ' ') }} FCFA</small>
                        </span>
                        <span class="br-qty">
                            <button type="button" data-cart-step="{{ $dish->id }}" data-step="-1" aria-label="{{ __('Retirer un « :name »', ['name' => $dish->name]) }}"><i class="fas fa-minus" aria-hidden="true"></i></button>
                            <b data-cart-qty="{{ $dish->id }}">0</b>
                            <button type="button" data-cart-step="{{ $dish->id }}" data-step="1" aria-label="{{ __('Ajouter un « :name »', ['name' => $dish->name]) }}"><i class="fas fa-plus" aria-hidden="true"></i></button>
                        </span>
                    </div>
                @endforeach
            </div>
            <button type="button" class="br-cart-btn" data-cart-open>
                <span><i class="fas fa-basket-shopping" aria-hidden="true"></i>{{ __('Voir mon panier') }}</span>
                <b><span data-br-cart-total>0</span> FCFA</b>
            </button>
            <p class="text-[#557466] text-[11px] text-center mt-2 mb-0">{{ __('Le restaurant confirme votre commande et son prix, puis vous réglez en ligne ou au retrait.') }}</p>
        </div>
        @endif
    @else
        <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
            <p class="text-[#0E3B2A] font-bold text-sm mb-1">{{ __('Connectez-vous pour réserver') }}</p>
            <div class="flex items-center justify-center gap-2 mt-3">
                <a href="{{ route('login', ['redirect' => request()->getRequestUri().'#reserver']) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                    <i class="fas fa-right-to-bracket text-xs"></i> {{ __('Se connecter') }}
                </a>
                <a href="{{ route('register', ['role' => 'visitor', 'redirect' => request()->getRequestUri().'#reserver']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 text-[#0E3B2A] transition">
                    {{ __('Créer un compte') }}
                </a>
            </div>
        </div>
    @endauth
</div>
@once
<style>
    .br-tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; padding: 5px; margin-bottom: 14px; border-radius: 999px; background: #F3F8F5; }
    .br-tabs button { height: 40px; border: 0; border-radius: 999px; background: transparent; color: #24503D; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 7px; cursor: pointer; }
    .br-tabs button.is-active { background: #EB6E00; color: #fff; box-shadow: 0 6px 16px -8px rgba(235,110,0,.7); }
    .br-tabs button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .br-dishes { display: flex; flex-direction: column; gap: 6px; max-height: 300px; overflow-y: auto; padding-right: 2px; }
    .br-dish { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 6px 8px 6px 6px; border: 1.5px solid #D8E9E0; border-radius: 14px; background: #fff; transition: border-color .15s ease, background .15s ease; }
    .br-dish.is-on { border-color: #EB6E00; background: #F3F8F5; }
    .br-dish__img { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
    .br-dish__img img { width: 100%; height: 100%; object-fit: cover; }
    .br-dish__txt { min-width: 0; display: flex; flex-direction: column; }
    .br-dish__txt strong { font-size: 13px; font-weight: 700; color: #0E3B2A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .br-dish__txt small { font-size: 12px; color: #557466; }
    .br-qty { display: inline-flex; align-items: center; gap: 4px; }
    .br-qty button { width: 28px; height: 28px; border: 0; border-radius: 999px; background: #F3F8F5; color: #0E3B2A; font-size: 11px; cursor: pointer; }
    .br-qty button:hover { background: #EB6E00; color: #fff; }
    .br-qty button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .br-qty input { width: 34px; height: 28px; border: 0; background: transparent; text-align: center; font-size: 14px; font-weight: 700; -moz-appearance: textfield; }
    .br-qty input::-webkit-outer-spin-button, .br-qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .br-total { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 14px; background: #00874F; color: #fff; font-size: 13px; }
    .br-total strong { font-size: 16px; }
    .br-modes { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(0, 1fr); gap: 6px; }
    .br-mode { position: relative; }
    .br-mode input { position: absolute; opacity: 0; pointer-events: none; }
    .br-mode span { display: flex; align-items: center; justify-content: center; gap: 7px; height: 40px; border: 1.5px solid #D8E9E0; border-radius: 12px; background: #fff; font-size: 13px; font-weight: 700; cursor: pointer; }
    .br-mode input:checked + span { border-color: #EB6E00; background: #F3F8F5; color: #B85600; }
    .br-mode input:focus-visible + span { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .br-qty b { min-width: 26px; text-align: center; font-size: 14px; }
    .br-cart-btn { display: flex; align-items: center; justify-content: space-between; width: 100%; height: 52px; margin-top: 10px; padding: 0 8px 0 18px; border: 0; border-radius: 999px; background: #0E3B2A; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; }
    .br-cart-btn span { display: inline-flex; align-items: center; gap: 9px; }
    .br-cart-btn b { height: 38px; padding: 0 14px; border-radius: 999px; background: #EB6E00; display: inline-flex; align-items: center; }
    .br-cart-btn:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
</style>
<script>
(function () {
    // Restaurant : bascule « Réserver une table » / « Commander ».
    document.querySelectorAll('.br-tabs').forEach(function (tabs) {
        const card = tabs.closest('.contact-card');
        tabs.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-br-tab]');
            if (!btn) return;
            tabs.querySelectorAll('[data-br-tab]').forEach(function (b) {
                const on = b === btn;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            card.querySelectorAll('[data-br-panel]').forEach(function (p) { p.hidden = p.dataset.brPanel !== btn.dataset.brTab; });
        });
    });
    document.addEventListener('ti-cart:change', function (e) {
        document.querySelectorAll('[data-br-cart-total]').forEach(function (el) { el.textContent = Number(e.detail.total).toLocaleString('fr-FR'); });
    });
    // Commande : quantités, total, adresse de livraison.
    document.querySelectorAll('form.br-order').forEach(function (form) {
        const count = form.querySelector('.br-count'), sum = form.querySelector('.br-sum');
        const address = form.querySelector('.br-address');
        function refresh() {
            let n = 0, total = 0;
            form.querySelectorAll('.br-dish').forEach(function (row) {
                const input = row.querySelector('input[type="number"]');
                const q = Math.max(0, Math.min(50, parseInt(input.value || '0', 10) || 0));
                input.value = q;
                row.classList.toggle('is-on', q > 0);
                n += q; total += q * Number(row.dataset.price || 0);
            });
            count.textContent = n;
            sum.textContent = total.toLocaleString('fr-FR');
        }
        form.addEventListener('click', function (e) {
            const b = e.target.closest('[data-qty]');
            if (!b) return;
            const input = b.parentElement.querySelector('input');
            input.value = (parseInt(input.value || '0', 10) || 0) + Number(b.dataset.qty);
            refresh();
        });
        form.addEventListener('input', refresh);
        form.addEventListener('change', function (e) {
            if (e.target.name === 'fulfillment' && address) {
                address.hidden = e.target.value !== 'delivery';
                address.required = e.target.value === 'delivery';
            }
        });
        form.addEventListener('submit', function (e) {
            refresh();
            if (count.textContent === '0') { e.preventDefault(); alert({{ \Illuminate\Support\Js::from(__('Choisissez au moins un plat de la carte.')) }}); }
        });
        refresh();
    });
})();
</script>
@endonce
@once
<script>
(function () {
    const T = {
        pick: {{ \Illuminate\Support\Js::from(__('Choisissez un créneau')) }},
        full: {{ \Illuminate\Support\Js::from(__('complet')) }},
        left: {{ \Illuminate\Support\Js::from(__('places')) }},
        none: {{ \Illuminate\Support\Js::from(__('Aucun créneau ce jour-là')) }},
        instant: {{ \Illuminate\Support\Js::from(__('Confirmation immédiate')) }},
    };
    // Circuits : dates de départ programmées (avec places restantes) et option « sur mesure ».
    document.querySelectorAll('select.br-tour').forEach(function (tourSel) {
        const form = tourSel.closest('form');
        const dateInput = form.querySelector('.br-date'), depSel = form.querySelector('.br-departures');
        const custom = form.querySelector('.br-custom'), tourField = form.querySelector('.br-tour-field');
        const L = { seats: {{ \Illuminate\Support\Js::from(__('places')) }}, full: {{ \Illuminate\Support\Js::from(__('complet')) }}, pick: {{ \Illuminate\Support\Js::from(__('Choisissez un départ')) }} };
        function sync() {
            const isCustom = custom && custom.checked;
            tourField.hidden = isCustom; tourSel.disabled = isCustom;
            let deps = [];
            try { deps = isCustom ? [] : JSON.parse(tourSel.selectedOptions[0]?.dataset.departures || '[]'); } catch (e) {}
            const useDeps = deps.length > 0;
            dateInput.hidden = useDeps; dateInput.disabled = useDeps;
            depSel.hidden = !useDeps; depSel.disabled = !useDeps;
            if (useDeps) {
                depSel.innerHTML = '<option value="">' + L.pick + '</option>' + deps.map(function (d) {
                    return '<option value="' + d.d + '"' + (d.left < 1 ? ' disabled' : '') + '>' + d.label + ' · ' + (d.left < 1 ? L.full : d.left + ' ' + L.seats) + ' · ' + d.price.toLocaleString('fr-FR') + ' FCFA</option>';
                }).join('');
                depSel.required = true;
            }
        }
        tourSel.addEventListener('change', sync);
        if (custom) custom.addEventListener('change', sync);
        sync();
    });

    document.querySelectorAll('select.br-slots').forEach(function (sel) {
        const form = sel.closest('form');
        const date = form.querySelector('[name="booking_date"]');
        const party = form.querySelector('[name="party_size"]');
        async function load() {
            if (!date.value) return;
            sel.disabled = true;
            try {
                const q = new URLSearchParams({ date: date.value, party: party ? party.value || 2 : 2 });
                const d = await (await fetch(sel.dataset.slotsUrl + '?' + q, { headers: { Accept: 'application/json' } })).json();
                const keep = sel.value;
                sel.innerHTML = '';
                const first = document.createElement('option'); first.value = '';
                first.textContent = (d.slots && d.slots.length) ? T.pick + (d.auto_confirm ? ' · ' + T.instant : '') : T.none;
                sel.appendChild(first);
                (d.slots || []).forEach(function (s) {
                    const o = document.createElement('option');
                    o.value = s.time; o.disabled = !s.available;
                    o.textContent = s.time + ' · ' + (s.available ? s.remaining + ' ' + T.left : T.full);
                    if (s.time === keep && s.available) o.selected = true;
                    sel.appendChild(o);
                });
            } catch (e) { /* le serveur revalide de toute façon */ }
            sel.disabled = false;
        }
        date.addEventListener('change', load);
        if (party) party.addEventListener('change', load);
        load();
    });
})();
</script>
@endonce
@once
<style>
    .br-plan { padding: 12px; border-radius: 18px; background: #F3F8F5; border: 1px solid #D8E9E0; }
    .br-plan__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px; }
    .br-plan__title { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 800; color: #0E3B2A; }
    .br-plan__title i { color: #EB6E00; }
    .br-plan__legend { display: inline-flex; gap: 10px; font-size: 11px; color: #557466; }
    .br-plan__legend span { display: inline-flex; align-items: center; gap: 4px; }
    .br-plan__legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .br-plan__legend i.is-free { background: #fff; border: 1.5px solid #00874F; }
    .br-plan__legend i.is-booked { background: #D8E9E0; }
    .br-plan__zones { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; margin-bottom: 8px; }
    .br-plan__zones:empty { display: none; }
    .br-plan__zones button { flex: none; height: 30px; padding: 0 12px; border: 1px solid #D8E9E0; border-radius: 999px; background: #fff; font-size: 12px; font-weight: 700; color: #24503D; cursor: pointer; }
    .br-plan__zones button.is-active { background: #0E3B2A; border-color: #0E3B2A; color: #fff; }
    .br-plan__room { display: grid; grid-template-columns: repeat(auto-fill, minmax(76px, 1fr)); gap: 10px; min-height: 84px; padding: 12px; border-radius: 14px; background: #fff; background-image: radial-gradient(#D8E9E0 1px, transparent 1px); background-size: 14px 14px; }
    .br-plan__hint { grid-column: 1 / -1; margin: auto; font-size: 12px; color: #557466; text-align: center; }
    .br-tbl { position: relative; display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 2px; border: 0; background: transparent; cursor: pointer; font-family: inherit; }
    .br-tbl__top { position: relative; display: flex; align-items: center; justify-content: center; width: 46px; height: 46px; border: 2px solid #00874F; background: #F3F8F5; color: #00874F; font-size: 12px; font-weight: 800; transition: transform .15s ease, background .15s ease, border-color .15s ease; }
    .br-tbl.is-round .br-tbl__top { border-radius: 999px; }
    .br-tbl.is-square .br-tbl__top { border-radius: 10px; }
    .br-tbl.is-long .br-tbl__top { width: 66px; height: 36px; border-radius: 10px; }
    /* Chaises autour de la table */
    .br-tbl__top::before, .br-tbl__top::after { content: ''; position: absolute; width: 14px; height: 5px; border-radius: 3px; background: currentColor; opacity: .35; }
    .br-tbl__top::before { top: -9px; } .br-tbl__top::after { bottom: -9px; }
    .br-tbl__seats { font-size: 11px; color: #24503D; font-weight: 600; }
    .br-tbl:hover .br-tbl__top { transform: translateY(-2px); }
    .br-tbl.is-selected .br-tbl__top { background: #EB6E00; border-color: #EB6E00; color: #fff; box-shadow: 0 0 0 4px rgba(235,110,0,.25); }
    .br-tbl.is-selected .br-tbl__seats { color: #B85600; font-weight: 800; }
    .br-tbl.is-booked, .br-tbl.is-small { cursor: not-allowed; }
    .br-tbl.is-booked .br-tbl__top { background: #D8E9E0; border-color: #8AA99B; color: #8AA99B; }
    .br-tbl.is-booked .br-tbl__top span { text-decoration: line-through; }
    .br-tbl.is-booked .br-tbl__seats { color: #8AA99B; }
    .br-tbl.is-small { opacity: .5; }
    .br-tbl.is-booked:hover .br-tbl__top, .br-tbl.is-small:hover .br-tbl__top { transform: none; }
    .br-tbl__lock { position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; border-radius: 999px; background: #557466; color: #fff; font-size: 8px; display: inline-flex; align-items: center; justify-content: center; }
    .br-tbl:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; border-radius: 12px; }
    .br-plan__any { display: block; width: 100%; margin-top: 8px; height: 36px; border: 1.5px dashed #8AA99B; border-radius: 12px; background: transparent; font-size: 12px; font-weight: 700; color: #557466; cursor: pointer; }
    .br-plan__any.is-on { border-style: solid; border-color: #00874F; color: #00874F; background: #F3F8F5; }
    @media (prefers-reduced-motion: reduce) { .br-tbl__top { transition: none; } }
</style>
<script>
(function () {
    const L = {
        seats: {{ \Illuminate\Support\Js::from(__('places')) }},
        booked: {{ \Illuminate\Support\Js::from(__('Réservée à cette heure')) }},
        small: {{ \Illuminate\Support\Js::from(__('Trop petite pour votre groupe')) }},
        table: {{ \Illuminate\Support\Js::from(__('Table')) }},
        all: {{ \Illuminate\Support\Js::from(__('Toutes')) }},
        pick: {{ \Illuminate\Support\Js::from(__('Choisissez une date et une heure pour voir les tables libres.')) }},
        none: {{ \Illuminate\Support\Js::from(__('Aucune table disponible à cette heure : essayez un autre horaire.')) }},
    };
    document.querySelectorAll('.br-plan').forEach(function (plan) {
        const form = plan.closest('form');
        const input = plan.querySelector('[name="restaurant_table_id"]');
        const room = plan.querySelector('.br-plan__room');
        const zonesBar = plan.querySelector('.br-plan__zones');
        const any = plan.querySelector('[data-table-any]');
        const date = form.querySelector('[name="booking_date"]');
        const party = form.querySelector('[name="party_size"]');
        let tables = [], zone = null, token = 0;

        function time() { const t = form.querySelector('[name="booking_time"]'); return t ? t.value : ''; }
        function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

        function render() {
            const zones = [...new Set(tables.map(t => t.zone))];
            zonesBar.innerHTML = zones.length > 1 ? ['<button type="button" data-zone=""' + (zone ? '' : ' class="is-active"') + '>' + esc(L.all) + '</button>']
                .concat(zones.map(z => '<button type="button" data-zone="' + esc(z) + '"' + (zone === z ? ' class="is-active"' : '') + '>' + esc(z) + '</button>')).join('') : '';
            const shown = tables.filter(t => !zone || t.zone === zone);
            if (!shown.length) { room.innerHTML = '<p class="br-plan__hint">' + esc(date.value && time() ? L.none : L.pick) + '</p>'; return; }
            room.innerHTML = shown.map(function (t) {
                const sel = String(t.id) === input.value;
                const title = t.status === 'booked' ? L.booked : (t.status === 'small' ? L.small : L.table + ' ' + t.label + ' · ' + t.seats + ' ' + L.seats + ' · ' + t.zone);
                return '<button type="button" class="br-tbl is-' + esc(t.shape) + ' is-' + t.status + (sel ? ' is-selected' : '') + '" data-table="' + t.id + '"'
                    + (t.status !== 'free' ? ' aria-disabled="true"' : '') + ' aria-pressed="' + sel + '" title="' + esc(title) + '" aria-label="' + esc(title) + '">'
                    + '<span class="br-tbl__top"><span>' + esc(t.label) + '</span>' + (t.status === 'booked' ? '<i class="br-tbl__lock fas fa-lock" aria-hidden="true"></i>' : '') + '</span>'
                    + '<span class="br-tbl__seats">' + t.seats + ' ' + esc(L.seats) + '</span></button>';
            }).join('');
            any.classList.toggle('is-on', !input.value);
        }

        async function load() {
            if (!date.value) { tables = []; render(); return; }
            const my = ++token;
            try {
                const q = new URLSearchParams({ date: date.value, party: party ? party.value || 2 : 2 });
                if (time()) q.set('time', time());
                const d = await (await fetch(plan.dataset.planUrl + '?' + q, { headers: { Accept: 'application/json' } })).json();
                if (my !== token) return;
                tables = d.tables || [];
                // La table choisie n'est plus libre (heure ou groupe modifiés) : on la désélectionne.
                const cur = tables.find(t => String(t.id) === input.value);
                if (input.value && (!cur || cur.status !== 'free')) input.value = '';
            } catch (e) { tables = []; }
            render();
        }

        plan.addEventListener('click', function (e) {
            const z = e.target.closest('[data-zone]');
            if (z) { zone = z.dataset.zone || null; render(); return; }
            const b = e.target.closest('[data-table]');
            if (b) {
                const t = tables.find(x => String(x.id) === b.dataset.table);
                if (!t || t.status !== 'free') return;
                input.value = input.value === String(t.id) ? '' : String(t.id);
                render(); return;
            }
            if (e.target.closest('[data-table-any]')) { input.value = ''; render(); }
        });
        form.addEventListener('change', function (e) {
            if (['booking_date', 'booking_time', 'party_size'].includes(e.target.name)) load();
        });
        load();
    });
})();
</script>
@endonce
@endif
