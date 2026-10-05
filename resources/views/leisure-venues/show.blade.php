<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $venue->name }} — Loisirs &amp; Culture — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary { background: #8E44AD; box-shadow: 0 12px 28px rgba(142,68,173,0.3); color: #fff; transition: transform .2s ease, filter .2s ease; }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .hero-cover { position: relative; overflow: hidden; background: #14130f; }
        .hero-cover img { transition: transform .6s ease; }
        .hero-cover:hover img { transform: scale(1.05); }
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: .85rem; }
        .gallery-tile img { transition: transform .4s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }
        .bento-card { border: 1px solid rgba(0,0,0,0.07); background: linear-gradient(180deg, #ffffff, #fbf8f2); box-shadow: 0 10px 28px rgba(20,18,12,0.06); }
        .bento-icon { width: 2.2rem; height: 2.2rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(142,68,173,0.18), rgba(142,68,173,0.06)); color: #8E44AD; }
        .section-kicker { letter-spacing: .22em; }
        .review-card { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(20,18,12,0.05); }
        .related-tile { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 10px 26px rgba(20,18,12,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .related-tile:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(142,68,173,0.16); }
        .contact-card { border: 1px solid rgba(0,0,0,0.08); background: linear-gradient(180deg, #ffffff, #fbf7ee); box-shadow: 0 20px 50px rgba(20,18,12,0.1); }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('leisure.index') }}" class="inline-flex items-center gap-2 text-[#8E44AD] hover:text-[#6c3483] transition text-sm font-semibold">
            <i class="fas fa-arrow-left text-[11px]"></i> Loisirs &amp; Culture
        </a>

        @php
            $photos = $venue->media;
            $mainPhoto = $photos->first()?->url ?: $venue->cover_image;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start mt-4">
            <div class="lg:col-span-3 space-y-14">

                {{-- ── GALERIE ────────────────────────────────────────────── --}}
                <div class="space-y-3 reveal is-visible">
                    <div class="hero-cover rounded-3xl {{ $mainPhoto ? 'cursor-zoom-in' : '' }}" style="height:clamp(320px,55vh,520px)" id="hero-cover-img-wrap"
                         @if($mainPhoto) onclick="openLightbox(document.getElementById('hero-cover-img').src)" @endif>
                        @if($mainPhoto)
                            <img id="hero-cover-img" src="{{ $mainPhoto }}" alt="{{ $venue->name }}" class="w-full h-full object-cover" loading="lazy">
                            <span class="zoom-hint absolute bottom-4 right-4 w-10 h-10 rounded-full bg-black/55 border border-white/20 backdrop-blur flex items-center justify-center text-white">
                                <i class="fas fa-magnifying-glass-plus text-sm"></i>
                            </span>
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-masks-theater text-4xl"></i></div>
                        @endif
                        <div class="absolute top-5 left-5 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3.5 py-1.5 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                                Loisirs &amp; Culture
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
                    <p class="section-kicker text-[#8E44AD] text-xs font-bold uppercase mb-2">Loisirs &amp; Culture</p>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold mb-2">{{ $venue->name }}</h1>
                    <p class="text-[#8a7f6b] text-sm mb-6">
                        <i class="fas fa-location-dot text-[#8E44AD]/70 mr-1"></i>
                        {{ $venue->city?->name }}{{ $venue->city?->region_administrative ? ' · '.$venue->city->region_administrative : '' }}
                        @if($venue->provider && $venue->provider->rating_avg)
                        <span class="ml-2 inline-flex items-center gap-1 text-[#8E44AD] font-bold"><i class="fas fa-star text-[10px]"></i> {{ number_format((float) $venue->provider->rating_avg, 1) }}</span>
                        @endif
                    </p>

                    @if($venue->short_description)
                    <p class="text-[#3d372c] text-base font-medium mb-3">{{ $venue->short_description }}</p>
                    @endif
                    <p class="text-[#3d372c] leading-relaxed text-[15px] whitespace-pre-line">{{ $venue->description ?: 'Description non disponible.' }}</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-city text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Ville</p>
                            <p class="text-sm font-bold mt-0.5">{{ $venue->city?->name ?: 'N/A' }}</p>
                        </div>
                        @if($venue->phone)
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-phone text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Contact</p>
                            <p class="text-sm font-bold mt-0.5 truncate">{{ $venue->phone }}</p>
                        </div>
                        @endif
                        @if($venue->adresse)
                        <div class="bento-card rounded-xl p-3.5 sm:col-span-2">
                            <div class="bento-icon mb-2"><i class="fas fa-location-dot text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Adresse</p>
                            <p class="text-sm font-bold mt-0.5">{{ $venue->adresse }}</p>
                        </div>
                        @endif
                    </div>
                </section>

                {{-- ── ÉQUIPEMENTS ────────────────────────────────────────── --}}
                @if(!empty($venue->amenities))
                <section class="reveal">
                    <p class="section-kicker text-[#8E44AD] text-xs font-bold uppercase mb-2">Confort</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Équipements &amp; services</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($venue->amenities as $am)
                        <div class="flex items-center gap-2.5 bento-card rounded-xl p-3">
                            <i class="{{ $am['icon'] ?? 'fas fa-check' }} text-[#8E44AD] text-sm shrink-0"></i>
                            <span class="text-sm font-medium truncate">{{ $am['label'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ── ACTIVITÉS PROPOSÉES ────────────────────────────────── --}}
                @if($activitiesByCategory->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#8E44AD] text-xs font-bold uppercase mb-2">À vivre sur place</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Activités proposées</h2>
                    <div class="space-y-6">
                        @foreach($activitiesByCategory as $categoryName => $activityGroup)
                        <div>
                            <h3 class="text-[#8E44AD] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
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
                                        <p class="text-[#8E44AD] text-sm font-bold mt-1">{{ number_format((int) $activity->price_xof, 0, ',', ' ') }} XOF</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('leisure.activities', $venue->slug) }}"
                       class="inline-flex items-center gap-1.5 mt-4 text-[#8E44AD] text-xs font-bold hover:text-[#6c3483] transition">
                        Voir sur une page dédiée <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </section>
                @endif

                {{-- ── LOCALISATION ─────────────────────────────────────────── --}}
                <section class="reveal">
                    <p class="section-kicker text-[#8E44AD] text-xs font-bold uppercase mb-2">Où se trouve l'établissement</p>
                    <h2 class="font-serif text-2xl font-bold mb-4">Localisation</h2>
                    @if($venue->latitude && $venue->longitude)
                        <div class="bento-card rounded-2xl overflow-hidden mb-3">
                            <iframe src="https://www.google.com/maps?q={{ $venue->latitude }},{{ $venue->longitude }}&output=embed"
                                    class="w-full" style="height:260px; border:0;" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <a href="{{ $venue->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-black/10 hover:border-purple-400/50 hover:bg-purple-50 text-[#8E44AD] text-xs font-bold transition">
                            <i class="fas fa-diamond-turn-right text-[10px]"></i> Itinéraire
                        </a>
                    @elseif($venue->city)
                        <div class="bento-card rounded-2xl overflow-hidden mb-3">
                            <iframe src="https://www.google.com/maps?q={{ urlencode($venue->city->name) }}&z=12&output=embed"
                                    class="w-full" style="height:220px; border:0;" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    @else
                        <p class="text-[#8a7f6b] text-sm">Coordonnées non renseignées pour cet établissement.</p>
                    @endif
                </section>

                {{-- ── AVIS CLIENTS ──────────────────────────────────────────── --}}
                @if($reviews->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#8E44AD] text-xs font-bold uppercase mb-2">Retours d'expérience</p>
                    <h2 class="font-serif text-2xl font-bold mb-6">Avis clients</h2>
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                        <div class="review-card rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-semibold text-sm">{{ $review->user->first_name ?? 'Visiteur' }}</p>
                                <span class="text-[#8E44AD] text-sm font-bold">{{ $review->rating_quality ?? '—' }} ★</span>
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
                    <div class="contact-card rounded-3xl p-5" id="contact-card">
                        <h2 class="font-serif text-xl font-bold mb-1">Contacter cet établissement</h2>
                        <p class="text-[#8a7f6b] text-xs mb-4">Posez vos questions directement, en toute sécurité, sans quitter la plateforme.</p>

                        @if($venue->provider)
                            @auth
                                @if(auth()->user()->role === 'visitor')
                                    <form method="POST" action="{{ route('visitor.conversations.store') }}" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="provider_id" value="{{ $venue->provider->id }}">
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
                                    <p class="text-[#1c1915] font-bold text-sm mb-1">Connectez-vous pour contacter cet établissement</p>
                                    <div class="flex items-center justify-center gap-2 mt-3">
                                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                            <i class="fas fa-right-to-bracket text-xs"></i> Se connecter
                                        </a>
                                        <a href="{{ route('register', ['role' => 'visitor', 'redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-purple-400/50 text-[#1c1915] transition">
                                            Créer un compte
                                        </a>
                                    </div>
                                </div>
                            @endauth
                        @else
                            @if($venue->phone)
                            <a href="tel:{{ $venue->phone }}" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                <i class="fas fa-phone text-sm"></i> {{ $venue->phone }}
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
            <p class="section-kicker text-[#8E44AD] text-xs font-bold uppercase mb-2">À découvrir aussi</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Établissements similaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($related as $r)
                <a href="{{ route('leisure.show', $r->slug) }}" class="related-tile rounded-2xl p-4">
                    <p class="text-[#8E44AD] text-[11px] uppercase tracking-[0.16em] font-bold">Loisirs &amp; Culture</p>
                    <p class="font-semibold mt-1.5 leading-snug">{{ $r->name }}</p>
                    <p class="text-[#8a7f6b] text-sm mt-1.5"><i class="fas fa-location-dot text-[#8E44AD]/60 mr-1"></i>{{ $r->city?->name ?: 'N/A' }}</p>
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
