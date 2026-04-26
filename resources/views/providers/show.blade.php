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

            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="font-serif text-3xl sm:text-4xl font-bold">{{ $provider->name }}</h1>
                        <p class="text-gray-500 text-sm mt-1">{{ $provider->category->name_fr ?? 'Catégorie' }} · {{ $provider->city ?: 'N/A' }}</p>
                        @if($provider->is_verified)
                            <span class="inline-flex mt-2 bg-emerald-500/20 text-emerald-300 text-xs px-2.5 py-1 rounded-full">Prestataire vérifié</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-amber-400 text-2xl font-semibold">{{ number_format((float) ($provider->rating_avg ?? 0), 1) }} ★</p>
                        <p class="text-gray-500 text-xs">{{ $provider->approvedReviews->count() }} avis</p>
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

                <p class="text-gray-300 mt-5 leading-relaxed">{{ $provider->description_fr ?: 'Description non disponible.' }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div class="bg-[#1c1c16] border border-white/5 rounded-lg p-4 text-sm">
                        <p><span class="text-gray-500">Adresse:</span> {{ $provider->address ?: 'N/A' }}</p>
                        <p class="mt-1"><span class="text-gray-500">Ville:</span> {{ $provider->city ?: 'N/A' }}</p>
                        <p class="mt-1"><span class="text-gray-500">Région:</span> {{ $provider->region ?: 'N/A' }}</p>
                    </div>
                    <div class="bg-[#1c1c16] border border-white/5 rounded-lg p-4 text-sm">
                        <p><span class="text-gray-500">Téléphone:</span> {{ $provider->phone ?: 'N/A' }}</p>
                        <p class="mt-1"><span class="text-gray-500">Email:</span> {{ $provider->email ?: 'N/A' }}</p>
                        <p class="mt-1"><span class="text-gray-500">Site web:</span>
                            @if($provider->website)
                                <a href="{{ $provider->website }}" target="_blank" class="text-amber-400 hover:text-amber-300">{{ $provider->website }}</a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
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
                        <a href="{{ route('providers.show', $r->slug) }}" class="bg-[#141410] border border-white/5 rounded-xl p-4 hover:border-amber-500/40 transition">
                            <p class="text-white font-semibold">{{ $r->name }}</p>
                            <p class="text-gray-500 text-sm mt-1">{{ $r->city ?: 'N/A' }}</p>
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
</body>
</html>
