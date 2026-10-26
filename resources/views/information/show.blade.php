<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->localized('title') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        /* Pages d'information (Guide, FAQ, À propos, mentions…) : contenu administré mis en forme —
           étapes numérotées, accordéon de questions avec recherche, encadrés, sommaire et menu des pages. */
        .if-hero { position: relative; overflow: hidden; background: #D8E9E0; border-bottom: 1px solid #D8E9E0; }
        .if-hero .ivm-g { opacity: .16 !important; }
        .if-hero__in { position: relative; z-index: 1; max-width: 860px; margin: 0 auto; padding: 40px 20px 50px; text-align: center; }
        .if-hero__card { padding: 26px 28px; border-radius: 28px; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .if-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #B85600; }
        .if-kicker .ico { font-size: 15px; }
        .if-title { margin: 14px 0 0; font-family: 'Montserrat', serif; font-size: clamp(32px, 2.4vw + 18px, 52px); font-weight: 700; line-height: 1.06; color: #0E3B2A; }
        .if-lead { margin: 12px auto 0; max-width: 600px; font-size: 16.5px; line-height: 1.6; color: #0E3B2A; }
        .if-updated { margin: 12px 0 0; font-size: 12.5px; color: #557466; }
        .if-search { display: flex; align-items: center; gap: 8px; max-width: 520px; height: 52px; margin: 18px auto 0; padding: 0 8px 0 16px; border-radius: 999px; background: #fff; border: 1.5px solid #D8E9E0; }
        .if-search:focus-within { border-color: #EB6E00; }
        .if-search .ico { color: #EB6E00; font-size: 18px; }
        .if-search input { flex: 1; min-width: 0; border: 0; outline: none; background: transparent; font-size: 15px; color: #0E3B2A; }
        .if-search small { flex: none; padding: 4px 10px; border-radius: 999px; background: #F3F8F5; font-size: 12px; font-weight: 700; color: #557466; }

        .if { max-width: 1120px; margin: 0 auto; padding: 34px 20px 64px; display: grid; grid-template-columns: 250px minmax(0, 1fr); gap: 40px; align-items: start; }
        .if-side { position: sticky; top: 140px; display: flex; flex-direction: column; gap: 22px; }
        .if-side h3 { margin: 0 0 10px; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #557466; }
        .if-toc { margin: 0; padding: 0; list-style: none; border-left: 2px solid #D8E9E0; }
        .if-toc a { display: block; margin-left: -2px; padding: 6px 12px; border-left: 2px solid transparent; font-size: 13.5px; color: #24503D; text-decoration: none; }
        .if-toc a:hover { color: #B85600; }
        .if-toc a.is-on { border-left-color: #EB6E00; color: #0E3B2A; font-weight: 700; }
        .if-pages { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .if-pages a { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 14px; color: #0E3B2A; font-size: 14px; font-weight: 600; text-decoration: none; }
        .if-pages a:hover { background: #fff; }
        .if-pages a.is-on { background: #fff; color: #0E3B2A; box-shadow: inset 3px 0 0 #EB6E00; }
        .if-pages .ico { width: 30px; height: 30px; border-radius: 10px; background: rgba(var(--c), .12); color: rgb(var(--c)); justify-content: center; font-size: 15px; }
        .lc { --c: 235,110,0; } .lc.c2 { --c: 0,135,79; } .lc.c3 { --c: 0,135,79; } .lc.c4 { --c: 235,110,0; } .lc.c5 { --c: 0,135,79; } .lc.c6 { --c: 235,110,0; } .lc.c7 { --c: 184,86,0; }

        /* Contenu administré */
        .if-body { min-width: 0; font-size: 16px; line-height: 1.75; color: #0E3B2A; }
        .if-body > section, .if-body > div { display: flex; flex-direction: column; }
        .if-body h2 { margin: 30px 0 12px; display: flex; align-items: center; gap: 12px; font-family: 'Montserrat', serif; font-size: clamp(22px, 1vw + 17px, 28px); font-weight: 700; color: #0E3B2A; scroll-margin-top: 150px; }
        .if-body h2::before { content: ''; flex: none; width: 10px; height: 10px; border-radius: 3px; background: #EB6E00; transform: rotate(45deg); }
        .if-body section > h2:first-child, .if-body > h2:first-child { margin-top: 0; }
        .if-body h3 { margin: 22px 0 8px; font-size: 18px; color: #0E3B2A; }
        .if-body p { margin: 0 0 14px; }
        .if-body a { color: #B85600; text-decoration: underline; text-underline-offset: 3px; }
        .if-body strong { color: #0E3B2A; }
        .if-body ul { margin: 0 0 16px; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .if-body ul li { position: relative; padding-left: 22px; }
        .if-body ul li::before { content: ''; position: absolute; left: 4px; top: .7em; width: 7px; height: 7px; border-radius: 2px; background: #00874F; transform: rotate(45deg); }
        /* Étapes numérotées */
        .if-body ol { margin: 0 0 18px; padding: 0; list-style: none; counter-reset: st; display: grid; gap: 10px; }
        .if-body ol li { counter-increment: st; position: relative; display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: 14px; align-items: start; padding: 14px 16px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; }
        .if-body ol li::before { content: counter(st); width: 40px; height: 40px; border-radius: 999px; background: #00874F; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; }
        /* Questions (accordéon) */
        .if-body details { margin-bottom: 10px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; transition: border-color .15s ease; }
        .if-body details[open] { border-color: #F7C396; }
        .if-body details[hidden] { display: none; }
        .if-body summary { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 18px; list-style: none; cursor: pointer; font-size: 15.5px; font-weight: 700; color: #0E3B2A; border-radius: 18px; }
        .if-body summary::-webkit-details-marker { display: none; }
        .if-body summary::after { content: '+'; flex: none; width: 30px; height: 30px; border-radius: 999px; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 19px; font-weight: 700; }
        .if-body details[open] summary::after { content: '–'; background: #EB6E00; color: #fff; }
        .if-body details > :not(summary) { margin: 0; padding: 0 18px 16px; }
        .if-body mark { background: #F7C396; color: #0E3B2A; border-radius: 3px; padding: 0 2px; }
        /* Encadrés */
        .if-body aside { margin: 18px 0; padding: 16px 18px 16px 52px !important; position: relative; border: 0 !important; border-radius: 18px; background: #F3F8F5; opacity: 1 !important; }
        .if-body aside::before { content: 'i'; position: absolute; left: 16px; top: 16px; width: 26px; height: 26px; border-radius: 999px; background: #EB6E00; color: #fff; font-family: Georgia, serif; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .if-body aside p:last-child { margin-bottom: 0; }
        .if-empty { padding: 30px; border-radius: 22px; background: #fff; border: 1px dashed #8AA99B; text-align: center; color: #557466; }
        .if-noresult { display: none; padding: 22px; border-radius: 18px; background: #fff; border: 1px dashed #8AA99B; text-align: center; color: #557466; }

        .if-help { margin-top: 34px; padding: 24px 26px; border-radius: 26px; background: #00874F; color: #fff; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; }
        .if-help b { display: block; font-family: 'Montserrat', serif; font-size: 22px; }
        .if-help span { display: block; margin-top: 4px; font-size: 14.5px; color: #D8E9E0; }
        .if-help button, .if-help a { display: inline-flex; align-items: center; gap: 8px; height: 48px; padding: 0 20px; border-radius: 999px; border: 0; background: #fff; color: #00874F; font-size: 14.5px; font-weight: 800; text-decoration: none; cursor: pointer; }
        .if a:focus-visible, .if button:focus-visible, .if summary:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

        @media (max-width: 960px) {
            .if { grid-template-columns: minmax(0, 1fr); gap: 20px; }
            .if-side { position: static; }
            .if-side .if-toc-wrap { display: none; }
            .if-pages { flex-direction: row; overflow-x: auto; scrollbar-width: none; }
            .if-pages a { flex: none; background: #fff; border: 1px solid #D8E9E0; }
        }
        @media (max-width: 640px) {
            .if-hero__in { padding: 28px 14px 34px; }
            .if-hero__card { padding: 18px 16px; border-radius: 20px; }
            .if-title { font-size: clamp(26px, 7.4vw, 32px); }
            .if-lead { font-size: 14.5px; }
            .if { padding: 22px 16px 48px; }
            .if-body ol li { grid-template-columns: 32px minmax(0, 1fr); gap: 10px; padding: 12px; }
            .if-body ol li::before { width: 32px; height: 32px; font-size: 13px; }
        }
    </style>
</head>
<body class="min-h-screen">
    @include('partials.page-background')
@include('partials.public-top-nav')
@php
    $intro = [
        'user-guide' => [__('Guide d\'utilisation'), __('Tout ce qu\'il faut savoir pour utiliser le site, étape par étape.'), 'book'],
        'faq' => [__('Questions fréquentes'), __('Les réponses aux questions les plus posées. Tapez un mot-clé pour trouver la vôtre.'), 'chat-round-dots'],
        'about' => [__('Qui sommes-nous'), __('Notre mission : faire découvrir la culture, le patrimoine et le tourisme de Côte d\'Ivoire.'), 'star-shine'],
    ][$page->slug] ?? [__('Informations'), null, 'info-circle'];
    $isFaq = $page->slug === 'faq';
    $pageIcons = ['about' => 'star-shine', 'user-guide' => 'book', 'faq' => 'chat-round-dots', 'legal-notice' => 'shield-check'];
    $otherPages = \App\Models\InformationPage::query()->orderBy('sort_order')->get(['id', 'slug', 'title_fr', 'title_en']);
@endphp

<section class="if-hero">
    @include('partials.ivorian-motifs', ['culture' => 'national'])
    <div class="if-hero__in">
        <div class="if-hero__card">
            <span class="if-kicker"><x-icon :name="$intro[2]" />{{ $intro[0] }}</span>
            <h1 class="if-title">{{ $page->localized('title') }}</h1>
            @if($intro[1])<p class="if-lead">{{ $intro[1] }}</p>@endif
            @if($isFaq)
                <label class="if-search">
                    <x-icon name="magnifier" />
                    <input type="search" placeholder="{{ __('Rechercher une question…') }}" aria-label="{{ __('Rechercher une question') }}" data-if-search>
                    <small data-if-count></small>
                </label>
            @endif
            @if($page->updated_at)<p class="if-updated">{{ __('Mis à jour le :d', ['d' => $page->updated_at->locale(app()->getLocale())->translatedFormat('d F Y')]) }}</p>@endif
        </div>
    </div>
</section>

<main class="if">
    <aside class="if-side" aria-label="{{ __('Navigation des pages d\'information') }}">
        <div class="if-toc-wrap" data-if-toc-wrap hidden>
            <h3>{{ __('Sur cette page') }}</h3>
            <ol class="if-toc" data-if-toc></ol>
        </div>
        @if($otherPages->count() > 1)
            <div>
                <h3>{{ __('Centre d\'aide') }}</h3>
                <ul class="if-pages">
                    @foreach($otherPages as $op)
                        <li><a href="{{ route('information.show', $op) }}" class="lc c{{ ($loop->index % 7) + 1 }} {{ $op->id === $page->id ? 'is-on' : '' }}" @if($op->id === $page->id) aria-current="page" @endif><x-icon :name="$pageIcons[$op->slug] ?? 'notes'" />{{ $op->localized('title') }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </aside>

    <div>
        <div class="if-body" data-if-body>
            @if(filled($page->body_fr))
                {!! \App\Support\HtmlSanitizer::pageBody($page->localized('body')) !!}
            @else
                <p class="if-empty">{{ __('Le contenu de cette page sera bientôt disponible.') }}</p>
            @endif
        </div>
        @if($isFaq)<p class="if-noresult" data-if-noresult>{{ __('Aucune question ne correspond. Essayez un autre mot, ou contactez-nous.') }}</p>@endif

        <div class="if-help">
            <div><b>{{ __('Vous n\'avez pas trouvé votre réponse ?') }}</b><span>{{ __('Notre équipe vous répond directement.') }}</span></div>
            <button type="button" onclick="window.openContactModal && window.openContactModal()"><x-icon name="chat-round-dots" />{{ __('Nous contacter') }}</button>
        </div>
    </div>
</main>

@include('partials.homepage-footer')
<script>
(function () {
    const body = document.querySelector('[data-if-body]');
    if (!body) return;
    // Sommaire automatique (intertitres h2), section en cours surlignée
    const heads = [...body.querySelectorAll('h2')].filter(h => h.textContent.trim());
    const toc = document.querySelector('[data-if-toc]'), wrap = document.querySelector('[data-if-toc-wrap]');
    if (toc && heads.length >= 2) {
        heads.forEach((h, i) => {
            if (!h.id) h.id = 'section-' + (i + 1);
            const li = document.createElement('li'), a = document.createElement('a');
            a.href = '#' + h.id; a.textContent = h.textContent.trim(); li.appendChild(a); toc.appendChild(li);
        });
        wrap.hidden = false;
        const links = [...toc.querySelectorAll('a')];
        const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) links.forEach(l => l.classList.toggle('is-on', l.hash === '#' + e.target.id)); }), { rootMargin: '-140px 0px -65% 0px' });
        heads.forEach(h => io.observe(h));
    }
    // FAQ : recherche instantanée dans les questions et réponses
    const input = document.querySelector('[data-if-search]');
    if (!input) return;
    const items = [...body.querySelectorAll('details')];
    const count = document.querySelector('[data-if-count]'), none = document.querySelector('[data-if-noresult]');
    const norm = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const label = n => n + ' ' + (n > 1 ? @json(__('questions')) : @json(__('question')));
    count.textContent = label(items.length);
    input.addEventListener('input', () => {
        const q = norm(input.value.trim());
        let shown = 0;
        items.forEach(d => {
            const hit = !q || norm(d.textContent).includes(q);
            d.hidden = !hit; if (hit) shown++;
            if (q && hit) d.open = true;
        });
        count.textContent = label(shown);
        if (none) none.style.display = shown ? 'none' : 'block';
    });
})();
</script>
</body>
</html>
