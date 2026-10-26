{{--
    Modèle G « épuré » du contenu riche (.prose-content) : directement sur le fond de la page (aucun panneau, aucune carte),
    listes en coches sur deux colonnes, titres avec trait orange, citation à filet, parcours en bandeau clair.
    À inclure APRÈS les styles propres à la page. Couleurs en CSS explicite (le pont de thème clair réécrit text-white).
--}}
<style>
    .prose-content {
        --pg-ink: #0E3B2A; --pg-body: #0E3B2A; --pg-accent: #EB6E00; --pg-deep: #EB6E00; --pg-line: rgba(14,59,42, .12);
        font-family: var(--font-sans, 'Montserrat', system-ui, sans-serif); font-size: clamp(1rem, .97rem + .2vw, 1.0625rem); line-height: 1.8; color: var(--pg-body);
        letter-spacing: -.004em; overflow-wrap: anywhere;
    }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }

    .prose-content p { margin: 0 0 1.35rem; }
    .prose-content p:empty, .prose-content p:has(> br:only-child) { display: none; }
    .prose-content .PDq2pG_selectionAnchor { display: none; }
    .prose-content strong, .prose-content b { color: var(--pg-ink); font-weight: 700; }
    .prose-content > p:first-of-type:not(.pm-flow) { font-size: 1.15em; color: var(--pg-ink); }

    /* Titres : trait orange sous le titre */
    .prose-content h2, .prose-content h3, .prose-content h4 { position: relative; color: var(--pg-ink); letter-spacing: -.02em; line-height: 1.2; padding-bottom: .65rem; }
    .prose-content h2::after, .prose-content h3::after, .prose-content h4::after {
        content: ''; position: absolute; left: 0; bottom: 0; height: 3px; border-radius: 3px; background: #EB6E00;
    }
    .prose-content h2 { margin: 3rem 0 1.2rem; font-family: var(--font-serif, 'Montserrat', serif); font-size: clamp(1.7rem, 1.3rem + 1.5vw, 2.25rem); font-weight: 800; }
    .prose-content h3 { margin: 2.6rem 0 1.1rem; font-family: var(--font-serif, 'Montserrat', serif); font-size: clamp(1.4rem, 1.15rem + 1vw, 1.8rem); font-weight: 700; }
    .prose-content h4 { margin: 2.2rem 0 1.1rem; font-size: clamp(1.15rem, 1.05rem + .5vw, 1.35rem); font-weight: 750; }
    .prose-content h2::after { width: 64px; height: 4px; }
    .prose-content h3::after { width: 48px; }
    .prose-content h4::after { width: 34px; height: 2px; }
    .prose-content h5 { margin: 1.5rem 0 .5rem; font-size: 1.02rem; font-weight: 750; color: var(--pg-ink); }

    /* Listes : coches sur deux colonnes, sans cadre */
    .prose-content ul {
        list-style: none; margin: 1.3rem 0 2.2rem; padding: 1.6rem 1.8rem; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem 2.4rem;
        background: #ffffff; border: 1px solid var(--pg-line); border-radius: 20px; box-shadow: 0 10px 30px -20px rgba(14,59,42, .35);
    }
    .prose-content ul > li { position: relative; margin: 0; padding: 0 0 0 2.2rem; line-height: 1.65; color: var(--pg-body); }
    .prose-content ul > li:last-child:nth-child(odd) { grid-column: 1 / -1; }
    .prose-content ul > li::before {
        content: ''; position: absolute; left: 0; top: .12rem; width: 1.45rem; height: 1.45rem; border-radius: 50%; background-color: #F3F8F5;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23EB6E00' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 12.5l4 4 8-9'/%3E%3C/svg%3E");
        background-position: center; background-size: 68%; background-repeat: no-repeat;
    }
    .prose-content ol {
        list-style: none; margin: 1.3rem 0 2.2rem; padding: 1.5rem 1.8rem; counter-reset: pg; display: grid; gap: 1.1rem;
        background: #ffffff; border: 1px solid var(--pg-line); border-radius: 20px; box-shadow: 0 10px 30px -20px rgba(14,59,42, .35);
    }
    .prose-content ol > li { counter-increment: pg; position: relative; margin: 0; padding: 0 0 0 2.6rem; line-height: 1.65; }
    .prose-content ol > li::before { content: counter(pg); position: absolute; left: 0; top: -.05rem; font: 300 1.7rem/1.2 var(--font-sans, 'Montserrat', sans-serif); color: var(--pg-accent); width: 2rem; }
    .prose-content li > p { margin: 0; }
    .prose-content li > img { grid-column: 1 / -1; margin: .9rem 0 .2rem; }
    .prose-content ul > li:has(> img) { grid-column: 1 / -1; }

    /* Citation / point important : grande phrase à filet orange */
    .prose-content blockquote { margin: 2.2rem 0; padding: .3rem 0 .3rem 1.4rem; border-left: 4px solid var(--pg-accent); font-style: normal; }
    .prose-content blockquote p { margin: 0 0 .7rem; font-size: 1.12em; line-height: 1.7; color: var(--pg-ink); }
    .prose-content blockquote p:last-child { margin-bottom: 0; }
    .prose-content blockquote strong { color: var(--pg-deep); }

    /* Parcours / texte entièrement en gras : bandeau clair */
    .prose-content p.pm-flow { margin: 1.7rem 0; padding: 1rem 1.3rem; border-radius: 14px; text-align: center; line-height: 1.6; background: #F3F8F5; border: 1px solid rgba(235,110,0, .3); }
    .prose-content p.pm-flow > strong { color: var(--pg-deep); font-weight: 700; }

    .prose-content img { display: block; max-width: 100%; height: auto; margin: 1.9rem auto; border-radius: 18px; box-shadow: 0 24px 44px -28px rgba(14,59,42, .55); }
    .prose-content figure { margin: 2rem 0; }
    .prose-content figcaption { margin-top: .6rem; text-align: center; font-size: .85rem; color: #557466; }
    .prose-content a { color: var(--pg-deep); font-weight: 600; text-decoration: underline; text-decoration-color: rgba(235,110,0, .5); text-decoration-thickness: 2px; text-underline-offset: 4px; transition: color .2s; }
    .prose-content a:hover { color: #B85600; }
    .prose-content hr { border: 0; height: 1px; margin: 2.6rem 0; background: var(--pg-line); }
    .prose-content code { padding: .12em .45em; border-radius: 6px; background: #F3F8F5; color: #B85600; font-size: .9em; }
    .prose-content pre { margin: 1.6rem 0; padding: 1.1rem 1.25rem; border-radius: 14px; background: #0E3B2A; color: #F3F8F5; overflow-x: auto; }
    .prose-content pre code { background: none; color: inherit; padding: 0; }
    .prose-content table { width: 100%; margin: 1.8rem 0; border-collapse: collapse; display: block; overflow-x: auto; font-size: .95rem; }
    .prose-content th { text-align: left; padding: .7rem .9rem; border-bottom: 2px solid var(--pg-ink); font-weight: 700; color: var(--pg-ink); }
    .prose-content td { padding: .7rem .9rem; border-bottom: 1px solid var(--pg-line); }

    @media (max-width: 720px) {
        .prose-content ul { grid-template-columns: 1fr; gap: 1rem; padding: 1.25rem 1.1rem; }
        .prose-content ol { padding: 1.25rem 1.1rem; }
        .prose-content img { border-radius: 14px; }
    }
</style>
@include('partials.lettrine-style')
