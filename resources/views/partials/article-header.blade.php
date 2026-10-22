{{--
    En-tête d'un article : badges (catégorie, à la une…), titre, chapô, bandeau d'informations (auteur, date, lecture, vues, partage).
    Couleurs unies (pas de dégradé), en CSS explicite : le pont de thème clair réécrit les utilitaires text-white / text-gray-*.
    À inclure AVANT le lien vers le CSS Tailwind compilé, pour que les surcharges !important du partage l'emportent proprement.
--}}
<style>
    .art-badges { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin: 0 0 1.3rem; }
    .art-badge {
        display: inline-flex; align-items: center; gap: .4rem; padding: .5rem 1rem; border-radius: 999px; border: 1px solid transparent;
        font: 700 .74rem/1 var(--font-sans, 'Inter', sans-serif); letter-spacing: .07em; text-transform: uppercase; text-decoration: none; transition: transform .15s ease, background .15s ease, color .15s ease;
    }
    .art-badge i { font-size: .68rem; }
    a.art-badge:hover { transform: translateY(-1px); }
    .art-badge--cat { background: #14532d; color: #ffffff; }
    a.art-badge--cat:hover { background: #0f3f22; }
    .art-badge--star { background: #f2790f; color: #ffffff; }
    .art-badge--dest { background: #e6f4ea; color: #14532d; border-color: #b9dcc5; }
    .art-badge--ghost { background: #ffffff; color: #5f584b; border-color: rgba(31, 27, 22, .14); }
    a.art-badge--ghost:hover { color: #b9500a; border-color: #f2790f; }

    .art-title {
        margin: 0 0 1.3rem; color: #16130f; font-weight: 800; letter-spacing: -.03em; line-height: 1.08;
        font-size: clamp(2rem, 1.35rem + 3.1vw, 3.5rem); text-wrap: balance;
    }
    .art-lead {
        margin: 0 0 1.9rem; padding: .35rem 0 .35rem 1.3rem; border-left: 4px solid #f2790f; color: #38342c;
        font-size: clamp(1.06rem, 1rem + .45vw, 1.28rem); font-weight: 400; line-height: 1.7;
    }

    .art-meta {
        display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 2rem; margin: 0 0 2.2rem; padding: 1rem 1.3rem;
        background: #ffffff; border: 1px solid rgba(31, 27, 22, .1); border-radius: 22px; box-shadow: 0 14px 34px -24px rgba(31, 27, 22, .4);
    }
    .art-meta__item { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .art-meta__item--wrap { flex-wrap: wrap; gap: .5rem; }
    .art-meta__avatar {
        flex: none; display: inline-flex; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; border-radius: 50%;
        background: #14532d; color: #ffffff; font: 800 1rem/1 var(--font-sans, 'Inter', sans-serif);
    }
    .art-meta__ico {
        flex: none; display: inline-flex; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; border-radius: 14px;
        background: #fff0dc; color: #f2790f; font-size: .95rem;
    }
    .art-meta__text { display: block; line-height: 1.25; min-width: 0; }
    .art-meta__text small { display: block; margin-bottom: .15rem; color: #7a7265; font: 600 .72rem/1.2 var(--font-sans, 'Inter', sans-serif); letter-spacing: .03em; }
    .art-meta__text b { display: block; color: #1c1915; font: 700 .95rem/1.25 var(--font-sans, 'Inter', sans-serif); }
    .art-chip { display: inline-flex; align-items: center; padding: .35rem .8rem; border-radius: 999px; background: #fff0dc; color: #8f3d06; font: 700 .76rem/1 var(--font-sans, 'Inter', sans-serif); }

    .art-share { margin-left: auto; display: flex; align-items: center; gap: .5rem; }
    .art-share > span.text-xs { color: #7a7265 !important; font-weight: 600; margin-right: .2rem; }
    .art-share > a, .art-share > button, .art-share > form > button, .art-share > span[title] {
        width: 2.5rem !important; height: 2.5rem !important; display: inline-flex !important; align-items: center; justify-content: center; border-radius: 50% !important;
        background: #ffffff !important; border: 1px solid rgba(31, 27, 22, .14) !important; color: #4a443a !important; font-size: .85rem !important; cursor: pointer; transition: background .15s, color .15s, border-color .15s;
    }
    .art-share > a:hover, .art-share > button:hover, .art-share > form > button:hover { background: #f2790f !important; border-color: #f2790f !important; color: #ffffff !important; }

    @media (max-width: 640px) {
        .art-meta { padding: .9rem 1rem; gap: .9rem 1.4rem; border-radius: 18px; }
        .art-share { margin-left: 0; width: 100%; padding-top: .8rem; border-top: 1px solid rgba(31, 27, 22, .1); }
    }
    @media (prefers-reduced-motion: reduce) { .art-badge { transition: none; } a.art-badge:hover { transform: none; } }
</style>
