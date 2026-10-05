<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="tvp-user-name" content="{{ trim(auth()->user()->first_name.' '.auth()->user()->last_name) }}">
        <meta name="tvp-user-email" content="{{ auth()->user()->email }}">
        <meta name="tvp-user-phone" content="{{ auth()->user()->phone }}">
    @endauth
    <title>{{ $experience->name }} — Sites Touristiques — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary { background: #27AE60; box-shadow: 0 12px 28px rgba(39,174,96,0.3); color: #fff; transition: transform .2s ease, filter .2s ease; }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .hero-cover { position: relative; overflow: hidden; background: #14130f; }
        .hero-cover img { transition: transform .6s ease; }
        .hero-cover:hover img { transform: scale(1.05); }
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: .85rem; }
        .gallery-tile img { transition: transform .4s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }
        .bento-card { border: 1px solid rgba(0,0,0,0.07); background: linear-gradient(180deg, #ffffff, #fbf8f2); box-shadow: 0 10px 28px rgba(20,18,12,0.06); }
        .bento-icon { width: 2.2rem; height: 2.2rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(39,174,96,0.18), rgba(39,174,96,0.06)); color: #27AE60; }
        .section-kicker { letter-spacing: .22em; }
        .review-card { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(20,18,12,0.05); }
        .related-tile { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 10px 26px rgba(20,18,12,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .related-tile:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(39,174,96,0.16); }
        .contact-card { border: 1px solid rgba(0,0,0,0.08); background: linear-gradient(180deg, #ffffff, #fbf7ee); box-shadow: 0 20px 50px rgba(20,18,12,0.1); }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('tourist-experience.index') }}" class="inline-flex items-center gap-2 text-[#27AE60] hover:text-[#1d8348] transition text-sm font-semibold">
            <i class="fas fa-arrow-left text-[11px]"></i> Sites Touristiques
        </a>

        @php
            $photos = $experience->media;
            $mainPhoto = $photos->first()?->url ?: $experience->cover_image;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start mt-4">
            <div class="lg:col-span-3 space-y-14">

                {{-- ── GALERIE ────────────────────────────────────────────── --}}
                <div class="space-y-3 reveal is-visible">
                    <div class="hero-cover rounded-3xl {{ $mainPhoto ? 'cursor-zoom-in' : '' }}" style="height:clamp(320px,55vh,520px)" id="hero-cover-img-wrap"
                         @if($mainPhoto) onclick="openLightbox(document.getElementById('hero-cover-img').src)" @endif>
                        @if($mainPhoto)
                            <img id="hero-cover-img" src="{{ $mainPhoto }}" alt="{{ $experience->name }}" class="w-full h-full object-cover" loading="lazy">
                            <span class="zoom-hint absolute bottom-4 right-4 w-10 h-10 rounded-full bg-black/55 border border-white/20 backdrop-blur flex items-center justify-center text-white">
                                <i class="fas fa-magnifying-glass-plus text-sm"></i>
                            </span>
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-landmark text-4xl"></i></div>
                        @endif
                        <div class="absolute top-5 left-5 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3.5 py-1.5 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                                Sites Touristiques
                            </span>
                        </div>
                    </div>

                    @if($photos->count() > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($photos->skip(1)->take(4) as $p)
                        <div class="gallery-tile aspect-square" onclick="document.getElementById('hero-cover-img').src='{{ $p->url }}'">
                            <img src="{{ $p->url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- ── EN-TÊTE + DESCRIPTION ──────────────────────────────── --}}
                <section class="reveal">
                    <p class="section-kicker text-[#27AE60] text-xs font-bold uppercase mb-2">Sites Touristiques</p>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold mb-2">{{ $experience->name }}</h1>
                    <p class="text-[#8a7f6b] text-sm mb-6">
                        <i class="fas fa-location-dot text-[#27AE60]/70 mr-1"></i>
                        {{ $experience->city?->name }}{{ $experience->city?->region_administrative ? ' · '.$experience->city->region_administrative : '' }}
                        @if($experience->provider && $experience->provider->rating_avg)
                        <span class="ml-2 inline-flex items-center gap-1 text-[#27AE60] font-bold"><i class="fas fa-star text-[10px]"></i> {{ number_format((float) $experience->provider->rating_avg, 1) }}</span>
                        @endif
                    </p>

                    @if($experience->short_description)
                    <p class="text-[#3d372c] text-base font-medium mb-3">{{ $experience->short_description }}</p>
                    @endif
                    <p class="text-[#3d372c] leading-relaxed text-[15px] whitespace-pre-line">{{ $experience->description ?: 'Description non disponible.' }}</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-city text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Ville</p>
                            <p class="text-sm font-bold mt-0.5">{{ $experience->city?->name ?: 'N/A' }}</p>
                        </div>
                        @if($experience->phone)
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-phone text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Contact</p>
                            <p class="text-sm font-bold mt-0.5 truncate">{{ $experience->phone }}</p>
                        </div>
                        @endif
                        @if($experience->adresse)
                        <div class="bento-card rounded-xl p-3.5 sm:col-span-2">
                            <div class="bento-icon mb-2"><i class="fas fa-location-dot text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Adresse</p>
                            <p class="text-sm font-bold mt-0.5">{{ $experience->adresse }}</p>
                        </div>
                        @endif
                    </div>
                </section>

                {{-- ── ÉQUIPEMENTS ────────────────────────────────────────── --}}
                @if(!empty($experience->amenities))
                <section class="reveal">
                    <p class="section-kicker text-[#27AE60] text-xs font-bold uppercase mb-2">Confort</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Équipements &amp; services</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($experience->amenities as $am)
                        <div class="flex items-center gap-2.5 bento-card rounded-xl p-3">
                            <i class="{{ $am['icon'] ?? 'fas fa-check' }} text-[#27AE60] text-sm shrink-0"></i>
                            <span class="text-sm font-medium truncate">{{ $am['label'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ── ACTIVITÉS PROPOSÉES ────────────────────────────────── --}}
                @if($activitiesByCategory->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#27AE60] text-xs font-bold uppercase mb-2">À vivre sur place</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Activités proposées</h2>
                    <div class="space-y-6">
                        @foreach($activitiesByCategory as $categoryName => $activityGroup)
                        <div>
                            <h3 class="text-[#27AE60] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($activityGroup as $activity)
                                <div class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                                    @if(!empty($activity->images[0]))
                                    <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-black/5">
                                        <img src="{{ $activity->images[0] }}" alt="" class="w-full h-full object-cover">
                                    </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $activity->name }}</p>
                                        <p class="text-[#8a7f6b] text-xs mt-0.5">
                                            @if($activity->duration_minutes) {{ $activity->duration_minutes }} min @endif
                                            @if($activity->duration_minutes && $activity->max_participants) · @endif
                                            @if($activity->max_participants) {{ $activity->max_participants }} pers. max @endif
                                        </p>
                                        <p class="text-[#27AE60] text-sm font-bold mt-1">{{ number_format((int) $activity->price_xof, 0, ',', ' ') }} XOF</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('tourist-experience.activities', $experience->slug) }}"
                       class="inline-flex items-center gap-1.5 mt-4 text-[#27AE60] text-xs font-bold hover:text-[#1d8348] transition">
                        Voir sur une page dédiée <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </section>
                @endif

                {{-- ── LOCALISATION ─────────────────────────────────────────── --}}
                <section class="reveal">
                    <p class="section-kicker text-[#27AE60] text-xs font-bold uppercase mb-2">Où se trouve le site</p>
                    <h2 class="font-serif text-2xl font-bold mb-4">Localisation</h2>
                    @if($experience->latitude && $experience->longitude)
                        <div class="bento-card rounded-2xl overflow-hidden mb-3">
                            <iframe src="https://www.google.com/maps?q={{ $experience->latitude }},{{ $experience->longitude }}&output=embed"
                                    class="w-full" style="height:260px; border:0;" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <a href="{{ $experience->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-black/10 hover:border-emerald-400/50 hover:bg-emerald-50 text-[#27AE60] text-xs font-bold transition">
                            <i class="fas fa-diamond-turn-right text-[10px]"></i> Itinéraire
                        </a>
                    @elseif($experience->city)
                        <div class="bento-card rounded-2xl overflow-hidden mb-3">
                            <iframe src="https://www.google.com/maps?q={{ urlencode($experience->city->name) }}&z=12&output=embed"
                                    class="w-full" style="height:220px; border:0;" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    @else
                        <p class="text-[#8a7f6b] text-sm">Coordonnées non renseignées pour ce site.</p>
                    @endif
                </section>

                {{-- ── AVIS CLIENTS ──────────────────────────────────────────── --}}
                @if($reviews->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#27AE60] text-xs font-bold uppercase mb-2">Retours d'expérience</p>
                    <h2 class="font-serif text-2xl font-bold mb-6">Avis clients</h2>
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                        <div class="review-card rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-semibold text-sm">{{ $review->user->first_name ?? 'Visiteur' }}</p>
                                <span class="text-[#27AE60] text-sm font-bold">{{ $review->rating_quality ?? '—' }} ★</span>
                            </div>
                            <p class="text-[#5c5548] text-sm mt-1">{{ $review->comment }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>{{-- fin col principale --}}

            {{-- ═══════════════ COLONNE LATÉRALE ═══════════════ --}}
            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24 space-y-3 reveal">
                    @if($experience->hasAnyVisitModeEnabled())
                    <div class="contact-card rounded-3xl p-5" id="tvp-booking-module" data-slug="{{ $experience->slug }}">
                        <h2 class="font-serif text-xl font-bold mb-1">Réserver une visite</h2>
                        <p class="text-[#8a7f6b] text-xs mb-4">Choisissez le mode de visite qui vous convient.</p>

                        @auth
                            @if(auth()->user()->role === 'visitor')
                                <div class="flex flex-wrap gap-1.5 mb-4" id="tvp-tabs">
                                    @if($experience->visit_individual_enabled)
                                        <button type="button" data-mode="individual" class="tvp-tab px-3 py-1.5 rounded-full text-xs font-bold border border-black/10">Individuelle</button>
                                    @endif
                                    @if($experience->visit_guided_enabled)
                                        <button type="button" data-mode="guided" class="tvp-tab px-3 py-1.5 rounded-full text-xs font-bold border border-black/10">Avec guide</button>
                                    @endif
                                    @if($experience->visit_group_enabled)
                                        <button type="button" data-mode="group" class="tvp-tab px-3 py-1.5 rounded-full text-xs font-bold border border-black/10">Groupée</button>
                                    @endif
                                </div>

                                <form id="tvp-form" class="space-y-3">
                                    <div id="tvp-panel-solo" class="space-y-3">
                                        <div>
                                            <label class="block text-[#8a7f6b] text-[11px] mb-1">Date souhaitée (optionnel)</label>
                                            <input type="date" name="desired_date" min="{{ now()->toDateString() }}"
                                                   class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm">
                                        </div>
                                    </div>

                                    <div id="tvp-panel-group" class="space-y-2 hidden">
                                        <label class="block text-[#8a7f6b] text-[11px] mb-1">Session</label>
                                        <div id="tvp-sessions" class="space-y-1.5 max-h-56 overflow-y-auto"></div>
                                    </div>

                                    <div>
                                        <label class="block text-[#8a7f6b] text-[11px] mb-1">Nombre de participants</label>
                                        <input type="number" name="participants_count" value="1" min="1" max="50"
                                               class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm" id="tvp-participants">
                                    </div>

                                    <div>
                                        <label class="block text-[#8a7f6b] text-[11px] mb-1">Message (optionnel)</label>
                                        <textarea name="message" rows="2" maxlength="1000"
                                                  class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm"></textarea>
                                    </div>

                                    <div class="rounded-xl bg-black/[0.03] border border-black/8 px-3.5 py-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[#8a7f6b]">Total</span>
                                            <span class="font-bold" id="tvp-total">—</span>
                                        </div>
                                    </div>

                                    <div id="tvp-payment-method" class="hidden grid grid-cols-2 gap-2">
                                        <button type="button" data-method="cinetpay" class="tvp-method px-3 py-2.5 rounded-xl text-xs font-bold border border-black/10">CinetPay</button>
                                        <button type="button" data-method="wallet" class="tvp-method px-3 py-2.5 rounded-xl text-xs font-bold border border-black/10">Mon solde</button>
                                    </div>

                                    <p id="tvp-error" class="text-red-600 text-xs hidden"></p>

                                    <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                        <i class="fas fa-ticket text-sm"></i> <span id="tvp-submit-label">Réserver</span>
                                    </button>
                                </form>
                            @endif
                        @else
                            <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
                                <p class="text-[#1c1915] font-bold text-sm mb-1">Connectez-vous pour réserver une visite</p>
                                <div class="flex items-center justify-center gap-2 mt-3">
                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                        <i class="fas fa-right-to-bracket text-xs"></i> Se connecter
                                    </a>
                                    <a href="{{ route('register', ['role' => 'visitor', 'redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-emerald-400/50 text-[#1c1915] transition">
                                        Créer un compte
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </div>
                    @endif

                    <div class="contact-card rounded-3xl p-5" id="contact-card">
                        <h2 class="font-serif text-xl font-bold mb-1">Contacter ce prestataire</h2>
                        <p class="text-[#8a7f6b] text-xs mb-4">Posez vos questions directement, en toute sécurité, sans quitter la plateforme.</p>

                        @if($experience->provider)
                            @auth
                                @if(auth()->user()->role === 'visitor')
                                    <form method="POST" action="{{ route('visitor.conversations.store') }}" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $experience->provider->id }}">
                                        <textarea name="message" id="contact-message" rows="3" required maxlength="4000"
                                                  class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-[#a89f8f]"
                                                  placeholder="Votre message..."></textarea>
                                        <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                            <i class="fas fa-paper-plane text-sm"></i> Envoyer le message
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
                                    <p class="text-[#1c1915] font-bold text-sm mb-1">Connectez-vous pour contacter ce prestataire</p>
                                    <div class="flex items-center justify-center gap-2 mt-3">
                                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                            <i class="fas fa-right-to-bracket text-xs"></i> Se connecter
                                        </a>
                                        <a href="{{ route('register', ['role' => 'visitor', 'redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-emerald-400/50 text-[#1c1915] transition">
                                            Créer un compte
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
                            <p class="text-[#8a7f6b] text-sm">Aucun moyen de contact renseigné.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
        <section class="mt-16 reveal">
            <p class="section-kicker text-[#27AE60] text-xs font-bold uppercase mb-2">À découvrir aussi</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Sites similaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($related as $r)
                <a href="{{ route('tourist-experience.show', $r->slug) }}" class="related-tile rounded-2xl p-4">
                    <p class="text-[#27AE60] text-[11px] uppercase tracking-[0.16em] font-bold">Sites Touristiques</p>
                    <p class="font-semibold mt-1.5 leading-snug">{{ $r->name }}</p>
                    <p class="text-[#8a7f6b] text-sm mt-1.5"><i class="fas fa-location-dot text-[#27AE60]/60 mr-1"></i>{{ $r->city?->name ?: 'N/A' }}</p>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

    <div class="pb-4">
@include('partials.homepage-footer')
    </div>
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
    if (ta) ta.value = 'Je suis intéressé par : ' + item;
})();

// ── Module de réservation de visite ──────────────────────────────────────
(function () {
    const root = document.getElementById('tvp-booking-module');
    if (!root) return;

    const slug = root.dataset.slug;
    const form = document.getElementById('tvp-form');
    if (!form) return;

    const tabs = Array.from(document.querySelectorAll('.tvp-tab'));
    const panelSolo = document.getElementById('tvp-panel-solo');
    const panelGroup = document.getElementById('tvp-panel-group');
    const sessionsBox = document.getElementById('tvp-sessions');
    const participantsInput = document.getElementById('tvp-participants');
    const totalEl = document.getElementById('tvp-total');
    const errorEl = document.getElementById('tvp-error');
    const submitLabel = document.getElementById('tvp-submit-label');
    const paymentMethodBox = document.getElementById('tvp-payment-method');
    const methodButtons = Array.from(document.querySelectorAll('.tvp-method'));

    let mode = tabs[0]?.dataset.mode || 'individual';
    let selectedSessionId = null;
    let selectedSessionPrice = 0;
    let paymentMethod = 'cinetpay';
    let pricing = { unit: {{ (int) ($experience->visit_individual_price_xof ?? 0) }}, guideSupplement: {{ (int) ($experience->visit_guide_supplement_xof ?? 0) }} };

    function setActiveTab() {
        tabs.forEach(btn => {
            const active = btn.dataset.mode === mode;
            btn.classList.toggle('bg-[#27AE60]', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('text-[#1c1915]', !active);
        });
        panelGroup.classList.toggle('hidden', mode !== 'group');
        panelSolo.classList.toggle('hidden', mode === 'group');
        if (mode === 'group') loadSessions();
        recomputeTotal();
    }

    function loadSessions() {
        sessionsBox.innerHTML = '<p class="text-[#8a7f6b] text-xs">Chargement…</p>';
        fetch('/experiences-touristiques/' + slug + '/visite/sessions')
            .then(r => r.json())
            .then(data => {
                const sessions = data.sessions || [];
                if (!sessions.length) {
                    sessionsBox.innerHTML = '<p class="text-[#8a7f6b] text-xs">Aucune session disponible pour le moment.</p>';
                    return;
                }
                sessionsBox.innerHTML = '';
                sessions.forEach(s => {
                    const full = s.remaining_seats <= 0;
                    const row = document.createElement('label');
                    row.className = 'flex items-center justify-between gap-2 bento-card rounded-xl px-3 py-2.5 text-xs cursor-pointer ' + (full ? 'opacity-40 cursor-not-allowed' : '');
                    row.innerHTML = '<span><input type="radio" name="session_id" value="' + s.id + '" ' + (full ? 'disabled' : '') + ' class="mr-2">' +
                        s.date_label + (s.period_label ? ' · ' + s.period_label : '') + '</span>' +
                        '<span class="font-bold">' + (s.price_per_person_xof ? Number(s.price_per_person_xof).toLocaleString('fr-FR') + ' XOF' : 'Gratuit') +
                        (full ? ' · Complet' : ' · ' + s.remaining_seats + ' place(s)') + '</span>';
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

    function recomputeTotal() {
        const participants = Math.max(1, parseInt(participantsInput.value || '1', 10));
        let unit = mode === 'group' ? selectedSessionPrice : pricing.unit;
        let total = unit * participants;
        if (mode === 'guided') total += pricing.guideSupplement;
        totalEl.textContent = total > 0 ? Number(total).toLocaleString('fr-FR') + ' XOF' : 'Gratuit';
        paymentMethodBox.classList.toggle('hidden', total <= 0);
        submitLabel.textContent = total > 0 ? 'Réserver et payer' : 'Réserver gratuitement';
    }

    tabs.forEach(btn => btn.addEventListener('click', () => { mode = btn.dataset.mode; setActiveTab(); }));
    methodButtons.forEach(btn => btn.addEventListener('click', () => {
        paymentMethod = btn.dataset.method;
        methodButtons.forEach(b => b.classList.toggle('bg-[#27AE60]', b === btn));
        methodButtons.forEach(b => b.classList.toggle('text-white', b === btn));
    }));
    participantsInput.addEventListener('input', recomputeTotal);
    if (methodButtons.length) { methodButtons[0].classList.add('bg-[#27AE60]', 'text-white'); }
    if (tabs.length) setActiveTab(); else recomputeTotal();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorEl.classList.add('hidden');

        if (mode === 'group' && !selectedSessionId) {
            errorEl.textContent = 'Veuillez choisir une session.';
            errorEl.classList.remove('hidden');
            return;
        }

        const payload = new FormData(form);
        payload.set('visit_type', mode);
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
                    errorEl.textContent = data.message || 'Une erreur est survenue.';
                    errorEl.classList.remove('hidden');
                    return;
                }
                window.location.href = data.redirect_url || data.payment_url;
            })
            .catch(() => {
                errorEl.textContent = 'Une erreur est survenue. Merci de réessayer.';
                errorEl.classList.remove('hidden');
            });
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
