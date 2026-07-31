<!DOCTYPE html>
<html lang="fr" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            background: linear-gradient(180deg, rgba(255, 255, 255,0.85), rgba(18,18,14,0.92));
            backdrop-filter: blur(8px);
        }
        .provider-chip {
            border: 1px solid rgba(255,255,255,0.11);
            background: rgba(233, 229, 217, 0.04);
        }
        .provider-book-btn {
            background: linear-gradient(135deg, #fa9a3c 0%, #f2790f 60%, #d4630a 100%);
            box-shadow: 0 12px 30px rgba(242, 121, 15,0.28);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .provider-book-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 16px 34px rgba(242, 121, 15,0.38);
        }
        .provider-similar-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(242, 121, 15,0.22);
            background: linear-gradient(155deg, rgba(255, 255, 255,0.92), rgba(17,17,14,0.96));
            box-shadow: 0 14px 30px rgba(0,0,0,0.28), inset 0 1px 0 rgba(255,255,255,0.04);
            transition: transform .26s cubic-bezier(0.2, 0.8, 0.2, 1), border-color .22s ease, box-shadow .26s ease;
        }
        .provider-similar-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(242, 121, 15,0.14), transparent 40%, rgba(255,255,255,0.03));
            opacity: .55;
            pointer-events: none;
        }
        .provider-similar-card:hover {
            transform: translateY(-4px);
            border-color: rgba(242, 121, 15,0.52);
            box-shadow: 0 20px 38px rgba(0,0,0,0.38), 0 0 22px rgba(242, 121, 15,0.14);
        }
        html:not(.dark) .provider-hero-panel {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #e9e5d9, #f8f4ec);
            box-shadow: 0 16px 30px rgba(0,0,0,0.07);
        }
        html:not(.dark) .provider-info-card {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(180deg, #e9e5d9, #f6f2ea);
        }
        html:not(.dark) .provider-chip {
            border-color: rgba(194, 94, 10,0.25);
            background: rgba(194, 94, 10,0.08);
        }
        html:not(.dark) .provider-similar-card {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(155deg, #e9e5d9, #f7f3eb);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body class="bg-[#ffffff] text-white">
    @include('partials.public-top-nav')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-900/30 border border-emerald-700 rounded-lg text-emerald-200 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-sm">{{ session('error') }}</div>
        @endif

        <a href="{{ route('providers.index') }}" class="text-orange-400 hover:text-orange-300 transition text-sm">← Retour à l'annuaire</a>

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

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start mt-4">
        <div class="lg:col-span-3 space-y-3">

            {{-- ── HERO ──────────────────────────────────────────────────────── --}}
            <div class="relative rounded-3xl overflow-hidden bg-[#ffffff]"
                 style="height:clamp(320px,60vh,540px)" id="prov-hero-wrap">

                @if($galleryPhotos->isNotEmpty())
                <img id="prov-hero-img"
                     src="{{ $galleryPhotos->first()['url'] }}"
                     alt="{{ $galleryPhotos->first()['alt'] }}"
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
                    <span class="inline-flex items-center gap-1.5 bg-green-950/35 backdrop-blur-md border border-white/10 text-white/80 text-xs px-3 py-1.5 rounded-full">
                        <i class="fas fa-location-dot text-orange-400 text-[10px]"></i> {{ $provider->city }}
                    </span>
                    @endif
                </div>

                {{-- Rating card top-right --}}
                <div class="absolute top-5 right-5 bg-green-950/45 backdrop-blur-xl border border-white/10 rounded-2xl px-4 py-3 text-center">
                    <p class="text-4xl font-black text-white tabular-nums leading-none">
                        {{ number_format((float)($provider->rating_avg ?? 0), 1) }}
                    </p>
                    @php $rounded = (int) round($provider->rating_avg ?? 0); @endphp
                    <div class="flex justify-center gap-0.5 mt-1.5">
                        @foreach(range(1,5) as $star)
                        <i class="fas fa-star text-[9px] @if($star <= $rounded) text-orange-400 @else text-white/20 @endif"></i>
                        @endforeach
                    </div>
                    <p class="text-white/35 text-[10px] mt-1.5 tabular-nums">{{ $provider->approvedReviews->count() }} avis</p>
                </div>

                {{-- Flèches pill --}}
                @if($galleryPhotos->count() > 1)
                <button onclick="provSlide(-1)"
                        class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-3 pr-4 rounded-full bg-green-950/35 hover:bg-green-950/65 backdrop-blur-md border border-white/12 text-white transition-all duration-200 hover:scale-105 active:scale-95">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span class="text-[11px] text-white/60 hidden sm:inline">Préc.</span>
                </button>
                <button onclick="provSlide(+1)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-4 pr-3 rounded-full bg-green-950/35 hover:bg-green-950/65 backdrop-blur-md border border-white/12 text-white transition-all duration-200 hover:scale-105 active:scale-95">
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
            <div class="bg-[#ffffff] rounded-3xl border border-white/6 overflow-hidden">
                <div class="px-5 pt-5 pb-4 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="font-serif text-2xl sm:text-3xl font-bold text-white leading-tight truncate">
                            {{ $provider->name }}
                        </h1>
                        <p class="text-orange-400/70 text-sm font-medium mt-0.5">
                            {{ $provider->category->name_fr ?? '' }}
                        </p>
                    </div>
                    @if($galleryPhotos->count() > 1)
                    <span id="prov-counter"
                          class="shrink-0 text-xs font-mono text-white/30 bg-white/5 px-2.5 py-1 rounded-lg tabular-nums mt-1">
                        {{ str_pad(1, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($galleryPhotos->count(), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    @endif
                </div>

                @if($galleryPhotos->count() > 1)
                <div class="flex gap-2 px-4 pb-4 overflow-x-auto" id="prov-thumbs"
                     style="scrollbar-width:none;-ms-overflow-style:none">
                    @foreach($galleryPhotos as $gi => $gp)
                    @php
                        $thumbCls = $gi === 0
                            ? 'shrink-0 w-21 h-14 rounded-xl overflow-hidden transition-all duration-300 ring-2 ring-orange-400 scale-105 shadow-lg shadow-orange-500/25'
                            : 'shrink-0 w-21 h-14 rounded-xl overflow-hidden transition-all duration-300 opacity-35 hover:opacity-75 hover:scale-105';
                    @endphp
                    <button data-idx="{{ $gi }}" onclick="provGoTo(+this.dataset.idx)"
                            id="prov-thumb-{{ $gi }}"
                            class="{{ $thumbCls }}">
                        <img src="{{ $gp['url'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ── PANEL INFO ───────────────────────────────────────────────── --}}
            <div class="bg-[#ffffff] rounded-3xl border border-white/6 p-5">

                {{-- Wishlist --}}
                @auth
                    @if(auth()->user()->role === 'visitor')
                    <div class="mb-5">
                        @if(!($isFavorited ?? false))
                        <form method="POST" action="{{ route('visitor.favorites.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="provider">
                            <input type="hidden" name="id" value="{{ $provider->id }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500/10 border border-orange-500/20 text-orange-300 text-xs font-medium hover:bg-orange-500/20 transition">
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
                    {{ $provider->description_fr ?: 'Description non disponible.' }}
                </p>

                {{-- Infos pratiques --}}
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach([
                        ['icon'=>'fa-location-dot','label'=>'Adresse',  'value'=>$provider->address],
                        ['icon'=>'fa-city',        'label'=>'Ville',    'value'=>$provider->city],
                        ['icon'=>'fa-map',         'label'=>'Région',   'value'=>$provider->region],
                        ['icon'=>'fa-phone',       'label'=>'Téléphone','value'=>$provider->phone],
                        ['icon'=>'fa-envelope',    'label'=>'Email',    'value'=>$provider->email],
                    ] as $row)
                    @if($row['value'])
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/4 hover:bg-white/6 transition">
                        <div class="w-7 h-7 rounded-lg bg-orange-500/12 flex items-center justify-center shrink-0">
                            <i class="fas {{ $row['icon'] }} text-orange-400 text-[11px]"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-600">{{ $row['label'] }}</p>
                            <p class="text-gray-200 text-sm truncate">{{ $row['value'] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach

                    @if($provider->website)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/4 hover:bg-white/6 transition sm:col-span-2">
                        <div class="w-7 h-7 rounded-lg bg-orange-500/12 flex items-center justify-center shrink-0">
                            <i class="fas fa-globe text-orange-400 text-[11px]"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-600">Site web</p>
                            <a href="{{ $provider->website }}" target="_blank"
                               class="text-orange-400 hover:text-orange-300 text-sm truncate block transition">
                                {{ $provider->website }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── AVIS CLIENTS ──────────────────────────────────────────────── --}}
            <div class="bg-[#ffffff] border border-white/8 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-4">Avis clients</h2>
                @php
                    $sortedReviews = $provider->approvedReviews->sortByDesc('created_at')->values();
                    $visibleReviews = $sortedReviews->take(2);
                    $extraReviews = $sortedReviews->slice(2);
                @endphp
                @if($sortedReviews->isEmpty())
                    <p class="text-gray-500 text-sm">Aucun avis approuvé pour ce prestataire.</p>
                @else
                    <div class="space-y-4">
                        @foreach($visibleReviews as $review)
                            <div class="border border-white/8 rounded-lg p-4 bg-[#ffffff]">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-white font-medium">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                                    <span class="text-orange-400 text-sm">{{ $review->rating }} ★</span>
                                </div>
                                @if($review->title)
                                    <p class="text-gray-200 text-sm font-medium">{{ $review->title }}</p>
                                @endif
                                <p class="text-gray-400 text-sm mt-1">{{ $review->comment }}</p>
                                @if($review->reply && $review->reply->is_visible)
                                    <div class="mt-3 bg-[#ffffff] rounded p-3 text-sm border border-white/5">
                                        <p class="text-orange-400 text-xs mb-1">Réponse du prestataire</p>
                                        <p class="text-gray-300">{{ $review->reply->reply_text }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($extraReviews->isNotEmpty())
                        <div id="reviews-more" class="hidden space-y-4 mt-4">
                            @foreach($extraReviews as $review)
                                <div class="border border-white/8 rounded-lg p-4 bg-[#ffffff]">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-white font-medium">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                                        <span class="text-orange-400 text-sm">{{ $review->rating }} ★</span>
                                    </div>
                                    @if($review->title)
                                        <p class="text-gray-200 text-sm font-medium">{{ $review->title }}</p>
                                    @endif
                                    <p class="text-gray-400 text-sm mt-1">{{ $review->comment }}</p>
                                    @if($review->reply && $review->reply->is_visible)
                                        <div class="mt-3 bg-[#ffffff] rounded p-3 text-sm border border-white/5">
                                            <p class="text-orange-400 text-xs mb-1">Réponse du prestataire</p>
                                            <p class="text-gray-300">{{ $review->reply->reply_text }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <button type="button" id="reviews-toggle"
                                data-more-label="Voir plus ({{ $extraReviews->count() }} avis)"
                                data-less-label="Voir moins"
                                class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-white/8 text-orange-400 text-sm font-semibold hover:bg-orange-500/5 hover:border-orange-500/30 transition">
                            <span>Voir plus ({{ $extraReviews->count() }} avis)</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                    @endif
                @endif
            </div>

            {{-- ── NOTE DÉTAILLÉE + INFOS PRATIQUES ─────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="bg-[#ffffff] border border-white/8 rounded-xl p-5">
                    <h2 class="text-white font-semibold mb-4">Note détaillée</h2>
                    <div class="space-y-3 text-sm">
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
                                    <span class="text-orange-400 font-semibold">{{ number_format((float) $criterion['value'], 1) }}/5 · {{ $criterion['count'] }} avis</span>
                                </div>
                                <p class="text-xs text-gray-500">{{ str_repeat('★', (int) round($criterion['value'])) }}{{ str_repeat('☆', max(0, 5 - (int) round($criterion['value']))) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-[#ffffff] border border-white/8 rounded-xl p-5">
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

            @auth
                @if($canReview)
                    <div class="bg-[#ffffff] border border-white/8 rounded-xl p-5">
                        <h2 class="text-white font-semibold mb-3">Laisser un avis</h2>
                        <form method="POST" action="{{ route('reviews.store', $provider) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @csrf
                            <div>
                                <label class="text-xs text-gray-400">Note globale *</label>
                                <select name="rating" required class="mt-1 w-full bg-[#ffffff] border border-white/10 rounded px-3 py-2 text-sm">
                                    <option value="">Choisir...</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ $i }} étoile(s)</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400">Titre</label>
                                <input type="text" name="title" class="mt-1 w-full bg-[#ffffff] border border-white/10 rounded px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs text-gray-400">Commentaire *</label>
                                <textarea name="comment" rows="4" required class="mt-1 w-full bg-[#ffffff] border border-white/10 rounded px-3 py-2 text-sm"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <button class="bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg transition">Envoyer l'avis</button>
                            </div>
                        </form>
                    </div>
                @endif
            @endauth
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             SIDEBAR RÉSERVATION (sticky)
        ═══════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2">
            <div class="lg:sticky lg:top-24 space-y-3">
                @if($accommodation && !empty($accommodation->room_types))
                @php
                    $depositPercent = app(\App\Services\ReservationPricingService::class)->depositPercent();
                @endphp
                <div class="bg-[#ffffff] rounded-3xl border border-white/6 p-5" id="booking-module" data-deposit-percent="{{ $depositPercent }}">
                    <h2 class="text-white font-semibold mb-1">Réserver une chambre</h2>
                    <p class="text-gray-500 text-xs mb-4">Acompte de {{ rtrim(rtrim(number_format($depositPercent, 1), '0'), '.') }}% payé en ligne · solde réglé sur place à l'hôtel.</p>

                    {{-- Cartes chambres --}}
                    <div class="grid grid-cols-1 gap-3 mb-5">
                        @foreach($accommodation->room_types as $room)
                        <button type="button"
                                class="bkp-room-card text-left rounded-2xl border border-white/8 bg-white/4 hover:border-orange-500/40 hover:bg-orange-500/5 p-3.5 transition flex items-center gap-3"
                                data-room-name="{{ $room['name'] ?? 'Chambre' }}"
                                data-room-price="{{ (int) ($room['price_xof'] ?? 0) }}">
                            @if(!empty($room['thumbnail']))
                            <div class="w-16 h-16 shrink-0 rounded-xl overflow-hidden bg-white/6">
                                <img src="{{ $room['thumbnail'] }}" alt="" class="w-full h-full object-cover">
                            </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-white text-sm font-semibold truncate">{{ $room['name'] ?? 'Chambre' }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">
                                    <i class="fas fa-user text-orange-400/60 mr-1"></i>{{ $room['max_adults'] ?? 2 }} pers. max
                                    @if(!empty($room['area_m2'])) · {{ $room['area_m2'] }} m² @endif
                                </p>
                                <p class="text-orange-400 text-sm font-bold mt-1">
                                    {{ number_format((int) ($room['price_xof'] ?? 0), 0, ',', ' ') }} XOF<span class="text-gray-500 font-normal text-xs">/nuit</span>
                                </p>
                            </div>
                        </button>
                        @endforeach
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-2.5 mb-3">
                        <div>
                            <label class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Arrivée</label>
                            <input type="date" id="bkp-checkin" class="w-full bg-white/4 border border-white/8 rounded-xl px-3 py-2 text-sm text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Départ</label>
                            <input type="date" id="bkp-checkout" class="w-full bg-white/4 border border-white/8 rounded-xl px-3 py-2 text-sm text-white">
                        </div>
                    </div>

                    {{-- Chambres / voyageurs --}}
                    <div class="grid grid-cols-2 gap-2.5 mb-4">
                        <div>
                            <label class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Chambres</label>
                            <div class="flex items-center bg-white/4 border border-white/8 rounded-xl overflow-hidden">
                                <button type="button" class="bkp-step w-9 h-9 text-gray-400 hover:text-orange-400" data-target="bkp-rooms" data-step="-1">−</button>
                                <span id="bkp-rooms" class="flex-1 text-center text-white text-sm font-semibold" data-value="1">1</span>
                                <button type="button" class="bkp-step w-9 h-9 text-gray-400 hover:text-orange-400" data-target="bkp-rooms" data-step="1">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Voyageurs</label>
                            <div class="flex items-center bg-white/4 border border-white/8 rounded-xl overflow-hidden">
                                <button type="button" class="bkp-step w-9 h-9 text-gray-400 hover:text-orange-400" data-target="bkp-guests" data-step="-1">−</button>
                                <span id="bkp-guests" class="flex-1 text-center text-white text-sm font-semibold" data-value="2">2</span>
                                <button type="button" class="bkp-step w-9 h-9 text-gray-400 hover:text-orange-400" data-target="bkp-guests" data-step="1">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Récapitulatif de prix en direct --}}
                    <div id="bkp-breakdown" class="hidden rounded-2xl border border-orange-500/20 bg-orange-500/5 px-4 py-3.5 mb-4 text-sm">
                        <div class="flex items-center justify-between text-gray-400 mb-1">
                            <span id="bkp-nights-label"></span>
                            <span id="bkp-total" class="text-gray-200 font-medium"></span>
                        </div>
                        <div class="flex items-center justify-between pt-1.5 mt-1.5 border-t border-orange-500/15">
                            <span class="text-gray-300">Acompte à payer maintenant</span>
                            <span id="bkp-deposit" class="text-orange-400 font-bold"></span>
                        </div>
                    </div>

                    {{-- Coordonnées --}}
                    <div class="grid grid-cols-1 gap-2.5 mb-3">
                        <input type="text" id="bkp-full-name" placeholder="Nom complet" class="w-full bg-white/4 border border-white/8 rounded-xl px-3 py-2.5 text-sm text-white placeholder:text-gray-600">
                        <input type="tel" id="bkp-phone" placeholder="Téléphone" class="w-full bg-white/4 border border-white/8 rounded-xl px-3 py-2.5 text-sm text-white placeholder:text-gray-600">
                        <input type="email" id="bkp-email" placeholder="Email" class="w-full bg-white/4 border border-white/8 rounded-xl px-3 py-2.5 text-sm text-white placeholder:text-gray-600">
                    </div>

                    <div id="bkp-error" class="hidden mb-3 px-3 py-2 bg-red-500/10 border border-red-500/25 rounded-lg text-red-300 text-xs"></div>

                    <button type="button" id="bkp-submit" disabled
                            class="flex items-center justify-center gap-2.5 w-full px-5 py-4 rounded-2xl bg-orange-500 hover:bg-orange-400 text-black font-bold text-[15px] transition-all duration-200 hover:shadow-xl hover:shadow-orange-500/20 active:scale-[.98] disabled:opacity-50 disabled:pointer-events-none">
                        <i class="fas fa-lock text-sm"></i>
                        <span id="bkp-submit-label">Sélectionnez une chambre</span>
                    </button>
                    <p class="text-center text-xs text-gray-600 py-1 mt-2">Paiement sécurisé via CinetPay · solde réglé sur place</p>
                </div>
                @elseif(!empty($provider->reserve_url) || !empty($provider->website))
                <div class="bg-[#ffffff] rounded-3xl border border-white/6 p-5">
                    <h2 class="text-white font-semibold mb-1">Contacter cet établissement</h2>
                    <p class="text-gray-500 text-xs mb-4">Réservation directement auprès du prestataire.</p>
                    <a href="{{ $provider->reserve_url ?: $provider->website }}"
                       target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-between w-full px-5 py-4 rounded-2xl bg-orange-500 hover:bg-orange-400 text-black font-bold text-[15px] transition-all duration-200 hover:shadow-xl hover:shadow-orange-500/20 active:scale-[.98] group">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-hotel text-sm"></i> Effectuer une réservation
                        </span>
                        <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
        </div>

        @if($related->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-white font-semibold mb-3">Prestataires similaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($related as $r)
                        <a href="{{ route('providers.show', $r->slug) }}" class="provider-similar-card rounded-xl p-4">
                            <p class="text-orange-300 text-[11px] uppercase tracking-[0.16em] font-medium">{{ $r->category->name_fr ?? 'Prestataire' }}</p>
                            <p class="text-white font-semibold mt-1.5 leading-snug">{{ $r->name }}</p>
                            <p class="text-gray-400 text-sm mt-1.5">{{ $r->city ?: 'N/A' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@include('partials.homepage-footer')

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

    const ACTIVE   = ['ring-2','ring-orange-400','ring-offset-2','ring-offset-[#ffffff]','scale-[1.06]','shadow-lg','shadow-orange-500/25'];
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

// ── Avis clients : voir plus / voir moins ───────────────────────────────────
(function () {
    const toggle = document.getElementById('reviews-toggle');
    const more = document.getElementById('reviews-more');
    if (!toggle || !more) return;

    const moreLabel = toggle.dataset.moreLabel;
    const lessLabel = toggle.dataset.lessLabel;
    const label = toggle.querySelector('span');
    const icon = toggle.querySelector('i');

    toggle.addEventListener('click', () => {
        const nowHidden = more.classList.toggle('hidden');
        label.textContent = nowHidden ? moreLabel : lessLabel;
        icon.classList.toggle('fa-chevron-down', nowHidden);
        icon.classList.toggle('fa-chevron-up', !nowHidden);
    });
})();

// ── Booking module (chambre + acompte en ligne) ────────────────────────────
(function () {
    const module = document.getElementById('booking-module');
    if (!module) return;

    const depositPercent = parseFloat(module.dataset.depositPercent || '30');
    let selectedRoom = null;

    const checkin = document.getElementById('bkp-checkin');
    const checkout = document.getElementById('bkp-checkout');
    const submitBtn = document.getElementById('bkp-submit');
    const submitLabel = document.getElementById('bkp-submit-label');
    const breakdown = document.getElementById('bkp-breakdown');
    const errorBox = document.getElementById('bkp-error');

    const today = new Date().toISOString().slice(0, 10);
    if (checkin) checkin.min = today;
    if (checkout) checkout.min = today;

    document.querySelectorAll('.bkp-room-card').forEach((card) => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.bkp-room-card').forEach((c) => {
                c.classList.remove('border-orange-500/60', 'bg-orange-500/5');
            });
            card.classList.add('border-orange-500/60', 'bg-orange-500/5');
            selectedRoom = {
                name: card.dataset.roomName,
                price: parseInt(card.dataset.roomPrice, 10) || 0,
            };
            recompute();
        });
    });

    document.querySelectorAll('.bkp-step').forEach((btn) => {
        btn.addEventListener('click', () => {
            const el = document.getElementById(btn.dataset.target);
            if (!el) return;
            const step = parseInt(btn.dataset.step, 10);
            const current = parseInt(el.dataset.value || '1', 10);
            const next = Math.max(1, current + step);
            el.dataset.value = String(next);
            el.textContent = String(next);
            recompute();
        });
    });

    if (checkin) checkin.addEventListener('change', () => { if (checkout) checkout.min = checkin.value; recompute(); });
    if (checkout) checkout.addEventListener('change', recompute);

    function nightsBetween(inStr, outStr) {
        if (!inStr || !outStr) return 0;
        const diff = Math.round((new Date(outStr) - new Date(inStr)) / 86400000);
        return diff > 0 ? diff : 0;
    }

    function recompute() {
        if (!selectedRoom) {
            submitLabel.textContent = 'Sélectionnez une chambre';
            submitBtn.disabled = true;
            breakdown.classList.add('hidden');
            return;
        }

        const nights = nightsBetween(checkin.value, checkout.value);
        const rooms = parseInt(document.getElementById('bkp-rooms').dataset.value || '1', 10);

        if (nights <= 0) {
            submitLabel.textContent = 'Choisissez vos dates';
            submitBtn.disabled = true;
            breakdown.classList.add('hidden');
            return;
        }

        const total = selectedRoom.price * nights * rooms;
        const deposit = Math.round(total * depositPercent / 100);

        document.getElementById('bkp-nights-label').textContent =
            nights + ' nuit' + (nights > 1 ? 's' : '') + ' × ' + rooms + ' chambre' + (rooms > 1 ? 's' : '');
        document.getElementById('bkp-total').textContent = total.toLocaleString('fr-FR') + ' XOF';
        document.getElementById('bkp-deposit').textContent = deposit.toLocaleString('fr-FR') + ' XOF';
        breakdown.classList.remove('hidden');

        submitLabel.textContent = "Réserver et payer l'acompte (" + deposit.toLocaleString('fr-FR') + ' XOF)';
        submitBtn.disabled = false;
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            errorBox.classList.add('hidden');

            const fullName = document.getElementById('bkp-full-name').value.trim();
            const email = document.getElementById('bkp-email').value.trim();
            const phone = document.getElementById('bkp-phone').value.trim();

            if (!fullName || !email) {
                errorBox.textContent = 'Merci de renseigner votre nom complet et votre email.';
                errorBox.classList.remove('hidden');
                return;
            }

            const originalLabel = submitLabel.textContent;
            submitBtn.disabled = true;
            submitLabel.textContent = 'Traitement en cours...';

            try {
                const res = await fetch('{{ route("reservations.payment.initiate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        accommodation_id: {{ $accommodation->id ?? 'null' }},
                        room_name: selectedRoom.name,
                        room_price_xof: selectedRoom.price,
                        check_in: checkin.value,
                        check_out: checkout.value,
                        rooms_count: parseInt(document.getElementById('bkp-rooms').dataset.value || '1', 10),
                        guests_count: parseInt(document.getElementById('bkp-guests').dataset.value || '2', 10),
                        full_name: fullName,
                        email: email,
                        phone: phone,
                    }),
                });
                const data = await res.json();

                if (data.success && data.payment_url) {
                    window.location.href = data.payment_url;
                    return;
                }

                errorBox.textContent = data.message || 'Une erreur est survenue, merci de réessayer.';
                errorBox.classList.remove('hidden');
            } catch (e) {
                errorBox.textContent = 'Erreur réseau, merci de réessayer.';
                errorBox.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitLabel.textContent = originalLabel;
            }
        });
    }
})();
</script>
@include('partials.image-protection')
</body>
</html>
