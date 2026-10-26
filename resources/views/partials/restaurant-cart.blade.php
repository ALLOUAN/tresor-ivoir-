{{--
    Panier du restaurant : bouton flottant + tiroir de commande (à emporter / livraison).
    Paramètres : $restaurant (avec provider_id, accepts_orders, offers_delivery), $dishes (plats disponibles).
    Le panier est gardé dans le navigateur (un par restaurant) et envoyé comme une commande
    (bookable_type = restaurant_order) : le restaurant la confirme avec son prix.
    API JS : window.TiCart.add(id, n), .set(id, n), .open() ; évènement « ti-cart:change ».
    Boutons déclaratifs : [data-cart-add="id"], [data-cart-step="id"][data-step="±1"], [data-cart-qty="id"], [data-cart-open].
--}}
@php
    $tcDishes = collect($dishes)->map(fn ($d) => [
        'id' => $d->id,
        'name' => $d->name,
        'price' => (int) $d->price_xof,
        'img' => collect((array) $d->images)->first(fn ($u) => is_string($u) && $u !== ''),
        'cat' => $d->category?->localized('name') ?: $d->category?->name_fr,
    ])->values();
    $tcUser = auth()->user();
    $tcErrors = $errors->getBag('booking');
    $tcReopen = old('bookable_type') === 'restaurant_order' && $tcErrors->any();
    $tcPlaced = (int) session('order_placed') === (int) $restaurant->id;
@endphp
@if($tcDishes->isNotEmpty())
<div class="tc" id="ti-cart" data-key="ti-cart-v1-{{ $restaurant->id }}" data-dishes='@json($tcDishes)'
     data-reopen="{{ $tcReopen ? '1' : '0' }}" data-placed="{{ $tcPlaced ? '1' : '0' }}"
     data-preset="{{ request()->has('commander') && is_string(request('item')) ? request('item') : '' }}">

    {{-- Bouton flottant : nombre de plats + total --}}
    <button type="button" class="tc-fab" data-cart-open aria-label="{{ __('Voir mon panier') }}" hidden>
        <span class="tc-fab__icon"><i class="fas fa-basket-shopping" aria-hidden="true"></i><span class="tc-fab__badge" data-tc-count>0</span></span>
        <span class="tc-fab__label">{{ __('Voir mon panier') }}</span>
        <span class="tc-fab__total"><span data-tc-total>0</span> FCFA</span>
    </button>

    {{-- Message après l'envoi de la commande --}}
    @if($tcPlaced && session('booking_success'))
        <div class="tc-toast is-success" role="status"><i class="fas fa-circle-check" aria-hidden="true"></i><span>{{ session('booking_success') }}</span>
            <button type="button" class="tc-toast__close" onclick="this.parentElement.remove()" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button></div>
    @endif
    <div class="tc-toast" data-tc-toast role="status" hidden></div>

    {{-- Tiroir --}}
    <div class="tc-overlay" data-tc-close hidden></div>
    <aside class="tc-drawer" role="dialog" aria-modal="true" aria-labelledby="tc-title" aria-hidden="true" tabindex="-1">
        <header class="tc-head">
            <div>
                <h2 id="tc-title" class="tc-title">{{ __('Votre panier') }}</h2>
                <p class="tc-sub"><i class="fas fa-store" aria-hidden="true"></i>{{ $restaurant->name }}</p>
            </div>
            <div class="tc-head__actions">
                <button type="button" class="tc-link" data-tc-clear>{{ __('Vider') }}</button>
                <button type="button" class="tc-close" data-tc-close aria-label="{{ __('Fermer le panier') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
            </div>
        </header>

        <form method="POST" action="{{ route('booking-requests.store') }}" class="tc-body" data-tc-form novalidate>
            @csrf
            <input type="hidden" name="bookable_type" value="restaurant_order">
            <input type="hidden" name="bookable_id" value="{{ $restaurant->id }}">
            <div data-tc-hidden></div>

            @if($tcReopen)
                <p class="tc-alert"><i class="fas fa-circle-exclamation" aria-hidden="true"></i>{{ $tcErrors->first() }}</p>
            @endif

            {{-- Lignes du panier (générées) --}}
            <ul class="tc-lines" data-tc-lines></ul>
            <div class="tc-empty" data-tc-empty>
                <span class="tc-empty__icon"><i class="fas fa-basket-shopping" aria-hidden="true"></i></span>
                <p class="tc-empty__title">{{ __('Votre panier est vide') }}</p>
                <p class="tc-empty__text">{{ __('Ajoutez des plats depuis la carte du restaurant.') }}</p>
                <a href="{{ route('restaurant.menu', $restaurant->slug) }}" class="tc-btn is-ghost">{{ __('Voir la carte') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>

            <div class="tc-checkout" data-tc-checkout>
                <p class="tc-step">{{ __('Retrait') }}</p>
                <div class="tc-modes" role="radiogroup" aria-label="{{ __('Mode de retrait') }}">
                    <label class="tc-mode"><input type="radio" name="fulfillment" value="pickup" @checked(old('fulfillment', 'pickup') === 'pickup')><span><i class="fas fa-bag-shopping" aria-hidden="true"></i><b>{{ __('À emporter') }}</b><small>{{ __('Au restaurant') }}</small></span></label>
                    @if($restaurant->offers_delivery)
                        <label class="tc-mode"><input type="radio" name="fulfillment" value="delivery" @checked(old('fulfillment') === 'delivery')><span><i class="fas fa-motorcycle" aria-hidden="true"></i><b>{{ __('Livraison') }}</b><small>{{ __('Frais confirmés par le restaurant') }}</small></span></label>
                    @endif
                </div>
                @if($restaurant->offers_delivery)
                    <input type="text" name="delivery_address" maxlength="255" value="{{ old('delivery_address') }}" class="tc-input" data-tc-address
                           placeholder="{{ __('Adresse de livraison (quartier, rue, repère…)') }}" @if(old('fulfillment') !== 'delivery') hidden @endif>
                @endif

                <p class="tc-step">{{ __('Quand ?') }}</p>
                <div class="tc-grid">
                    <label class="tc-field"><span>{{ __('Date') }}</span>
                        <input type="date" name="booking_date" required min="{{ now('Africa/Abidjan')->toDateString() }}" value="{{ old('booking_date', now('Africa/Abidjan')->toDateString()) }}" class="tc-input">
                    </label>
                    <label class="tc-field"><span>{{ __('Heure') }}</span>
                        <input type="time" name="booking_time" required value="{{ old('booking_time') }}" class="tc-input">
                    </label>
                </div>

                @auth
                    <p class="tc-step">{{ __('Vos coordonnées') }}</p>
                    <div class="tc-grid">
                        <input type="text" name="full_name" required maxlength="255" value="{{ old('full_name', $tcUser?->full_name) }}" placeholder="{{ __('Nom complet') }}" class="tc-input">
                        <input type="tel" name="phone" maxlength="50" value="{{ old('phone', $tcUser?->phone) }}" placeholder="{{ __('Téléphone') }}" class="tc-input">
                    </div>
                    <input type="email" name="email" required maxlength="255" value="{{ old('email', $tcUser?->email) }}" placeholder="{{ __('E-mail') }}" class="tc-input">
                    <textarea name="message" rows="2" maxlength="2000" placeholder="{{ __('Précisions (sans piment, allergies, couverts…)') }}" class="tc-input">{{ old('message') }}</textarea>
                @endauth
            </div>
        </form>

        <footer class="tc-foot" data-tc-foot>
            <dl class="tc-sum">
                <div><dt>{{ __('Sous-total') }}</dt><dd><span data-tc-total>0</span> FCFA</dd></div>
                <div data-tc-delivery hidden><dt>{{ __('Livraison') }}</dt><dd>{{ __('À confirmer') }}</dd></div>
                <div class="is-total"><dt>{{ __('Total estimé') }}</dt><dd><span data-tc-total>0</span> FCFA</dd></div>
            </dl>
            @auth
                <button type="button" class="tc-btn is-main" data-tc-submit>
                    <i class="fas fa-basket-shopping" aria-hidden="true"></i>{{ __('Commander') }} · <span data-tc-total>0</span> FCFA
                </button>
            @else
                <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="tc-btn is-main"><i class="fas fa-right-to-bracket" aria-hidden="true"></i>{{ __('Se connecter pour commander') }}</a>
                <p class="tc-note">{{ __('Votre panier est conservé pendant la connexion.') }}</p>
            @endauth
            <p class="tc-note">{{ __('Sans engagement : le restaurant confirme votre commande et son prix, puis vous réglez en ligne ou au retrait.') }}</p>
        </footer>
    </aside>
</div>

@once
<style>
    .tc-fab[hidden] { display: none; }
    .tc-fab { position: fixed; z-index: 60; left: 50%; bottom: 24px; transform: translate(-50%, 0); display: inline-flex; align-items: center; gap: 14px; height: 58px; padding: 0 10px 0 8px; border: 0; border-radius: 999px; background: #0E3B2A; color: #fff; box-shadow: 0 18px 40px -14px rgba(14,59,42,.6); cursor: pointer; animation: tc-in .35s cubic-bezier(.2,.9,.3,1.2); }
    .tc-fab:hover { background: #0E3B2A; }
    .tc-fab:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
    .tc-fab__icon { position: relative; width: 42px; height: 42px; border-radius: 999px; background: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
    .tc-fab__badge { position: absolute; top: -4px; right: -4px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: #EB6E00; color: #0E3B2A; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #0E3B2A; }
    .tc-fab__label { font-size: 14px; font-weight: 700; }
    .tc-fab__total { height: 40px; padding: 0 14px; border-radius: 999px; background: rgba(255,255,255,.12); font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; }
    .tc-fab.is-bump .tc-fab__icon { animation: tc-bump .45s ease; }
    @keyframes tc-in { from { opacity: 0; transform: translate(-50%, 20px); } to { opacity: 1; transform: translate(-50%, 0); } }
    @keyframes tc-bump { 30% { transform: scale(1.22) rotate(-8deg); } 60% { transform: scale(.94); } }

    .tc-toast { position: fixed; z-index: 70; left: 50%; top: 96px; transform: translateX(-50%); display: flex; align-items: center; gap: 10px; max-width: min(92vw, 520px); padding: 12px 16px; border-radius: 16px; background: #0E3B2A; color: #fff; font-size: 14px; font-weight: 600; box-shadow: 0 18px 40px -16px rgba(14,59,42,.6); }
    .tc-toast.is-success { background: #00874F; }
    .tc-toast i { color: #EB6E00; }
    .tc-toast__close { margin-left: 6px; border: 0; background: transparent; color: #fff; cursor: pointer; }

    .tc-overlay { position: fixed; inset: 0; z-index: 80; background: rgba(14,59,42,.45); backdrop-filter: blur(2px); }
    .tc-drawer { position: fixed; z-index: 90; top: 0; right: 0; bottom: 0; width: min(440px, 100vw); display: flex; flex-direction: column; background: #FFFFFF; box-shadow: -24px 0 60px -30px rgba(14,59,42,.6); transform: translateX(105%); transition: transform .35s cubic-bezier(.2,.8,.2,1); visibility: hidden; }
    .tc-drawer.is-open { transform: none; visibility: visible; }
    .tc-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 20px 20px 14px; border-bottom: 1px solid #D8E9E0; background: #fff; }
    .tc-title { margin: 0; font-family: 'Montserrat', serif; font-size: 24px; font-weight: 700; color: #0E3B2A; }
    .tc-sub { margin: 4px 0 0; display: flex; align-items: center; gap: 6px; font-size: 13px; color: #557466; }
    .tc-sub i { color: #EB6E00; }
    .tc-head__actions { display: flex; align-items: center; gap: 6px; }
    .tc-link { border: 0; background: transparent; color: #B85600; font-size: 13px; font-weight: 700; cursor: pointer; padding: 6px 8px; }
    .tc-close { width: 40px; height: 40px; border: 0; border-radius: 999px; background: #F3F8F5; color: #0E3B2A; cursor: pointer; }
    .tc-close:hover { background: #EB6E00; color: #fff; }
    .tc-body { flex: 1; overflow-y: auto; padding: 14px 20px 20px; }
    .tc-alert { display: flex; gap: 8px; align-items: flex-start; margin: 0 0 12px; padding: 10px 12px; border-radius: 12px; background: #fdecea; color: #9b1c1c; font-size: 13px; font-weight: 600; }

    .tc-lines { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
    .tc-line { display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 8px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; animation: tc-line .25s ease; }
    @keyframes tc-line { from { opacity: 0; transform: translateY(6px); } }
    .tc-line__img { width: 64px; height: 64px; border-radius: 14px; overflow: hidden; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
    .tc-line__img img { width: 100%; height: 100%; object-fit: cover; }
    .tc-line__txt { min-width: 0; }
    .tc-line__name { margin: 0; font-size: 14.5px; font-weight: 700; color: #0E3B2A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tc-line__meta { margin: 3px 0 0; font-size: 12px; color: #557466; }
    .tc-line__total { margin: 4px 0 0; font-size: 14px; font-weight: 800; color: #0E3B2A; }
    .tc-line__side { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .tc-qty { display: inline-flex; align-items: center; gap: 2px; padding: 3px; border-radius: 999px; background: #F3F8F5; }
    .tc-qty button { width: 30px; height: 30px; border: 0; border-radius: 999px; background: #fff; color: #0E3B2A; font-size: 11px; cursor: pointer; box-shadow: 0 1px 2px rgba(14,59,42,.08); }
    .tc-qty button:hover { background: #EB6E00; color: #fff; }
    .tc-qty button:focus-visible, .tc-remove:focus-visible, .tc-mode input:focus-visible + span, .tc-btn:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .tc-qty b { min-width: 26px; text-align: center; font-size: 14px; }
    .tc-remove { border: 0; background: transparent; color: #557466; font-size: 12px; cursor: pointer; }
    .tc-remove:hover { color: #b42318; }

    .tc-empty { text-align: center; padding: 36px 12px; }
    .tc-empty__icon { width: 76px; height: 76px; border-radius: 999px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; }
    .tc-empty__title { margin: 14px 0 4px; font-family: 'Montserrat', serif; font-size: 20px; font-weight: 700; color: #0E3B2A; }
    .tc-empty__text { margin: 0 0 16px; font-size: 14px; color: #557466; }

    .tc-step { margin: 18px 0 8px; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #557466; }
    .tc-modes { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(0, 1fr); gap: 8px; }
    .tc-mode { position: relative; }
    .tc-mode input { position: absolute; opacity: 0; pointer-events: none; }
    .tc-mode span { display: grid; grid-template-columns: auto 1fr; column-gap: 10px; align-items: center; padding: 10px 12px; border: 1.5px solid #D8E9E0; border-radius: 14px; background: #fff; cursor: pointer; }
    .tc-mode span i { grid-row: span 2; width: 34px; height: 34px; border-radius: 10px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; }
    .tc-mode b { font-size: 13.5px; color: #0E3B2A; }
    .tc-mode small { font-size: 11.5px; color: #557466; }
    .tc-mode input:checked + span { border-color: #EB6E00; background: #F3F8F5; }
    .tc-mode input:checked + span i { background: #EB6E00; color: #fff; }
    .tc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .tc-field span { display: block; margin-bottom: 4px; font-size: 12px; font-weight: 600; color: #24503D; }
    .tc-input { display: block; width: 100%; margin-top: 8px; padding: 11px 12px; border: 1px solid #D8E9E0; border-radius: 12px; background: #fff; font-size: 14px; color: #0E3B2A; }
    .tc-grid .tc-input, .tc-field .tc-input { margin-top: 0; }
    .tc-input:focus { outline: 2px solid #EB6E00; outline-offset: 0; border-color: #EB6E00; }

    .tc-foot { padding: 14px 20px calc(16px + env(safe-area-inset-bottom, 0)); border-top: 1px solid #D8E9E0; background: #fff; }
    .tc-sum { margin: 0 0 12px; display: flex; flex-direction: column; gap: 4px; }
    .tc-sum div { display: flex; justify-content: space-between; font-size: 13.5px; color: #24503D; }
    .tc-sum dt, .tc-sum dd { margin: 0; }
    .tc-sum .is-total { margin-top: 4px; padding-top: 8px; border-top: 1px dashed #D8E9E0; font-size: 16px; font-weight: 800; color: #0E3B2A; }
    .tc-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 52px; border: 0; border-radius: 999px; font-size: 15px; font-weight: 800; cursor: pointer; text-decoration: none; }
    .tc-btn.is-main { background: #EB6E00; color: #fff; box-shadow: 0 14px 30px -14px rgba(235,110,0,.7); }
    .tc-btn.is-main:hover { background: #B85600; color: #fff; }
    .tc-btn.is-main[disabled] { opacity: .5; cursor: not-allowed; }
    .tc-btn.is-ghost { display: inline-flex; width: auto; height: 42px; padding: 0 18px; background: #fff; border: 1.5px solid #D8E9E0; color: #B85600; font-size: 14px; }
    .tc-note { margin: 8px 0 0; text-align: center; font-size: 11.5px; color: #557466; }

    /* Boutons « Ajouter » des cartes de plats */
    .tc-add { display: inline-flex; align-items: center; gap: 10px; height: 42px; padding: 0 6px 0 16px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 13.5px; font-weight: 700; cursor: pointer; transition: background .15s ease, transform .15s ease; }
    .tc-add:hover { background: #B85600; }
    .tc-add:active { transform: scale(.96); }
    .tc-add > span { width: 30px; height: 30px; border-radius: 999px; background: #fff; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; }
    .tc-add.is-in { background: #00874F; }
    .tc-add.is-in > span { color: #00874F; }
    .tc-add:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

    @media (max-width: 767px) {
        .tc-fab { bottom: 84px; gap: 10px; height: 54px; }
        .tc-fab__label { display: none; }
        .tc-drawer { top: auto; left: 0; width: 100vw; height: 92vh; border-radius: 26px 26px 0 0; transform: translateY(105%); }
        .tc-grid { grid-template-columns: 1fr; }
    }
    @media (prefers-reduced-motion: reduce) { .tc-fab, .tc-drawer, .tc-line, .tc-fab.is-bump .tc-fab__icon { animation: none; transition: none; } }
</style>
<script>
(function () {
    const root = document.getElementById('ti-cart');
    if (!root) return;
    const dishes = JSON.parse(root.dataset.dishes || '[]');
    const byId = Object.fromEntries(dishes.map(d => [String(d.id), d]));
    const key = root.dataset.key;
    const fmt = n => Number(n).toLocaleString('fr-FR');
    const T = {
        added: {{ \Illuminate\Support\Js::from(__('ajouté au panier')) }},
        inCart: {{ \Illuminate\Support\Js::from(__('Dans le panier')) }},
        add: {{ \Illuminate\Support\Js::from(__('Ajouter')) }},
        remove: {{ \Illuminate\Support\Js::from(__('Retirer')) }},
        unit: {{ \Illuminate\Support\Js::from(__('l\'unité')) }},
        empty: {{ \Illuminate\Support\Js::from(__('Choisissez au moins un plat de la carte.')) }},
        less: {{ \Illuminate\Support\Js::from(__('Un de moins')) }},
        more: {{ \Illuminate\Support\Js::from(__('Un de plus')) }},
    };

    // État : { id: quantité }, gardé dans le navigateur (si disponible).
    let cart = {};
    try { cart = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (e) { cart = {}; }
    if (root.dataset.placed === '1') { cart = {}; }
    Object.keys(cart).forEach(id => { if (!byId[id] || !(cart[id] > 0)) delete cart[id]; });

    const fab = root.querySelector('.tc-fab');
    const drawer = root.querySelector('.tc-drawer');
    const overlay = root.querySelector('.tc-overlay');
    const lines = root.querySelector('[data-tc-lines]');
    const emptyBox = root.querySelector('[data-tc-empty]');
    const checkout = root.querySelector('[data-tc-checkout]');
    const foot = root.querySelector('[data-tc-foot]');
    const form = root.querySelector('[data-tc-form]');
    const hidden = root.querySelector('[data-tc-hidden]');
    const toastBox = root.querySelector('[data-tc-toast]');
    const address = root.querySelector('[data-tc-address]');
    const deliveryRow = root.querySelector('[data-tc-delivery]');
    let lastFocus = null, toastTimer;

    function save() { try { localStorage.setItem(key, JSON.stringify(cart)); } catch (e) {} }
    const count = () => Object.values(cart).reduce((a, b) => a + b, 0);
    const total = () => Object.entries(cart).reduce((s, [id, q]) => s + q * (byId[id]?.price || 0), 0);

    function esc(s) { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    function render() {
        const n = count(), t = total();
        root.querySelectorAll('[data-tc-count]').forEach(el => el.textContent = n);
        root.querySelectorAll('[data-tc-total]').forEach(el => el.textContent = fmt(t));
        fab.hidden = n === 0 || drawer.classList.contains('is-open');
        emptyBox.hidden = n > 0; checkout.hidden = n === 0; foot.hidden = n === 0;
        lines.innerHTML = Object.entries(cart).map(([id, q]) => {
            const d = byId[id];
            return '<li class="tc-line">'
                + '<span class="tc-line__img">' + (d.img ? '<img src="' + esc(d.img) + '" alt="">' : '<i class="fas fa-utensils" aria-hidden="true"></i>') + '</span>'
                + '<div class="tc-line__txt"><p class="tc-line__name">' + esc(d.name) + '</p>'
                + '<p class="tc-line__meta">' + fmt(d.price) + ' FCFA · ' + esc(T.unit) + '</p>'
                + '<p class="tc-line__total">' + fmt(d.price * q) + ' FCFA</p></div>'
                + '<div class="tc-line__side"><span class="tc-qty">'
                + '<button type="button" data-cart-step="' + id + '" data-step="-1" aria-label="' + esc(T.less + ' : ' + d.name) + '"><i class="fas fa-minus" aria-hidden="true"></i></button>'
                + '<b>' + q + '</b>'
                + '<button type="button" data-cart-step="' + id + '" data-step="1" aria-label="' + esc(T.more + ' : ' + d.name) + '"><i class="fas fa-plus" aria-hidden="true"></i></button>'
                + '</span><button type="button" class="tc-remove" data-cart-remove="' + id + '">' + esc(T.remove) + '</button></div></li>';
        }).join('');
        // Boutons et compteurs ailleurs dans la page (cartes de plats, onglet « Commander »).
        document.querySelectorAll('[data-cart-qty]').forEach(el => el.textContent = cart[el.dataset.cartQty] || 0);
        document.querySelectorAll('[data-cart-add]').forEach(btn => {
            const q = cart[btn.dataset.cartAdd] || 0;
            btn.classList.toggle('is-in', q > 0);
            const label = btn.querySelector('[data-cart-add-label]');
            if (label) label.textContent = q > 0 ? T.inCart + ' (' + q + ')' : T.add;
        });
        document.dispatchEvent(new CustomEvent('ti-cart:change', { detail: { count: n, total: t } }));
    }

    function set(id, q) {
        id = String(id);
        if (!byId[id]) return;
        q = Math.max(0, Math.min(50, q));
        if (q === 0) delete cart[id]; else cart[id] = q;
        save(); render();
    }
    function toast(msg) {
        toastBox.textContent = msg; toastBox.hidden = false;
        clearTimeout(toastTimer); toastTimer = setTimeout(() => { toastBox.hidden = true; }, 2200);
    }
    function add(id, n) {
        set(id, (cart[String(id)] || 0) + (n || 1));
        fab.classList.remove('is-bump'); void fab.offsetWidth; fab.classList.add('is-bump');
        toast((byId[String(id)]?.name || '') + ' — ' + T.added);
    }
    function open() {
        lastFocus = document.activeElement;
        overlay.hidden = false; drawer.classList.add('is-open'); drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden'; render(); drawer.focus();
    }
    function close() {
        drawer.classList.remove('is-open'); drawer.setAttribute('aria-hidden', 'true'); overlay.hidden = true;
        document.body.style.overflow = ''; render(); if (lastFocus) lastFocus.focus();
    }

    document.addEventListener('click', e => {
        const t = e.target;
        const addBtn = t.closest('[data-cart-add]'); if (addBtn) { e.preventDefault(); add(addBtn.dataset.cartAdd, 1); return; }
        const step = t.closest('[data-cart-step]'); if (step) { set(step.dataset.cartStep, (cart[step.dataset.cartStep] || 0) + Number(step.dataset.step)); return; }
        const rm = t.closest('[data-cart-remove]'); if (rm) { set(rm.dataset.cartRemove, 0); return; }
        if (t.closest('[data-cart-open]')) { e.preventDefault(); open(); return; }
        if (t.closest('[data-tc-close]')) { close(); return; }
        if (t.closest('[data-tc-clear]')) { cart = {}; save(); render(); return; }
        if (t.closest('[data-tc-submit]')) {
            if (count() === 0) { toast(T.empty); return; }
            hidden.innerHTML = Object.entries(cart).map(([id, q]) => '<input type="hidden" name="order[' + id + ']" value="' + q + '">').join('');
            if (form.reportValidity()) form.submit();
        }
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && drawer.classList.contains('is-open')) close(); });
    form.addEventListener('change', e => {
        if (e.target.name !== 'fulfillment') return;
        const delivery = e.target.value === 'delivery';
        if (address) { address.hidden = !delivery; address.required = delivery; }
        deliveryRow.hidden = !delivery;
    });
    if (form.querySelector('[name="fulfillment"]:checked')?.value === 'delivery') { deliveryRow.hidden = false; if (address) address.required = true; }

    window.TiCart = { add, set, open, close, get: () => ({ ...cart }) };

    // Arrivée depuis « Commander » sur un plat : ajouté une fois au panier.
    const preset = root.dataset.preset;
    if (preset) {
        const d = dishes.find(x => x.name === preset);
        if (d && !cart[String(d.id)]) { cart[String(d.id)] = 1; save(); }
    }
    if (root.dataset.placed === '1') save();
    render();
    if (root.dataset.reopen === '1') open();
})();
</script>
@endonce
@endif
