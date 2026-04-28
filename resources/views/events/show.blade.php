<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .event-detail-shell {
            position: relative;
            isolation: isolate;
        }
        .event-detail-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(120% 80% at 0% 0%, rgba(232,160,32,0.14), transparent 48%),
                radial-gradient(80% 70% at 100% 0%, rgba(120,90,40,0.08), transparent 55%);
            pointer-events: none;
        }
        .event-hero {
            border: 1px solid rgba(255,255,255,0.1);
            background:
                linear-gradient(130deg, rgba(22,22,18,0.92), rgba(12,12,10,0.96)),
                radial-gradient(circle at top right, rgba(232,160,32,0.12), transparent 45%);
            box-shadow: 0 24px 56px rgba(0,0,0,0.36), inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .event-cover-frame {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.12);
            background: #11110e;
        }
        .event-cover-frame::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.68), rgba(0,0,0,0.15) 45%, rgba(0,0,0,0.2));
            pointer-events: none;
        }
        .event-cover-fallback {
            background:
                linear-gradient(140deg, rgba(60,60,48,0.7), rgba(22,22,18,0.95)),
                repeating-linear-gradient(45deg, rgba(232,160,32,0.09), rgba(232,160,32,0.09) 8px, transparent 8px, transparent 16px);
        }
        .event-stat-chip {
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04);
        }
        .event-ticket-btn {
            background: linear-gradient(135deg, #f5b942 0%, #e8a020 60%, #c4811a 100%);
            box-shadow: 0 10px 26px rgba(232,160,32,0.25);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .event-ticket-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 14px 34px rgba(232,160,32,0.35);
        }
        .related-card {
            border: 1px solid rgba(255,255,255,0.07);
            background: linear-gradient(180deg, rgba(20,20,16,0.95), rgba(13,13,11,0.96));
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .related-card:hover {
            transform: translateY(-3px);
            border-color: rgba(232,160,32,0.45);
            box-shadow: 0 16px 32px rgba(0,0,0,0.35);
        }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white">
    @include('partials.public-top-nav')
    <div class="event-detail-shell max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-amber-300 hover:text-amber-200 transition text-sm">
            <i class="fas fa-arrow-left text-[11px]"></i>
            Retour à l'agenda
        </a>

        <article class="event-hero mt-4 rounded-2xl p-4 sm:p-6">
            <div class="event-cover-frame rounded-2xl h-56 sm:h-72 lg:h-80">
                @if(!empty($event->cover_url))
                    <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->title_fr }}" class="h-full w-full object-cover" decoding="async">
                @else
                    <div class="event-cover-fallback h-full w-full flex items-center justify-center">
                        <div class="text-center px-5">
                            <i class="fas fa-image text-amber-300/80 text-2xl mb-3"></i>
                            <p class="text-[11px] uppercase tracking-[0.24em] text-amber-200/90 font-semibold">Aucune couverture</p>
                        </div>
                    </div>
                @endif
                <div class="absolute bottom-3 left-3 z-[1]">
                    <span class="inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3 py-1.5 text-[10px] uppercase tracking-[0.15em] text-amber-200 font-semibold backdrop-blur">
                        {{ $event->category->name_fr ?? 'Événement' }}
                    </span>
                </div>
            </div>

            <div class="pt-5">
                <h1 class="font-serif text-3xl sm:text-4xl font-bold leading-tight">{{ $event->title_fr }}</h1>
                <div class="mt-4 flex flex-wrap gap-2.5">
                    <span class="event-stat-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs text-gray-300">
                        <i class="fas fa-calendar text-amber-400/80"></i>
                        {{ $event->starts_at?->format('d/m/Y H:i') }} @if($event->ends_at) - {{ $event->ends_at->format('d/m/Y H:i') }} @endif
                    </span>
                    <span class="event-stat-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs text-gray-300">
                        <i class="fas fa-location-dot text-amber-400/80"></i>
                        {{ $event->location_name ?: 'Lieu non précisé' }} · {{ $event->city ?: 'Côte d\'Ivoire' }}
                    </span>
                </div>
            </div>

            @auth
                @if(auth()->user()->role === 'visitor')
                    <div class="mt-4">
                        @if(!($isFavorited ?? false))
                            <form method="POST" action="{{ route('visitor.favorites.store') }}">
                                @csrf
                                <input type="hidden" name="type" value="event">
                                <input type="hidden" name="id" value="{{ $event->id }}">
                                <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs hover:bg-amber-500/30 transition">
                                    <i class="fas fa-heart"></i> Ajouter à ma wishlist
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs">
                                <i class="fas fa-heart-circle-check"></i> Déjà dans vos favoris
                            </span>
                        @endif
                    </div>
                @endif
            @endauth
            <div class="mt-5 leading-relaxed text-gray-300 prose-content max-w-none">{!! \App\Support\HtmlSanitizer::articleBody($event->description_fr) !!}</div>

            @if($event->ticket_url)
                <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener noreferrer" class="event-ticket-btn inline-flex items-center gap-2 mt-6 text-black font-bold px-5 py-2.5 rounded-xl text-sm">
                    <i class="fas fa-ticket-simple text-[11px]"></i>
                    Réserver / Billetterie
                </a>
            @endif
        </article>

        @if($related->isNotEmpty())
            <div class="mt-8">
                <h2 class="font-serif text-xl font-semibold mb-3">Événements liés</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($related as $r)
                        <a href="{{ route('events.show', $r->slug) }}" class="related-card rounded-xl overflow-hidden">
                            <div class="h-32 bg-[#141410]">
                                @if(!empty($r->cover_url))
                                    <img src="{{ $r->cover_url }}" alt="{{ $r->cover_alt ?: $r->title_fr }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                @else
                                    <div class="event-cover-fallback h-full w-full flex items-center justify-center">
                                        <i class="fas fa-image text-amber-300/70 text-lg"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-amber-400 text-xs uppercase">{{ $r->category->name_fr ?? 'Événement' }}</p>
                                <p class="font-semibold mt-1">{{ $r->title_fr }}</p>
                                <p class="text-gray-500 text-xs mt-2">{{ $r->starts_at?->format('d/m/Y H:i') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <footer class="border-t border-white/5 bg-[#0d0d0b] py-6 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-gray-600">
            <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
            <a href="{{ route('events.index') }}" class="hover:text-amber-400 transition">← Retour aux événements</a>
        </div>
    </footer>
@include('partials.homepage-footer')
</body>
</html>
