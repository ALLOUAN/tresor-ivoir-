@php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;
    $metaDesc = Str::limit(strip_tags(trim((string) ($media->caption ?? $media->title ?? $media->original_name ?? ''))), 165);
    if ($metaDesc === '') {
        $metaDesc = 'Visuel — Galerie '.$siteBrand['site_name'];
    }
    $imgAlt = trim((string) ($media->alt_text ?? ''));
    if ($imgAlt === '') {
        $imgAlt = trim((string) ($media->title ?? $media->original_name ?? 'Image'));
    }
    $sectionLabels = [
        'home_gallery' => 'Accueil — Galerie',
        'hero' => 'Hero',
        'discoveries' => 'Découvertes',
    ];
    $sectionLabel = $sectionLabels[(string) ($media->section ?? '')] ?? ($media->section ?: '—');
    $bytes = (int) $media->size_bytes;
    $sizeHuman = $bytes >= 1048576
        ? number_format($bytes / 1048576, 1, ',', ' ').' Mo'
        : ($bytes >= 1024 ? number_format($bytes / 1024, 1, ',', ' ').' Ko' : $bytes.' o');
    $uploaderName = $media->uploader
        ? trim(($media->uploader->first_name ?? '').' '.($media->uploader->last_name ?? ''))
        : null;
    $priceLabel = isset($media->price) && $media->price !== null && (float) $media->price > 0
        ? number_format((float) $media->price, 0, ',', ' ').' FCFA'
        : null;
    $hasDisplayOrderColumn = Schema::hasColumn('site_media_items', 'display_order');
    $hasFeaturedColumn = Schema::hasColumn('site_media_items', 'is_featured');
@endphp
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ $pageTitle }} — Galerie — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif:   ['Playfair Display', 'Georgia', 'serif'],
                        elegant: ['Cormorant Garamond', 'Georgia', 'serif'],
                        sans:    ['Inter', 'system-ui', 'sans-serif'],
                        plus:    ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: { 300:'#fcd68a', 400:'#f5b942', 500:'#e8a020', 600:'#c4811a' },
                        dark: { 600:'#252520', 700:'#1c1c16', 800:'#141410', 900:'#0d0d0b' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }
        .font-plus    { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0d0d0b; }
        ::-webkit-scrollbar-thumb { background: #e8a020; border-radius: 3px; }
        .detail-mesh {
            background-color: #050403;
            background-image:
                radial-gradient(ellipse 100% 70% at 50% -15%, rgba(232, 160, 32, 0.12), transparent 55%),
                radial-gradient(ellipse 60% 40% at 100% 50%, rgba(99, 102, 241, 0.05), transparent 50%),
                radial-gradient(ellipse 50% 45% at 0% 100%, rgba(232, 160, 32, 0.04), transparent 50%);
        }
        .detail-noise {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
        }
        .detail-viewer-frame {
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.06) inset,
                0 32px 64px -12px rgba(0,0,0,0.65),
                0 0 80px -20px rgba(232, 160, 32, 0.08);
        }
        .detail-spec-tile {
            transition: border-color .25s ease, background-color .25s ease, transform .25s ease;
        }
        .detail-spec-tile:hover {
            border-color: rgba(232, 160, 32, 0.22);
            background-color: rgba(255,255,255,0.045);
        }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">

@include('partials.public-top-nav')

<section class="relative pt-24 sm:pt-28 pb-8 overflow-hidden border-b border-white/5 bg-dark-800/80">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <nav class="text-xs text-gray-500 mb-4 font-plus flex flex-wrap items-center gap-x-2 gap-y-1">
            <a href="{{ route('home') }}" class="hover:text-gold-400 transition">Accueil</a>
            <span class="opacity-40">/</span>
            <a href="{{ route('gallery.public') }}" class="hover:text-gold-400 transition">Galerie</a>
            <span class="opacity-40">/</span>
            <span class="text-gold-500/90 truncate max-w-[min(100%,14rem)] sm:max-w-md">{{ $pageTitle }}</span>
        </nav>
        <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl font-bold text-white leading-tight">{{ $pageTitle }}</h1>
        @if(trim((string) ($media->caption ?? '')) !== '')
            <p class="mt-3 text-gray-400 text-sm sm:text-base leading-relaxed max-w-3xl">{{ $media->caption }}</p>
        @endif
    </div>
</section>

<section class="relative isolate py-12 sm:py-20 detail-mesh border-t border-white/[0.05] overflow-hidden">
    <div class="pointer-events-none absolute inset-0 detail-noise opacity-80"></div>
    <div class="pointer-events-none absolute top-0 left-1/2 h-72 w-[min(100%,48rem)] -translate-x-1/2 rounded-full bg-gradient-to-b from-amber-500/12 via-transparent to-transparent blur-3xl"></div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 space-y-8 sm:space-y-10">
        {{-- Visionneuse --}}
        <div class="relative rounded-[1.35rem] p-[1px] bg-gradient-to-br from-white/15 via-white/[0.04] to-amber-500/20 detail-viewer-frame">
            <div class="rounded-[1.3rem] border border-white/[0.06] bg-zinc-950/90 backdrop-blur-xl overflow-hidden">
                <div class="relative flex items-center justify-center p-4 sm:p-10 md:p-12 min-h-[220px] bg-zinc-950"
                     style="background-image: radial-gradient(ellipse 80% 70% at 50% 45%, rgba(55,55,62,0.45) 0%, #090807 68%);">
                    <div class="pointer-events-none absolute inset-0 opacity-[0.15]" style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 48px 48px;"></div>
                    <img src="{{ url($media->url) }}" alt="{{ $imgAlt }}"
                         class="relative z-[1] max-w-full w-auto max-h-[min(70vh,800px)] h-auto object-contain rounded-xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.85)] ring-1 ring-black/40">
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4">
            <button type="button"
                    class="group inline-flex flex-1 sm:flex-initial min-w-0 items-center justify-center gap-2.5 px-6 py-3.5 rounded-full bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 text-black text-sm font-bold shadow-lg shadow-amber-900/35 hover:from-amber-300 hover:via-amber-400 hover:to-amber-500 transition-all duration-300 font-plus">
                <i class="fas fa-cart-shopping text-xs opacity-90 group-hover:scale-110 transition-transform"></i>
                Acheter
            </button>
            <a href="{{ route('gallery.public') }}"
               class="inline-flex flex-1 sm:flex-initial items-center justify-center gap-2.5 px-6 py-3.5 rounded-full border border-white/12 bg-white/[0.04] backdrop-blur-md text-gray-100 text-sm font-semibold hover:bg-white/[0.08] hover:border-amber-400/30 transition-all duration-300 font-plus">
                <i class="fas fa-arrow-left text-xs text-amber-400/80"></i>
                Retour à la galerie
            </a>
        </div>

        {{-- Métadonnées --}}
        <div class="rounded-[1.35rem] border border-white/[0.08] bg-white/[0.03] backdrop-blur-2xl shadow-[0_4px_24px_-4px_rgba(0,0,0,0.5),inset_0_1px_0_0_rgba(255,255,255,0.05)] p-6 sm:p-8 md:p-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 pb-6 border-b border-white/[0.06]">
                <div>
                    <p class="text-[10px] font-plus font-bold uppercase tracking-[0.22em] text-amber-500/80 mb-2">Fiche technique</p>
                    <h2 class="font-serif text-xl sm:text-2xl font-semibold tracking-tight text-white flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/25 bg-gradient-to-br from-amber-500/15 to-transparent text-amber-400">
                            <i class="fas fa-layer-group text-sm"></i>
                        </span>
                        Informations sur l’image
                    </h2>
                </div>
                <span class="inline-flex items-center gap-2 self-start rounded-full border border-white/10 bg-black/30 px-3 py-1.5 text-[11px] text-gray-500 font-plus">
                    <i class="fas fa-fingerprint text-amber-500/50 text-[10px]"></i>
                    Média sécurisé
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm">
                @if(trim((string) ($media->title ?? '')) !== '')
                    <div class="detail-spec-tile sm:col-span-2 rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="text-gray-500 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Titre</dt>
                        <dd class="text-white font-serif text-base sm:text-lg font-medium leading-snug">{{ $media->title }}</dd>
                    </div>
                @endif
                @if(trim((string) ($media->alt_text ?? '')) !== '')
                    <div class="detail-spec-tile sm:col-span-2 rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="text-gray-500 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Texte alternatif</dt>
                        <dd class="text-gray-300 leading-relaxed text-[13px] sm:text-sm">{{ $media->alt_text }}</dd>
                    </div>
                @endif
                @if(trim((string) ($media->credit ?? '')) !== '')
                    <div class="detail-spec-tile rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="text-gray-500 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Crédit photo</dt>
                        <dd class="text-gray-100 flex items-center gap-2 text-[13px] sm:text-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400/90"><i class="fas fa-camera text-xs"></i></span>
                            <span class="break-words">{{ $media->credit }}</span>
                        </dd>
                    </div>
                @endif
                @if($priceLabel)
                    <div class="detail-spec-tile rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="text-gray-500 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Prix</dt>
                        <dd><span class="inline-flex items-center rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-emerald-200 text-sm font-bold tracking-tight font-plus">{{ $priceLabel }}</span></dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</section>

<footer class="bg-dark-800 border-t border-white/5 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-600">
        <p>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés</p>
        <div class="flex items-center gap-5">
            <a href="{{ route('gallery.public') }}" class="hover:text-gold-400 transition">Galerie</a>
            <a href="{{ route('home') }}" class="hover:text-gold-400 transition">Accueil</a>
        </div>
    </div>
</footer>

@include('partials.homepage-footer')
</body>
</html>
