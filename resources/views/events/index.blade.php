<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
                radial-gradient(120% 80% at 5% 0%, rgba(232,160,32,0.12), transparent 50%),
                radial-gradient(110% 70% at 90% 8%, rgba(148, 163, 184, 0.08), transparent 50%);
            pointer-events: none;
        }
        .events-hero {
            border: 1px solid rgba(255,255,255,0.09);
            background:
                linear-gradient(130deg, rgba(24,24,21,0.86), rgba(14,14,12,0.94)),
                radial-gradient(circle at top right, rgba(232,160,32,0.1), transparent 40%);
            box-shadow: 0 25px 60px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .events-filter-panel {
            border: 1px solid rgba(255,255,255,0.09);
            background: rgba(17,17,14,0.75);
            backdrop-filter: blur(12px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.32);
        }
        .events-input {
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(28,28,22,0.86);
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }
        .events-input:focus {
            outline: none;
            border-color: rgba(232,160,32,0.5);
            box-shadow: 0 0 0 3px rgba(232,160,32,0.14);
            background: rgba(34,34,28,0.95);
        }
        .event-card {
            border: 1px solid rgba(255,255,255,0.07);
            background: linear-gradient(180deg, rgba(20,20,16,0.96), rgba(14,14,12,0.96));
            box-shadow: 0 12px 36px rgba(0,0,0,0.3);
            transition: transform .3s cubic-bezier(0.2, 0.8, 0.2, 1), border-color .25s ease, box-shadow .3s ease;
        }
        .event-card:hover {
            transform: translateY(-4px);
            border-color: rgba(232,160,32,0.45);
            box-shadow: 0 18px 48px rgba(0,0,0,0.4), 0 0 20px rgba(232,160,32,0.14);
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
            background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0.08) 45%, rgba(0,0,0,0.15));
            pointer-events: none;
        }
        .event-cover img {
            transition: transform .5s ease;
        }
        .event-card:hover .event-cover img {
            transform: scale(1.06);
        }
        .event-cover-missing {
            background:
                linear-gradient(140deg, rgba(56,56,46,0.7), rgba(28,28,22,0.95)),
                repeating-linear-gradient(45deg, rgba(232,160,32,0.1), rgba(232,160,32,0.1) 8px, transparent 8px, transparent 16px);
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
            background: linear-gradient(130deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
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
            border: 1px solid rgba(232,160,32,0.35);
            background: linear-gradient(145deg, rgba(232,160,32,0.22), rgba(255,255,255,0.08));
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
            background: linear-gradient(135deg, #f4c65a 0%, #e8a020 65%, #cb8517 100%);
            box-shadow: 0 10px 20px rgba(232,160,32,0.28);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .event-ticket-btn-modern::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-130%);
            background: linear-gradient(100deg, transparent 20%, rgba(255,255,255,0.38) 50%, transparent 80%);
            transition: transform .55s ease;
            pointer-events: none;
        }
        .event-ticket-btn-modern:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 14px 24px rgba(232,160,32,0.35);
        }
        .event-ticket-btn-modern:hover::after {
            transform: translateX(130%);
        }
        .group:hover .event-cta-row {
            border-color: rgba(232,160,32,0.35);
            box-shadow: 0 10px 22px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.08);
            transform: translateY(-1px);
        }
        @keyframes ctaRowIn {
            0% { opacity: 0; transform: translateY(8px) scale(0.985); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pricePulseSoft {
            0%, 100% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 8px 18px rgba(0,0,0,0.25); }
            50% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 10px 22px rgba(232,160,32,0.22); }
        }
        html:not(.dark) .events-hero {
            border-color: rgba(0,0,0,0.1);
            background:
                linear-gradient(130deg, rgba(255,255,255,0.96), rgba(247,243,235,0.98)),
                radial-gradient(circle at top right, rgba(232,160,32,0.1), transparent 40%);
            box-shadow: 0 14px 30px rgba(0,0,0,0.07);
        }
        html:not(.dark) .events-filter-panel {
            border-color: rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.94);
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
        }
        html:not(.dark) .events-input {
            border-color: rgba(0,0,0,0.12);
            background: #ffffff;
            color: #1c1915;
        }
        html:not(.dark) .events-input::placeholder { color:#7c796f; }
        html:not(.dark) .event-card {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(180deg, #ffffff, #f8f4ec);
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
        }
        html:not(.dark) .event-card:hover {
            border-color: rgba(180,83,9,0.35);
            box-shadow: 0 14px 28px rgba(180,83,9,0.14);
        }
        html:not(.dark) .event-cover {
            border-bottom-color: rgba(0,0,0,0.08);
            background: #f4f0e8;
        }
        html:not(.dark) .event-cover::after {
            background: linear-gradient(to top, rgba(255,255,255,0.92), rgba(255,255,255,0.2) 45%, rgba(255,255,255,0.06));
        }
        html:not(.dark) .event-cover-missing {
            background:
                linear-gradient(140deg, rgba(240,235,226,0.9), rgba(231,224,214,0.92)),
                repeating-linear-gradient(45deg, rgba(180,83,9,0.12), rgba(180,83,9,0.12) 8px, transparent 8px, transparent 16px);
        }
        html:not(.dark) .event-cta-row {
            border-color: rgba(0,0,0,0.08);
            background: linear-gradient(130deg, rgba(255,255,255,0.95), rgba(248,244,236,0.92));
        }
        html:not(.dark) .event-price-badge-modern {
            border-color: rgba(180,83,9,0.28);
            background: linear-gradient(145deg, rgba(245,158,11,0.24), rgba(255,255,255,0.95));
            color: #78350f;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 8px 16px rgba(180,83,9,0.14);
        }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white">
    @include('partials.public-top-nav')
    <div class="events-shell max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <section class="events-hero rounded-2xl p-5 sm:p-8 mb-6 sm:mb-7">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-amber-300/90 text-[11px] tracking-[.26em] uppercase mb-2 font-semibold">Agenda culturel</p>
                    <h1 class="font-serif text-3xl sm:text-4xl lg:text-[2.65rem] font-bold leading-tight">Agenda des événements</h1>
                    <p class="text-gray-400 mt-2 text-sm sm:text-base">Explorez les rendez-vous à venir avec une expérience de lecture premium.</p>
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.03] px-4 py-2 text-sm text-amber-300 hover:text-amber-200 hover:border-amber-400/45 hover:bg-amber-500/10 transition">
                    <i class="fas fa-arrow-left text-[11px]"></i>
                    Retour accueil
                </a>
            </div>
        </section>

        <form method="GET" action="{{ route('events.index') }}" class="events-filter-panel rounded-2xl p-4 sm:p-5 grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un événement..." class="events-input md:col-span-2 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder:text-gray-500">
            <select name="categorie" class="events-input rounded-xl px-3.5 py-2.5 text-sm text-gray-200">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($activeCategory?->id === $cat->id)>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <select name="ville" class="events-input rounded-xl px-3.5 py-2.5 text-sm text-gray-200">
                <option value="">Toutes villes</option>
                @foreach($cities as $v)
                    <option value="{{ $v }}" @selected($city === $v)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="periode" class="events-input rounded-xl px-3.5 py-2.5 text-sm text-gray-200">
                <option value="upcoming" @selected($period === 'upcoming')>À venir</option>
                <option value="past" @selected($period === 'past')>Passés</option>
                <option value="all" @selected($period === 'all')>Tous</option>
            </select>
            <div class="md:col-span-5 flex flex-wrap gap-2">
                <button class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-400 text-black font-semibold px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-sliders text-[11px]"></i>
                    Filtrer
                </button>
                <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 bg-[#1c1c16] hover:bg-[#252520] border border-white/10 px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-rotate-right text-[11px]"></i>
                    Réinitialiser
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($events as $event)
                <a href="{{ route('events.show', $event->slug) }}" class="event-card rounded-2xl overflow-hidden">
                    <div class="event-cover h-44">
                        @if(!empty($event->cover_url))
                            <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->title_fr }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        @else
                            <div class="event-cover-missing h-full w-full flex items-center justify-center">
                                <div class="text-center px-4">
                                    <i class="fas fa-image text-amber-300/75 text-xl mb-2"></i>
                                    <p class="text-[11px] uppercase tracking-[0.2em] text-amber-200/85 font-semibold">Couverture requise</p>
                                </div>
                            </div>
                        @endif
                        <div class="absolute top-3 left-3 z-[1]">
                            <span class="inline-flex items-center rounded-full bg-black/50 border border-white/20 px-2.5 py-1 text-[10px] uppercase tracking-wide text-amber-200 font-semibold backdrop-blur">
                                {{ $event->category->name_fr ?? 'Événement' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold leading-snug">{{ $event->title_fr }}</h2>
                        <p class="text-gray-400 text-sm mt-3 flex items-center gap-1.5">
                            <i class="fas fa-calendar text-[11px] text-amber-400/70"></i>
                            {{ $event->starts_at?->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-gray-500 text-sm mt-1.5 flex items-center gap-1.5">
                            <i class="fas fa-location-dot text-[11px] text-amber-400/70"></i>
                            {{ $event->city ?: 'Côte d\'Ivoire' }}
                        </p>
                        <div class="event-cta-row">
                            <span class="event-price-badge-modern">
                                <i class="fas fa-ticket text-[10px]"></i>
                                @if($event->is_free || (float) ($event->price ?? 0) <= 0)
                                    Gratuit
                                @else
                                    {{ number_format((float) $event->price, 0, ',', ' ') }} FCFA
                                @endif
                            </span>
                            <span class="event-ticket-btn-modern">
                                <i class="fas fa-arrow-right text-[10px]"></i> Voir détails
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 col-span-3 rounded-2xl border border-dashed border-white/12 bg-white/[0.02] px-5 py-10 text-center">Aucun événement trouvé.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $events->links() }}</div>
    </div>
@include('partials.homepage-footer')
</body>
</html>
