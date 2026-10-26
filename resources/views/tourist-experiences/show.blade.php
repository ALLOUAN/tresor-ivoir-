<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="tvp-user-name" content="{{ trim(auth()->user()->first_name.' '.auth()->user()->last_name) }}">
        <meta name="tvp-user-email" content="{{ auth()->user()->email }}">
        <meta name="tvp-user-phone" content="{{ auth()->user()->phone }}">
    @endauth
    <title>{{ $experience->name }} — {{ __('Sites Touristiques') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => $experience->name, 'description' => $experience->short_description ?: $experience->description, 'image' => $experience->cover_image ?: ($experience->media->first()?->url ?: $experience->thumbnail)])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .btn-primary { background: #00874F; box-shadow: 0 12px 28px rgba(0,135,79,0.3); color: #fff; transition: transform .2s ease, filter .2s ease; }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .hero-cover { position: relative; overflow: hidden; background: #0E3B2A; }
        .hero-cover img { transition: transform .6s ease; }
        .hero-cover:hover img { transform: scale(1.05); }
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: .85rem; }
        .gallery-tile img { transition: transform .4s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }
        .bento-card { border: 1px solid rgba(14,59,42,0.07); background: #FFFFFF; box-shadow: 0 10px 28px rgba(14,59,42,0.06); }
        .bento-icon { width: 2.2rem; height: 2.2rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; background: rgba(0,135,79, 0.12); color: #00874F; }
        .section-kicker { letter-spacing: .22em; }
        .review-card { border: 1px solid rgba(14,59,42,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(14,59,42,0.05); }
        .related-tile { border: 1px solid rgba(14,59,42,0.07); background: #ffffff; box-shadow: 0 10px 26px rgba(14,59,42,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .related-tile:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(0,135,79,0.16); }
        .contact-card { border: 1px solid rgba(14,59,42,0.08); background: #FFFFFF; box-shadow: 0 20px 50px rgba(14,59,42,0.1); }
    </style>
    @include('partials.detail-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        @php
            $photos = $experience->media;
            $mainPhoto = $photos->first()?->url ?: $experience->cover_image;
        @endphp

        @include('partials.detail-hero', ['model' => $experience, 'sectorName' => __('Sites Touristiques'), 'sectorIcon' => 'fa-mountain-sun', 'backUrl' => route('tourist-experience.index'), 'shareUrl' => route('tourist-experience.show', $experience->slug), 'bookAnchor' => '#tvp-booking-module', 'items' => ($activitiesByCategory ?? collect())->flatten(1)])

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start mt-8">
            <div class="lg:col-span-3 space-y-12 dx-main">
                @include('partials.detail-tabs', ['offerLabel' => __('Activités')])

                {{-- ── EN-TÊTE + DESCRIPTION ──────────────────────────────── --}}
                <section class="reveal" id="apercu">
                    @include('partials.detail-overview', ['model' => $experience])
                </section>

                {{-- ── ÉQUIPEMENTS ────────────────────────────────────────── --}}
                @if(!empty($experience->amenities))
                <section class="reveal" id="equipements">
                    <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('Confort') }}</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">{{ __('Équipements & services') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($experience->amenities as $am)
                        <div class="flex items-center gap-2.5 bento-card rounded-xl p-3">
                            <span class="hx-amen__icon"><i class="{{ $am['icon'] ?? 'fas fa-check' }}" aria-hidden="true"></i></span>
                            <span class="text-sm font-medium truncate">{{ $am['label'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ── ACTIVITÉS PROPOSÉES ────────────────────────────────── --}}
                @if($activitiesByCategory->isNotEmpty())
                <section class="reveal" id="offre">
                    @include('partials.detail-offers', ['groups' => $activitiesByCategory, 'itemUrl' => fn ($i) => $i->slug ? route('activity.show', $i->slug) : null, 'kicker' => __('À vivre sur place'), 'title' => __('Activités proposées'), 'pageUrl' => route('tourist-experience.activities', $experience->slug), 'askUrl' => route('tourist-experience.show', $experience->slug), 'itemIcon' => 'fa-person-hiking', 'meta' => fn ($i) => array_values(array_filter([$i->duration_minutes ? ['fa-clock', $i->duration_minutes.' '.__('min')] : null, $i->max_participants ? ['fa-user', $i->max_participants.' '.__('pers. max')] : null]))])
                </section>
                @endif

                {{-- ── LOCALISATION ─────────────────────────────────────────── --}}
                <section class="reveal" id="localisation">
                    <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('Où se trouve le site') }}</p>
                    <h2 class="font-serif text-2xl font-bold mb-4">{{ __('Localisation') }}</h2>
                    @if($experience->latitude && $experience->longitude)
                        @include('partials.route-map', ['type' => 'experience', 'slug' => $experience->slug, 'lat' => $experience->latitude, 'lng' => $experience->longitude, 'name' => $experience->name, 'accent' => '#EB6E00'])
                        <a href="{{ route('directions.go', ['experience', $experience->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer"
                           class="dx-route mt-3"><i class="fas fa-diamond-turn-right" aria-hidden="true"></i> {{ __('Itinéraire') }}
                        </a>
                    @elseif($experience->city)
                        <div class="hx-map">
                            <iframe src="https://www.google.com/maps?q={{ urlencode($experience->city->name) }}&z=12&output=embed" title="{{ __('Carte : :name', ['name' => $experience->city->name]) }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <div class="dx-map-card">
                                <p><span class="hx-facts__icon"><i class="fas fa-location-dot" aria-hidden="true"></i></span><span><small>{{ __('Adresse') }}</small>{{ $experience->adresse ?: $experience->city->name }}</span></p>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(trim($experience->name.' '.($experience->adresse ?: $experience->city->name).' Côte d\'Ivoire')) }}" target="_blank" rel="noopener noreferrer" class="dx-route"><i class="fas fa-diamond-turn-right" aria-hidden="true"></i>{{ __('Itinéraire') }}</a>
                            </div>
                        </div>
                    @else
                        <p class="text-[#557466] text-sm">{{ __('Coordonnées non renseignées pour ce site.') }}</p>
                    @endif
                </section>

                {{-- ── AVIS CLIENTS ──────────────────────────────────────────── --}}
                @if($reviews->isNotEmpty())
                <section class="reveal" id="avis">
                    @include('partials.detail-reviews', ['reviews' => $reviews, 'model' => $experience])
                </section>
                @endif
            </div>{{-- fin col principale --}}

            {{-- ═══════════════ COLONNE LATÉRALE ═══════════════ --}}
            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24 space-y-3 reveal">
                    {{-- Toujours affiché : sans type de visite, on explique et on renvoie vers le contact. --}}
                    @if(true)
                    <div class="contact-card rounded-3xl p-5" id="tvp-booking-module" data-slug="{{ $experience->slug }}">
                        <h2 class="font-serif text-xl font-bold mb-1">{{ __('Réserver une visite') }}</h2>
                        <p class="text-[#557466] text-xs mb-4">{{ __('Choisissez le mode de visite qui vous convient.') }}</p>

                        @php
                            // Modes réellement réservables : la visite groupée exige au moins une session à venir.
                            $tvpGroupOpen = $experience->visit_group_enabled
                                && $experience->visitSessions()->where('is_active', true)->whereDate('session_date', '>=', today())->exists();
                            $tvpBookable = $experience->visit_individual_enabled || $experience->visit_guided_enabled || $tvpGroupOpen;
                            $tvpUnit = (int) ($experience->visit_individual_price_xof ?? 0);
                            $tvpPrice = fn (int $xof) => $xof > 0 ? number_format($xof, 0, ',', ' ').' FCFA' : __('Gratuit');
                            // Types de visite proposés, présentés en cartes de choix.
                            $tvpCustom = $experience->visitTypes()->active()->get();
                            $tvpFmtMin = fn (?int $m) => $m ? ($m >= 60 ? intdiv($m, 60).' h'.($m % 60 ? ' '.str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT) : '') : $m.' min') : null;
                            $tvpModes = $tvpCustom->map(fn ($t) => [
                                'mode' => 'custom', 'id' => $t->id, 'icon' => $t->iconClass(), 'label' => $t->name, 'type' => $t,
                                'hint' => collect([$t->description, $tvpFmtMin($t->duration_minutes), $t->max_participants ? __(':n pers. max', ['n' => $t->max_participants]) : null])->filter()->join(' · '),
                                'price' => ($t->hasTiers() && $t->fromPrice() > 0 ? __('dès').' ' : '').$tvpPrice($t->hasTiers() ? $t->fromPrice() : (int) $t->price_xof),
                                'unit' => collect([(int) $t->price_xof > 0 || $t->fromPrice() > 0 ? '/ '.__('pers.') : null, $t->appliedGuideSupplement() > 0 ? '+ '.$tvpPrice($t->appliedGuideSupplement()).' '.__('guide') : null])->filter()->join(' '),
                                'badges' => array_values(array_filter([
                                    $t->cancellationLabel() ? ['icon' => 'fa-rotate-left', 'text' => $t->cancellationLabel(), 'tone' => 'ok'] : null,
                                    $t->allow_pay_on_site ? ['icon' => 'fa-hand-holding-dollar', 'text' => __('Payer sur place possible'), 'tone' => 'ok'] : null,
                                    $t->hasSlots() ? ['icon' => 'fa-clock', 'text' => __('Entrée à heure fixe'), 'tone' => ''] : null,
                                    $t->languagesList() ? ['icon' => 'fa-language', 'text' => implode(', ', array_slice($t->languagesList(), 0, 3)).(count($t->languagesList()) > 3 ? '…' : ''), 'tone' => ''] : null,
                                    $t->weekend_adjust_percent < 0 ? ['icon' => 'fa-calendar-week', 'text' => __('Week-end :p %', ['p' => $t->weekend_adjust_percent]), 'tone' => 'deal'] : null,
                                    $t->offpeak_discount_percent ? ['icon' => 'fa-sun', 'text' => __('−:p % avant :h', ['p' => $t->offpeak_discount_percent, 'h' => str_replace(':', ' h ', $t->offpeak_until)]), 'tone' => 'deal'] : null,
                                ])),
                                'config' => [
                                    'tiers' => $t->tiers(), 'addons' => $t->addonsList(), 'languages' => $t->languagesList(),
                                    'slots' => $t->hasSlots(), 'guide' => $t->appliedGuideSupplement(), 'max' => $t->max_participants,
                                    'payOnSite' => (bool) $t->allow_pay_on_site, 'cancel' => $t->cancellationLabel(),
                                ],
                                'unit_xof' => (int) $t->price_xof, 'guide_xof' => $t->appliedGuideSupplement(), 'max' => $t->max_participants,
                            ])->all();
                            $tvpModes = array_values(array_filter(array_merge($tvpModes, [
                                $tvpCustom->isEmpty() && $experience->visit_individual_enabled ? ['mode' => 'individual', 'icon' => 'fa-person-walking', 'label' => __('Individuelle'), 'hint' => __('À votre rythme, à la date de votre choix'), 'price' => $tvpPrice($tvpUnit), 'unit' => $tvpUnit > 0 ? '/ '.__('pers.') : null] : null,
                                $tvpCustom->isEmpty() && $experience->visit_guided_enabled ? ['mode' => 'guided', 'icon' => 'fa-user-tie', 'label' => __('Avec guide'), 'hint' => __('Accompagné d\'un guide du site'), 'price' => $tvpPrice($tvpUnit), 'unit' => (int) $experience->visit_guide_supplement_xof > 0 ? '+ '.$tvpPrice((int) $experience->visit_guide_supplement_xof).' '.__('guide') : null] : null,
                                $tvpGroupOpen ? ['mode' => 'group', 'icon' => 'fa-people-group', 'label' => __('Groupée'), 'hint' => __('Sessions à date fixe avec d\'autres visiteurs'), 'price' => __('Selon la session'), 'unit' => null] : null,
                            ])));
                            $tvpBookable = $tvpBookable || $tvpCustom->isNotEmpty();
                        @endphp
                        @if(empty($tvpModes))
                            <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-4 text-sm text-[#24503D]">
                                <p class="m-0"><i class="fas fa-circle-info mr-1 text-[#EB6E00]"></i>{{ __('Ce site ne propose pas encore de réservation en ligne. Contactez-le pour organiser votre visite.') }}</p>
                                <a href="#contact-card" class="dx-route mt-3"><i class="fas fa-comment-dots" aria-hidden="true"></i>{{ __('Contacter le site') }}</a>
                            </div>
                            @php
                                $tvpManageUrl = match (true) {
                                    auth()->user()?->role === 'admin' => route('admin.tourist-experiences.visit-types.index', $experience),
                                    auth()->check() && (int) $experience->provider?->user_id === (int) auth()->id() => route('provider.tourist-experience.visit-types.index'),
                                    default => null,
                                };
                            @endphp
                            @if($tvpManageUrl)
                                <a href="{{ $tvpManageUrl }}" class="mt-3 flex items-center gap-2 rounded-2xl bg-[#F3F8F5] border border-[#EB6E00]/60 px-4 py-3 text-sm font-semibold text-[#B85600]">
                                    <i class="fas fa-ticket-simple"></i>{{ __('Créer les types de visite de ce site') }}<i class="fas fa-arrow-right ml-auto text-xs"></i>
                                </a>
                            @endif
                        @else
                        @auth
                            @if(auth()->user()->role !== 'visitor')
                                {{-- Comptes prestataire / admin / éditeur : on montre ce que voit le client, sans formulaire. --}}
                                @include('partials.tvp-preview')
                                <div class="rounded-2xl bg-[#F3F8F5] border border-[#EB6E00]/60 px-4 py-3 text-sm text-[#B85600]">
                                    <i class="fas fa-circle-info mr-1"></i>
                                    {{ __('Vous êtes connecté avec un compte :role. Les réservations se font avec un compte visiteur.', ['role' => match (auth()->user()->role) { 'provider' => __('prestataire'), 'admin' => __('administrateur'), 'editor' => __('éditeur'), default => auth()->user()->role }]) }}
                                </div>
                            @elseif(! $tvpBookable)
                                <p class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-4 text-sm text-[#24503D] text-center">
                                    {{ __('Aucune date de visite n\'est ouverte pour le moment. Revenez bientôt ou contactez le site ci-dessous.') }}
                                </p>
                            @else
                                <p class="tvp-step">{{ __('Choisissez le type de visite') }}</p>
                                <div class="tvp-modes mb-4" id="tvp-tabs" role="radiogroup" aria-label="{{ __('Type de visite') }}">
                                    @foreach($tvpModes as $m)
                                        <button type="button" data-mode="{{ $m['mode'] }}" class="tvp-tab" role="radio" aria-checked="false"
                                                @if($m['mode'] === 'custom') data-type-id="{{ $m['id'] }}" data-unit="{{ $m['unit_xof'] }}" data-guide="{{ $m['guide_xof'] }}" data-max="{{ $m['max'] }}" data-config="{{ json_encode($m['config']) }}" @endif>
                                            <span class="tvp-tab__radio" aria-hidden="true"></span>
                                            <span class="tvp-tab__icon"><i class="fas {{ $m['icon'] }}" aria-hidden="true"></i></span>
                                            <span class="tvp-tab__txt"><strong>{{ $m['label'] }}</strong>@if($m['hint'])<small>{{ $m['hint'] }}</small>@endif
                                                </span>
<span class="tvp-tab__price">{{ $m['price'] }}@if($m['unit'])<small>{{ $m['unit'] }}</small>@endif</span>
@if(! empty($m['badges']))<span class="tvp-badges">@foreach($m['badges'] as $b)<span class="tvp-badge {{ $b['tone'] ? 'is-'.$b['tone'] : '' }}"><i class="fas {{ $b['icon'] }}" aria-hidden="true"></i>{{ $b['text'] }}</span>@endforeach</span>@endif
                                        </button>
                                    @endforeach
                                </div>
                                @foreach($tvpModes as $m)
                                    @if($m['mode'] === 'custom' && $m['type']->hasDetails())
                                        <div class="tvp-info mb-4" data-info-for="{{ $m['id'] }}" hidden>@include('partials.tvp-type-details', ['t' => $m['type']])</div>
                                    @endif
                                @endforeach

                                <form id="tvp-form" class="space-y-3">
                                    <div id="tvp-panel-custom" class="space-y-3 hidden">
                                        <div>
                                            <p class="tvp-step">{{ __('Date de la visite') }}</p>
                                            <input type="date" id="tvp-c-date" min="{{ now('Africa/Abidjan')->toDateString() }}" value="{{ now('Africa/Abidjan')->toDateString() }}"
                                                   class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm" aria-label="{{ __('Date de la visite') }}">
                                        </div>
                                        <div id="tvp-slots-wrap" class="hidden">
                                            <p class="tvp-step">{{ __('Heure d\'entrée') }}</p>
                                            <div id="tvp-slots" class="tvp-slots" role="radiogroup" aria-label="{{ __('Heure d\'entrée') }}"></div>
                                            <p class="tvp-legend"><span class="tvp-legend__dot"></span>{{ __('Disponible') }}<span class="tvp-legend__dot is-off"></span>{{ __('Complet ou passé') }}</p>
                                        </div>
                                        <p id="tvp-day-status" class="text-[12px] text-[#557466] hidden"></p>
                                        <div>
                                            <p class="tvp-step">{{ __('Participants') }}</p>
                                            <div id="tvp-tiers" class="tvp-tiers"></div>
                                        </div>
                                        <div id="tvp-lang-wrap" class="hidden">
                                            <label for="tvp-language" class="tvp-step block">{{ __('Langue de la visite') }}</label>
                                            <select id="tvp-language" class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm"></select>
                                        </div>
                                        <div id="tvp-addons-wrap" class="hidden">
                                            <p class="tvp-step">{{ __('Options') }}</p>
                                            <div id="tvp-addons" class="tvp-addons"></div>
                                        </div>
                                    </div>

                                    <div id="tvp-panel-solo" class="space-y-3">
                                        <div>
                                            <label class="block text-[#557466] text-[11px] mb-1">{{ __('Date souhaitée (optionnel)') }}</label>
                                            <input type="date" name="desired_date" min="{{ now()->toDateString() }}"
                                                   class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm">
                                        </div>
                                    </div>

                                    <div id="tvp-panel-group" class="space-y-2 hidden">
                                        <label class="block text-[#557466] text-[11px] mb-1">{{ __('Session') }}</label>
                                        <div id="tvp-sessions" class="space-y-1.5 max-h-56 overflow-y-auto"></div>
                                    </div>

                                    <div id="tvp-participants-wrap">
                                        <label class="block text-[#557466] text-[11px] mb-1">{{ __('Nombre de participants') }}</label>
                                        <input type="number" name="participants_count" value="1" min="1" max="50"
                                               class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm" id="tvp-participants">
                                    </div>
                                    @if($experience->visit_child_price_xof !== null || $experience->visit_resident_price_xof !== null)
                                    <div id="tvp-rates" class="grid grid-cols-2 gap-2">
                                        @if($experience->visit_child_price_xof !== null)
                                        <div>
                                            <label class="block text-[#557466] text-[11px] mb-1">{{ __('dont enfants (−12 ans)') }} · {{ number_format($experience->visit_child_price_xof, 0, ',', ' ') }} F</label>
                                            <input type="number" name="children_count" value="0" min="0" max="50" id="tvp-children"
                                                   class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm">
                                        </div>
                                        @endif
                                        @if($experience->visit_resident_price_xof !== null)
                                        <div>
                                            <label class="block text-[#557466] text-[11px] mb-1">{{ __('dont résidents') }} · {{ number_format($experience->visit_resident_price_xof, 0, ',', ' ') }} F</label>
                                            <input type="number" name="residents_count" value="0" min="0" max="50" id="tvp-residents"
                                                   class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm">
                                        </div>
                                        @endif
                                        <p class="col-span-2 text-[11px] text-[#557466]">{{ __('Résidents de Côte d\'Ivoire / CEDEAO : pièce justificative demandée à l\'entrée.') }}</p>
                                    </div>
                                    @endif

                                    <div>
                                        <label class="block text-[#557466] text-[11px] mb-1">{{ __('Message (optionnel)') }}</label>
                                        <textarea name="message" rows="2" maxlength="1000"
                                                  class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm"></textarea>
                                    </div>

                                    <div class="rounded-xl bg-black/[0.03] border border-black/8 px-3.5 py-3 text-sm">
                                        <div id="tvp-breakdown" class="tvp-breakdown hidden"></div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[#557466]">{{ __('Total') }}</span>
                                            <span class="font-bold" id="tvp-total">—</span>
                                        </div>
                                    </div>

                                    <div id="tvp-payment-method" class="hidden grid grid-cols-2 gap-2">
                                        <button type="button" data-method="cinetpay" class="tvp-method px-3 py-2.5 rounded-xl text-xs font-bold border border-black/10">{{ __('CinetPay') }}</button>
                                        <button type="button" data-method="wallet" class="tvp-method px-3 py-2.5 rounded-xl text-xs font-bold border border-black/10">{{ __('Mon solde') }}</button>
                                        <button type="button" data-method="on_site" class="tvp-method col-span-2 px-3 py-2.5 rounded-xl text-xs font-bold border border-black/10 hidden" id="tvp-method-onsite">
                                            <i class="fas fa-hand-holding-dollar mr-1" aria-hidden="true"></i>{{ __('Réserver maintenant, payer sur place') }}
                                        </button>
                                    </div>
                                    <p id="tvp-cancel-note" class="text-[12px] text-[#00874F] hidden"><i class="fas fa-rotate-left mr-1" aria-hidden="true"></i><span></span></p>

                                    <p id="tvp-error" class="text-red-600 text-xs hidden"></p>

                                    <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                        <i class="fas fa-ticket text-sm"></i> <span id="tvp-submit-label">{{ __('Réserver') }}</span>
                                    </button>
                                </form>
                            @endif
                        @else
                            @include('partials.tvp-preview')
                            <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
                                <p class="text-[#0E3B2A] font-bold text-sm mb-1">{{ __('Connectez-vous pour réserver une visite') }}</p>
                                <div class="flex items-center justify-center gap-2 mt-3">
                                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                        <i class="fas fa-right-to-bracket text-xs"></i> {{ __('Se connecter') }}
                                    </a>
                                    <a href="{{ route('register', ['role' => 'visitor', 'redirect' => request()->getRequestUri()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-orange-400/50 text-[#0E3B2A] transition">
                                        {{ __('Créer un compte') }}
                                    </a>
                                </div>
                            </div>
                        @endauth
                        @endif
                    </div>
                    @endif

                    <div class="contact-card rounded-3xl p-5" id="contact-card">
                        <h2 class="font-serif text-xl font-bold mb-1">{{ __('Contacter ce prestataire') }}</h2>
                        <p class="text-[#557466] text-xs mb-4">{{ __('Posez vos questions directement, en toute sécurité, sans quitter la plateforme.') }}</p>
                        {{-- Arrivée depuis « Contacter à ce sujet » (?item=…) : rappel de l'élément choisi. --}}
                        @if(is_string(request('item')) && request('item') !== '')
                        <div class="ti-contact-item">
                            <i class="fas fa-tag"></i>
                            <span>{{ __('Votre demande concerne') }} <strong>{{ str(request('item'))->limit(80) }}</strong></span>
                        </div>
                        @endif

                        @if($experience->provider)
                            @auth
                                @if(auth()->user()->role === 'visitor')
                                    <form method="POST" action="{{ route('visitor.conversations.store') }}" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $experience->provider->id }}">
                                        <textarea name="message" id="contact-message" rows="3" required maxlength="4000"
                                                  class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-[#8AA99B]"
                                                  placeholder="{{ __('Votre message...') }}"></textarea>
                                        <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                            <i class="fas fa-paper-plane text-sm"></i> {{ __('Envoyer le message') }}
                                        </button>
                                    </form>
                                @else
                                    <p class="dx-note"><i class="fas fa-circle-info mr-1" aria-hidden="true"></i>{{ __('La messagerie avec les établissements est réservée aux comptes visiteurs.') }}</p>
                                @endif
                            @else
                                <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
                                    <p class="text-[#0E3B2A] font-bold text-sm mb-1">{{ __('Connectez-vous pour contacter ce prestataire') }}</p>
                                    <div class="flex items-center justify-center gap-2 mt-3">
                                        <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                            <i class="fas fa-right-to-bracket text-xs"></i> {{ __('Se connecter') }}
                                        </a>
                                        <a href="{{ route('register', ['role' => 'visitor', 'redirect' => request()->getRequestUri()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-orange-400/50 text-[#0E3B2A] transition">
                                            {{ __('Créer un compte') }}
                                        </a>
                                    </div>
                                </div>
                            @endauth
                        @else
                            @if($experience->phone)
                            <a href="tel:{{ $experience->phone }}" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                <i class="fas fa-phone text-sm"></i> {{ $experience->phone }}
                            </a>
                            @else
                            <p class="text-[#557466] text-sm">{{ __('Aucun moyen de contact renseigné.') }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
        <section class="mt-16 reveal">
            <p class="section-kicker text-[#EB6E00] text-xs font-bold uppercase mb-2">{{ __('À découvrir aussi') }}</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">{{ __('Sites similaires') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($related as $r)
                    @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($r, 'sites-touristiques')])
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0E3B2A]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

    <div class="pb-4">
@include('partials.homepage-footer')
    </div>
@include('partials.place-card-script')
@include('partials.image-protection')
<script>
function openLightbox(url) {
    document.getElementById('lightbox-img').src = url;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Depuis la page dédiée : pré-remplit le message de contact (?item=...) ──
(function () {
    const item = new URLSearchParams(window.location.search).get('item');
    if (!item) return;
    const ta = document.getElementById('contact-message');
    if (ta) ta.value = {{ \Illuminate\Support\Js::from(__('Je suis intéressé par : ')) }} + item;
})();

// ── Module de réservation de visite ──────────────────────────────────────
(function () {
    const root = document.getElementById('tvp-booking-module');
    if (!root) return;

    const slug = root.dataset.slug;
    const form = document.getElementById('tvp-form');
    if (!form) return;

    const T = {{ \Illuminate\Support\Js::from([
        'free' => __('Gratuit'), 'pay' => __('Réserver et payer'), 'payFree' => __('Réserver gratuitement'), 'onSite' => __('Réserver, payer sur place'),
        'loading' => __('Chargement…'), 'full' => __('Complet'), 'past' => __('Passé'), 'left' => __('place(s)'), 'leftOne' => __('Dernière place'),
        'dayFull' => __('Plus de place ce jour-là. Choisissez une autre date.'), 'dayLeft' => __('Places restantes ce jour-là :'),
        'pickSlot' => __('Choisissez une heure d\'entrée.'), 'pickPeople' => __('Indiquez au moins un participant.'), 'pickSession' => __('Veuillez choisir une session.'),
        'pickDate' => __('Choisissez la date de votre visite.'), 'noSlot' => __('Aucun créneau disponible ce jour-là. Choisissez une autre date.'),
        'perPerson' => __('/ pers.'), 'perBooking' => __('/ réservation'), 'guide' => __('Supplément guide'), 'weekend' => __('Tarif week-end'), 'offpeak' => __('Tarif réduit'),
        'max' => __('Maximum :n participants pour ce type de visite.'), 'error' => __('Une erreur est survenue.'), 'retry' => __('Une erreur est survenue. Merci de réessayer.'),
        'loadError' => __('Erreur de chargement des créneaux.'), 'minus' => __('Retirer un'), 'plus' => __('Ajouter un'),
    ]) }};
    const fmt = n => Number(n).toLocaleString('fr-FR') + ' FCFA';
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const tabs = Array.from(document.querySelectorAll('.tvp-tab'));
    const panelSolo = document.getElementById('tvp-panel-solo');
    const panelGroup = document.getElementById('tvp-panel-group');
    const panelCustom = document.getElementById('tvp-panel-custom');
    const sessionsBox = document.getElementById('tvp-sessions');
    const participantsInput = document.getElementById('tvp-participants');
    const participantsWrap = document.getElementById('tvp-participants-wrap');
    const totalEl = document.getElementById('tvp-total');
    const breakdownEl = document.getElementById('tvp-breakdown');
    const errorEl = document.getElementById('tvp-error');
    const submitLabel = document.getElementById('tvp-submit-label');
    const paymentMethodBox = document.getElementById('tvp-payment-method');
    const methodButtons = Array.from(document.querySelectorAll('.tvp-method'));
    const onSiteBtn = document.getElementById('tvp-method-onsite');
    const cancelNote = document.getElementById('tvp-cancel-note');
    const cDate = document.getElementById('tvp-c-date');
    const slotsWrap = document.getElementById('tvp-slots-wrap');
    const slotsBox = document.getElementById('tvp-slots');
    const dayStatus = document.getElementById('tvp-day-status');
    const tiersBox = document.getElementById('tvp-tiers');
    const langWrap = document.getElementById('tvp-lang-wrap');
    const langSelect = document.getElementById('tvp-language');
    const addonsWrap = document.getElementById('tvp-addons-wrap');
    const addonsBox = document.getElementById('tvp-addons');

    let activeTab = tabs[0] || null;
    let mode = activeTab?.dataset.mode || 'individual';
    let cfg = null;              // configuration du type personnalisé choisi
    let qty = [];                // quantités par catégorie de tarif
    let slot = null;             // créneau choisi { time, remaining, adjust_percent }
    let dayInfo = null;          // entrée « journée » quand le type n'a pas de créneaux
    let selectedSessionId = null;
    let selectedSessionPrice = 0;
    let paymentMethod = 'cinetpay';
    let pricing = { unit: {{ (int) ($experience->visit_individual_price_xof ?? 0) }}, guideSupplement: {{ (int) ($experience->visit_guide_supplement_xof ?? 0) }}, child: {{ (int) ($experience->visit_child_price_xof ?? 0) }}, resident: {{ (int) ($experience->visit_resident_price_xof ?? 0) }} };
    const childrenInput = document.getElementById('tvp-children');
    const residentsInput = document.getElementById('tvp-residents');
    const ratesBox = document.getElementById('tvp-rates');

    function setMethod(method) {
        paymentMethod = method;
        methodButtons.forEach(b => {
            const on = b.dataset.method === method;
            b.classList.toggle('bg-[#EB6E00]', on);
            b.classList.toggle('text-white', on);
            b.classList.toggle('is-active', on);
        });
    }

    function setActiveTab() {
        tabs.forEach(btn => {
            const active = btn === activeTab;
            btn.classList.toggle('bg-[#EB6E00]', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('text-[#0E3B2A]', !active);
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-checked', active ? 'true' : 'false');
        });
        const custom = mode === 'custom';
        panelGroup.classList.toggle('hidden', mode !== 'group');
        panelSolo.classList.toggle('hidden', mode === 'group' || custom);
        panelCustom.classList.toggle('hidden', !custom);
        participantsWrap.classList.toggle('hidden', custom);
        document.querySelectorAll('.tvp-info').forEach(el => { el.hidden = !(custom && el.dataset.infoFor === activeTab.dataset.typeId); });

        cfg = custom ? JSON.parse(activeTab.dataset.config || '{}') : null;
        onSiteBtn?.classList.toggle('hidden', !(cfg && cfg.payOnSite));
        if (paymentMethod === 'on_site' && !(cfg && cfg.payOnSite)) setMethod('cinetpay');
        cancelNote.classList.toggle('hidden', !(cfg && cfg.cancel));
        if (cfg && cfg.cancel) cancelNote.querySelector('span').textContent = cfg.cancel;

        if (custom) buildCustom();
        if (mode === 'group') loadSessions();
        recomputeTotal();
    }

    // ── Type personnalisé : catégories de tarif, langues, options, créneaux ──
    function buildCustom() {
        qty = cfg.tiers.map((t, i) => (i === 0 ? 1 : 0));
        tiersBox.innerHTML = cfg.tiers.map((t, i) =>
            '<div class="tvp-tier"><span class="tvp-tier__txt"><strong>' + esc(t.label) + '</strong><small>' + (t.price_xof > 0 ? fmt(t.price_xof) : T.free) + '</small></span>' +
            '<span class="tvp-stepper"><button type="button" data-step="-1" data-i="' + i + '" aria-label="' + esc(T.minus + ' ' + t.label) + '"><i class="fas fa-minus"></i></button>' +
            '<output data-qty="' + i + '" aria-live="polite">' + qty[i] + '</output>' +
            '<button type="button" data-step="1" data-i="' + i + '" aria-label="' + esc(T.plus + ' ' + t.label) + '"><i class="fas fa-plus"></i></button></span></div>'
        ).join('');

        langWrap.classList.toggle('hidden', cfg.languages.length < 2);
        langSelect.innerHTML = cfg.languages.map(l => '<option>' + esc(l) + '</option>').join('');

        addonsWrap.classList.toggle('hidden', !cfg.addons.length);
        addonsBox.innerHTML = cfg.addons.map((a, i) =>
            '<label class="tvp-addon"><input type="checkbox" value="' + i + '" data-addon>' +
            '<span class="tvp-addon__txt">' + esc(a.label) + '</span>' +
            '<span class="tvp-addon__price">+ ' + fmt(a.price_xof) + ' <small>' + (a.per === 'booking' ? T.perBooking : T.perPerson) + '</small></span></label>'
        ).join('');
        addonsBox.querySelectorAll('[data-addon]').forEach(c => c.addEventListener('change', recomputeTotal));

        loadSlots();
    }

    tiersBox?.addEventListener('click', e => {
        const b = e.target.closest('[data-step]');
        if (!b) return;
        const i = +b.dataset.i;
        const next = Math.max(0, Math.min(50, qty[i] + (+b.dataset.step)));
        const total = qty.reduce((s, q) => s + q, 0) - qty[i] + next;
        if (cfg.max && total > cfg.max) { showError(T.max.replace(':n', cfg.max)); return; }
        hideError();
        qty[i] = next;
        tiersBox.querySelector('[data-qty="' + i + '"]').textContent = next;
        refreshSlotStates();
        recomputeTotal();
    });

    function people() { return qty.reduce((s, q) => s + q, 0); }

    function loadSlots() {
        slot = null; dayInfo = null;
        if (!cDate.value) return;
        slotsWrap.classList.toggle('hidden', !cfg.slots);
        if (cfg.slots) slotsBox.innerHTML = '<p class="text-[#557466] text-xs">' + T.loading + '</p>';
        dayStatus.classList.add('hidden');
        const typeId = activeTab.dataset.typeId;
        fetch('/experiences-touristiques/' + slug + '/visite/creneaux?type=' + typeId + '&date=' + cDate.value, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (activeTab.dataset.typeId !== typeId) return;
                const list = data.slots || [];
                if (!data.has_slots) {
                    dayInfo = list[0] || null;
                    if (dayInfo && dayInfo.remaining !== null) {
                        dayStatus.textContent = dayInfo.remaining > 0 ? T.dayLeft + ' ' + dayInfo.remaining : T.dayFull;
                        dayStatus.classList.remove('hidden');
                    }
                    recomputeTotal();
                    return;
                }
                slotsBox.innerHTML = list.map(s => {
                    const pct = s.adjust_percent ? '<em class="' + (s.adjust_percent < 0 ? 'is-deal' : 'is-up') + '">' + (s.adjust_percent > 0 ? '+' : '−') + Math.abs(s.adjust_percent) + ' %</em>' : '';
                    const sub = s.past ? T.past : (s.remaining === null ? '' : (s.remaining <= 0 ? T.full : (s.remaining === 1 ? T.leftOne : s.remaining + ' ' + T.left)));
                    return '<button type="button" class="tvp-slot" role="radio" aria-checked="false" data-time="' + s.time + '" data-remaining="' + (s.remaining ?? '') + '" data-adjust="' + s.adjust_percent + '"' + (s.available ? '' : ' disabled') + '>' +
                        '<strong>' + s.time.replace(':', ' h ') + '</strong><small>' + sub + '</small>' + pct + '</button>';
                }).join('') || '<p class="text-[#557466] text-xs">' + T.noSlot + '</p>';
                if (list.length && !list.some(s => s.available)) { dayStatus.textContent = T.noSlot; dayStatus.classList.remove('hidden'); }
                refreshSlotStates();
                recomputeTotal();
            })
            .catch(() => { slotsBox.innerHTML = '<p class="text-red-600 text-xs">' + T.loadError + '</p>'; });
    }

    // Un créneau sans assez de places pour le groupe est grisé, comme un créneau complet.
    function refreshSlotStates() {
        if (!slotsBox) return;
        slotsBox.querySelectorAll('.tvp-slot').forEach(b => {
            const rem = b.dataset.remaining === '' ? null : +b.dataset.remaining;
            const tooSmall = rem !== null && rem > 0 && rem < people();
            b.classList.toggle('is-small', tooSmall);
            if (tooSmall && slot && slot.time === b.dataset.time) { slot = null; b.classList.remove('is-active'); b.setAttribute('aria-checked', 'false'); }
        });
    }

    slotsBox?.addEventListener('click', e => {
        const b = e.target.closest('.tvp-slot');
        if (!b || b.disabled || b.classList.contains('is-small')) return;
        slotsBox.querySelectorAll('.tvp-slot').forEach(x => { x.classList.toggle('is-active', x === b); x.setAttribute('aria-checked', x === b ? 'true' : 'false'); });
        slot = { time: b.dataset.time, adjust_percent: +b.dataset.adjust };
        hideError();
        recomputeTotal();
    });
    cDate?.addEventListener('change', () => { if (cfg) loadSlots(); });

    function loadSessions() {
        sessionsBox.innerHTML = '<p class="text-[#557466] text-xs">' + T.loading + '</p>';
        fetch('/experiences-touristiques/' + slug + '/visite/sessions')
            .then(r => r.json())
            .then(data => {
                const sessions = data.sessions || [];
                if (!sessions.length) {
                    sessionsBox.innerHTML = '<p class="text-[#557466] text-xs">Aucune session disponible pour le moment.</p>';
                    return;
                }
                sessionsBox.innerHTML = '';
                sessions.forEach(s => {
                    const full = s.remaining_seats <= 0;
                    const row = document.createElement('label');
                    row.className = 'flex items-center justify-between gap-2 bento-card rounded-xl px-3 py-2.5 text-xs cursor-pointer ' + (full ? 'opacity-40 cursor-not-allowed' : '');
                    row.innerHTML = '<span><input type="radio" name="session_id" value="' + s.id + '" ' + (full ? 'disabled' : '') + ' class="mr-2">' +
                        s.date_label + (s.period_label ? ' · ' + s.period_label : '') + '</span>' +
                        '<span class="font-bold">' + (s.price_per_person_xof ? Number(s.price_per_person_xof).toLocaleString('fr-FR') + ' FCFA' : 'Gratuit') +
                        (full ? ' · Complet' : ' · ' + s.remaining_seats + (s.remaining_seats > 1 ? ' places' : ' place')) + '</span>';
                    const radio = row.querySelector('input');
                    radio.addEventListener('change', () => {
                        selectedSessionId = s.id;
                        selectedSessionPrice = s.price_per_person_xof || 0;
                        recomputeTotal();
                    });
                    sessionsBox.appendChild(row);
                });
            })
            .catch(() => { sessionsBox.innerHTML = '<p class="text-red-600 text-xs">Erreur de chargement des sessions.</p>'; });
    }

    // Même calcul que le serveur : catégories ajustées (week-end / heures creuses) + options + guide.
    function customQuote() {
        const adjust = slot ? slot.adjust_percent : (dayInfo ? dayInfo.adjust_percent : 0);
        const ap = p => adjust ? Math.round(p * (100 + adjust) / 100) : p;
        const n = people();
        const lines = [];
        cfg.tiers.forEach((t, i) => { if (qty[i] > 0) lines.push([qty[i] + ' × ' + t.label, qty[i] * ap(t.price_xof)]); });
        addonsBox.querySelectorAll('[data-addon]:checked').forEach(c => {
            const a = cfg.addons[+c.value];
            const q = a.per === 'booking' ? 1 : n;
            lines.push([(q > 1 ? q + ' × ' : '') + a.label, q * a.price_xof]);
        });
        if (cfg.guide > 0) lines.push([T.guide, cfg.guide]);
        const total = lines.reduce((s, l) => s + l[1], 0);
        return { lines, total, adjust };
    }

    function recomputeTotal() {
        let total;
        if (mode === 'custom' && cfg) {
            const q = customQuote();
            total = q.total;
            breakdownEl.innerHTML = q.lines.map(l => '<p><span>' + esc(l[0]) + '</span><span>' + (l[1] > 0 ? fmt(l[1]) : T.free) + '</span></p>').join('') +
                (q.adjust ? '<p class="' + (q.adjust < 0 ? 'is-deal' : '') + '"><span>' + (q.adjust < 0 && slot ? T.offpeak : T.weekend) + '</span><span>' + (q.adjust > 0 ? '+' : '−') + Math.abs(q.adjust) + ' %</span></p>' : '');
            breakdownEl.classList.toggle('hidden', !q.lines.length);
            participantsInput.value = String(Math.max(1, people()));
        } else {
            breakdownEl.classList.add('hidden');
            const participants = Math.max(1, parseInt(participantsInput.value || '1', 10));
            const unit = mode === 'group' ? selectedSessionPrice : pricing.unit;
            total = unit * participants;
            ratesBox?.classList.toggle('hidden', mode === 'group');
            if (mode !== 'group') {
                const kids = childrenInput ? Math.max(0, parseInt(childrenInput.value || '0', 10)) : 0;
                const res = residentsInput ? Math.max(0, parseInt(residentsInput.value || '0', 10)) : 0;
                total = Math.max(0, participants - kids - res) * unit + kids * pricing.child + res * pricing.resident;
            }
            if (mode === 'guided') total += pricing.guideSupplement;
        }
        totalEl.textContent = total > 0 ? fmt(total) : T.free;
        paymentMethodBox.classList.toggle('hidden', total <= 0);
        submitLabel.textContent = total <= 0 ? T.payFree : (paymentMethod === 'on_site' ? T.onSite : T.pay);
    }

    function showError(msg) { errorEl.textContent = msg; errorEl.classList.remove('hidden'); }
    function hideError() { errorEl.classList.add('hidden'); }

    tabs.forEach(btn => btn.addEventListener('click', () => {
        activeTab = btn;
        mode = btn.dataset.mode;
        // Plafond de participants propre au type choisi.
        const max = parseInt(btn.dataset.max || '50', 10) || 50;
        participantsInput.max = String(max);
        if (parseInt(participantsInput.value || '1', 10) > max) participantsInput.value = String(max);
        hideError();
        setActiveTab();
    }));
    methodButtons.forEach(btn => btn.addEventListener('click', () => { setMethod(btn.dataset.method); recomputeTotal(); }));
    participantsInput.addEventListener('input', recomputeTotal);
    childrenInput?.addEventListener('input', recomputeTotal);
    residentsInput?.addEventListener('input', recomputeTotal);
    setMethod('cinetpay');
    if (tabs.length) tabs[0].click(); else recomputeTotal();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError();

        if (mode === 'group' && !selectedSessionId) return showError(T.pickSession);
        if (mode === 'custom') {
            if (!cDate.value) return showError(T.pickDate);
            if (people() < 1) return showError(T.pickPeople);
            if (cfg.slots && !slot) return showError(T.pickSlot);
        }

        const payload = new FormData(form);
        payload.set('visit_type', mode);
        if (mode === 'custom') {
            payload.set('visit_type_id', activeTab.dataset.typeId);
            payload.set('desired_date', cDate.value);
            payload.set('participants_count', String(people()));
            if (slot) payload.set('visit_time', slot.time);
            qty.forEach((q, i) => payload.set('tiers[' + i + ']', String(q)));
            addonsBox.querySelectorAll('[data-addon]:checked').forEach(c => payload.append('addons[]', c.value));
            if (cfg.languages.length) payload.set('language', langSelect.value || cfg.languages[0]);
            payload.delete('children_count');
            payload.delete('residents_count');
        }
        if (mode === 'group') payload.set('session_id', selectedSessionId);
        payload.set('payment_method', paymentMethod);
        payload.set('full_name', document.querySelector('meta[name="tvp-user-name"]')?.content || '');
        payload.set('email', document.querySelector('meta[name="tvp-user-email"]')?.content || '');
        payload.set('phone', document.querySelector('meta[name="tvp-user-phone"]')?.content || '');

        fetch('/experiences-touristiques/' + slug + '/visite/payer', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: payload,
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    showError(data.message || T.error);
                    if (mode === 'custom') loadSlots(); // places peut-être prises entre-temps
                    return;
                }
                window.location.href = data.redirect_url || data.payment_url;
            })
            .catch(() => showError(T.retry));
    });
})();

// ── Révélation au défilement ─────────────────────────────────────────────
(function () {
    // Arrivée directe via ancre (#contact-card...) : le navigateur y saute instantanément,
    // souvent avant que l'IntersectionObserver n'ait eu la main — la section resterait
    // invisible (opacity:0) malgré le saut. On révèle tout d'un coup dans ce cas précis.
    if (window.location.hash) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const items = document.querySelectorAll('.reveal:not(.is-visible)');
    if (!items.length) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    items.forEach((item) => observer.observe(item));
})();
</script>
</body>
</html>
