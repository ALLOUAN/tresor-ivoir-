<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $people->name }} — Cultures Ivoiriennes — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0a0a09; color: #fff; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .hero-img { transition: transform 8s ease; }
        .hero-wrap:hover .hero-img { transform: scale(1.04); }
        .tabs-bar { position: sticky; top: 0; z-index: 30; background: rgba(10,10,9,.92); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,.06); }
        .tab-btn { position: relative; padding: 14px 20px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; transition: color .2s; white-space: nowrap; }
        .tab-btn.active { color: #f59e0b; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 2px; background: #f59e0b; border-radius: 2px 2px 0 0; }
        .tab-btn:hover:not(.active) { color: #cbd5e1; }
        .tab-section { display: none; }
        .tab-section.active { display: block; }
        .element-card { transition: transform .2s ease, border-color .2s ease; }
        .element-card:hover { transform: translateY(-3px); }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .tabs-bar { background: rgba(245,240,235,.95); border-color: rgba(0,0,0,.08); }
        html:not(.dark) .info-block { background: #fff !important; border-color: rgba(0,0,0,.08) !important; }
    </style>
</head>
<body class="min-h-screen">

@include('partials.public-top-nav')

{{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
<section class="hero-wrap relative h-[500px] md:h-[640px] overflow-hidden">

    @if($people->cover_image)
    <img src="{{ $people->cover_image }}" alt="Bannière {{ $people->name }}"
        class="hero-img w-full h-full object-cover scale-105"
        style="object-position: center 40%;">
    @elseif($people->thumbnail)
    <img src="{{ $people->thumbnail }}" alt="Bannière {{ $people->name }}"
        class="hero-img w-full h-full object-cover scale-105">
    @else
    <div class="w-full h-full"
        style="background: linear-gradient(135deg, #78350f 0%, #1c1917 50%, #0a0a09 100%);">
        <div class="absolute inset-0 flex items-center justify-center opacity-10">
            <i class="fas fa-users text-white" style="font-size: 12rem;"></i>
        </div>
    </div>
    @endif

    <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(10,10,9,1) 0%, rgba(10,10,9,.55) 40%, rgba(10,10,9,.1) 100%);"></div>
    <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(10,10,9,.55) 0%, transparent 60%);"></div>

    {{-- Breadcrumb --}}
    <div class="absolute top-6 left-0 right-0 max-w-6xl mx-auto px-6">
        <nav class="flex items-center gap-1.5 text-xs text-white/50">
            <a href="{{ route('cultural.peoples') }}" class="hover:text-amber-400 transition flex items-center gap-1">
                <i class="fas fa-users text-amber-400/60 text-[10px]"></i> Cultures
            </a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-white/80 truncate">{{ $people->name }}</span>
        </nav>
    </div>

    {{-- Contenu bas --}}
    <div class="absolute bottom-0 left-0 right-0 max-w-6xl mx-auto px-6 pb-10">
        {{-- Badges --}}
        <div class="flex flex-wrap gap-2 mb-4">
            @if($people->is_featured)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500 text-black text-xs font-bold">
                <i class="fas fa-star text-[10px]"></i> Peuple vedette
            </span>
            @endif
            @if($people->zone_geographique)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 text-white text-xs">
                <i class="fas fa-compass text-amber-400/70 text-[10px]"></i>
                {{ $people->zone_geographique }}
            </span>
            @endif
            @if($people->famille_linguistique)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-white/70 text-xs">
                <i class="fas fa-language text-amber-400/50 text-[10px]"></i>
                {{ $people->famille_linguistique }}
            </span>
            @endif
        </div>

        <h1 class="font-serif text-4xl md:text-5xl font-bold text-white leading-tight mb-4 drop-shadow-xl">
            {{ $people->name }}
        </h1>

        <div class="flex flex-wrap items-center gap-4 text-sm">
            @if($people->capitale_culturelle)
            <span class="flex items-center gap-1.5 text-amber-300 font-semibold">
                <i class="fas fa-landmark text-xs"></i> {{ $people->capitale_culturelle }}
            </span>
            @endif
            @if($people->langue_principale)
            <span class="flex items-center gap-1.5 text-white/60">
                <i class="fas fa-comment-dots text-amber-400/60 text-xs"></i>
                {{ $people->langue_principale }}
            </span>
            @endif
            @if($people->population_estimee)
            <span class="flex items-center gap-1.5 text-white/50">
                <i class="fas fa-people-group text-xs"></i>
                ~{{ number_format($people->population_estimee) }} personnes
            </span>
            @endif
            <span class="flex items-center gap-1.5 text-white/40">
                <i class="fas fa-book-open text-xs"></i>
                {{ $elements->count() }} élément(s) culturel(s)
            </span>
        </div>
    </div>
</section>

{{-- ══ ONGLETS STICKY ═══════════════════════════════════════════════════════ --}}
<div class="tabs-bar">
    <div class="max-w-6xl mx-auto px-6 flex items-center gap-0 overflow-x-auto">
        <button class="tab-btn active" onclick="showTab('presentation', this)">
            <i class="fas fa-align-left mr-1.5"></i>Présentation
        </button>
        @if($elements->isNotEmpty())
        <button class="tab-btn" onclick="showTab('elements', this)">
            <i class="fas fa-masks-theater mr-1.5"></i>Éléments culturels
            <span class="ml-1.5 text-xs bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded-full">{{ $elements->count() }}</span>
        </button>
        @endif
        @if(!empty($people->symboles))
        <button class="tab-btn" onclick="showTab('symboles', this)">
            <i class="fas fa-star-of-life mr-1.5"></i>Symboles
        </button>
        @endif
        @if($people->histoire)
        <button class="tab-btn" onclick="showTab('histoire', this)">
            <i class="fas fa-scroll mr-1.5"></i>Histoire
        </button>
        @endif
    </div>
</div>

{{-- ══ CONTENU PRINCIPAL ════════════════════════════════════════════════════ --}}
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- ── COLONNE PRINCIPALE ─────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- ONGLET : Présentation --}}
            <div id="tab-presentation" class="tab-section active space-y-8">

                @if($people->description)
                <p class="text-lg text-slate-300 font-light leading-relaxed border-l-2 border-amber-500/60 pl-5 italic">
                    {{ $people->description }}
                </p>
                @endif

                {{-- Domaines culturels représentés --}}
                @if($domains->isNotEmpty())
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4">Domaines culturels</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($domains as $domain)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-sm"
                            style="{{ $domain->color
                                ? 'background:'.$domain->color.'18; border-color:'.$domain->color.'40; color:'.$domain->color
                                : 'background:#1e293b; border-color:#334155; color:#94a3b8' }}">
                            @if($domain->icon)<i class="{{ $domain->icon }} text-xs"></i>@endif
                            {{ $domain->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- ONGLET : Éléments culturels --}}
            <div id="tab-elements" class="tab-section space-y-4">
                <h2 class="text-white font-serif text-xl font-bold mb-4">
                    Patrimoine culturel du peuple {{ $people->name }}
                </h2>

                @if($elements->isEmpty())
                <div class="text-center py-16 text-slate-500 border border-slate-800 rounded-2xl">
                    <i class="fas fa-masks-theater text-4xl mb-3 block text-slate-700"></i>
                    Aucun élément culturel enregistré pour l'instant.
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($elements as $el)
                    @php
                        $riskColors = [
                            'stable'     => ['bg' => '#16a34a18', 'border' => '#16a34a40', 'text' => '#4ade80'],
                            'vulnerable' => ['bg' => '#d9770618', 'border' => '#d9770640', 'text' => '#fb923c'],
                            'en_danger'  => ['bg' => '#dc262618', 'border' => '#dc262640', 'text' => '#f87171'],
                            'disparu'    => ['bg' => '#71717118', 'border' => '#71717140', 'text' => '#9ca3af'],
                        ];
                        $rc = $riskColors[$el->niveau_risque] ?? $riskColors['stable'];
                    @endphp
                    <a href="{{ route('cultural.element', $el->slug) }}"
                        class="element-card flex gap-4 bg-[#111110] border border-slate-800 hover:border-amber-500/30 rounded-2xl p-4 group">
                        <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 bg-slate-800">
                            @if($el->thumbnail)
                            <img src="{{ $el->thumbnail }}" alt="{{ $el->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            @elseif($el->domain)
                            <div class="w-full h-full flex items-center justify-center text-xl"
                                style="{{ $el->domain->color ? 'background:'.$el->domain->color.'22; color:'.$el->domain->color : 'background:#1e293b; color:#475569' }}">
                                <i class="{{ $el->domain->icon ?: 'fas fa-masks-theater' }}"></i>
                            </div>
                            @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-800">
                                <i class="fas fa-masks-theater text-slate-600 text-xl"></i>
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="text-white font-semibold group-hover:text-amber-400 transition text-sm leading-snug">{{ $el->name }}</h3>
                                @if($el->niveau_risque && $el->niveau_risque !== 'stable')
                                <span class="shrink-0 text-[10px] px-2 py-0.5 rounded-full border font-medium"
                                    style="background:{{ $rc['bg'] }}; border-color:{{ $rc['border'] }}; color:{{ $rc['text'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $el->niveau_risque)) }}
                                </span>
                                @endif
                            </div>
                            @if($el->domain)
                            <p class="text-xs mb-1" style="{{ $el->domain->color ? 'color:'.$el->domain->color : 'color:#94a3b8' }}">
                                @if($el->domain->icon)<i class="{{ $el->domain->icon }} text-[10px] mr-0.5"></i>@endif
                                {{ $el->domain->name }}
                            </p>
                            @endif
                            @if($el->short_description)
                            <p class="text-slate-500 text-xs line-clamp-2">{{ $el->short_description }}</p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ONGLET : Symboles --}}
            <div id="tab-symboles" class="tab-section space-y-4">
                <h2 class="text-white font-serif text-xl font-bold mb-4">Symboles du peuple {{ $people->name }}</h2>

                @if(!empty($people->symboles))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($people->symboles as $symbole)
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-star-of-life text-amber-400 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-slate-500 text-xs mb-0.5">{{ $symbole['label'] ?? '' }}</p>
                            <p class="text-white text-sm font-medium">{{ $symbole['valeur'] ?? '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ONGLET : Histoire --}}
            <div id="tab-histoire" class="tab-section">
                <h2 class="text-white font-serif text-xl font-bold mb-6">Histoire du peuple {{ $people->name }}</h2>
                @if($people->histoire)
                <div class="text-slate-300 leading-8 text-base whitespace-pre-line space-y-4">
                    {{ $people->histoire }}
                </div>
                @endif
            </div>

        </div>

        {{-- ── PANNEAU STICKY DROITE ──────────────────────────────────────── --}}
        <div class="lg:sticky lg:top-16">
            <div class="bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden">

                {{-- En-tête panneau --}}
                <div class="relative h-40 overflow-hidden">
                    @if($people->thumbnail)
                    <img src="{{ $people->thumbnail }}" alt="" class="w-full h-full object-cover">
                    @elseif($people->cover_image)
                    <img src="{{ $people->cover_image }}" alt="" class="w-full h-full object-cover" style="object-position: center 30%;">
                    @else
                    <div class="w-full h-full" style="background: linear-gradient(135deg, #78350f 0%, #1c1917 100%);"></div>
                    @endif
                    <div class="absolute inset-0" style="background: linear-gradient(to top, #111110, transparent);"></div>
                    <div class="absolute bottom-3 left-4">
                        <p class="text-slate-400 text-xs">{{ $people->zone_geographique ?? '' }}</p>
                    </div>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Infos clés --}}
                    <div class="space-y-2.5">
                        @if($people->langue_principale)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-800/50 border border-slate-800">
                            <i class="fas fa-comment-dots text-amber-400/70 text-sm w-4 text-center shrink-0"></i>
                            <div>
                                <p class="text-slate-600 text-[10px] uppercase tracking-wide">Langue</p>
                                <p class="text-white text-sm font-medium">{{ $people->langue_principale }}</p>
                            </div>
                        </div>
                        @endif

                        @if($people->capitale_culturelle)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-800/50 border border-slate-800">
                            <i class="fas fa-landmark text-amber-400/70 text-sm w-4 text-center shrink-0"></i>
                            <div>
                                <p class="text-slate-600 text-[10px] uppercase tracking-wide">Capitale culturelle</p>
                                <p class="text-white text-sm font-medium">{{ $people->capitale_culturelle }}</p>
                            </div>
                        </div>
                        @endif

                        @if($people->famille_linguistique)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-800/50 border border-slate-800">
                            <i class="fas fa-language text-amber-400/70 text-sm w-4 text-center shrink-0"></i>
                            <div>
                                <p class="text-slate-600 text-[10px] uppercase tracking-wide">Famille linguistique</p>
                                <p class="text-white text-sm font-medium">{{ $people->famille_linguistique }}</p>
                            </div>
                        </div>
                        @endif

                        @if($people->population_estimee)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-amber-500/10 border border-amber-500/25">
                            <i class="fas fa-people-group text-amber-400 text-sm w-4 text-center shrink-0"></i>
                            <div>
                                <p class="text-amber-400/70 text-[10px] uppercase tracking-wide">Population estimée</p>
                                <p class="text-amber-300 text-sm font-bold">~{{ number_format($people->population_estimee) }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Retour à la liste --}}
                    <a href="{{ route('cultural.peoples') }}"
                        class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-slate-800 hover:bg-amber-500/15 border border-slate-700 hover:border-amber-500/30 text-slate-300 hover:text-white text-sm font-medium transition">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Tous les peuples
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

@include('partials.homepage-footer')

<script>
function showTab(name, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    const section = document.getElementById('tab-' + name);
    if (section) section.classList.add('active');
    btn.classList.add('active');
    window.scrollTo({ top: document.querySelector('.tabs-bar').offsetTop - 10, behavior: 'smooth' });
}
</script>
</body>
</html>
