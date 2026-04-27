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

<section class="py-8 sm:py-12 bg-dark-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 space-y-8">
        <div class="rounded-2xl border border-white/10 bg-black/40 overflow-hidden shadow-2xl shadow-black/40">
            <div class="bg-black/40 flex items-center justify-center p-4 sm:p-8 min-h-[200px]">
                <img src="{{ url($media->url) }}" alt="{{ $imgAlt }}"
                     class="max-w-full w-auto max-h-[min(72vh,820px)] h-auto object-contain rounded-lg">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <a href="{{ url($media->url) }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm transition shadow-lg shadow-gold-500/20">
                <i class="fas fa-up-right-from-square text-xs"></i>
                Ouvrir le fichier en grand
            </a>
            <a href="{{ route('gallery.public') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-white/15 text-gray-200 hover:bg-white/5 hover:border-gold-500/30 text-sm font-medium transition">
                <i class="fas fa-arrow-left text-xs"></i>
                Retour à la galerie
            </a>
        </div>

        <div class="rounded-2xl border border-white/10 bg-dark-800/60 p-5 sm:p-8">
            <h2 class="font-serif text-lg sm:text-xl font-semibold text-gold-200 mb-6 flex items-center gap-2">
                <i class="fas fa-circle-info text-gold-500/80 text-sm"></i>
                Informations sur l’image
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                @if(trim((string) ($media->title ?? '')) !== '')
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Titre</dt>
                        <dd class="text-white font-medium">{{ $media->title }}</dd>
                    </div>
                @endif
                @if(trim((string) ($media->alt_text ?? '')) !== '')
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Texte alternatif</dt>
                        <dd class="text-gray-300">{{ $media->alt_text }}</dd>
                    </div>
                @endif
                @if(trim((string) ($media->credit ?? '')) !== '')
                    <div>
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Crédit photo</dt>
                        <dd class="text-gray-200"><i class="fas fa-camera text-gold-500/60 mr-1.5"></i>{{ $media->credit }}</dd>
                    </div>
                @endif
                @if($priceLabel)
                    <div>
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Prix</dt>
                        <dd><span class="inline-flex rounded-lg px-2.5 py-1 bg-emerald-950/70 border border-emerald-500/25 text-emerald-200 font-semibold">{{ $priceLabel }}</span></dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Section</dt>
                    <dd class="text-gray-300">{{ $sectionLabel }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Fichier</dt>
                    <dd class="text-gray-300 break-all">{{ $media->original_name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Type MIME</dt>
                    <dd class="text-gray-400 font-mono text-xs">{{ $media->mime_type }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Taille</dt>
                    <dd class="text-gray-300">{{ $sizeHuman }}</dd>
                </div>
                @if($media->published_at)
                    <div>
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Date de publication</dt>
                        <dd class="text-gray-300">{{ $media->published_at->translatedFormat('d F Y, H:i') }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Ajouté le</dt>
                    <dd class="text-gray-300">{{ $media->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                @if($hasDisplayOrderColumn)
                    <div>
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Ordre d’affichage</dt>
                        <dd class="text-gray-300 tabular-nums">{{ (int) ($media->display_order ?? 0) }}</dd>
                    </div>
                @endif
                @if($hasFeaturedColumn)
                    <div>
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Vedette</dt>
                        <dd class="text-gray-300">{{ ($media->is_featured ?? false) ? 'Oui' : 'Non' }}</dd>
                    </div>
                @endif
                @if($uploaderName !== null && $uploaderName !== '')
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 text-xs uppercase tracking-wider mb-1">Téléversé par</dt>
                        <dd class="text-gray-300">{{ $uploaderName }}</dd>
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

</body>
</html>
