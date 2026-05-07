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
                        <p class="mt-1.5"><span class="text-gray-500">Lien réserver :</span>
                            @if($provider->reserve_url || $provider->website)
                                <a href="{{ $provider->reserve_url ?: $provider->website }}" target="_blank" class="text-amber-400 hover:text-amber-300">{{ $provider->reserve_url ?: $provider->website }}</a>
                            @else
                                N/A
                            @endif
                        </p>
                        <p class="mt-1.5"><span class="text-gray-500">Lien commander :</span>
                            Formulaire de commande (bouton ci-dessous)
                        </p>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    @if(!empty($provider->reserve_url) || !empty($provider->website))
                        <div class="flex justify-center">
                            <a href="{{ $provider->reserve_url ?: $provider->website }}"
                               target="_blank" rel="noopener noreferrer"
                               class="provider-book-btn inline-flex items-center justify-center gap-2.5 rounded-xl text-black font-bold px-7 py-3.5 text-base">
                                <i class="fas fa-hotel text-sm"></i>
                                Effectuer une réservation d’hôtel
                            </a>
                        </div>
                    @endif

                    @auth
                        @if(auth()->user()->role === 'visitor')
                            <div class="flex justify-center">
                                <button type="button"
                                        onclick="openOrderModal()"
                                        class="provider-book-btn inline-flex items-center justify-center gap-2.5 rounded-xl text-black font-bold px-7 py-3 text-base">
                                    <i class="fas fa-cart-shopping text-sm"></i>
                                    Passer une commande
                                </button>
                            </div>
                        @else
                            <p class="text-center text-xs text-gray-500">
                                Le formulaire de commande est disponible pour les comptes visiteurs.
                            </p>
                        @endif
                    @else
                        <div class="flex justify-center">
                            <a href="{{ route('login') }}" class="provider-book-btn inline-flex items-center justify-center gap-2.5 rounded-xl text-black font-bold px-7 py-3 text-base">
                                <i class="fas fa-right-to-bracket text-sm"></i>
                                Se connecter pour commander
                            </a>
                        </div>
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
        <div id="order-modal" class="fixed inset-0 z-[90] hidden">
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
</body>
</html>
