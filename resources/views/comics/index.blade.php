<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Les Astuces de Mian') }} — {{ __('Bandes dessinées') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', [
        'title' => __('Les Astuces de Mian'),
        'description' => __('Des bandes dessinées éducatives, ludiques et divertissantes pour découvrir la Côte d\'Ivoire avec Mian.'),
        'image' => $featured?->coverUrl(),
    ])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        /* « Les Astuces de Mian » : vitrine de BD. Crème + vert forêt + orange, couvertures au format portrait,
           rubriques aux couleurs du logo. Aplats, ombre au survol seulement. */
        .bd { --sun: 201,138,12; --palm: 74,138,42; --lagoon: 31,128,121; padding-bottom: 72px; color: #1d1a16; }
        .bd-wrap { max-width: 1240px; margin: 0 auto; padding: 0 20px; }
        .bd-hero { position: relative; overflow: hidden; background: #123a25; color: #fff; }
        .bd-hero .ivm-g { opacity: .12 !important; }
        .bd-hero .ivm-g > * { stroke: #f6c38f !important; }
        .bd-hero__in { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr); align-items: center; gap: 40px; padding: 56px 20px 64px; max-width: 1240px; margin: 0 auto; }
        .bd-kick { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f6c38f; font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .bd-kick .ico { width: 15px; height: 15px; }
        .bd-h1 { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(34px, 4.6vw, 58px); font-weight: 700; line-height: 1.04; color: #fff; }
        .bd-h1 em { font-style: normal; color: #f6c38f; }
        .bd-lead { margin: 14px 0 0; max-width: 46ch; font-size: 17px; line-height: 1.6; color: #d5e8dc; }
        .bd-facts { display: flex; flex-wrap: wrap; gap: 8px; margin: 22px 0 0; padding: 0; list-style: none; }
        .bd-facts li { display: inline-flex; align-items: center; gap: 8px; height: 36px; padding: 0 14px; border-radius: 999px; background: rgba(255,255,255,.1); font-size: 13.5px; font-weight: 600; color: #fff; }
        .bd-facts .ico { width: 16px; height: 16px; color: #f6c38f !important; }
        .bd-cta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }
        .bd-btn { display: inline-flex; align-items: center; gap: 9px; height: 50px; padding: 0 24px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; transition: background .15s ease; }
        .bd-btn:hover { background: #9a4308; }
        .bd-btn .ico { width: 18px; height: 18px; }
        .bd-btn.is-ghost { background: rgba(255,255,255,.12); }
        .bd-btn.is-ghost:hover { background: rgba(255,255,255,.2); }
        .bd-btn:focus-visible, .bd-chip:focus-visible, .bd-card:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 3px; }

        /* Éventail de couvertures */
        .bd-fan { position: relative; height: 380px; }
        .bd-fan a { position: absolute; top: 20px; left: 50%; width: 230px; aspect-ratio: 3 / 4; border-radius: 18px; overflow: hidden; background: #e9e1d2; border: 5px solid #fff; transition: transform .25s ease; }
        .bd-fan a img { width: 100%; height: 100%; object-fit: cover; }
        .bd-fan a:nth-child(1) { transform: translateX(-50%) rotate(-2deg); z-index: 3; }
        .bd-fan a:nth-child(2) { transform: translateX(-108%) rotate(-11deg) translateY(26px); z-index: 2; }
        .bd-fan a:nth-child(3) { transform: translateX(8%) rotate(10deg) translateY(26px); z-index: 1; }
        .bd-fan a:hover { z-index: 4; }
        .bd-fan a:nth-child(1):hover { transform: translateX(-50%) rotate(0) translateY(-6px); }
        .bd-fan__empty { position: absolute; inset: 30px 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; border-radius: 24px; border: 2px dashed rgba(246,195,143,.5); color: #f6c38f; text-align: center; font-weight: 700; }
        .bd-fan__empty .ico { width: 42px; height: 42px; }

        /* Filtres */
        .bd-bar { position: sticky; top: 64px; z-index: 20; background: #f6f3ed; border-bottom: 1px solid #ebe3d3; }
        .bd-bar__in { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 20px; max-width: 1240px; margin: 0 auto; }
        .bd-chips { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; }
        .bd-chips::-webkit-scrollbar { display: none; }
        .bd-chip { --c: 185,80,10; flex: none; display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px 0 8px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #3a2f22; font-size: 14px; font-weight: 700; text-decoration: none; }
        .bd-chip i.dot, .bd-chip > .ico-wrap { width: 28px; height: 28px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: rgba(var(--c), .12); color: rgb(var(--c)); }
        .bd-chip .ico { width: 15px; height: 15px; }
        .bd-chip small { font-size: 12px; font-weight: 700; color: #8a7f6b; }
        .bd-chip:hover { border-color: rgba(var(--c), .5); }
        .bd-chip.is-on { background: #b9500a; border-color: #b9500a; color: #fff; }
        .bd-chip.is-on > .ico-wrap { background: rgba(255,255,255,.18); color: #fff; }
        .bd-chip.is-on small { color: #fde3cc; }
        .bd-count { flex: none; font-size: 14px; color: #6b6458; }
        .bd-count b { color: #1d1a16; }
        .is-palm { --c: var(--palm); } .is-sun { --c: var(--sun); } .is-lagoon { --c: var(--lagoon); }

        /* À la une */
        .bd-sec { padding-top: 34px; }
        .bd-h2 { margin: 0 0 16px; font-family: 'Playfair Display', serif; font-size: clamp(22px, 2.4vw, 28px); font-weight: 700; color: #1d1a16; }
        .bd-feat { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 30px; align-items: center; padding: 18px; border-radius: 28px; background: #fff; border: 1px solid #ece5d6; text-decoration: none; color: inherit; transition: border-color .15s ease, box-shadow .2s ease; }
        .bd-feat:hover { border-color: #d8cdb8; box-shadow: 0 24px 44px -34px rgba(29,26,22,.5); }
        .bd-feat__cover { aspect-ratio: 3 / 4; border-radius: 18px; overflow: hidden; background: #f0e8da; }
        .bd-feat__cover img { width: 100%; height: 100%; object-fit: cover; }
        .bd-feat h3 { margin: 10px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 2.8vw, 34px); line-height: 1.12; color: #1d1a16; }
        .bd-feat p { margin: 12px 0 0; max-width: 56ch; font-size: 16px; line-height: 1.6; color: #4a443a; }
        .bd-feat .bd-btn { margin-top: 20px; }

        .bd-tag { --c: var(--palm); display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgba(var(--c), .12); color: rgb(var(--c)); font-size: 12px; font-weight: 800; letter-spacing: .02em; }
        .bd-tag .ico { width: 13px; height: 13px; }
        .bd-meta { display: flex; flex-wrap: wrap; gap: 6px 14px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 13.5px; color: #6b6458; }
        .bd-meta li { display: inline-flex; align-items: center; gap: 6px; }
        .bd-meta .ico { width: 15px; height: 15px; color: #b9500a; }
        .bd li > .ico { margin-right: 0; border: 0; border-radius: 0; background: none; }

        /* Grille */
        .bd-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; }
        .bd-card { display: flex; flex-direction: column; text-decoration: none; color: inherit; }
        .bd-card__cover { position: relative; aspect-ratio: 3 / 4; border-radius: 20px; overflow: hidden; background: #f0e8da; border: 1px solid #ece5d6; transition: transform .2s ease, box-shadow .2s ease; }
        .bd-card__cover img { width: 100%; height: 100%; object-fit: cover; }
        .bd-card:hover .bd-card__cover { transform: translateY(-4px); box-shadow: 0 22px 36px -26px rgba(29,26,22,.55); }
        .bd-card__ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #b9500a; }
        .bd-card__ph .ico { width: 42px; height: 42px; }
        .bd-card__badges { position: absolute; left: 10px; top: 10px; right: 10px; display: flex; justify-content: space-between; gap: 6px; }
        .bd-card__badges .bd-tag { background: #fff; }
        .bd-new { display: inline-flex; align-items: center; height: 28px; padding: 0 10px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 11.5px; font-weight: 800; }
        .bd-card__read { position: absolute; left: 10px; right: 10px; bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; height: 42px; border-radius: 999px; background: #fff; color: #9a4308; font-size: 14px; font-weight: 800; opacity: 0; transform: translateY(6px); transition: opacity .2s ease, transform .2s ease; }
        .bd-card__read .ico { width: 16px; height: 16px; }
        .bd-card:hover .bd-card__read, .bd-card:focus-visible .bd-card__read { opacity: 1; transform: none; }
        .bd-card h3 { margin: 12px 2px 0; font-size: 16px; font-weight: 700; line-height: 1.3; color: #1d1a16; }
        .bd-card .bd-meta { margin: 6px 2px 0; font-size: 12.5px; }

        .bd-empty { padding: 56px 20px; border-radius: 28px; background: #fff; border: 1px dashed #e3d5bb; text-align: center; color: #6b6458; }
        .bd-empty .ico { width: 44px; height: 44px; color: #b9500a; }
        .bd-empty h2 { margin: 12px 0 6px; font-family: 'Playfair Display', serif; font-size: 24px; color: #1d1a16; }

        @media (max-width: 1023px) {
            .bd-hero__in { grid-template-columns: minmax(0, 1fr); gap: 10px; padding: 40px 20px 46px; }
            .bd-fan { height: 300px; }
            .bd-fan a { width: 180px; }
            .bd-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .bd-hero__in { padding: 28px 16px 34px; }
            .bd-lead { font-size: 15.5px; }
            .bd-facts li { height: 32px; font-size: 12.5px; padding: 0 12px; }
            .bd-btn { height: 46px; padding: 0 20px; font-size: 14.5px; }
            .bd-fan { height: 230px; }
            .bd-fan a { width: 140px; border-width: 4px; border-radius: 14px; }
            .bd-bar { top: 56px; }
            .bd-bar__in { padding: 10px 16px; }
            .bd-count { display: none; }
            .bd-wrap { padding: 0 16px; }
            .bd-feat { grid-template-columns: 110px minmax(0, 1fr); gap: 14px; padding: 12px; border-radius: 22px; align-items: start; }
            .bd-feat__cover { border-radius: 14px; }
            .bd-feat h3 { font-size: 20px; margin-top: 8px; }
            .bd-feat p { font-size: 14px; display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
            .bd-feat .bd-btn { height: 42px; margin-top: 12px; }
            .bd-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
            .bd-card__read { display: none; }
            .bd-card h3 { font-size: 14.5px; }
        }
        @media (prefers-reduced-motion: reduce) { .bd-fan a, .bd-card__cover, .bd-card__read { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915]">
    @include('partials.public-top-nav')

<main class="bd">
    <section class="bd-hero" aria-labelledby="bd-title">
        @include('partials.ivorian-motifs', ['culture' => 'bd'])
        <div class="bd-hero__in">
            <div>
                <span class="bd-kick"><x-icon name="book" />{{ __('Bandes dessinées') }}</span>
                <h1 class="bd-h1" id="bd-title">{{ __('Les Astuces') }} <em>{{ __('de Mian') }}</em></h1>
                <p class="bd-lead">{{ __('Des histoires courtes en images pour apprendre, jouer et sourire en découvrant la Côte d\'Ivoire. À lire sur téléphone comme sur ordinateur.') }}</p>
                <ul class="bd-facts">
                    <li><x-icon name="book" />{{ trans_choice(':count BD|:count BD', $total, ['count' => $total]) }}</li>
                    <li><x-icon name="widget" />{{ __('Éducatif · Ludique · Divertissant') }}</li>
                    <li><x-icon name="smile-circle" />{{ __('Gratuit, sans inscription') }}</li>
                </ul>
                @if($featured)
                    <div class="bd-cta">
                        <a href="{{ route('comics.show', $featured) }}" class="bd-btn"><x-icon name="book" />{{ __('Commencer la lecture') }}</a>
                        <a href="#bd-list" class="bd-btn is-ghost">{{ __('Voir toutes les BD') }}</a>
                    </div>
                @endif
            </div>
            <div class="bd-fan">
                @php $fan = $comics->take(3); @endphp
                @forelse($fan as $c)
                    <a href="{{ route('comics.show', $c) }}" aria-label="{{ $c->title }}" tabindex="-1">
                        @if($c->coverUrl())<img src="{{ $c->coverUrl() }}" alt="" decoding="async" @if($loop->first) fetchpriority="high" @endif>@endif
                    </a>
                @empty
                    <div class="bd-fan__empty"><x-icon name="book" />{{ __('Les premières aventures de Mian arrivent bientôt') }}</div>
                @endforelse
            </div>
        </div>
    </section>

    <nav class="bd-bar" aria-label="{{ __('Rubriques') }}">
        <div class="bd-bar__in">
            <div class="bd-chips">
                <a href="{{ route('comics.index') }}#bd-list" class="bd-chip {{ !$category ? 'is-on' : '' }}" @if(!$category) aria-current="page" @endif>
                    <span class="ico-wrap"><x-icon name="widget" /></span>{{ __('Toutes') }} <small>{{ $total }}</small>
                </a>
                @foreach(\App\Models\Comic::CATEGORIES as $key => $cat)
                    <a href="{{ route('comics.index', ['rubrique' => $key]) }}#bd-list" class="bd-chip is-{{ $cat['tone'] }} {{ $category === $key ? 'is-on' : '' }}" @if($category === $key) aria-current="page" @endif>
                        <span class="ico-wrap"><x-icon name="{{ $cat['icon'] }}" /></span>{{ __($cat['label']) }} <small>{{ $counts[$key] ?? 0 }}</small>
                    </a>
                @endforeach
            </div>
            <p class="bd-count"><b>{{ $comics->count() }}</b> {{ trans_choice('histoire|histoires', $comics->count()) }}</p>
        </div>
    </nav>

    <div class="bd-wrap">
        @if($featured && $comics->count() > 1)
            <section class="bd-sec" aria-labelledby="bd-feat-title">
                <h2 class="bd-h2" id="bd-feat-title">{{ __('À la une') }}</h2>
                <a href="{{ route('comics.show', $featured) }}" class="bd-feat">
                    <span class="bd-feat__cover">@if($featured->coverUrl())<img src="{{ $featured->coverUrl() }}" alt="" loading="lazy" decoding="async">@endif</span>
                    <span>
                        <span class="bd-tag is-{{ $featured->categoryTone() }}"><x-icon name="{{ $featured->categoryIcon() }}" />{{ $featured->categoryLabel() }}</span>
                        <h3>{{ $featured->title }}</h3>
                        @if($featured->summary)<p>{{ $featured->summary }}</p>@endif
                        <ul class="bd-meta">
                            <li><x-icon name="gallery" />{{ trans_choice(':count planche|:count planches', $featured->pageCount(), ['count' => $featured->pageCount()]) }}</li>
                            <li><x-icon name="clock-circle" />{{ __(':n min de lecture', ['n' => $featured->readingMinutes()]) }}</li>
                            @if($featured->age_label)<li><x-icon name="users-group-rounded" />{{ $featured->age_label }}</li>@endif
                        </ul>
                        <span class="bd-btn"><x-icon name="book" />{{ __('Lire la BD') }}</span>
                    </span>
                </a>
            </section>
        @endif

        <section class="bd-sec" id="bd-list" aria-labelledby="bd-list-title" style="scroll-margin-top: 140px">
            <h2 class="bd-h2" id="bd-list-title">{{ $category ? __(\App\Models\Comic::CATEGORIES[$category]['label']) : __('Toutes les histoires') }}</h2>
            @if($comics->isEmpty())
                <div class="bd-empty">
                    <x-icon name="book" />
                    <h2>{{ __('Les premières aventures de Mian arrivent bientôt') }}</h2>
                    <p>{{ __('Revenez très vite : de nouvelles bandes dessinées sont en préparation.') }}</p>
                </div>
            @else
                <div class="bd-grid">
                    @foreach($comics as $c)
                        <a href="{{ route('comics.show', $c) }}" class="bd-card">
                            <span class="bd-card__cover">
                                @if($c->coverUrl())
                                    <img src="{{ $c->coverUrl() }}" alt="" loading="lazy" decoding="async">
                                @else
                                    <span class="bd-card__ph"><x-icon name="book" /></span>
                                @endif
                                <span class="bd-card__badges">
                                    <span class="bd-tag is-{{ $c->categoryTone() }}"><x-icon name="{{ $c->categoryIcon() }}" />{{ $c->categoryLabel() }}</span>
                                    @if($c->isNew())<span class="bd-new">{{ __('Nouveau') }}</span>@endif
                                </span>
                                <span class="bd-card__read"><x-icon name="book" />{{ __('Lire') }}</span>
                            </span>
                            <h3>{{ $c->title }}</h3>
                            <ul class="bd-meta">
                                <li><x-icon name="gallery" />{{ trans_choice(':count planche|:count planches', $c->pageCount(), ['count' => $c->pageCount()]) }}</li>
                                @if($c->age_label)<li>{{ $c->age_label }}</li>@endif
                            </ul>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</main>

@include('partials.homepage-footer')
</body>
</html>
