{{--
    Modèle C « bento » du contenu riche (.prose-content) : listes en tuiles colorées numérotées, citation en panneau dégradé,
    titres à dégradé, parcours en puces. Alternatives : partials.prose-modern (A, cartes) et partials.prose-editorial (B, éditorial).
    À inclure APRÈS les styles propres à la page. Couleurs en CSS explicite (le pont de thème clair réécrit text-white).
--}}
<style>
    .prose-content {
        --pb-ink: #1f1c17; --pb-soft: #5f584b; --pb-accent: #f2790f; --pb-green: #14532d; --pb-line: rgba(31, 28, 23, .08);
        font-family: var(--font-sans, 'Inter', system-ui, sans-serif);
        font-size: clamp(1rem, .97rem + .2vw, 1.0625rem); line-height: 1.78; color: var(--pb-ink); letter-spacing: -.004em;
        overflow-wrap: anywhere; counter-reset: pb-h;
    }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }

    .prose-content p { margin: 0 0 1.3rem; color: #39342b; }
    .prose-content p:empty, .prose-content p:has(> br:only-child) { display: none; }
    .prose-content .PDq2pG_selectionAnchor { display: none; }
    .prose-content strong, .prose-content b { color: #12100c; font-weight: 700; }
    .prose-content > p:first-of-type:not(.pm-flow) { font-size: 1.16em; line-height: 1.7; color: #1f1c17; font-weight: 500; }

    /* Titres : dégradé + pastille numérotée */
    .prose-content h2, .prose-content h3 {
        counter-increment: pb-h; display: flex; align-items: center; gap: .85rem; margin: 2.8rem 0 1.1rem; line-height: 1.15; letter-spacing: -.025em;
        font-family: var(--font-serif, 'Playfair Display', serif); font-weight: 800;
    }
    .prose-content h2 { font-size: clamp(1.6rem, 1.25rem + 1.5vw, 2.2rem); }
    .prose-content h3 { font-size: clamp(1.3rem, 1.1rem + 1vw, 1.75rem); }
    .prose-content h2::before, .prose-content h3::before {
        content: counter(pb-h); flex: none; width: 2.3rem; height: 2.3rem; border-radius: 14px; text-align: center;
        font: 800 .95rem/2.3rem var(--font-sans, 'Inter', sans-serif); color: #fff; background: #1a6739;
        box-shadow: 0 10px 18px -8px rgba(20, 83, 45, .7); transform: rotate(-4deg);
    }
    .prose-content h2 > *, .prose-content h3 > * { min-width: 0; }
    .prose-content > h2:first-child, .prose-content > h3:first-child { margin-top: 0; }
    .prose-content h4 {
        display: inline-flex; align-items: center; gap: .55rem; margin: 2rem 0 1rem; padding: .4rem .9rem .4rem .5rem; border-radius: 999px;
        border: 1.5px solid #14532d; color: #14532d; background: #f2faf4; font: 800 .74rem/1 var(--font-sans, 'Inter', sans-serif); letter-spacing: .12em; text-transform: uppercase;
    }
    .prose-content h4::before { content: ''; width: 1.1rem; height: 1.1rem; border-radius: 50%; background: #2f9957; }
    .prose-content h5 { margin: 1.6rem 0 .5rem; font: 800 .82rem/1.2 var(--font-sans, 'Inter', sans-serif); letter-spacing: .12em; text-transform: uppercase; color: #14532d; }

    /* Listes à puces : tuiles « bento » numérotées */
    .prose-content ul, .prose-content ol { list-style: none; margin: 1.3rem 0 2rem; padding: 0; display: grid; gap: .8rem; counter-reset: pb-li; }
    .prose-content ul { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .prose-content ul > li, .prose-content ol > li {
        counter-increment: pb-li; position: relative; margin: 0; padding: 3.3rem 1.2rem 1.15rem; border-radius: 22px; border: 1px solid rgba(31,28,23,.06);
        color: #2e2a22; line-height: 1.6; background: #fff3e4; transition: transform .2s ease, box-shadow .2s ease;
    }
    .prose-content ul > li:nth-child(4n+1) { background: #fff1e0; }
    .prose-content ul > li:nth-child(4n+2) { background: #e8f6ec; }
    .prose-content ul > li:nth-child(4n+3) { background: #e9f0ff; }
    .prose-content ul > li:nth-child(4n+4) { background: #f2ebff; }
    .prose-content ul > li:last-child:nth-child(odd) { grid-column: 1 / -1; }
    .prose-content ul > li:hover, .prose-content ol > li:hover { transform: translateY(-3px); box-shadow: 0 22px 34px -24px rgba(31,28,23,.55); }
    .prose-content ul > li::before, .prose-content ol > li::before {
        content: counter(pb-li, decimal-leading-zero); position: absolute; left: 1.1rem; top: 1rem; min-width: 2rem; height: 2rem; padding: 0 .55rem; border-radius: 999px;
        background: #1f1c17; color: #fff; text-align: center; font: 800 .74rem/2rem var(--font-sans, 'Inter', sans-serif); letter-spacing: .08em;
    }
    .prose-content ol { grid-template-columns: 1fr; }
    .prose-content ol > li { padding: 1.05rem 1.2rem 1.05rem 5.2rem; background: #fff; border-color: var(--pb-line); }
    .prose-content ol > li::before { top: 50%; transform: translateY(-50%); left: 1.05rem; background: #f38e28; }
    .prose-content li > p { margin: 0; }
    .prose-content li > img { margin: .9rem 0 .1rem; }

    /* Citation / point important : panneau dégradé */
    .prose-content blockquote {
        position: relative; overflow: hidden; margin: 2.4rem 0; padding: 1.6rem 1.7rem 1.6rem 1.7rem; border-radius: 26px; font-style: normal;
        background: #15542f; color: #e9f7ee; border: 1px solid rgba(255,255,255,.08);
        box-shadow: 0 28px 44px -28px rgba(13, 46, 26, .9);
    }
    .prose-content blockquote::after {
        content: ''; position: absolute; right: -40px; top: -40px; width: 160px; height: 160px; border-radius: 50%;
        background: rgba(244, 166, 64, 0.28); pointer-events: none;
    }
    .prose-content blockquote p { position: relative; margin: 0 0 .75rem; color: #e9f7ee; }
    .prose-content blockquote p:last-child { margin-bottom: 0; }
    .prose-content blockquote strong, .prose-content blockquote b { color: #ffd9a3; }

    /* Parcours / texte entièrement en gras : bandeau lumineux */
    .prose-content p.pm-flow {
        position: relative; margin: 1.8rem 0; padding: 1.15rem 1.4rem; border-radius: 20px; text-align: center; line-height: 1.65;
        background: #12100c; border: 1px solid #3a342a; box-shadow: 0 0 0 4px rgba(242,121,15,.14), 0 24px 40px -26px rgba(242,121,15,.7);
    }
    .prose-content p.pm-flow > strong { color: #ffc27a; letter-spacing: .01em; font-weight: 700; }

    /* Images */
    .prose-content img {
        display: block; max-width: 100%; height: auto; margin: 2rem auto; border-radius: 28px; border: 1px solid var(--pb-line);
        box-shadow: 0 34px 56px -32px rgba(31,28,23,.65);
    }
    .prose-content figure { margin: 2rem 0; }
    .prose-content figcaption { margin-top: .6rem; text-align: center; font-size: .85rem; color: var(--pb-soft); }

    .prose-content a { color: #14532d; font-weight: 700; text-decoration: none; border-bottom: 2px solid rgba(20,83,45,.3); transition: border-color .2s, background .2s; }
    .prose-content a:hover { border-bottom-color: #14532d; background: rgba(20,83,45,.07); }
    .prose-content hr { border: 0; height: 6px; margin: 2.6rem auto; width: 90px; border-radius: 6px; background: #83661e; }
    .prose-content code { padding: .12em .45em; border-radius: 8px; background: #eaf1ee; color: #14532d; font-size: .9em; }
    .prose-content pre { margin: 1.6rem 0; padding: 1.1rem 1.25rem; border-radius: 18px; background: #0f1f16; color: #e2f3e8; overflow-x: auto; }
    .prose-content pre code { background: none; color: inherit; padding: 0; }
    .prose-content table { width: 100%; margin: 1.8rem 0; border-collapse: separate; border-spacing: 0; border: 1px solid var(--pb-line); border-radius: 18px; overflow-x: auto; display: block; font-size: .95rem; }
    .prose-content th { background: #14532d; color: #fff; text-align: left; font-weight: 700; padding: .8rem 1rem; }
    .prose-content td { padding: .75rem 1rem; border-top: 1px solid var(--pb-line); background: #fff; }
    .prose-content tr:nth-child(even) td { background: #f5faf6; }

    @media (max-width: 640px) {
        .prose-content ul { grid-template-columns: 1fr; }
        .prose-content ul > li { padding: 3.1rem 1rem 1rem; border-radius: 20px; }
        .prose-content ol > li { padding: .95rem 1rem .95rem 4.8rem; }
        .prose-content blockquote { padding: 1.3rem 1.2rem; border-radius: 22px; }
        .prose-content img { border-radius: 20px; }
        .prose-content h2::before, .prose-content h3::before { width: 2rem; height: 2rem; font-size: .85rem; line-height: 2rem; }
    }
    @media (prefers-reduced-motion: reduce) { .prose-content ul > li, .prose-content ol > li { transition: none; } .prose-content ul > li:hover, .prose-content ol > li:hover { transform: none; } }
</style>
