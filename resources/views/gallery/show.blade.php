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
<html lang="fr" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.theme-init')
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ $pageTitle }} — Galerie — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        html:not(.dark) .detail-mesh {
            background-color: #f8f5ee;
            background-image:
                radial-gradient(ellipse 90% 65% at 50% -10%, rgba(232, 160, 32, 0.16), transparent 55%),
                radial-gradient(ellipse 55% 40% at 100% 45%, rgba(99, 102, 241, 0.08), transparent 50%),
                radial-gradient(ellipse 50% 45% at 0% 100%, rgba(232, 160, 32, 0.07), transparent 55%);
        }
        html:not(.dark) .detail-viewer-frame {
            box-shadow:
                0 0 0 1px rgba(0,0,0,0.08) inset,
                0 22px 50px -20px rgba(15, 23, 42, 0.24),
                0 0 70px -24px rgba(232, 160, 32, 0.16);
        }
        html:not(.dark) .detail-viewer-surface {
            border-color: rgba(15,23,42,0.10);
            background: rgba(255,255,255,0.86);
        }
        html:not(.dark) .detail-viewer-stage {
            background-color: #f3efe6;
            background-image: radial-gradient(ellipse 80% 70% at 50% 45%, rgba(120,120,130,0.20) 0%, #e7e1d5 68%) !important;
        }
        html:not(.dark) .detail-meta-card {
            border-color: rgba(15,23,42,0.12);
            background: rgba(255,255,255,0.84);
            box-shadow: 0 12px 36px -20px rgba(15,23,42,0.25), inset 0 1px 0 rgba(255,255,255,0.8);
        }
        html:not(.dark) .detail-back-btn {
            border-color: rgba(15,23,42,0.15);
            background: rgba(255,255,255,0.92);
            color: #1f2937;
        }
        html:not(.dark) .detail-back-btn:hover {
            background: #ffffff;
            border-color: rgba(180,83,9,0.35);
        }
        html:not(.dark) .detail-spec-tile {
            border-color: rgba(15,23,42,0.10);
            background: rgba(255,255,255,0.88);
        }
        html:not(.dark) .detail-spec-tile:hover {
            border-color: rgba(180,83,9,0.35);
            background: #ffffff;
        }
        html:not(.dark) .detail-muted { color: #4b5563 !important; }
        html:not(.dark) .detail-text { color: #111827 !important; }
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
            <div class="detail-viewer-surface rounded-[1.3rem] border border-white/[0.06] bg-zinc-950/90 backdrop-blur-xl overflow-hidden">
                <div class="detail-viewer-stage relative flex items-center justify-center p-4 sm:p-10 md:p-12 min-h-[220px] bg-zinc-950"
                     style="background-image: radial-gradient(ellipse 80% 70% at 50% 45%, rgba(55,55,62,0.45) 0%, #090807 68%);">
                    <div class="pointer-events-none absolute inset-0 opacity-[0.15]" style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 48px 48px;"></div>
                    <img src="{{ url($media->url) }}" alt="{{ $imgAlt }}"
                         class="relative z-[1] max-w-full w-auto max-h-[min(70vh,800px)] h-auto object-contain rounded-xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.85)] ring-1 ring-black/40">
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4">
            @if(isset($media->price) && $media->price > 0)
            <button type="button" id="btn-acheter"
                    data-uuid="{{ $media->uuid }}"
                    data-title="{{ $media->title ?? $media->original_name }}"
                    data-price-label="{{ $priceLabel }}"
                    data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
                    @auth
                    data-user-name="{{ trim(auth()->user()->first_name . ' ' . auth()->user()->last_name) }}"
                    data-user-email="{{ auth()->user()->email }}"
                    data-user-phone="{{ auth()->user()->phone ?? '' }}"
                    @endauth
                    class="group inline-flex flex-1 sm:flex-initial min-w-0 items-center justify-center gap-2.5 px-6 py-3.5 rounded-full bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 text-black text-sm font-bold shadow-lg shadow-amber-900/35 hover:from-amber-300 hover:via-amber-400 hover:to-amber-500 transition-all duration-300 font-plus">
                <i class="fas fa-cart-shopping text-xs opacity-90 group-hover:scale-110 transition-transform"></i>
                Acheter — {{ $priceLabel }}
            </button>
            @endif
            <a href="{{ route('gallery.public') }}"
               class="detail-back-btn inline-flex flex-1 sm:flex-initial items-center justify-center gap-2.5 px-6 py-3.5 rounded-full border border-white/12 bg-white/[0.04] backdrop-blur-md text-gray-100 text-sm font-semibold hover:bg-white/[0.08] hover:border-amber-400/30 transition-all duration-300 font-plus">
                <i class="fas fa-arrow-left text-xs text-amber-400/80"></i>
                Retour à la galerie
            </a>
        </div>

        {{-- Métadonnées --}}
        <div class="detail-meta-card rounded-[1.35rem] border border-white/[0.08] bg-white/[0.03] backdrop-blur-2xl shadow-[0_4px_24px_-4px_rgba(0,0,0,0.5),inset_0_1px_0_0_rgba(255,255,255,0.05)] p-6 sm:p-8 md:p-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 pb-6 border-b border-white/[0.06]">
                <div>
                    <p class="text-[10px] font-plus font-bold uppercase tracking-[0.22em] text-amber-500/80 mb-2">Fiche technique</p>
                    <h2 class="detail-text font-serif text-xl sm:text-2xl font-semibold tracking-tight text-white flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/25 bg-gradient-to-br from-amber-500/15 to-transparent text-amber-400">
                            <i class="fas fa-layer-group text-sm"></i>
                        </span>
                        Informations sur l’image
                    </h2>
                </div>
                <span class="detail-muted inline-flex items-center gap-2 self-start rounded-full border border-white/10 bg-black/30 px-3 py-1.5 text-[11px] text-gray-300 font-plus">
                    <i class="fas fa-fingerprint text-amber-500/50 text-[10px]"></i>
                    Média sécurisé
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm">
                @if(trim((string) ($media->title ?? '')) !== '')
                    <div class="detail-spec-tile sm:col-span-2 rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="detail-muted text-gray-300 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Titre</dt>
                        <dd class="detail-text text-white font-serif text-base sm:text-lg font-medium leading-snug">{{ $media->title }}</dd>
                    </div>
                @endif
                @if(trim((string) ($media->alt_text ?? '')) !== '')
                    <div class="detail-spec-tile sm:col-span-2 rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="detail-muted text-gray-300 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Texte alternatif</dt>
                        <dd class="detail-text text-gray-100 leading-relaxed text-[13px] sm:text-sm">{{ $media->alt_text }}</dd>
                    </div>
                @endif
                @if(trim((string) ($media->credit ?? '')) !== '')
                    <div class="detail-spec-tile rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="detail-muted text-gray-300 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Crédit photo</dt>
                        <dd class="detail-text text-gray-100 flex items-center gap-2 text-[13px] sm:text-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400/90"><i class="fas fa-camera text-xs"></i></span>
                            <span class="break-words">{{ $media->credit }}</span>
                        </dd>
                    </div>
                @endif
                @if($priceLabel)
                    <div class="detail-spec-tile rounded-xl border border-white/[0.06] bg-black/20 p-4 sm:p-5">
                        <dt class="detail-muted text-gray-300 text-[10px] font-plus font-bold uppercase tracking-[0.18em] mb-2">Prix</dt>
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

{{-- ═══════════════════════ MODAL ACHAT ═══════════════════════ --}}
<div id="modal-achat" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    {{-- Backdrop --}}
    <div id="modal-backdrop" class="absolute inset-0 bg-black/75 backdrop-blur-sm"></div>

    {{-- Fenêtre --}}
    <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-[#0f1a0f] shadow-2xl overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3 bg-[#1a3a1a] px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                    <i class="fas fa-cart-shopping text-sm"></i>
                </div>
                <h2 id="modal-title" class="font-bold text-white text-base">Finaliser votre achat</h2>
            </div>
            <button id="modal-close" class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-white/10 hover:text-white transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Produit sélectionné --}}
        <div class="mx-5 mt-5 flex items-center justify-between rounded-xl border border-white/8 bg-white/[0.04] px-4 py-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Produit sélectionné</p>
                <p id="modal-media-title" class="mt-0.5 text-sm font-semibold text-white"></p>
            </div>
            <span id="modal-media-price" class="rounded-full bg-emerald-500 px-3 py-1 text-sm font-bold text-white"></span>
        </div>

        {{-- ÉTAPE 1 : Création de compte --}}
        <div id="step-register">
            <div class="mx-5 mt-4 rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-3 text-sm text-blue-200">
                <i class="fas fa-circle-info mr-2"></i>
                Remplissez vos informations pour créer votre compte et finaliser votre achat.
            </div>

            <form id="form-register" class="space-y-3 px-5 pt-4 pb-5">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-300">Prénom <span class="text-red-400">*</span></label>
                        <input type="text" name="first_name" placeholder="Prénom" required
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-emerald-500/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-300">Nom <span class="text-red-400">*</span></label>
                        <input type="text" name="last_name" placeholder="Nom" required
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-emerald-500/50 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-300">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" placeholder="email@exemple.ci" required
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-emerald-500/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-300">Téléphone <span class="text-red-400">*</span></label>
                        <input type="tel" name="phone" placeholder="+225 07 00 00 00 00" required
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-emerald-500/50 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-300">Mot de passe <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" placeholder="••••••••" required
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 pr-9 text-sm text-white placeholder:text-gray-600 focus:border-emerald-500/50 focus:outline-none">
                        <button type="button" class="toggle-pw absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300"><i class="fas fa-eye-slash text-xs"></i></button>
                    </div>
                </div>
                <p class="text-[11px] text-gray-500">Minimum 8 caractères</p>

                <div id="register-error" class="hidden rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-xs text-red-300"></div>

                <div class="rounded-xl border border-white/6 bg-white/[0.03] px-4 py-2.5 text-xs text-gray-400">
                    <i class="fas fa-shield-halved mr-1.5 text-emerald-400/70"></i>
                    En continuant, vous acceptez nos conditions d'utilisation et notre politique de confidentialité.
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" id="btn-cancel-register"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-full border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/5">
                        <i class="fas fa-times text-xs"></i> Annuler
                    </button>
                    <button type="submit" id="btn-submit-register"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-500 disabled:opacity-50">
                        <i class="fas fa-cart-plus text-xs"></i>
                        <span id="btn-register-label">Créer mon compte et continuer</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ÉTAPE 2 : Récapitulatif + paiement --}}
        <div id="step-summary" class="hidden px-5 pb-5 pt-4 space-y-4">
            {{-- Utilisateur --}}
            <div class="flex items-center gap-3 rounded-xl border border-white/8 bg-white/[0.04] px-4 py-3">
                <div id="summary-avatar" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white"></div>
                <div class="min-w-0">
                    <p id="summary-name" class="truncate text-sm font-semibold text-white"></p>
                    <p id="summary-email" class="truncate text-xs text-gray-400"></p>
                    <p id="summary-phone" class="text-xs text-gray-500"></p>
                </div>
                <span class="ml-auto shrink-0 rounded-full border border-emerald-500/40 bg-emerald-500/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-300">
                    <i class="fas fa-circle-check mr-1 text-[10px]"></i>Connecté
                </span>
            </div>

            {{-- Détails commande --}}
            <div class="rounded-xl border border-white/8 bg-white/[0.04] divide-y divide-white/5 text-sm">
                <div class="flex justify-between px-4 py-3 text-gray-400">
                    <span>Point de retrait</span><span class="text-white">Numérique</span>
                </div>
                <div class="flex justify-between px-4 py-3 text-gray-400">
                    <span>Date</span><span id="summary-date" class="text-white"></span>
                </div>
                <div class="flex justify-between px-4 py-3 text-gray-400">
                    <span>Statut</span>
                    <span class="rounded-full border border-amber-500/40 bg-amber-500/15 px-2.5 py-0.5 text-[11px] font-semibold text-amber-300">
                        <i class="fas fa-clock mr-1 text-[10px]"></i>En attente de paiement
                    </span>
                </div>
            </div>

            {{-- Total --}}
            <div class="flex items-center justify-between rounded-xl border border-emerald-500/20 bg-emerald-500/8 px-4 py-3">
                <span class="text-sm font-semibold text-gray-200">Total à payer</span>
                <span id="summary-total" class="text-xl font-bold text-emerald-300"></span>
            </div>

            <div id="pay-error" class="hidden rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-xs text-red-300"></div>

            {{-- CTA paiement --}}
            <button type="button" id="btn-pay-cinetpay"
                    class="flex w-full items-center justify-center gap-2.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 py-3.5 text-sm font-bold text-white shadow-lg transition hover:brightness-110 disabled:opacity-50">
                <i class="fas fa-shield-halved text-xs"></i>
                <span id="btn-pay-label">Payer via CinetPay</span>
            </button>

            <div class="flex flex-wrap justify-center gap-4 text-[11px] text-gray-500">
                <span><i class="fas fa-shield-halved mr-1 text-emerald-400/60"></i>Paiement 100% sécurisé</span>
                <span><i class="fas fa-lock mr-1 text-emerald-400/60"></i>Données chiffrées SSL</span>
                <span><i class="fas fa-mobile-screen mr-1 text-emerald-400/60"></i>Mobile Money disponible</span>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal       = document.getElementById('modal-achat');
    const backdrop    = document.getElementById('modal-backdrop');
    const btnAcheter  = document.getElementById('btn-acheter');
    const btnClose    = document.getElementById('modal-close');
    const btnCancel   = document.getElementById('btn-cancel-register');
    const stepReg     = document.getElementById('step-register');
    const stepSum     = document.getElementById('step-summary');
    const formReg     = document.getElementById('form-register');
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let registerUrl = null;
    let payUrl = null;

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    function showStep(step) {
        stepReg.classList.toggle('hidden', step !== 'register');
        stepSum.classList.toggle('hidden', step !== 'summary');
        document.getElementById('modal-title').textContent =
            step === 'register' ? 'Créer un compte et finaliser votre achat' : 'Récapitulatif de votre commande';
    }

    function fillSummary(user, media) {
        const initials = user.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        document.getElementById('summary-avatar').textContent = initials;
        document.getElementById('summary-name').textContent   = user.name;
        document.getElementById('summary-email').textContent  = user.email;
        document.getElementById('summary-phone').textContent  = user.phone || '';
        document.getElementById('summary-total').textContent  = media.price_label;
        document.getElementById('summary-date').textContent   = new Date().toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
    }

    if (btnAcheter) {
        btnAcheter.addEventListener('click', () => {
            openModal();
            const d = btnAcheter.dataset;
            document.getElementById('modal-media-title').textContent = d.title;
            document.getElementById('modal-media-price').textContent  = d.priceLabel;

            registerUrl = `/galerie/achat/${d.uuid}/creer-et-payer`;
            payUrl      = `/galerie/achat/${d.uuid}/payer`;

            if (d.authenticated === 'true') {
                fillSummary(
                    { name: d.userName, email: d.userEmail, phone: d.userPhone },
                    { price_label: d.priceLabel }
                );
                showStep('summary');
            } else {
                showStep('register');
            }
        });
    }

    [btnClose, btnCancel, backdrop].forEach(el => el?.addEventListener('click', closeModal));

    // Afficher/masquer mot de passe
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.querySelector('i').className = isHidden ? 'fas fa-eye text-xs' : 'fas fa-eye-slash text-xs';
        });
    });

    // Soumission du formulaire d'inscription
    formReg?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errEl  = document.getElementById('register-error');
        const btnLbl = document.getElementById('btn-register-label');
        const btnBtn = document.getElementById('btn-submit-register');
        errEl.classList.add('hidden');
        btnBtn.disabled = true;
        btnLbl.textContent = 'Création en cours…';

        const body = Object.fromEntries(new FormData(formReg));

        try {
            const res  = await fetch(registerUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (data.success) {
                fillSummary(data.user, data.media);
                showStep('summary');
                if (data.payment_url) {
                    window.location.href = data.payment_url;
                }
            } else {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.message || 'Une erreur est survenue.');
                errEl.textContent = msg;
                errEl.classList.remove('hidden');
            }
        } catch (err) {
            errEl.textContent = 'Erreur réseau. Veuillez réessayer.';
            errEl.classList.remove('hidden');
        }

        btnBtn.disabled = false;
        btnLbl.textContent = 'Créer mon compte et continuer';
    });

    // Bouton payer CinetPay (utilisateur déjà connecté)
    document.getElementById('btn-pay-cinetpay')?.addEventListener('click', async () => {
        const errEl  = document.getElementById('pay-error');
        const btnLbl = document.getElementById('btn-pay-label');
        const btnBtn = document.getElementById('btn-pay-cinetpay');
        errEl.classList.add('hidden');
        btnBtn.disabled = true;
        btnLbl.textContent = 'Redirection en cours…';

        try {
            const res  = await fetch(payUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({}),
            });
            const data = await res.json();

            if (data.success && data.payment_url) {
                window.location.href = data.payment_url;
            } else {
                errEl.textContent = data.message || 'Une erreur est survenue.';
                errEl.classList.remove('hidden');
                btnBtn.disabled = false;
                btnLbl.textContent = 'Payer via CinetPay';
            }
        } catch (err) {
            errEl.textContent = 'Erreur réseau. Veuillez réessayer.';
            errEl.classList.remove('hidden');
            btnBtn.disabled = false;
            btnLbl.textContent = 'Payer via CinetPay';
        }
    });
})();
</script>
</body>
</html>
