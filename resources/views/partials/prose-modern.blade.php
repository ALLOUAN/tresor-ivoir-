{{--
    Mise en forme « magazine moderne » du contenu riche (articles, événements) : .prose-content.
    À inclure APRÈS les styles propres à la page. Couleurs en CSS explicite (le pont de thème clair réécrit les utilitaires text-white).
--}}
<style>
    .prose-content {
        --pm-ink: #0E3B2A; --pm-soft: #24503D; --pm-accent: #EB6E00; --pm-accent-deep: #EB6E00;
        --pm-line: rgba(14,59,42, .09); --pm-card: #ffffff; --pm-tint: #F3F8F5;
        font-family: var(--font-sans, 'Montserrat', system-ui, sans-serif);
        font-size: clamp(1rem, .96rem + .2vw, 1.0625rem);
        line-height: 1.8; color: var(--pm-ink); letter-spacing: -.003em;
        overflow-wrap: anywhere;
    }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }

    /* Paragraphes */
    .prose-content p { margin: 0 0 1.35rem; color: #24503D; }
    .prose-content > p:first-of-type:not(.pm-flow) { font-size: 1.14em; color: var(--pm-ink); }
    .prose-content p:empty, .prose-content p:has(> br:only-child) { display: none; }
    .prose-content .PDq2pG_selectionAnchor { display: none; }
    .prose-content strong, .prose-content b { color: #0E3B2A; font-weight: 700; }
    .prose-content em, .prose-content i { color: #24503D; }
    .prose-content mark { background: rgba(235,110,0, 0.14); color: inherit; padding: 0 .1em; }

    /* Titres */
    .prose-content h2, .prose-content h3, .prose-content h4, .prose-content h5 { color: #0E3B2A; line-height: 1.2; letter-spacing: -.015em; }
    .prose-content h2 {
        font-family: var(--font-serif, 'Montserrat', serif); font-size: clamp(1.6rem, 1.3rem + 1.2vw, 2.1rem); font-weight: 800;
        margin: 3rem 0 1.1rem; padding-bottom: .7rem; position: relative;
    }
    .prose-content h2::after { content: ''; position: absolute; left: 0; bottom: 0; width: 64px; height: 4px; border-radius: 4px; background: #EB6E00; }
    .prose-content h3 {
        font-family: var(--font-serif, 'Montserrat', serif); font-size: clamp(1.3rem, 1.1rem + .9vw, 1.65rem); font-weight: 700;
        margin: 2.6rem 0 1rem; padding: .15rem 0 .15rem 1rem; border-left: 4px solid transparent;
        border-image: linear-gradient(#EB6E00, #EB6E00) 1;
    }
    /* Sous-titre : vrai titre lisible (pas d'étiquette), souligné d'un court trait orange */
    .prose-content h4 {
        position: relative; display: block; margin: 2.3rem 0 1.2rem; padding-bottom: .7rem;
        font-family: var(--font-sans, 'Montserrat', sans-serif); font-size: clamp(1.2rem, 1.08rem + .5vw, 1.4rem); font-weight: 750; letter-spacing: -.02em; line-height: 1.25; text-transform: none; color: #0E3B2A;
    }
    .prose-content h4::before { content: none; }
    .prose-content h4::after { content: ''; position: absolute; left: 0; bottom: 0; width: 38px; height: 3px; border-radius: 3px; background: #EB6E00; }
    .prose-content h5 { font-size: 1rem; font-weight: 700; margin: 1.6rem 0 .6rem; }

    /* Listes à puces : cartes avec coche */
    .prose-content ul { list-style: none; padding: 0; margin: 1.2rem 0 1.8rem; display: grid; gap: .65rem; }
    .prose-content ul > li {
        position: relative; margin: 0; padding: .95rem 1.1rem .95rem 3.1rem; background: var(--pm-card); border: 1px solid var(--pm-line);
        border-radius: 16px; box-shadow: 0 1px 2px rgba(14,59,42,.04), 0 8px 22px -14px rgba(14,59,42,.18); color: #24503D; line-height: 1.65;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .prose-content ul > li:hover { transform: translateY(-2px); border-color: rgba(235,110,0,.35); box-shadow: 0 1px 2px rgba(14,59,42,.05), 0 14px 28px -14px rgba(235,110,0,.35); }
    .prose-content ul > li::before {
        content: ''; position: absolute; left: 1rem; top: 1rem; width: 1.35rem; height: 1.35rem; border-radius: 50%;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 12.5l4 4 8-9'/%3E%3C/svg%3E") center / 72% no-repeat,
            linear-gradient(#EB6E00, #EB6E00);
        box-shadow: 0 4px 10px -3px rgba(235,110,0,.55);
    }
    .prose-content li > p { margin: 0; }
    .prose-content li > img { display: block; margin: .9rem 0 .2rem; }

    /* Listes numérotées */
    .prose-content ol { list-style: none; counter-reset: pm; padding: 0; margin: 1.2rem 0 1.8rem; display: grid; gap: .65rem; }
    .prose-content ol > li {
        counter-increment: pm; position: relative; padding: .95rem 1.1rem .95rem 3.4rem; background: var(--pm-card);
        border: 1px solid var(--pm-line); border-radius: 16px; box-shadow: 0 8px 22px -14px rgba(14,59,42,.18);
    }
    .prose-content ol > li::before {
        content: counter(pm); position: absolute; left: .95rem; top: .9rem; width: 1.65rem; height: 1.65rem; border-radius: 50%;
        background: #0E3B2A; color: #fff; font: 800 .8rem/1.65rem var(--font-sans, 'Montserrat', sans-serif); text-align: center;
    }

    /* Citation / point important : encart */
    .prose-content blockquote {
        position: relative; margin: 2.2rem 0; padding: 1.35rem 1.5rem 1.35rem 4.1rem; border: 1px solid rgba(235,110,0,.28); border-radius: 20px;
        background: #FFFFFF; color: #24503D; font-style: normal;
        box-shadow: 0 18px 36px -24px rgba(235,110,0,.55);
    }
    .prose-content blockquote::before {
        content: '!'; position: absolute; left: 1.2rem; top: 1.3rem; width: 2rem; height: 2rem; border-radius: 12px;
        background: #EB6E00; color: #fff; font: 800 1.1rem/2rem var(--font-sans, 'Montserrat', sans-serif); text-align: center;
        box-shadow: 0 8px 16px -6px rgba(235,110,0,.6);
    }
    .prose-content blockquote p { margin: 0 0 .7rem; }
    .prose-content blockquote p:last-child { margin-bottom: 0; }

    /* Ligne de parcours / mot-clé en gras seul : bandeau mis en valeur */
    .prose-content p.pm-flow {
        margin: 1.6rem 0; padding: 1.05rem 1.3rem; border-radius: 16px; text-align: center; line-height: 1.6;
        background: #0E3B2A; color: #F3F8F5; box-shadow: 0 18px 34px -22px rgba(14,59,42,.7);
        border: 1px solid rgba(255,255,255,.08);
    }
    .prose-content p.pm-flow > strong { color: #F3F8F5; font-weight: 700; letter-spacing: .005em; }

    /* Images */
    .prose-content img {
        display: block; max-width: 100%; height: auto; margin: 1.8rem auto; border-radius: 20px;
        border: 1px solid var(--pm-line); box-shadow: 0 24px 48px -28px rgba(14,59,42,.55);
    }
    .prose-content figure { margin: 2rem 0; }
    .prose-content figcaption { margin-top: .6rem; text-align: center; font-size: .85rem; color: var(--pm-soft); }

    /* Liens */
    .prose-content a { color: var(--pm-accent-deep); font-weight: 600; text-decoration: none; background: linear-gradient(currentColor, currentColor) 0 100% / 100% 1.5px no-repeat; transition: background-size .2s, color .2s; }
    .prose-content a:hover { color: #B85600; background-size: 100% 100%; background-color: rgba(235,110,0, 0.14); }

    /* Séparateur, code, tableaux */
    .prose-content hr { border: 0; height: 2px; margin: 2.6rem 0; background: rgba(235,110,0, 0.17); }
    .prose-content code { padding: .12em .45em; border-radius: 7px; background: #F3F8F5; color: #B85600; font-size: .9em; }
    .prose-content pre { margin: 1.6rem 0; padding: 1.1rem 1.25rem; border-radius: 16px; background: #0E3B2A; color: #F3F8F5; overflow-x: auto; }
    .prose-content pre code { background: none; color: inherit; padding: 0; }
    .prose-content table { width: 100%; margin: 1.8rem 0; border-collapse: separate; border-spacing: 0; overflow: hidden; border: 1px solid var(--pm-line); border-radius: 16px; font-size: .95rem; display: block; overflow-x: auto; }
    .prose-content th { background: #0E3B2A; color: #fff; text-align: left; font-weight: 700; padding: .8rem 1rem; }
    .prose-content td { padding: .75rem 1rem; border-top: 1px solid var(--pm-line); background: #fff; }
    .prose-content tr:nth-child(even) td { background: #FFFFFF; }

    @media (max-width: 640px) {
        .prose-content ul > li { padding: .85rem .9rem .85rem 2.8rem; }
        .prose-content ul > li::before { left: .8rem; top: .9rem; }
        .prose-content ol > li { padding: .85rem .9rem .85rem 3.1rem; }
        .prose-content blockquote { padding: 1.1rem 1.1rem 1.1rem 3.5rem; }
        .prose-content blockquote::before { left: .9rem; top: 1.1rem; }
        .prose-content img { border-radius: 14px; }
    }
    @media (prefers-reduced-motion: reduce) { .prose-content ul > li { transition: none; } .prose-content ul > li:hover { transform: none; } }
</style>
