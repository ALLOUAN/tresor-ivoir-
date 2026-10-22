{{--
    Modèle B « éditorial / chronologie » du contenu riche (.prose-content) : lecture serif, titres numérotés,
    listes en ligne du temps, citation en grand. Alternative à partials.prose-modern (modèle A, cartes).
    À inclure APRÈS les styles propres à la page. Couleurs en CSS explicite (le pont de thème clair réécrit text-white).
--}}
<style>
    .prose-content {
        --pe-ink: #26221b; --pe-soft: #6a6254; --pe-accent: #e56d0b; --pe-deep: #a94608; --pe-line: #e4dac6; --pe-paper: #fffdf8;
        font-family: var(--font-reading, 'Lora', Georgia, serif);
        font-size: clamp(1.04rem, 1rem + .25vw, 1.15rem); line-height: 1.9; color: var(--pe-ink);
        counter-reset: pe-h; overflow-wrap: anywhere;
    }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }

    .prose-content p { margin: 0 0 1.5rem; color: #342f26; }
    .prose-content p:empty, .prose-content p:has(> br:only-child) { display: none; }
    .prose-content .PDq2pG_selectionAnchor { display: none; }
    .prose-content strong, .prose-content b { color: #14110d; font-weight: 700; }

    /* Lettrine sur le premier paragraphe */
    .prose-content > p:first-of-type:not(.pm-flow)::first-letter,
    .prose-content > h2:first-child + p::first-letter, .prose-content > h3:first-child + p::first-letter {
        float: left; font-family: var(--font-serif, 'Playfair Display', serif); font-size: 3.9em; line-height: .82; font-weight: 800;
        padding: .06em .12em 0 0; color: var(--pe-accent);
    }

    /* Titres numérotés « 01 » */
    .prose-content h2, .prose-content h3 {
        counter-increment: pe-h; position: relative; font-family: var(--font-serif, 'Playfair Display', serif); color: #14110d;
        letter-spacing: -.02em; line-height: 1.15; margin: 3.2rem 0 1.2rem; padding-top: 2.1rem;
    }
    .prose-content h2 { font-size: clamp(1.7rem, 1.3rem + 1.6vw, 2.35rem); font-weight: 800; }
    .prose-content h3 { font-size: clamp(1.4rem, 1.15rem + 1.1vw, 1.9rem); font-weight: 700; }
    .prose-content h2::before, .prose-content h3::before {
        content: counter(pe-h, decimal-leading-zero); position: absolute; left: 0; top: 0;
        font: 800 .78rem/1 var(--font-sans, 'Inter', sans-serif); letter-spacing: .2em; color: var(--pe-accent);
    }
    .prose-content h2::after, .prose-content h3::after {
        content: ''; position: absolute; left: 2.2rem; right: 0; top: .42rem; height: 1px; background: var(--pe-line);
    }
    .prose-content > h2:first-child, .prose-content > h3:first-child { margin-top: 0; }

    .prose-content h4 {
        display: inline-flex; align-items: center; gap: .5rem; margin: 2rem 0 1rem; padding: .42rem .95rem; border-radius: 999px;
        background: #ed8826; color: #fff; box-shadow: 0 10px 18px -10px rgba(229,109,11,.7);
        font: 800 .72rem/1 var(--font-sans, 'Inter', sans-serif); letter-spacing: .14em; text-transform: uppercase;
    }
    .prose-content h5 { font: 800 .8rem/1.2 var(--font-sans, 'Inter', sans-serif); letter-spacing: .12em; text-transform: uppercase; color: var(--pe-deep); margin: 1.6rem 0 .5rem; }

    /* Listes : chronologie verticale */
    .prose-content ul, .prose-content ol { list-style: none; margin: 1.4rem 0 2rem; padding: 0 0 0 2.2rem; position: relative; }
    .prose-content ul::before, .prose-content ol::before {
        content: ''; position: absolute; left: .62rem; top: .6rem; bottom: .6rem; width: 2px; border-radius: 2px;
        background: var(--pe-accent);
    }
    .prose-content ul > li, .prose-content ol > li { position: relative; margin: 0 0 1.05rem; padding: 0; color: #342f26; line-height: 1.75; }
    .prose-content ul > li:last-child, .prose-content ol > li:last-child { margin-bottom: 0; }
    .prose-content ul > li::before {
        content: ''; position: absolute; left: -2.2rem; top: .42rem; width: 1.3rem; height: 1.3rem; border-radius: 50%;
        background: var(--pe-paper); border: 3px solid var(--pe-accent); box-shadow: 0 0 0 5px rgba(229,109,11,.12);
    }
    .prose-content ol { counter-reset: pe-ol; }
    .prose-content ol > li { counter-increment: pe-ol; }
    .prose-content ol > li::before {
        content: counter(pe-ol); position: absolute; left: -2.55rem; top: .12rem; width: 1.95rem; height: 1.95rem; border-radius: 50%;
        background: #14110d; color: #fff; text-align: center; font: 800 .8rem/1.95rem var(--font-sans, 'Inter', sans-serif); box-shadow: 0 0 0 5px #faf7f1;
    }
    .prose-content li > p { margin: 0; }
    .prose-content li > img { margin: 1rem 0 .3rem; }

    /* Citation / point important : grande citation éditoriale */
    .prose-content blockquote {
        position: relative; margin: 2.6rem 0; padding: 1.6rem 1.6rem 1.6rem 2rem; border-left: 5px solid var(--pe-accent); border-radius: 0 22px 22px 0;
        background: rgba(255, 240, 220, 0.5); font-style: normal;
    }
    .prose-content blockquote::before {
        content: '“'; position: absolute; right: 1.2rem; top: -.5rem; font: 800 6rem/1 var(--font-serif, 'Playfair Display', serif); color: rgba(229,109,11,.22);
    }
    .prose-content blockquote p { position: relative; margin: 0 0 .8rem; font-size: 1.06em; color: #26221b; }
    .prose-content blockquote p:last-child { margin-bottom: 0; }

    /* Ligne de parcours / texte entièrement en gras : bandeau à bordure dégradée */
    .prose-content p.pm-flow {
        margin: 2rem 0; padding: 1.1rem 1.4rem; border-radius: 18px; text-align: center; line-height: 1.6;
        border: 2px solid transparent;
        background: linear-gradient(var(--pe-paper), var(--pe-paper)) padding-box, linear-gradient(#ea8b25, #ea8b25) border-box;
        box-shadow: 0 20px 34px -26px rgba(229,109,11,.8);
    }
    .prose-content p.pm-flow > strong { color: var(--pe-deep); font-family: var(--font-sans, 'Inter', sans-serif); font-size: .96em; letter-spacing: .01em; }

    /* Images : cadre « photo » */
    .prose-content img {
        display: block; max-width: 100%; height: auto; margin: 2rem auto; padding: .45rem; background: #fff; border-radius: 22px;
        border: 1px solid var(--pe-line); box-shadow: 0 30px 50px -30px rgba(31,27,22,.6);
    }
    .prose-content figure { margin: 2rem 0; }
    .prose-content figcaption { margin-top: .5rem; text-align: center; font: italic .9rem var(--font-reading, 'Lora', serif); color: var(--pe-soft); }

    .prose-content a { color: var(--pe-deep); font-weight: 600; text-decoration: underline; text-decoration-color: rgba(229,109,11,.5); text-decoration-thickness: 2px; text-underline-offset: 4px; transition: text-decoration-color .2s, color .2s; }
    .prose-content a:hover { color: #7a3204; text-decoration-color: var(--pe-accent); }
    .prose-content hr { border: 0; margin: 2.8rem auto; width: 120px; height: 3px; border-radius: 3px; background: var(--pe-accent); }
    .prose-content code { padding: .1em .4em; border-radius: 6px; background: #f3ede2; color: var(--pe-deep); font-size: .88em; }
    .prose-content pre { margin: 1.6rem 0; padding: 1.1rem 1.25rem; border-radius: 16px; background: #1b1814; color: #f6ead8; overflow-x: auto; }
    .prose-content pre code { background: none; color: inherit; padding: 0; }
    .prose-content table { width: 100%; margin: 1.8rem 0; border-collapse: collapse; font: .95rem var(--font-sans, 'Inter', sans-serif); display: block; overflow-x: auto; }
    .prose-content th { text-align: left; font-weight: 800; padding: .7rem .9rem; border-bottom: 2px solid var(--pe-accent); color: #14110d; }
    .prose-content td { padding: .7rem .9rem; border-bottom: 1px solid var(--pe-line); }

    @media (max-width: 640px) {
        .prose-content { line-height: 1.8; }
        .prose-content ul, .prose-content ol { padding-left: 2rem; }
        .prose-content ul > li::before { left: -2rem; }
        .prose-content ol > li::before { left: -2.3rem; }
        .prose-content blockquote { padding: 1.3rem 1.1rem 1.3rem 1.3rem; }
        .prose-content img { padding: .3rem; border-radius: 16px; }
    }
</style>
