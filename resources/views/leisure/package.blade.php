<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $package->name }}@if($place) — {{ $place->name }}@endif — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .lx-slots { display: grid; grid-template-columns: repeat(auto-fill, minmax(86px, 1fr)); gap: 6px; }
        .lx-slot { padding: 10px 4px; border-radius: 12px; border: 1.5px solid #D8E9E0; background: #fff; font-weight: 800; cursor: pointer; }
        .lx-slot:disabled { background: #F3F8F5; color: #8AA99B; border-style: dashed; text-decoration: line-through; cursor: not-allowed; }
        .lx-slot.is-active { background: #EB6E00; border-color: #EB6E00; color: #fff; }
        .lx-slot:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    </style>
    @include('partials.detail-theme')
    @include('partials.booking-page-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $incl = \App\Models\Activity::lines($package->includes);
    $user = auth()->user();
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
    @if($place)<a href="{{ route('leisure.show', $place->slug) }}" class="hx-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ $place->name }}</a>@endif
    <header class="hx-head">
        <div class="min-w-0">
            <div class="hx-pills"><span class="hx-pill"><i class="fas {{ \App\Models\LeisurePackage::KIND_ICONS[$package->kind] ?? 'fa-star' }}" aria-hidden="true"></i>{{ $package->kindLabel() }}</span></div>
            <h1 class="hx-title">{{ $package->name }}</h1>
            <p class="hx-meta">
                <span><i class="fas fa-users text-[#EB6E00] mr-1" aria-hidden="true"></i>{{ __(':n participants inclus', ['n' => $package->included_participants]) }}@if($package->extra_participant_xof) · {{ __('+ :a par participant en plus', ['a' => $fmt($package->extra_participant_xof)]) }}@endif</span>
                <span><i class="fas fa-clock text-[#EB6E00] mr-1" aria-hidden="true"></i>{{ intdiv($package->duration_minutes, 60) }} h{{ $package->duration_minutes % 60 ? ' '.($package->duration_minutes % 60) : '' }}</span>
            </p>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start">
        <div class="lg:col-span-3 space-y-8 min-w-0">
            @if($package->description)<p class="hx-text">{{ $package->description }}</p>@endif
            @if($incl)<div class="tp-box"><h3 class="text-[#00874F]">{{ __('La formule comprend') }}</h3><ul class="tp-list">@foreach($incl as $l)<li><i class="fas fa-check text-[#00874F]" aria-hidden="true"></i>{{ $l }}</li>@endforeach</ul></div>@endif
            <div class="tp-incl">
                <div class="tp-box"><h3>{{ __('Tarifs') }}</h3><ul class="tp-list">
                    <li><i class="fas fa-calendar text-[#EB6E00]" aria-hidden="true"></i>{{ __('Du lundi au vendredi : :a', ['a' => $fmt($package->weekday_price_xof)]) }}</li>
                    @if($package->weekend_price_xof)<li><i class="fas fa-calendar-week text-[#EB6E00]" aria-hidden="true"></i>{{ __('Samedi et dimanche : :a', ['a' => $fmt($package->weekend_price_xof)]) }}</li>@endif
                    <li><i class="fas fa-users text-[#EB6E00]" aria-hidden="true"></i>{{ __('De :min à :max participants', ['min' => $package->min_participants, 'max' => $package->max_participants ?: '∞']) }}</li>
                </ul></div>
                <div class="tp-box"><h3>{{ __('Paiement et annulation') }}</h3><ul class="tp-list">
                    <li><i class="fas fa-wallet text-[#EB6E00]" aria-hidden="true"></i>{{ $package->deposit_xof ? __('Acompte de :a en ligne pour bloquer la date, solde sur place', ['a' => $fmt($package->deposit_xof)]) : __('Paiement intégral en ligne') }}</li>
                    <li><i class="fas fa-rotate-left text-[#EB6E00]" aria-hidden="true"></i>{{ __('Annulation gratuite jusqu\'à :d jours avant (remboursement sur votre solde)', ['d' => $package->free_cancellation_days]) }}</li>
                </ul></div>
            </div>
        </div>
        <aside class="lg:col-span-2 w-full" id="reserver">
            <div class="tp-card">
                <p class="tp-card__price">{{ __('À partir de') }} <b>{{ $fmt($package->weekday_price_xof) }}</b></p>
                @if(! $user)
                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="tp-submit inline-flex items-center justify-center gap-2"><i class="fas fa-right-to-bracket" aria-hidden="true"></i>{{ __('Se connecter pour réserver') }}</a>
                @elseif($user->role !== 'visitor')
                    <p class="tp-note">{{ __('Les réservations se font avec un compte visiteur.') }}</p>
                @else
                    <form method="POST" action="{{ route('leisure-package.book', $package->slug) }}" id="pk-form" data-slots="{{ route('leisure-package.slots', $package->slug) }}">
                        @csrf
                        <label for="pk-date" class="tp-label">{{ __('Date') }}</label>
                        <input id="pk-date" type="date" name="date" required min="{{ now('Africa/Abidjan')->addDays(2)->toDateString() }}" value="{{ old('date', now('Africa/Abidjan')->next(\Carbon\Carbon::SATURDAY)->toDateString()) }}" class="tp-input">
                        <span class="tp-label">{{ __('Heure de début') }}</span>
                        <div class="lx-slots" id="pk-slots" role="radiogroup" aria-label="{{ __('Heure de début') }}"></div>
                        <input type="hidden" name="time" id="pk-time" value="{{ old('time') }}">
                        <label for="pk-n" class="tp-label">{{ __('Nombre de participants') }}</label>
                        <input id="pk-n" type="number" name="participants" required min="{{ $package->min_participants }}" max="{{ $package->max_participants ?: 500 }}" value="{{ old('participants', max($package->min_participants, $package->included_participants)) }}" class="tp-input">
                        @if($package->kind === 'birthday')
                            <label for="pk-c" class="tp-label">{{ __('Prénom de l\'enfant fêté') }}</label>
                            <input id="pk-c" type="text" name="celebrant_name" maxlength="120" value="{{ old('celebrant_name') }}" class="tp-input">
                        @endif
                        <label for="pk-p" class="tp-label">{{ __('Téléphone') }}</label>
                        <input id="pk-p" type="tel" name="phone" required maxlength="30" value="{{ old('phone', $user->phone) }}" class="tp-input">
                        <label for="pk-m" class="tp-label">{{ __('Demandes particulières') }}</label>
                        <textarea id="pk-m" name="message" rows="2" maxlength="1000" class="tp-input" placeholder="{{ __('Allergies, thème, gâteau…') }}">{{ old('message') }}</textarea>
                        <span class="tp-label">{{ __('Paiement') }}</span>
                        <div class="tp-choice">
                            <label><input type="radio" name="payment_method" value="cinetpay" @checked(old('payment_method', 'cinetpay') === 'cinetpay')>{{ __('Mobile Money / carte') }}<small>CinetPay</small></label>
                            <label><input type="radio" name="payment_method" value="wallet" @checked(old('payment_method') === 'wallet')>{{ __('Mon solde') }}<small>Trésors d'Ivoire</small></label>
                        </div>
                        <div class="tp-sum" id="pk-sum" aria-live="polite"></div>
                        @if($errors->leisure->any())<p class="tp-err">{{ $errors->leisure->first() }}</p>@endif
                        <button type="submit" class="tp-submit"><i class="fas fa-cake-candles mr-1.5" aria-hidden="true"></i><span id="pk-label">{{ __('Réserver') }}</span></button>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</div>
<script>
(function () {
    const form = document.getElementById('pk-form'); if (!form) return;
    const P = { included: {{ $package->included_participants }}, extra: {{ (int) $package->extra_participant_xof }}, deposit: {{ (int) $package->deposit_xof }} };
    const T = {{ \Illuminate\Support\Js::from(['none' => __('Aucun horaire ce jour-là.'), 'total' => __('Total'), 'now' => __('À payer maintenant'), 'site' => __('Solde sur place'), 'pay' => __('Réserver et payer :a'), 'extra' => __('participant(s) en plus'), 'base' => __('Forfait')]) }};
    const fmt = n => Number(n).toLocaleString('fr-FR') + ' FCFA';
    const date = document.getElementById('pk-date'), box = document.getElementById('pk-slots'), time = document.getElementById('pk-time'), n = document.getElementById('pk-n');
    let base = 0;
    function load() {
        fetch(form.dataset.slots + '?date=' + date.value, { headers: { Accept: 'application/json' } }).then(r => r.json()).then(j => {
            base = j.base_price;
            box.innerHTML = (j.slots || []).map(s => '<button type="button" class="lx-slot' + (time.value === s.time && s.available ? ' is-active' : '') + '" data-time="' + s.time + '"' + (s.available ? '' : ' disabled') + '>' + s.time.replace(':', ' h ') + '</button>').join('') || '<p class="tp-note">' + T.none + '</p>';
            if (!(j.slots || []).some(s => s.available && s.time === time.value)) time.value = '';
            sum();
        });
    }
    box.addEventListener('click', e => { const b = e.target.closest('.lx-slot'); if (!b || b.disabled) return; time.value = b.dataset.time; box.querySelectorAll('.lx-slot').forEach(x => x.classList.toggle('is-active', x === b)); });
    function sum() {
        const extra = Math.max(0, (+n.value || 0) - P.included), total = base + extra * P.extra, now = P.deposit ? Math.min(P.deposit, total) : total;
        document.getElementById('pk-sum').innerHTML = '<p><span>' + T.base + '</span><span>' + fmt(base) + '</span></p>' + (extra ? '<p><span>' + extra + ' ' + T.extra + '</span><span>' + fmt(extra * P.extra) + '</span></p>' : '')
            + '<p class="is-total"><span>' + T.total + '</span><span>' + fmt(total) + '</span></p><p class="is-now"><span>' + T.now + '</span><span>' + fmt(now) + '</span></p>'
            + (total > now ? '<p><span>' + T.site + '</span><span>' + fmt(total - now) + '</span></p>' : '');
        document.getElementById('pk-label').textContent = T.pay.replace(':a', fmt(now));
    }
    date.addEventListener('change', load); n.addEventListener('input', sum); load();
})();
</script>
@include('partials.homepage-footer')
</body>
</html>
