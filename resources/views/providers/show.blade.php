<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $provider->name }} — Annuaire {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .provider-hero-panel {
            border: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(135deg, rgba(24,24,20,0.9), rgba(14,14,12,0.96));
            box-shadow: 0 24px 50px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .provider-info-card {
            border: 1px solid rgba(255,255,255,0.09);
            background: linear-gradient(180deg, rgba(28,28,22,0.85), rgba(18,18,14,0.92));
            backdrop-filter: blur(8px);
        }
        .provider-chip {
            border: 1px solid rgba(255,255,255,0.11);
            background: rgba(255,255,255,0.04);
        }
        .provider-book-btn {
            background: linear-gradient(135deg, #f5b942 0%, #e8a020 60%, #c4811a 100%);
            box-shadow: 0 12px 30px rgba(232,160,32,0.28);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .provider-book-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 16px 34px rgba(232,160,32,0.38);
        }
        .provider-similar-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(232,160,32,0.22);
            background: linear-gradient(155deg, rgba(28,28,22,0.92), rgba(17,17,14,0.96));
            box-shadow: 0 14px 30px rgba(0,0,0,0.28), inset 0 1px 0 rgba(255,255,255,0.04);
            transition: transform .26s cubic-bezier(0.2, 0.8, 0.2, 1), border-color .22s ease, box-shadow .26s ease;
        }
        .provider-similar-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(232,160,32,0.14), transparent 40%, rgba(255,255,255,0.03));
            opacity: .55;
            pointer-events: none;
        }
        .provider-similar-card:hover {
            transform: translateY(-4px);
            border-color: rgba(232,160,32,0.52);
            box-shadow: 0 20px 38px rgba(0,0,0,0.38), 0 0 22px rgba(232,160,32,0.14);
        }
        html:not(.dark) .provider-hero-panel {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #ffffff, #f8f4ec);
            box-shadow: 0 16px 30px rgba(0,0,0,0.07);
        }
        html:not(.dark) .provider-info-card {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(180deg, #ffffff, #f6f2ea);
        }
        html:not(.dark) .provider-chip {
            border-color: rgba(180,83,9,0.25);
            background: rgba(180,83,9,0.08);
        }
        html:not(.dark) .provider-similar-card {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(155deg, #ffffff, #f7f3eb);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        }
        .order-modal-backdrop {
            backdrop-filter: blur(5px);
        }
        .order-modal-card {
            transition: opacity .22s ease, transform .24s cubic-bezier(.2,.8,.2,1);
            transform-origin: center;
        }
        .order-pill {
            border: 1px solid rgba(16, 78, 45, 0.9);
            border-radius: 999px;
            background: rgba(255,255,255,0.02);
        }
        .guest-pop {
            border: 1px solid rgba(255,255,255,0.12);
            background: #141410;
            box-shadow: 0 18px 40px rgba(0,0,0,0.45);
        }
        .qty-btn {
            width: 36px; height: 36px; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #003b22; color: #fff; font-weight: 700;
        }
        .qty-btn:disabled { background: #9aaea4; cursor: not-allowed; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white" data-order-errors="{{ $errors->any() ? '1' : '0' }}">
    @include('partials.public-top-nav')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-900/30 border border-emerald-700 rounded-lg text-emerald-200 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-sm">{{ session('error') }}</div>
        @endif

        <a href="{{ route('providers.index') }}" class="text-amber-400 hover:text-amber-300 transition text-sm">← Retour à l'annuaire</a>

        @php
            $galleryPhotos = collect();
            if ($provider->cover_url) {
                $galleryPhotos->push(['url' => $provider->cover_url, 'alt' => $provider->name]);
            }
            foreach ($provider->media->where('type', 'image')->sortBy('sort_order') as $gm) {
                $galleryPhotos->push(['url' => $gm->url, 'alt' => $gm->alt_text ?: $provider->name]);
            }
        @endphp

        <style>
            @@keyframes heroFade { from { opacity:0; transform:scale(1.05) } to { opacity:1; transform:scale(1) } }
            #prov-hero-img { animation: heroFade .6s cubic-bezier(.22,1,.36,1) both }
        </style>
        <script id="gallery-json" type="application/json">@json($galleryPhotos->values())</script>

        <div class="mt-4 space-y-3">

            {{-- ── HERO ──────────────────────────────────────────────────────── --}}
            <div class="relative rounded-3xl overflow-hidden bg-[#0d0d0b]"
                 style="height:clamp(320px,60vh,540px)" id="prov-hero-wrap">

                @if($galleryPhotos->isNotEmpty())
                <img id="prov-hero-img"
                     src="{{ $galleryPhotos->first()[‘url’] }}"
                     alt="{{ $galleryPhotos->first()[‘alt’] }}"
                     class="w-full h-full object-cover">

                {{-- Gradient diagonal --}}
                <div class="absolute inset-0 pointer-events-none"
                     style="background:linear-gradient(135deg,rgba(0,0,0,.55) 0%,transparent 45%,rgba(0,0,0,.65) 100%)"></div>

                {{-- Badges top-left --}}
                <div class="absolute top-5 left-5 flex flex-wrap gap-2">
                    @if($provider->is_verified)
                    <span class="inline-flex items-center gap-1.5 bg-emerald-500/20 backdrop-blur-md border border-emerald-400/25 text-emerald-300 text-xs font-semibold px-3 py-1.5 rounded-full">
                        <i class="fas fa-circle-check text-[10px]"></i> Vérifié
                    </span>
                    @endif
                    @if($provider->city)
                    <span class="inline-flex items-center gap-1.5 bg-black/35 backdrop-blur-md border border-white/10 text-white/80 text-xs px-3 py-1.5 rounded-full">
                        <i class="fas fa-location-dot text-amber-400 text-[10px]"></i> {{ $provider->city }}
                    </span>
                    @endif
                </div>

                {{-- Rating card top-right --}}
                <div class="absolute top-5 right-5 bg-black/45 backdrop-blur-xl border border-white/10 rounded-2xl px-4 py-3 text-center">
                    <p class="text-4xl font-black text-white tabular-nums leading-none">
                        {{ number_format((float)($provider->rating_avg ?? 0), 1) }}
                    </p>
                    @php $rounded = (int) round($provider->rating_avg ?? 0); @endphp
                    <div class="flex justify-center gap-0.5 mt-1.5">
                        @foreach(range(1,5) as $star)
                        <i class="fas fa-star text-[9px] @if($star <= $rounded) text-amber-400 @else text-white/20 @endif"></i>
                        @endforeach
                    </div>
                    <p class="text-white/35 text-[10px] mt-1.5 tabular-nums">{{ $provider->approvedReviews->count() }} avis</p>
                </div>

                {{-- Flèches pill --}}
                @if($galleryPhotos->count() > 1)
                <button onclick="provSlide(-1)"
                        class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-3 pr-4 rounded-full bg-black/35 hover:bg-black/65 backdrop-blur-md border border-white/12 text-white transition-all duration-200 hover:scale-105 active:scale-95">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span class="text-[11px] text-white/60 hidden sm:inline">Préc.</span>
                </button>
                <button onclick="provSlide(+1)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-4 pr-3 rounded-full bg-black/35 hover:bg-black/65 backdrop-blur-md border border-white/12 text-white transition-all duration-200 hover:scale-105 active:scale-95">
                    <span class="text-[11px] text-white/60 hidden sm:inline">Suiv.</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
                @endif

                @else
                <div class="w-full h-full flex flex-col items-center justify-center gap-4">
                    <div class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center">
                        <i class="fas fa-store text-4xl text-gray-600"></i>
                    </div>
                    <p class="text-gray-600 text-sm">Aucune photo disponible</p>
                </div>
                @endif
            </div>

            {{-- ── CARTE TITRE + THUMBNAILS ────────────────────────────────── --}}
            <div class="bg-[#141410] rounded-3xl border border-white/6 overflow-hidden">
                <div class="px-5 pt-5 pb-4 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="font-serif text-2xl sm:text-3xl font-bold text-white leading-tight truncate">
                            {{ $provider->name }}
                        </h1>
                        <p class="text-amber-400/70 text-sm font-medium mt-0.5">
                            {{ $provider->category->name_fr ?? ‘’ }}
                        </p>
                    </div>
                    @if($galleryPhotos->count() > 1)
                    <span id="prov-counter"
                          class="shrink-0 text-xs font-mono text-white/30 bg-white/5 px-2.5 py-1 rounded-lg tabular-nums mt-1">
                        {{ str_pad(1, 2, ‘0’, STR_PAD_LEFT) }} / {{ str_pad($galleryPhotos->count(), 2, ‘0’, STR_PAD_LEFT) }}
                    </span>
                    @endif
                </div>

                @if($galleryPhotos->count() > 1)
                <div class="flex gap-2 px-4 pb-4 overflow-x-auto" id="prov-thumbs"
                     style="scrollbar-width:none;-ms-overflow-style:none">
                    @foreach($galleryPhotos as $gi => $gp)
                    @php
                        $thumbCls = $gi === 0
                            ? ‘shrink-0 w-21 h-14 rounded-xl overflow-hidden transition-all duration-300 ring-2 ring-amber-400 scale-105 shadow-lg shadow-amber-500/25’
                            : ‘shrink-0 w-21 h-14 rounded-xl overflow-hidden transition-all duration-300 opacity-35 hover:opacity-75 hover:scale-105’;
                    @endphp
                    <button data-idx="{{ $gi }}" onclick="provGoTo(+this.dataset.idx)"
                            id="prov-thumb-{{ $gi }}"
                            class="{{ $thumbCls }}">
                        <img src="{{ $gp[‘url’] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ── PANEL INFO ───────────────────────────────────────────────── --}}
            <div class="bg-[#141410] rounded-3xl border border-white/6 p-5">

                {{-- Wishlist --}}
                @auth
                    @if(auth()->user()->role === ‘visitor’)
                    <div class="mb-5">
                        @if(!($isFavorited ?? false))
                        <form method="POST" action="{{ route(‘visitor.favorites.store’) }}">
                            @csrf
                            <input type="hidden" name="type" value="provider">
                            <input type="hidden" name="id" value="{{ $provider->id }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-medium hover:bg-amber-500/20 transition">
                                <i class="fas fa-heart text-[10px]"></i> Ajouter à ma wishlist
                            </button>
                        </form>
                        @else
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-medium">
                            <i class="fas fa-heart-circle-check text-[10px]"></i> Déjà dans vos favoris
                        </span>
                        @endif
                    </div>
                    @endif
                @endauth

                {{-- Description --}}
                <p class="text-gray-300 leading-relaxed text-[15px]">
                    {{ $provider->description_fr ?: ‘Description non disponible.’ }}
                </p>

                {{-- Infos pratiques --}}
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach([
                        [‘icon’=>’fa-location-dot’,’label’=>’Adresse’,  ‘value’=>$provider->address],
                        [‘icon’=>’fa-city’,        ‘label’=>’Ville’,    ‘value’=>$provider->city],
                        [‘icon’=>’fa-map’,         ‘label’=>’Région’,   ‘value’=>$provider->region],
                        [‘icon’=>’fa-phone’,       ‘label’=>’Téléphone’,’value’=>$provider->phone],
                        [‘icon’=>’fa-envelope’,    ‘label’=>’Email’,    ‘value’=>$provider->email],
                    ] as $row)
                    @if($row[‘value’])
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/4 hover:bg-white/6 transition">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/12 flex items-center justify-center shrink-0">
                            <i class="fas {{ $row[‘icon’] }} text-amber-400 text-[11px]"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-600">{{ $row[‘label’] }}</p>
                            <p class="text-gray-200 text-sm truncate">{{ $row[‘value’] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach

                    @if($provider->website)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/4 hover:bg-white/6 transition sm:col-span-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/12 flex items-center justify-center shrink-0">
                            <i class="fas fa-globe text-amber-400 text-[11px]"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-600">Site web</p>
                            <a href="{{ $provider->website }}" target="_blank"
                               class="text-amber-400 hover:text-amber-300 text-sm truncate block transition">
                                {{ $provider->website }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- CTAs --}}
                <div class="mt-5 space-y-2.5">
                    @if(!empty($provider->reserve_url) || !empty($provider->website))
                    <a href="{{ $provider->reserve_url ?: $provider->website }}"
                       target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-between w-full px-5 py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-black font-bold text-[15px] transition-all duration-200 hover:shadow-xl hover:shadow-amber-500/20 active:scale-[.98] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-hotel text-sm"></i> Effectuer une réservation
                        </span>
                        <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    @endif

                    @auth
                        @if(auth()->user()->role === ‘visitor’)
                        <button onclick="openOrderModal()"
                                class="flex items-center justify-between w-full px-5 py-4 rounded-2xl bg-white/6 hover:bg-white/10 border border-white/8 text-white font-semibold text-[15px] transition-all duration-200 active:scale-[.98] group">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-cart-shopping text-sm text-amber-400"></i> Passer une commande
                            </span>
                            <i class="fas fa-arrow-right text-sm text-white/30 group-hover:translate-x-1 group-hover:text-white/60 transition"></i>
                        </button>
                        @else
                        <p class="text-center text-xs text-gray-600 py-1">
                            Le formulaire de commande est disponible pour les comptes visiteurs.
                        </p>
                        @endif
                    @else
                    <a href="{{ route(‘login’) }}"
                       class="flex items-center justify-between w-full px-5 py-4 rounded-2xl bg-white/6 hover:bg-white/10 border border-white/8 text-white font-semibold text-[15px] transition-all duration-200 active:scale-[.98] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-right-to-bracket text-sm text-amber-400"></i> Se connecter pour commander
                        </span>
                        <i class="fas fa-arrow-right text-sm text-white/30 group-hover:translate-x-1 group-hover:text-white/60 transition"></i>
                    </a>
                    @endauth
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="lg:col-span-2 bg-[#141410] border border-white/8 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-4">Avis clients</h2>
                <div class="space-y-4">
                    @forelse($provider->approvedReviews as $review)
                        <div class="border border-white/8 rounded-lg p-4 bg-[#0d0d0b]">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-white font-medium">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                                <span class="text-amber-400 text-sm">{{ $review->rating }} ★</span>
                            </div>
                            @if($review->title)
                                <p class="text-gray-200 text-sm font-medium">{{ $review->title }}</p>
                            @endif
                            <p class="text-gray-400 text-sm mt-1">{{ $review->comment }}</p>
                            @if($review->reply && $review->reply->is_visible)
                                <div class="mt-3 bg-[#1c1c16] rounded p-3 text-sm border border-white/5">
                                    <p class="text-amber-400 text-xs mb-1">Réponse du prestataire</p>
                                    <p class="text-gray-300">{{ $review->reply->reply_text }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Aucun avis approuvé pour ce prestataire.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-[#141410] border border-white/8 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-4">Note détaillée</h2>
                <div class="space-y-3 text-sm mb-5">
                    @php
                        $criteria = [
                            'quality' => ['label' => 'Qualité', 'value' => $ratingBreakdown['quality'] ?? 0, 'count' => $ratingBreakdownCounts['quality'] ?? 0],
                            'price' => ['label' => 'Prix', 'value' => $ratingBreakdown['price'] ?? 0, 'count' => $ratingBreakdownCounts['price'] ?? 0],
                            'welcome' => ['label' => 'Accueil', 'value' => $ratingBreakdown['welcome'] ?? 0, 'count' => $ratingBreakdownCounts['welcome'] ?? 0],
                            'clean' => ['label' => 'Propreté', 'value' => $ratingBreakdown['clean'] ?? 0, 'count' => $ratingBreakdownCounts['clean'] ?? 0],
                        ];
                    @endphp
                    @foreach($criteria as $criterion)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-300">{{ $criterion['label'] }}</span>
                                <span class="text-amber-400 font-semibold">{{ number_format((float) $criterion['value'], 1) }}/5 · {{ $criterion['count'] }} avis</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ str_repeat('★', (int) round($criterion['value'])) }}{{ str_repeat('☆', max(0, 5 - (int) round($criterion['value']))) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-white/8 pt-5">
                <h2 class="text-white font-semibold mb-4">Infos pratiques</h2>
                <p class="text-gray-300 text-sm">Prix: <span class="uppercase">{{ $provider->price_range ?: 'N/A' }}</span></p>
                <p class="text-gray-300 text-sm mt-1">Plage prix: {{ number_format((float) ($provider->price_min ?? 0), 0, ',', ' ') }} - {{ number_format((float) ($provider->price_max ?? 0), 0, ',', ' ') }} FCFA</p>

                <div class="mt-5">
                    <h3 class="text-gray-200 text-sm font-semibold mb-2">Horaires</h3>
                    <div class="space-y-1 text-xs text-gray-400">
                        @php
                            $days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                        @endphp
                        @forelse($provider->hours as $hour)
                            <p>{{ $days[$hour->day_of_week] ?? 'Jour' }}: {{ $hour->is_closed ? 'Fermé' : (($hour->open_time ?: '--:--') . ' - ' . ($hour->close_time ?: '--:--')) }}</p>
                        @empty
                            <p>Horaires non renseignés.</p>
                        @endforelse
                    </div>
                </div>
                </div>
            </div>
        </div>

        @auth
            @if($canReview)
                <div class="mt-6 bg-[#141410] border border-white/8 rounded-xl p-5">
                    <h2 class="text-white font-semibold mb-3">Laisser un avis</h2>
                    <form method="POST" action="{{ route('reviews.store', $provider) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @csrf
                        <div>
                            <label class="text-xs text-gray-400">Note globale *</label>
                            <select name="rating" required class="mt-1 w-full bg-[#1c1c16] border border-white/10 rounded px-3 py-2 text-sm">
                                <option value="">Choisir...</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} étoile(s)</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400">Titre</label>
                            <input type="text" name="title" class="mt-1 w-full bg-[#1c1c16] border border-white/10 rounded px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-400">Commentaire *</label>
                            <textarea name="comment" rows="4" required class="mt-1 w-full bg-[#1c1c16] border border-white/10 rounded px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button class="bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold px-4 py-2 rounded-lg transition">Envoyer l'avis</button>
                        </div>
                    </form>
                </div>
            @endif
        @endauth

        @if($related->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-white font-semibold mb-3">Prestataires similaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($related as $r)
                        <a href="{{ route('providers.show', $r->slug) }}" class="provider-similar-card rounded-xl p-4">
                            <p class="text-amber-300 text-[11px] uppercase tracking-[0.16em] font-medium">{{ $r->category->name_fr ?? 'Prestataire' }}</p>
                            <p class="text-white font-semibold mt-1.5 leading-snug">{{ $r->name }}</p>
                            <p class="text-gray-400 text-sm mt-1.5">{{ $r->city ?: 'N/A' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@include('partials.homepage-footer')

@auth
    @if(auth()->user()->role === 'visitor')
        <div id="order-modal" class="fixed inset-0 z-90 hidden">
            <div id="order-modal-backdrop" class="absolute inset-0 bg-black/70 order-modal-backdrop opacity-0 transition-opacity duration-200" onclick="closeOrderModal()"></div>
            <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
                <div id="order-modal-card" class="order-modal-card w-full max-w-2xl bg-[#141410] border border-white/10 rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col opacity-0 scale-95 translate-y-3">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                        <h2 class="text-white font-semibold">Passer une commande</h2>
                        <button type="button" onclick="closeOrderModal()" class="text-slate-400 hover:text-white">
                            <i class="fas fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('providers.order', $provider->slug) }}" class="p-5 overflow-y-auto space-y-3">
                        @csrf
                        @if($errors->any())
                            <div class="p-2.5 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-xs">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Nom complet *</label>
                                <input type="text" name="customer_name" required
                                       value="{{ old('customer_name', auth()->user()->full_name) }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Téléphone *</label>
                                <input type="text" name="customer_phone" required value="{{ old('customer_phone') }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100"
                                       placeholder="+225 ...">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Email</label>
                                <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Service souhaité *</label>
                                <input type="text" name="service_requested" required value="{{ old('service_requested') }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100"
                                       placeholder="Ex: Réservation, livraison, devis...">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Arrivée</label>
                                <input type="date" name="arrival_date" value="{{ old('arrival_date') }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Départ</label>
                                <input type="date" name="departure_date" value="{{ old('departure_date') }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">Budget estimé (FCFA)</label>
                                <input type="number" min="0" step="1" name="estimated_budget" value="{{ old('estimated_budget') }}"
                                       class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100"
                                       placeholder="Ex: 100000">
                            </div>
                            <div class="md:col-span-2 relative">
                                <label class="block text-xs text-gray-400 mb-1.5">Chambres/Personnes</label>
                                <button type="button" id="guest-trigger"
                                        class="order-pill w-full px-4 py-2.5 text-left text-sm text-white">
                                    <span id="guest-summary">1 chambre, 2 adultes, 0 enfant</span>
                                </button>
                                <input type="hidden" name="rooms" id="rooms" value="{{ old('rooms', 1) }}">
                                <input type="hidden" name="adults" id="adults" value="{{ old('adults', 2) }}">
                                <input type="hidden" name="children" id="children" value="{{ old('children', 0) }}">

                                <div id="guest-popover" class="guest-pop hidden absolute z-20 mt-2 w-full rounded-2xl p-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-white font-semibold">Chambres</p>
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="qty-btn" data-target="rooms" data-step="-1">−</button>
                                                <span id="rooms-value" class="text-white font-semibold w-4 text-center">1</span>
                                                <button type="button" class="qty-btn" data-target="rooms" data-step="1">+</button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-white font-semibold">Adultes</p>
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="qty-btn" data-target="adults" data-step="-1">−</button>
                                                <span id="adults-value" class="text-white font-semibold w-4 text-center">2</span>
                                                <button type="button" class="qty-btn" data-target="adults" data-step="1">+</button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-white font-semibold">Enfants</p>
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="qty-btn" data-target="children" data-step="-1">−</button>
                                                <span id="children-value" class="text-white font-semibold w-4 text-center">0</span>
                                                <button type="button" class="qty-btn" data-target="children" data-step="1">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Détails de la commande (optionnel)</label>
                            <textarea name="order_message" rows="4" maxlength="2000"
                                      class="w-full bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-100"
                                      placeholder="Ex: 2 chambres du 15 au 17 août, arrivée à 18h...">{{ old('order_message') }}</textarea>
                        </div>
                        <div class="pt-1 flex justify-end gap-2">
                            <button type="button" onclick="closeOrderModal()"
                                    class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="provider-book-btn inline-flex items-center justify-center gap-2 rounded-xl text-black font-bold px-5 py-2.5 text-sm">
                                <i class="fas fa-cart-shopping text-sm"></i>
                                Envoyer la commande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endauth

<script>
// ── Provider photo gallery ────────────────────────────────────────────────
(function () {
    const photos = JSON.parse(document.getElementById('gallery-json').textContent);
    if (photos.length <= 1) return;

    let current = 0;
    const hero    = document.getElementById('prov-hero-img');
    const counter = document.getElementById('prov-counter');
    const total   = photos.length;
    const pad     = n => String(n).padStart(2, '0');

    const ACTIVE   = ['ring-2','ring-amber-400','ring-offset-2','ring-offset-[#141410]','scale-[1.06]','shadow-lg','shadow-amber-500/25'];
    const INACTIVE = ['opacity-35'];

    function go(idx) {
        current = (idx + total) % total;

        // Animate image
        hero.style.animation = 'none';
        hero.offsetHeight;
        hero.style.animation = 'heroFade .6s cubic-bezier(.22,1,.36,1) both';
        hero.src = photos[current].url;
        hero.alt = photos[current].alt;

        if (counter) counter.textContent = pad(current + 1) + ' / ' + pad(total);

        document.querySelectorAll('[id^="prov-thumb-"]').forEach((btn, i) => {
            if (i === current) {
                btn.classList.add(...ACTIVE);
                btn.classList.remove(...INACTIVE);
            } else {
                btn.classList.remove(...ACTIVE);
                btn.classList.add(...INACTIVE);
            }
        });

        document.getElementById('prov-thumb-' + current)
            ?.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
    }

    window.provGoTo  = go;
    window.provSlide = (dir) => go(current + dir);
})();

// ── Orders modal ─────────────────────────────────────────────────────────
function openOrderModal() {
    const modal = document.getElementById('order-modal');
    const backdrop = document.getElementById('order-modal-backdrop');
    const card = document.getElementById('order-modal-card');
    if (!modal) return;
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        backdrop?.classList.remove('opacity-0');
        backdrop?.classList.add('opacity-100');
        card?.classList.remove('opacity-0', 'scale-95', 'translate-y-3');
        card?.classList.add('opacity-100', 'scale-100', 'translate-y-0');
    });
}

function closeOrderModal() {
    const modal = document.getElementById('order-modal');
    const backdrop = document.getElementById('order-modal-backdrop');
    const card = document.getElementById('order-modal-card');
    if (!modal) return;
    backdrop?.classList.remove('opacity-100');
    backdrop?.classList.add('opacity-0');
    card?.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
    card?.classList.add('opacity-0', 'scale-95', 'translate-y-3');
    setTimeout(() => modal.classList.add('hidden'), 240);
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeOrderModal();
    }
});

const guestTrigger = document.getElementById('guest-trigger');
const guestPopover = document.getElementById('guest-popover');
const roomsInput = document.getElementById('rooms');
const adultsInput = document.getElementById('adults');
const childrenInput = document.getElementById('children');
const summary = document.getElementById('guest-summary');

function clamp(val, min, max) {
    return Math.max(min, Math.min(max, val));
}

function refreshGuestUi() {
    if (!roomsInput || !adultsInput || !childrenInput || !summary) return;
    const r = parseInt(roomsInput.value || '1', 10);
    const a = parseInt(adultsInput.value || '2', 10);
    const c = parseInt(childrenInput.value || '0', 10);
    const roomsVal = document.getElementById('rooms-value');
    const adultsVal = document.getElementById('adults-value');
    const childrenVal = document.getElementById('children-value');
    if (roomsVal) roomsVal.textContent = String(r);
    if (adultsVal) adultsVal.textContent = String(a);
    if (childrenVal) childrenVal.textContent = String(c);
    summary.textContent = `${r} chambre${r > 1 ? 's' : ''}, ${a} adulte${a > 1 ? 's' : ''}, ${c} enfant${c > 1 ? 's' : ''}`;
}

if (guestTrigger && guestPopover) {
    guestTrigger.addEventListener('click', () => {
        guestPopover.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Node)) return;
        if (!guestPopover.contains(target) && !guestTrigger.contains(target)) {
            guestPopover.classList.add('hidden');
        }
    });

    guestPopover.querySelectorAll('.qty-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            const step = parseInt(btn.getAttribute('data-step') || '0', 10);
            if (!target || !['rooms', 'adults', 'children'].includes(target)) return;
            const input = document.getElementById(target);
            if (!input) return;

            const current = parseInt(input.value || '0', 10);
            const limits = target === 'children' ? [0, 20] : [1, 20];
            input.value = String(clamp(current + step, limits[0], limits[1]));
            refreshGuestUi();
        });
    });

    refreshGuestUi();
}

const hasOrderErrors = document.body?.dataset?.orderErrors === '1';
if (hasOrderErrors) {
    openOrderModal();
}
</script>
@include('partials.image-protection')
</body>
</html>
