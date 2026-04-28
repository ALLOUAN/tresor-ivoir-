<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $provider->name }} — Annuaire {{ $siteBrand['site_name'] }}</title>
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
    </style>
</head>
<body class="bg-[#0d0d0b] text-white">
    @include('partials.public-top-nav')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-900/30 border border-emerald-700 rounded-lg text-emerald-200 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-sm">{{ session('error') }}</div>
        @endif

        <a href="{{ route('providers.index') }}" class="text-amber-400 hover:text-amber-300 transition text-sm">← Retour à l'annuaire</a>

        <div class="mt-4 bg-[#141410] border border-white/8 rounded-xl overflow-hidden">
            <div class="h-56 bg-[#1c1c16]">
                @if($provider->cover_url)
                    <img src="{{ $provider->cover_url }}" alt="{{ $provider->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-600">
                        <i class="fas fa-store text-5xl"></i>
                    </div>
                @endif
            </div>

            <div class="p-6 provider-hero-panel rounded-b-xl">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="font-serif text-3xl sm:text-4xl font-bold leading-tight">{{ $provider->name }}</h1>
                        <p class="text-gray-400 text-sm mt-1.5">{{ $provider->category->name_fr ?? 'Catégorie' }} · {{ $provider->city ?: 'N/A' }}</p>
                        @if($provider->is_verified)
                            <span class="inline-flex mt-3 items-center gap-1.5 bg-emerald-500/18 border border-emerald-500/30 text-emerald-300 text-xs px-3 py-1.5 rounded-full">
                                <i class="fas fa-badge-check"></i> Prestataire vérifié
                            </span>
                        @endif
                    </div>
                    <div class="provider-chip rounded-xl px-4 py-3 text-right">
                        <p class="text-amber-300 text-2xl font-semibold leading-none">{{ number_format((float) ($provider->rating_avg ?? 0), 1) }} <span class="text-amber-400">★</span></p>
                        <p class="text-gray-400 text-xs mt-1">{{ $provider->approvedReviews->count() }} avis</p>
                    </div>
                </div>
                @auth
                    @if(auth()->user()->role === 'visitor')
                        <div class="mt-4">
                            @if(!($isFavorited ?? false))
                                <form method="POST" action="{{ route('visitor.favorites.store') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="provider">
                                    <input type="hidden" name="id" value="{{ $provider->id }}">
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

                <p class="text-gray-200 mt-6 leading-relaxed text-[15px]">{{ $provider->description_fr ?: 'Description non disponible.' }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div class="provider-info-card rounded-xl p-4 text-sm">
                        <p><span class="text-gray-500">Adresse :</span> {{ $provider->address ?: 'N/A' }}</p>
                        <p class="mt-1.5"><span class="text-gray-500">Ville :</span> {{ $provider->city ?: 'N/A' }}</p>
                        <p class="mt-1.5"><span class="text-gray-500">Région :</span> {{ $provider->region ?: 'N/A' }}</p>
                    </div>
                    <div class="provider-info-card rounded-xl p-4 text-sm">
                        <p><span class="text-gray-500">Téléphone :</span> {{ $provider->phone ?: 'N/A' }}</p>
                        <p class="mt-1.5"><span class="text-gray-500">Email :</span> {{ $provider->email ?: 'N/A' }}</p>
                        <p class="mt-1.5"><span class="text-gray-500">Site web :</span>
                            @if($provider->website)
                                <a href="{{ $provider->website }}" target="_blank" class="text-amber-400 hover:text-amber-300">{{ $provider->website }}</a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                @if(!empty($provider->website))
                    <div class="mt-5 flex justify-center">
                        <a href="{{ $provider->website }}"
                           target="_blank" rel="noopener noreferrer"
                           class="provider-book-btn inline-flex items-center justify-center gap-2.5 rounded-xl text-black font-bold px-7 py-3.5 text-base">
                            <i class="fas fa-hotel text-sm"></i>
                            Effectuer une réservation d’hôtel
                        </a>
                    </div>
                @endif
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
    <footer class="border-t border-white/5 bg-[#0d0d0b] py-6 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-gray-600">
            <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
            <a href="{{ route('providers.index') }}" class="hover:text-amber-400 transition">← Retour à l'annuaire</a>
        </div>
    </footer>
@include('partials.homepage-footer')
</body>
</html>
