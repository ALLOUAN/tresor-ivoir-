<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $comic->title }} — {{ __('Les Astuces de Mian') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', [
        'title' => $comic->title.' — '.__('Les Astuces de Mian'),
        'description' => $comic->summary ?: __('Une bande dessinée à lire gratuitement sur Trésors d\'Ivoire.'),
        'image' => $comic->coverUrl(),
    ])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @php $pages = $comic->pageUrls(); $n = count($pages); @endphp
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        /* Liseuse BD : en-tête de fiche, barre de lecture fixe (planche n / total, progression, mode),
           lecture en défilement (par défaut, idéal sur téléphone) ou planche par planche. Aplats. */
        .cr { --sun: 201,138,12; --palm: 74,138,42; --lagoon: 31,128,121; padding-bottom: 72px; color: #1d1a16; }
        .cr-wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .cr-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 22px 0 0; font-size: 13.5px; color: #6b6458; }
        .cr-crumb a { color: #9a4308; font-weight: 600; text-decoration: none; }
        .cr-crumb a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .cr-head { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: 20px 30px; align-items: center; margin-top: 18px; padding: 18px; border-radius: 28px; background: #fff; border: 1px solid #ece5d6; }
        .cr-cover { aspect-ratio: 3 / 4; border-radius: 18px; overflow: hidden; background: #f0e8da; }
        .cr-cover img { width: 100%; height: 100%; object-fit: cover; }
        .cr-tag { --c: var(--palm); display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgba(var(--c), .12); color: rgb(var(--c)); font-size: 12px; font-weight: 800; }
        .cr-tag .ico { width: 13px; height: 13px; }
        .is-palm { --c: var(--palm); } .is-sun { --c: var(--sun); } .is-lagoon { --c: var(--lagoon); }
        .cr-title { margin: 10px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(26px, 3.4vw, 40px); line-height: 1.1; color: #1d1a16; }
        .cr-sum { margin: 10px 0 0; max-width: 60ch; font-size: 16px; line-height: 1.6; color: #4a443a; }
        .cr-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 13.5px; color: #6b6458; }
        .cr-meta li { display: inline-flex; align-items: center; gap: 6px; }
        .cr-meta li > .ico { width: 15px; height: 15px; margin: 0; border: 0; border-radius: 0; background: none; color: #b9500a; }
        .cr-actions { grid-column: 2; align-self: start; display: flex; flex-wrap: wrap; gap: 10px; }
        .cr-head > div:nth-child(2) { align-self: end; }
        .cr-cover { grid-row: span 2; }
        .cr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 48px; padding: 0 22px; border: 0; border-radius: 999px; background: #b9500a; color: #fff; font: inherit; font-size: 15px; font-weight: 800; text-decoration: none; cursor: pointer; transition: background .15s ease; }
        .cr-btn:hover { background: #9a4308; }
        .cr-btn .ico { width: 18px; height: 18px; }
        .cr-btn.is-ghost { background: #fff; color: #3a2f22; border: 1px solid #e6dccb; }
        .cr-btn.is-ghost:hover { border-color: #b9500a; color: #9a4308; background: #fff; }
        .cr button:focus-visible, .cr a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        /* Barre de lecture */
        .cr-bar { position: sticky; top: 64px; z-index: 30; margin-top: 22px; background: #f6f3ed; }
        .cr-bar__in { display: flex; align-items: center; gap: 12px; max-width: 1100px; margin: 0 auto; padding: 10px 20px; }
        .cr-bar__title { min-width: 0; flex: 1; font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cr-count { flex: none; font-size: 13.5px; font-weight: 700; color: #6b6458; font-variant-numeric: tabular-nums; }
        .cr-count b { color: #1d1a16; }
        .cr-mode { flex: none; display: inline-flex; padding: 3px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; }
        .cr-mode button { height: 34px; padding: 0 13px; border: 0; border-radius: 999px; background: transparent; color: #4a443a; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
        .cr-mode button[aria-pressed="true"] { background: #123a25; color: #fff; }
        .cr-icon-btn { flex: none; width: 40px; height: 40px; border-radius: 999px; border: 1px solid #e6dccb; background: #fff; color: #3a2f22; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .cr-icon-btn:hover { border-color: #b9500a; color: #9a4308; }
        .cr-icon-btn .ico { width: 18px; height: 18px; }
        .cr-prog { height: 3px; background: #ebe3d3; }
        .cr-prog i { display: block; height: 100%; width: 0; background: #b9500a; transition: width .2s ease; }

        /* Planches */
        .cr-stage { position: relative; max-width: 860px; margin: 22px auto 0; padding: 0 20px; }
        .cr-pages { display: flex; flex-direction: column; gap: 16px; margin: 0; padding: 0; list-style: none; }
        .cr-page { margin: 0; scroll-margin-top: 150px; }
        .cr-stage { scroll-margin-top: 150px; }
        .cr-page img { display: block; width: 100%; height: auto; border-radius: 10px; background: #fff; border: 1px solid #ece5d6; }
        .cr-page figcaption { margin-top: 6px; text-align: center; font-size: 12px; color: #8a7f6b; }
        .cr-stage.is-paged .cr-page:not(.is-cur) { display: none; }
        .cr-stage.is-paged .cr-page img { max-height: calc(100vh - 190px); width: auto; max-width: 100%; margin: 0 auto; object-fit: contain; }
        .cr-nav { display: none; }
        .cr-stage.is-paged .cr-nav { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 14px; }
        .cr-nav .cr-btn[disabled] { opacity: .4; cursor: default; }
        .cr-empty { padding: 48px 20px; border-radius: 24px; background: #fff; border: 1px dashed #e3d5bb; text-align: center; color: #6b6458; }

        /* Fin de lecture */
        .cr-end { max-width: 860px; margin: 34px auto 0; padding: 0 20px; }
        .cr-end__box { position: relative; overflow: hidden; padding: 28px; border-radius: 28px; background: #123a25; color: #fff; }
        .cr-end__box .ivm-g { opacity: .1 !important; }
        .cr-end__box .ivm-g > * { stroke: #f6c38f !important; }
        .cr-end__box > :not(.ivm) { position: relative; z-index: 1; }
        .cr-end h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: 28px; color: #fff; }
        .cr-end p { margin: 6px 0 0; color: #cfe6d7; }
        .cr-next { display: grid; grid-template-columns: 96px minmax(0, 1fr) auto; gap: 16px; align-items: center; margin-top: 18px; padding: 10px; border-radius: 20px; background: #fff; color: #1d1a16; text-decoration: none; }
        .cr-next__cover { aspect-ratio: 3 / 4; border-radius: 12px; overflow: hidden; background: #f0e8da; }
        .cr-next__cover img { width: 100%; height: 100%; object-fit: cover; }
        .cr-next small { display: block; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #b9500a; }
        .cr-next b { display: block; margin-top: 4px; font-family: 'Playfair Display', serif; font-size: 20px; line-height: 1.2; }
        .cr-end__actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
        .cr-end__actions .cr-btn.is-ghost { background: rgba(255,255,255,.12); color: #fff; border: 0; }
        .cr-more { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-top: 24px; }
        .cr-more a { text-decoration: none; color: #1d1a16; }
        .cr-more span { display: block; aspect-ratio: 3 / 4; border-radius: 16px; overflow: hidden; background: #f0e8da; border: 1px solid #ece5d6; transition: transform .2s ease; }
        .cr-more a:hover span { transform: translateY(-3px); }
        .cr-more img { width: 100%; height: 100%; object-fit: cover; }
        .cr-more b { display: block; margin-top: 8px; font-size: 14px; line-height: 1.3; }

        .cr:fullscreen { overflow-y: auto; background: #f6f3ed; }
        .cr:fullscreen .cr-bar { top: 0; }
        @media (max-width: 640px) {
            .cr-wrap, .cr-bar__in, .cr-stage, .cr-end { padding-left: 12px; padding-right: 12px; }
            .cr-head { grid-template-columns: 96px minmax(0, 1fr); gap: 14px; padding: 12px; border-radius: 22px; align-items: start; }
            .cr-cover { border-radius: 12px; }
            .cr-title { font-size: 22px; margin-top: 8px; }
            .cr-sum { font-size: 14px; }
            .cr-actions { grid-column: 1 / -1; margin-top: 4px; }
            .cr-cover { grid-row: auto; }
            .cr-head > div:nth-child(2) { align-self: start; }
            .cr-actions .cr-btn { flex: 1 1 auto; height: 44px; padding: 0 14px; font-size: 14px; white-space: nowrap; }
            .cr-actions .cr-btn.is-ghost { flex: 0 0 auto; }
            .cr-bar { top: 56px; }
            .cr-bar__title { display: none; }
            .cr-bar__in { justify-content: space-between; gap: 8px; }
            .cr-mode button { padding: 0 9px; font-size: 12.5px; }
            .cr-count__w { display: none; }
            .cr-bar__in { gap: 6px; }
            .cr-pages { gap: 10px; }
            .cr-page img { border-radius: 6px; }
            .cr-end__box { padding: 20px 16px; border-radius: 22px; }
            .cr-end h2 { font-size: 22px; }
            .cr-next { grid-template-columns: 64px minmax(0, 1fr); }
            .cr-next .cr-btn { grid-column: 1 / -1; }
            .cr-more { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        }
        @media (prefers-reduced-motion: reduce) { .cr-prog i, .cr-more span { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915]">
    @include('partials.public-top-nav')

<main class="cr" id="cr-root" data-total="{{ $n }}">
    <div class="cr-wrap">
        <nav class="cr-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
            <a href="{{ route('comics.index') }}">{{ __('Les Astuces de Mian') }}</a><span aria-hidden="true">/</span>
            <a href="{{ route('comics.index', ['rubrique' => $comic->category]) }}">{{ $comic->categoryLabel() }}</a>
        </nav>
        <header class="cr-head">
            <div class="cr-cover">@if($comic->coverUrl())<img src="{{ $comic->coverUrl() }}" alt="" decoding="async" fetchpriority="high">@endif</div>
            <div>
                <span class="cr-tag is-{{ $comic->categoryTone() }}"><x-icon name="{{ $comic->categoryIcon() }}" />{{ $comic->categoryLabel() }}</span>
                <h1 class="cr-title">{{ $comic->title }}</h1>
                @if($comic->summary)<p class="cr-sum">{{ $comic->summary }}</p>@endif
                <ul class="cr-meta">
                    <li><x-icon name="gallery" />{{ trans_choice(':count planche|:count planches', $n, ['count' => $n]) }}</li>
                    <li><x-icon name="clock-circle" />{{ __(':n min de lecture', ['n' => $comic->readingMinutes()]) }}</li>
                    @if($comic->age_label)<li><x-icon name="users-group-rounded" />{{ $comic->age_label }}</li>@endif
                </ul>
            </div>
            @if($n)
                <div class="cr-actions">
                    <a href="#planche-1" class="cr-btn" data-cr-start><x-icon name="book" />{{ __('Commencer la lecture') }}</a>
                    <button type="button" class="cr-btn is-ghost" data-cr-share><x-icon name="share" />{{ __('Partager') }}</button>
                </div>
            @endif
        </header>
    </div>

    @if($n)
        <div class="cr-bar">
            <div class="cr-bar__in">
                <a href="{{ route('comics.index') }}" class="cr-icon-btn" aria-label="{{ __('Retour aux BD') }}"><x-icon name="alt-arrow-left" /></a>
                <p class="cr-bar__title">{{ $comic->title }}</p>
                <p class="cr-count" aria-live="polite"><span class="cr-count__w">{{ __('Planche') }}</span> <b data-cr-cur>1</b> / {{ $n }}</p>
                <div class="cr-mode" role="group" aria-label="{{ __('Mode de lecture') }}">
                    <button type="button" data-cr-mode="scroll" aria-pressed="true">{{ __('Défilement') }}</button>
                    <button type="button" data-cr-mode="paged" aria-pressed="false">{{ __('Page par page') }}</button>
                </div>
                <button type="button" class="cr-icon-btn" data-cr-full aria-label="{{ __('Plein écran') }}"><x-icon name="window-frame" /></button>
            </div>
            <div class="cr-prog" aria-hidden="true"><i data-cr-prog></i></div>
        </div>

        <div class="cr-stage" data-cr-stage>
            <ol class="cr-pages">
                @foreach($pages as $i => $url)
                    <li class="cr-page {{ $i === 0 ? 'is-cur' : '' }}" id="planche-{{ $i + 1 }}" data-cr-page="{{ $i + 1 }}">
                        <figure style="margin:0">
                            <img src="{{ $url }}" alt="{{ __(':t — planche :n', ['t' => $comic->title, 'n' => $i + 1]) }}" @if($i > 1) loading="lazy" @endif decoding="async">
                            <figcaption>{{ $i + 1 }} / {{ $n }}</figcaption>
                        </figure>
                    </li>
                @endforeach
            </ol>
            <div class="cr-nav">
                <button type="button" class="cr-btn is-ghost" data-cr-step="-1"><x-icon name="alt-arrow-left" />{{ __('Précédente') }}</button>
                <button type="button" class="cr-btn" data-cr-step="1">{{ __('Suivante') }}<x-icon name="alt-arrow-right" /></button>
            </div>
        </div>
    @else
        <div class="cr-stage"><div class="cr-empty">{{ __('Les planches de cette histoire arrivent bientôt.') }}</div></div>
    @endif

    <section class="cr-end" aria-labelledby="cr-end-title">
        <div class="cr-end__box">
            @include('partials.ivorian-motifs', ['culture' => 'bd', 'band' => false])
            <h2 id="cr-end-title">{{ __('Fin de l\'histoire') }}</h2>
            <p>{{ __('Merci d\'avoir lu cette astuce de Mian ! Une autre aventure vous attend.') }}</p>
            @if($next)
                <a href="{{ route('comics.show', $next) }}" class="cr-next">
                    <span class="cr-next__cover">@if($next->coverUrl())<img src="{{ $next->coverUrl() }}" alt="" loading="lazy" decoding="async">@endif</span>
                    <span><small>{{ __('À lire ensuite') }}</small><b>{{ $next->title }}</b></span>
                    <span class="cr-btn">{{ __('Lire') }}<x-icon name="alt-arrow-right" /></span>
                </a>
            @endif
            <div class="cr-end__actions">
                <a href="{{ route('comics.index') }}" class="cr-btn is-ghost"><x-icon name="widget" />{{ __('Toutes les BD') }}</a>
                @if($n)<a href="#planche-1" class="cr-btn is-ghost" data-cr-start><x-icon name="restart" />{{ __('Relire') }}</a>@endif
            </div>
        </div>
        @if($others->count() > 1)
            <div class="cr-more">
                @foreach($others as $o)
                    <a href="{{ route('comics.show', $o) }}"><span>@if($o->coverUrl())<img src="{{ $o->coverUrl() }}" alt="" loading="lazy" decoding="async">@endif</span><b>{{ $o->title }}</b></a>
                @endforeach
            </div>
        @endif
    </section>
</main>

@include('partials.homepage-footer')
<script>
(function () {
    var root = document.getElementById('cr-root'), stage = document.querySelector('[data-cr-stage]');
    if (!stage) return;
    var pages = Array.prototype.slice.call(stage.querySelectorAll('[data-cr-page]')), total = pages.length, cur = 1;
    var curEl = document.querySelector('[data-cr-cur]'), prog = document.querySelector('[data-cr-prog]');
    var prevBtn = stage.querySelector('[data-cr-step="-1"]'), nextBtn = stage.querySelector('[data-cr-step="1"]');
    var paged = false;

    function show(n) {
        cur = Math.max(1, Math.min(total, n));
        curEl.textContent = cur;
        prog.style.width = (cur / total * 100) + '%';
        pages.forEach(function (p, i) { p.classList.toggle('is-cur', i + 1 === cur); });
        prevBtn.disabled = cur === 1; nextBtn.disabled = cur === total;
    }
    function setMode(mode, keep) {
        paged = mode === 'paged';
        stage.classList.toggle('is-paged', paged);
        document.querySelectorAll('[data-cr-mode]').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.crMode === mode ? 'true' : 'false'); });
        try { localStorage.setItem('tiv_comic_mode', mode); } catch (e) {}
        if (!keep) (paged ? stage : pages[cur - 1]).scrollIntoView({ block: 'start' });
    }
    function step(d) {
        if (paged) { show(cur + d); stage.scrollIntoView({ block: 'start' }); }
        else if (pages[cur - 1 + d]) pages[cur - 1 + d].scrollIntoView({ block: 'start', behavior: 'smooth' });
    }

    // Défilement : la planche la plus visible devient la planche courante.
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            if (paged) return;
            entries.forEach(function (e) { if (e.isIntersecting) show(+e.target.dataset.crPage); });
        }, { rootMargin: '-45% 0px -45% 0px' });
        pages.forEach(function (p) { io.observe(p); });
        // Arrivé à la fin : progression complète.
        new IntersectionObserver(function (en) { if (!paged && en[0].isIntersecting) show(total); }, { threshold: .4 }).observe(document.querySelector('.cr-end'));
    }

    document.querySelectorAll('[data-cr-mode]').forEach(function (b) { b.addEventListener('click', function () { setMode(b.dataset.crMode); }); });
    stage.querySelectorAll('[data-cr-step]').forEach(function (b) { b.addEventListener('click', function () { step(+b.dataset.crStep); }); });
    document.querySelectorAll('[data-cr-start]').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(1); (paged ? stage : pages[0]).scrollIntoView({ block: 'start', behavior: 'smooth' }); }); });

    // Clavier : flèches gauche / droite (hors champs de saisie).
    document.addEventListener('keydown', function (e) {
        if (/input|textarea|select/i.test((e.target.tagName || '')) || e.target.isContentEditable) return;
        if (e.key === 'ArrowRight') { step(1); e.preventDefault(); }
        if (e.key === 'ArrowLeft') { step(-1); e.preventDefault(); }
    });
    // Glisser du doigt en mode page par page.
    var x0 = null;
    stage.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', function (e) {
        if (!paged || x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0; x0 = null;
        if (Math.abs(dx) > 50) step(dx < 0 ? 1 : -1);
    });

    var full = document.querySelector('[data-cr-full]');
    if (full) {
        if (!root.requestFullscreen) full.hidden = true;
        full.addEventListener('click', function () { document.fullscreenElement ? document.exitFullscreen() : root.requestFullscreen().catch(function () {}); });
    }
    var share = document.querySelector('[data-cr-share]');
    if (share) share.addEventListener('click', function () {
        var data = { title: document.title, url: location.href.split('#')[0] };
        if (navigator.share) { navigator.share(data).catch(function () {}); return; }
        if (navigator.clipboard) navigator.clipboard.writeText(data.url).then(function () { share.lastChild.textContent = {{ \Illuminate\Support\Js::from(__('Lien copié')) }}; });
    });

    var saved = null; try { saved = localStorage.getItem('tiv_comic_mode'); } catch (e) {}
    show(1);
    if (saved === 'paged') setMode('paged', true);
})();
</script>
</body>
</html>
