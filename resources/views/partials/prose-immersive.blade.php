{{--
    Modèle E « immersif sombre » du contenu riche (.prose-content) : le contenu devient un grand panneau sombre en verre dépoli,
    titres à dégradé lumineux, listes en cartes translucides, parcours et citation lumineux.
    Alternatives : A prose-modern, B prose-editorial, C prose-bento, D prose-minimal.
    À inclure APRÈS les styles propres à la page. Couleurs en CSS explicite (le pont de thème clair réécrit text-white).
--}}
<style>
    .prose-content {
        --pe-text: #D8E9E0; --pe-strong: #ffffff; --pe-soft: #8AA99B; --pe-glow: #EB6E00; --pe-amber: #F7C396; --pe-glass: rgba(255, 255, 255, .055); --pe-edge: rgba(255, 255, 255, .1);
        position: relative; isolation: isolate; overflow: hidden;
        font-family: var(--font-sans, 'Montserrat', system-ui, sans-serif); font-size: clamp(1rem, .97rem + .2vw, 1.0625rem); line-height: 1.8; letter-spacing: -.003em; color: var(--pe-text);
        padding: clamp(1.4rem, 4.5vw, 3.2rem); border-radius: clamp(22px, 3vw, 34px); border: 1px solid rgba(255, 255, 255, .08);
        background: linear-gradient(transparent, transparent), linear-gradient(transparent, transparent), linear-gradient(#006B3F, #006B3F);
        box-shadow: 0 50px 90px -50px rgba(0,107,63, .95), inset 0 1px 0 rgba(255, 255, 255, .06);
        overflow-wrap: anywhere;
    }
    .prose-content::before {   /* grain lumineux discret */
        content: ''; position: absolute; inset: 0; z-index: -1; pointer-events: none; opacity: .5;
        background: linear-gradient(transparent, transparent), linear-gradient(transparent, transparent),
                    linear-gradient(transparent, transparent), linear-gradient(transparent, transparent);
        background-size: 260px 260px;
    }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }

    .prose-content p { margin: 0 0 1.35rem; color: var(--pe-text); }
    .prose-content p:empty, .prose-content p:has(> br:only-child) { display: none; }
    .prose-content .PDq2pG_selectionAnchor { display: none; }
    .prose-content strong, .prose-content b { color: var(--pe-strong); font-weight: 650; }
    .prose-content > p:first-of-type:not(.pm-flow) { font-size: 1.2em; line-height: 1.65; color: #F3F8F5; }

    /* Titres : dégradé lumineux + trait incandescent */
    .prose-content h2, .prose-content h3 {
        position: relative; margin: 2.8rem 0 1.1rem; padding-top: 1.1rem; font-family: var(--font-serif, 'Montserrat', serif); font-weight: 800; line-height: 1.15; letter-spacing: -.02em;
        background: #F7C396; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;
    }
    .prose-content h2 { font-size: clamp(1.7rem, 1.3rem + 1.6vw, 2.35rem); }
    .prose-content h3 { font-size: clamp(1.4rem, 1.15rem + 1.1vw, 1.85rem); }
    .prose-content h2::before, .prose-content h3::before {
        content: ''; position: absolute; left: 0; top: 0; width: 44px; height: 4px; border-radius: 4px; background: #EB6E00; box-shadow: 0 0 18px 2px rgba(235,110,0, .65);
    }
    .prose-content > h2:first-child, .prose-content > h3:first-child { margin-top: 0; }
    .prose-content h4 {
        display: inline-flex; align-items: center; gap: .55rem; margin: 2rem 0 1rem; padding: .45rem 1rem; border-radius: 999px;
        background: var(--pe-glass); border: 1px solid rgba(247,195,150, .35); color: var(--pe-amber); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        font: 800 .72rem/1 var(--font-sans, 'Montserrat', sans-serif); letter-spacing: .15em; text-transform: uppercase; box-shadow: 0 0 22px -6px rgba(235,110,0, .55);
    }
    .prose-content h4::before { content: ''; width: .5rem; height: .5rem; border-radius: 50%; background: var(--pe-glow); box-shadow: 0 0 10px 2px rgba(235,110,0, .9); }
    .prose-content h5 { margin: 1.6rem 0 .5rem; font: 800 .8rem/1.2 var(--font-sans, 'Montserrat', sans-serif); letter-spacing: .13em; text-transform: uppercase; color: var(--pe-amber); }

    /* Listes : cartes en verre dépoli */
    .prose-content ul, .prose-content ol { list-style: none; margin: 1.2rem 0 1.9rem; padding: 0; display: grid; gap: .7rem; counter-reset: pe-li; }
    .prose-content ul > li, .prose-content ol > li {
        counter-increment: pe-li; position: relative; margin: 0; padding: 1rem 1.15rem 1rem 3.4rem; border-radius: 18px; color: var(--pe-text); line-height: 1.65;
        background: var(--pe-glass); border: 1px solid var(--pe-edge); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .prose-content ul > li:hover, .prose-content ol > li:hover { transform: translateX(4px); border-color: rgba(235,110,0, .55); background: rgba(255, 255, 255, .08); box-shadow: 0 0 34px -10px rgba(235,110,0, .55); }
    .prose-content ul > li::before {
        content: ''; position: absolute; left: 1rem; top: 1.12rem; width: 1.3rem; height: 1.3rem; border-radius: 50%;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 12.5l4 4 8-9'/%3E%3C/svg%3E") center / 70% no-repeat, linear-gradient(#EB6E00, #EB6E00);
        box-shadow: 0 0 16px 1px rgba(235,110,0, .7);
    }
    .prose-content ol > li::before {
        content: counter(pe-li); position: absolute; left: .95rem; top: .95rem; width: 1.7rem; height: 1.7rem; border-radius: 50%; text-align: center;
        font: 800 .8rem/1.7rem var(--font-sans, 'Montserrat', sans-serif); color: #0E3B2A; background: #EB6E00; box-shadow: 0 0 16px 1px rgba(235,110,0, .6);
    }
    .prose-content li > p { margin: 0; }
    .prose-content li > img { margin: .9rem 0 .2rem; }

    /* Citation / point important : panneau lumineux */
    .prose-content blockquote {
        position: relative; margin: 2.2rem 0; padding: 1.4rem 1.5rem 1.4rem 1.6rem; border-radius: 22px; font-style: normal; color: #F3F8F5;
        background: rgba(235,110,0, 0.18); border: 1px solid rgba(247,195,150, .4); border-left: 5px solid #F7C396;
        box-shadow: 0 0 46px -14px rgba(235,110,0, .65), inset 0 1px 0 rgba(255, 255, 255, .1);
    }
    .prose-content blockquote p { margin: 0 0 .7rem; color: #F3F8F5; }
    .prose-content blockquote p:last-child { margin-bottom: 0; }
    .prose-content blockquote strong, .prose-content blockquote b { color: #F7C396; }

    /* Parcours / texte entièrement en gras : ruban lumineux */
    .prose-content p.pm-flow {
        margin: 1.8rem 0; padding: 1.15rem 1.4rem; border-radius: 20px; text-align: center; line-height: 1.65;
        background: rgba(184,86,0, 0.44); border: 1px solid rgba(247,195,150, .4);
        box-shadow: 0 0 44px -12px rgba(235,110,0, .7), inset 0 1px 0 rgba(255, 255, 255, .15);
    }
    .prose-content p.pm-flow > strong { color: #F3F8F5; letter-spacing: .01em; text-shadow: 0 0 18px rgba(247,195,150, .55); }

    /* Images */
    .prose-content img {
        display: block; max-width: 100%; height: auto; margin: 2rem auto; border-radius: 24px; border: 1px solid rgba(255, 255, 255, .14);
        box-shadow: 0 0 0 6px rgba(255, 255, 255, .04), 0 40px 70px -30px rgba(14,59,42, .9);
    }
    .prose-content figure { margin: 2rem 0; }
    .prose-content figcaption { margin-top: .6rem; text-align: center; font-size: .85rem; color: var(--pe-soft); }

    .prose-content a { color: var(--pe-amber); font-weight: 600; text-decoration: underline; text-decoration-color: rgba(247,195,150, .45); text-decoration-thickness: 2px; text-underline-offset: 4px; transition: color .2s, text-decoration-color .2s; }
    .prose-content a:hover { color: #fff; text-decoration-color: var(--pe-glow); }
    .prose-content hr { border: 0; height: 2px; margin: 2.6rem 0; background: rgba(235,110,0, 0.27); box-shadow: 0 0 14px rgba(235,110,0, .6); }
    .prose-content code { padding: .12em .45em; border-radius: 7px; background: rgba(255, 255, 255, .09); color: var(--pe-amber); font-size: .9em; }
    .prose-content pre { margin: 1.6rem 0; padding: 1.1rem 1.25rem; border-radius: 16px; background: rgba(14,59,42, .45); border: 1px solid var(--pe-edge); color: #F3F8F5; overflow-x: auto; }
    .prose-content pre code { background: none; color: inherit; padding: 0; }
    .prose-content table { width: 100%; margin: 1.8rem 0; border-collapse: separate; border-spacing: 0; display: block; overflow-x: auto; border: 1px solid var(--pe-edge); border-radius: 16px; font-size: .95rem; }
    .prose-content th { background: rgba(235,110,0, .25); color: #fff; text-align: left; font-weight: 700; padding: .8rem 1rem; }
    .prose-content td { padding: .75rem 1rem; border-top: 1px solid var(--pe-edge); color: var(--pe-text); }

    @media (max-width: 640px) {
        .prose-content ul > li, .prose-content ol > li { padding: .9rem .9rem .9rem 3.1rem; }
        .prose-content ul > li::before { left: .8rem; top: 1rem; }
        .prose-content ol > li::before { left: .75rem; top: .85rem; }
        .prose-content blockquote { padding: 1.2rem 1.1rem 1.2rem 1.2rem; }
        .prose-content img { border-radius: 16px; }
    }
    @media (prefers-reduced-motion: reduce) { .prose-content ul > li, .prose-content ol > li { transition: none; } .prose-content ul > li:hover, .prose-content ol > li:hover { transform: none; } }
</style>
