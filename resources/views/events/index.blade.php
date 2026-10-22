<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Événements') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .events-shell {
            position: relative;
            isolation: isolate;
        }
        .events-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(transparent, transparent),
                linear-gradient(transparent, transparent);
            pointer-events: none;
        }
        .events-hero {
            background-image:
                linear-gradient(rgba(19, 19, 17, 0.9), rgba(19, 19, 17, 0.9)),
                linear-gradient(transparent, transparent);
            background-size: auto, auto;
            background-position: 0 0, 0 0;
            background-repeat: repeat, no-repeat;
        }
        /* Quand une image est configurée (back-office), on l'affiche sans voile
           d'opacité par-dessus — la lisibilité du titre est déjà assurée par le
           cadre bg-green-950/70 qui l'entoure, pas besoin d'assombrir la photo. */
        .events-hero[style*="--events-hero-bg-image"] {
            background-image: var(--events-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .events-filter-panel {
            border: 1px solid rgba(255,255,255,0.09);
            background: rgba(17,17,14,0.75);
            backdrop-filter: blur(12px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.32);
        }
        .events-input {
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(233, 229, 217, 0.86);
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }
        .events-input:focus {
            outline: none;
            border-color: rgba(242, 121, 15,0.5);
            box-shadow: 0 0 0 3px rgba(242, 121, 15,0.14);
            background: rgba(34,34,28,0.95);
        }
        .event-card {
            border: 1px solid rgba(255,255,255,0.07);
            background: rgba(135, 135, 134, 0.96);
            box-shadow: 0 12px 36px rgba(0,0,0,0.3);
            transition: transform .3s cubic-bezier(0.2, 0.8, 0.2, 1), border-color .25s ease, box-shadow .3s ease;
        }
        .event-card:hover {
            transform: translateY(-4px);
            border-color: rgba(242, 121, 15,0.45);
            box-shadow: 0 18px 48px rgba(0,0,0,0.4), 0 0 20px rgba(242, 121, 15,0.14);
        }
        .event-cover {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            background: #12120f;
        }
        .event-cover::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.28);
            pointer-events: none;
        }
        .event-cover img {
            transition: transform .5s ease;
        }
        .event-card:hover .event-cover img {
            transform: scale(1.06);
        }
        .event-cover-missing {
            background: rgba(56,56,46,0.85);
        }
        .event-cta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            margin-top: 0.75rem;
            padding: 0.36rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            animation: ctaRowIn .55s cubic-bezier(.2,.8,.2,1) both;
        }
        .event-price-badge-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            min-height: 2rem;
            padding: 0.38rem 0.7rem;
            border-radius: 0.7rem;
            border: 1px solid rgba(242, 121, 15,0.35);
            background: rgba(242, 121, 15,0.16);
            color: #f8d79a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.02em;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 8px 18px rgba(0,0,0,0.25);
            animation: pricePulseSoft 2.7s ease-in-out infinite;
        }
        .event-ticket-btn-modern {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 2rem;
            padding: 0.38rem 0.78rem;
            border-radius: 0.65rem;
            font-size: 11px;
            font-weight: 800;
            color: #1b1408;
            background: #f2790f;
            box-shadow: 0 10px 20px rgba(242, 121, 15,0.28);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .event-ticket-btn-modern:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 14px 24px rgba(242, 121, 15,0.35);
        }
        .group:hover .event-cta-row {
            border-color: rgba(242, 121, 15,0.35);
            box-shadow: 0 10px 22px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.08);
            transform: translateY(-1px);
        }
        @keyframes ctaRowIn {
            0% { opacity: 0; transform: translateY(8px) scale(0.985); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pricePulseSoft {
            0%, 100% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 8px 18px rgba(0,0,0,0.25); }
            50% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 10px 22px rgba(242, 121, 15,0.22); }
        }
        html:not(.dark) .events-hero {
            background-image:
                linear-gradient(rgba(251, 249, 245, 0.97), rgba(251, 249, 245, 0.97)),
                linear-gradient(transparent, transparent);
            background-size: auto, auto;
            background-position: 0 0, 0 0;
            background-repeat: repeat, no-repeat;
        }
        html:not(.dark) .events-hero[style*="--events-hero-bg-image"] {
            background-image: var(--events-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        html:not(.dark) .events-filter-panel {
            border-color: rgba(0,0,0,0.1);
            background: rgba(233, 229, 217, 0.94);
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
        }
        html:not(.dark) .events-input {
            border-color: rgba(0,0,0,0.12);
            background:#e9e5d9;
            color: #1c1915;
        }
        html:not(.dark) .events-input::placeholder { color:#5c574e; }
        html:not(.dark) .event-card {
            border-color: rgba(0,0,0,0.1);
            background: #f1ede3;
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
        }
        html:not(.dark) .event-card:hover {
            border-color: rgba(194, 94, 10,0.35);
            box-shadow: 0 14px 28px rgba(194, 94, 10,0.14);
        }
        html:not(.dark) .event-cover {
            border-bottom-color: rgba(0,0,0,0.08);
            background:#e9e5d9;
        }
        html:not(.dark) .event-cover::after {
            background: rgba(255, 255, 255, 0.39);
        }
        html:not(.dark) .event-cover-missing {
            background: rgba(235,229,218,0.95);
        }
        html:not(.dark) .event-cta-row {
            border-color: rgba(0,0,0,0.08);
            background: rgba(252, 250, 246, 0.94);
        }
        html:not(.dark) .event-price-badge-modern {
            border-color: rgba(194, 94, 10,0.28);
            background: rgba(255,255,255,0.92);
            color: #7a3c08;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 8px 16px rgba(194, 94, 10,0.14);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="bg-[#ffffff] text-white">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    @php
        $eventsHeroImage = \Illuminate\Support\Facades\Schema::hasTable('events_hero_images')
            ? \App\Models\EventsHeroImage::query()->find(1)
            : null;
    @endphp

    <section class="events-hero hero-events relative py-20 overflow-hidden"
        @if($eventsHeroImage && $eventsHeroImage->isVisible())
            style="--events-hero-bg-image: url('{{ $eventsHeroImage->image_url }}');"
        @endif
    >
        @include('partials.ivorian-motifs', ['culture' => 'evenements', 'band' => ! ($eventsHeroImage && $eventsHeroImage->isVisible())])
        <a href="{{ route('home') }}" class="absolute top-6 right-6 z-10 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.03] px-4 py-2 text-sm text-orange-300 hover:text-orange-200 hover:border-orange-400/45 hover:bg-orange-500/10 transition">
            <i class="fas fa-arrow-left text-[11px]"></i>
            {{ __('Retour accueil') }}
        </a>
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
                <p class="text-orange-300/90 text-[11px] tracking-[.26em] uppercase mb-3 font-semibold">{{ __('Agenda culturel') }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white leading-tight mb-4">{{ __('Agenda des événements') }}</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">{{ __('Explorez les rendez-vous à venir avec une expérience de lecture premium.') }}</p>
            </div>
        </div>
    </section>

@include('partials.hero-separator')

    <div class="events-shell max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <form method="GET" action="{{ route('events.index') }}" class="events-filter-panel rounded-2xl p-4 sm:p-5 grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Rechercher un événement...') }}" class="events-input md:col-span-2 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder:text-gray-500">
            <select name="categorie" class="events-input rounded-xl px-3.5 py-2.5 text-sm text-gray-200">
                <option value="">{{ __('Toutes catégories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($activeCategory?->id === $cat->id)>{{ $cat->localized('name') }}</option>
                @endforeach
            </select>
            <select name="ville" class="events-input rounded-xl px-3.5 py-2.5 text-sm text-gray-200">
                <option value="">{{ __('Toutes villes') }}</option>
                @foreach($cities as $v)
                    <option value="{{ $v }}" @selected($city === $v)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="periode" class="events-input rounded-xl px-3.5 py-2.5 text-sm text-gray-200">
                <option value="upcoming" @selected($period === 'upcoming')>{{ __('À venir') }}</option>
                <option value="past" @selected($period === 'past')>{{ __('Passés') }}</option>
                <option value="all" @selected($period === 'all')>{{ __('Tous') }}</option>
            </select>
            <div class="md:col-span-5 flex flex-wrap gap-2">
                <button class="inline-flex items-center gap-1.5 bg-orange-500 hover:bg-orange-400 text-black font-semibold px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-sliders text-[11px]"></i>
                    {{ __('Filtrer') }}
                </button>
                <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 bg-[#ffffff] hover:bg-[#ffffff] border border-white/10 px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-rotate-right text-[11px]"></i>
                    {{ __('Réinitialiser') }}
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($events as $event)
                @include('partials.event-card', ['event' => $event, 'headingTag' => 'h2'])
            @empty
                <p class="text-gray-500 col-span-3 rounded-2xl border border-dashed border-white/12 bg-white/[0.02] px-5 py-10 text-center">{{ __('Aucun événement trouvé.') }}</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $events->links() }}</div>
    </div>
@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
