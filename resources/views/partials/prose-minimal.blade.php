{{--
    Modèle D « minimal / suisse » du contenu riche (.prose-content) : beaucoup d'air, titres sobres surmontés d'un filet,
    listes en lignes à grands chiffres fins, citation centrée sans cadre. Alternatives : A prose-modern, B prose-editorial, C prose-bento.
    À inclure APRÈS les styles propres à la page. Couleurs en CSS explicite (le pont de thème clair réécrit text-white).
--}}
<style>
    .prose-content {
        --pd-ink: #0E3B2A; --pd-body: #24503D; --pd-soft: #557466; --pd-accent: #EB6E00; --pd-line: #D8E9E0;
        font-family: var(--font-sans, 'Montserrat', system-ui, sans-serif);
        font-size: clamp(1.02rem, .98rem + .2vw, 1.1rem); line-height: 1.8; color: var(--pd-body); letter-spacing: -.006em;
        overflow-wrap: anywhere; counter-reset: pd-h;
    }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }

    .prose-content p { margin: 0 0 1.4rem; max-width: 68ch; }
    .prose-content p:empty, .prose-content p:has(> br:only-child) { display: none; }
    .prose-content .PDq2pG_selectionAnchor { display: none; }
    .prose-content strong, .prose-content b { color: var(--pd-ink); font-weight: 650; }
    .prose-content > p:first-of-type:not(.pm-flow) { font-size: 1.28em; line-height: 1.55; letter-spacing: -.02em; color: var(--pd-ink); font-weight: 450; max-width: 60ch; }

    /* Titres : filet + numéro discret, sans-serif serré */
    .prose-content h2, .prose-content h3 {
        counter-increment: pd-h; position: relative; margin: 3.4rem 0 1.2rem; padding-top: 1.5rem; border-top: 1px solid var(--pd-line);
        font-family: var(--font-sans, 'Montserrat', sans-serif); font-weight: 750; color: var(--pd-ink); line-height: 1.12; letter-spacing: -.035em;
    }
    .prose-content h2 { font-size: clamp(1.9rem, 1.4rem + 2vw, 2.7rem); }
    .prose-content h3 { font-size: clamp(1.55rem, 1.2rem + 1.5vw, 2.15rem); }
    .prose-content h2::before, .prose-content h3::before {
        content: counter(pd-h, decimal-leading-zero) ' — '; display: block; margin-bottom: .55rem;
        font: 600 .74rem/1 var(--font-sans, 'Montserrat', sans-serif); letter-spacing: .18em; color: var(--pd-accent);
    }
    .prose-content > h2:first-child, .prose-content > h3:first-child { margin-top: 0; padding-top: 0; border-top: 0; }
    .prose-content h4 {
        margin: 2.2rem 0 .9rem; font: 650 .8rem/1.3 var(--font-sans, 'Montserrat', sans-serif); letter-spacing: .14em; text-transform: uppercase; color: var(--pd-soft);
    }
    .prose-content h4::before { content: '/ '; color: var(--pd-accent); font-weight: 800; }
    .prose-content h5 { margin: 1.6rem 0 .5rem; font-weight: 700; color: var(--pd-ink); font-size: 1rem; }

    /* Listes : lignes séparées par des filets, grands chiffres fins */
    .prose-content ul, .prose-content ol { list-style: none; margin: 1.1rem 0 2.2rem; padding: 0; counter-reset: pd-li; border-bottom: 1px solid var(--pd-line); }
    .prose-content ul > li, .prose-content ol > li {
        counter-increment: pd-li; position: relative; margin: 0; padding: 1.15rem .5rem 1.15rem 4.4rem; border-top: 1px solid var(--pd-line);
        color: var(--pd-body); line-height: 1.65; transition: background .2s ease, padding-left .2s ease;
    }
    .prose-content ul > li::before, .prose-content ol > li::before {
        content: counter(pd-li, decimal-leading-zero); position: absolute; left: .4rem; top: 1rem;
        font: 200 1.9rem/1.1 var(--font-sans, 'Montserrat', sans-serif); letter-spacing: -.04em; color: var(--pd-accent); transition: transform .2s ease;
    }
    .prose-content ul > li:hover, .prose-content ol > li:hover { background: rgba(235,110,0, 0.04); }
    .prose-content ul > li:hover::before, .prose-content ol > li:hover::before { transform: translateX(4px); }
    .prose-content li > p { margin: 0; max-width: none; }
    .prose-content li > img { margin: 1rem 0 .2rem; }

    /* Citation / point important : texte centré, sans cadre */
    .prose-content blockquote {
        position: relative; margin: 3rem auto; padding: 2.6rem 1rem 1.6rem; max-width: 46rem; text-align: center; font-style: normal;
        border-top: 1px solid var(--pd-line); border-bottom: 1px solid var(--pd-line);
    }
    .prose-content blockquote::before {
        content: '“'; position: absolute; left: 50%; top: -1.1rem; transform: translateX(-50%); width: 3.2rem; height: 3.2rem; border-radius: 50%;
        background: var(--pd-accent); color: #fff; font: 800 2.4rem/3.6rem var(--font-serif, 'Montserrat', serif); text-align: center;
        box-shadow: 0 0 0 8px #F3F8F5;
    }
    .prose-content blockquote p { max-width: none; margin: 0 0 .8rem; font-size: 1.22em; line-height: 1.55; letter-spacing: -.015em; color: var(--pd-ink); font-weight: 500; }
    .prose-content blockquote p:last-child { margin-bottom: 0; }
    .prose-content blockquote strong { color: var(--pd-accent); }

    /* Parcours / texte entièrement en gras : pastille sobre à point lumineux */
    .prose-content p.pm-flow {
        position: relative; max-width: none; margin: 1.8rem 0; padding: 1.1rem 1.4rem 1.1rem 3.2rem; border-radius: 16px; line-height: 1.6;
        background: #fff; border: 1px solid var(--pd-line); box-shadow: 0 1px 2px rgba(14,59,42,.04), 0 18px 30px -24px rgba(14,59,42,.35);
    }
    .prose-content p.pm-flow::before {
        content: ''; position: absolute; left: 1.2rem; top: 1.55rem; width: .7rem; height: .7rem; border-radius: 50%; background: var(--pd-accent);
        box-shadow: 0 0 0 5px rgba(235,110,0,.18), 0 0 14px 2px rgba(235,110,0,.55);
    }
    .prose-content p.pm-flow > strong { color: var(--pd-ink); font-weight: 650; }

    /* Images */
    .prose-content img { display: block; max-width: 100%; height: auto; margin: 2.2rem auto; border-radius: 24px; box-shadow: 0 40px 60px -36px rgba(14,59,42,.55); }
    .prose-content figure { margin: 2.2rem 0; }
    .prose-content figcaption { margin-top: .7rem; font-size: .84rem; color: var(--pd-soft); text-align: center; }

    .prose-content a { color: var(--pd-ink); font-weight: 600; text-decoration: underline; text-decoration-color: var(--pd-accent); text-decoration-thickness: 2px; text-underline-offset: 4px; transition: color .2s; }
    .prose-content a:hover { color: var(--pd-accent); }
    .prose-content hr { border: 0; border-top: 1px solid var(--pd-line); margin: 3rem 0; }
    .prose-content code { padding: .12em .4em; border-radius: 6px; background: #F3F8F5; color: #B85600; font-size: .9em; }
    .prose-content pre { margin: 1.6rem 0; padding: 1.1rem 1.25rem; border-radius: 16px; background: #0E3B2A; color: #F3F8F5; overflow-x: auto; }
    .prose-content pre code { background: none; color: inherit; padding: 0; }
    .prose-content table { width: 100%; margin: 1.8rem 0; border-collapse: collapse; display: block; overflow-x: auto; font-size: .95rem; }
    .prose-content th { text-align: left; padding: .7rem .9rem; border-bottom: 2px solid var(--pd-ink); font-weight: 700; color: var(--pd-ink); }
    .prose-content td { padding: .7rem .9rem; border-bottom: 1px solid var(--pd-line); }

    @media (max-width: 640px) {
        .prose-content ul > li, .prose-content ol > li { padding: 1rem .2rem 1rem 3.4rem; }
        .prose-content ul > li::before, .prose-content ol > li::before { font-size: 1.55rem; top: .9rem; left: .2rem; }
        .prose-content blockquote { padding: 2.4rem .2rem 1.3rem; }
        .prose-content img { border-radius: 18px; }
    }
    @media (prefers-reduced-motion: reduce) { .prose-content ul > li, .prose-content ul > li::before, .prose-content ol > li::before { transition: none; } }
</style>
