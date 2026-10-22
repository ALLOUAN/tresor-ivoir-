{{--
    Habillage des fiches « offre réservable » (activité de loisirs, offre de transport) : préfixe ox-.
    À inclure après partials.booking-page-theme. Présentation seulement : les formulaires tp-* restent tels quels.
    Comportements (script en bas) :
      - onglets de sections [data-ox-tabs] qui suivent le défilement ;
      - barre mobile [data-ox-bar] (prix + Réserver) masquée quand le module #reserver est à l'écran.
--}}
<style>
    /* En-tête */
    .ox-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 20px; }
    .ox-head .hx-title { margin-bottom: 0; }
    .ox-where { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 16px; margin: 10px 0 0; font-size: 14.5px; color: #4a443a; }
    .ox-where a, .ox-where span { display: inline-flex; align-items: center; gap: 7px; }
    .ox-where a { color: #1d1a16; font-weight: 600; text-decoration: underline; text-decoration-color: #d8cdb8; text-underline-offset: 3px; }
    .ox-where a:hover { color: #9a4308; }
    .ox-where i { color: #b9500a; font-size: 15px; }
    .ox-head__side { display: flex; gap: 8px; flex: none; }

    /* Bande d'informations clés */
    .ox-keys { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(0, 1fr); margin: 0 0 8px; padding: 0; list-style: none; background: #fff; border: 1px solid #ece5d6; border-radius: 22px; }
    .ox-keys li { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 16px 18px; }
    .ox-keys li + li { border-left: 1px solid #ece5d6; }
    .ox-keys i { flex: none; width: 42px; height: 42px; border-radius: 13px; background: #fdf4ec; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
    .ox-keys small { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #8a7f6b; }
    .ox-keys b { display: block; font-size: 14.5px; font-weight: 700; line-height: 1.35; color: #1d1a16; }
    .ox-keys li.is-ok i { background: #e7f3ec; color: #1b6b3a; }
    .ox-keys li.is-ok b { color: #1b6b3a; }

    /* Onglets de sections */
    .ox-tabs { position: sticky; top: 76px; z-index: 15; display: flex; gap: 4px; margin: 26px 0 0; padding: 6px 0; overflow-x: auto; scrollbar-width: none; background: #f6f3ed; border-bottom: 1px solid #e6dccb; }
    .ox-tabs::-webkit-scrollbar { display: none; }
    .ox-tabs a { flex: none; padding: 9px 14px; border-radius: 10px; font-size: 14px; font-weight: 600; color: #6b6458; text-decoration: none; white-space: nowrap; transition: background .15s ease, color .15s ease; }
    .ox-tabs a:hover { background: #efe8da; color: #1d1a16; }
    .ox-tabs a.is-active { color: #1d1a16; background: #fff; box-shadow: inset 0 -2px 0 #b9500a; }
    .ox-tabs a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 1px; }
    .ox-sec { scroll-margin-top: 140px; }

    /* Points forts */
    .ox-hl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 24px; margin: 18px 0 0; padding: 0; list-style: none; }
    .ox-hl li { display: flex; align-items: flex-start; gap: 12px; font-size: 14.5px; line-height: 1.5; color: #3a352c; }
    .ox-hl i { flex: none; width: 36px; height: 36px; border-radius: 11px; background: #fdf4ec; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; }
    .ox-hl b { display: block; color: #1d1a16; }

    /* Boîtes « conditions / à prévoir », formules, cartes */
    .ox-sec .tp-box { border-radius: 18px; }
    .ox-sec .lx-pk a, .ox-sec .lx-pass { border-radius: 18px; transition: border-color .15s ease, box-shadow .2s ease; }
    .ox-sec .lx-pk a:hover { border-color: #d8cdb8; box-shadow: 0 14px 30px -24px rgba(29,26,22,.45); }

    /* Tarifs par zone (transferts) */
    .ox-route { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding: 14px 16px; border-radius: 18px; background: #1d1a16; color: #fff; }
    .ox-route i { width: 38px; height: 38px; border-radius: 12px; background: rgba(255,255,255,.12); display: inline-flex; align-items: center; justify-content: center; }
    .ox-route b { font-size: 15px; } .ox-route small { display: block; font-size: 12px; color: rgba(255,255,255,.7); }
    .ox-zones { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin: 0; padding: 0; list-style: none; }
    .ox-zone { display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; padding: 13px 14px; border-radius: 16px; background: #fff; border: 1px solid #ece5d6; text-align: left; font: inherit; color: inherit; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
    .ox-zone:hover { border-color: #b9500a; }
    .ox-zone.is-on { border-color: #b9500a; background: #fdf4ec; }
    .ox-zone span { min-width: 0; font-size: 14px; font-weight: 600; color: #1d1a16; }
    .ox-zone b { flex: none; font-size: 14px; font-weight: 800; color: #9a4308; white-space: nowrap; }
    .ox-zone:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
    .ox-opts { margin: 0; padding: 0; list-style: none; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
    .ox-opts li { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 13px 16px; font-size: 14px; }
    .ox-opts li + li { border-top: 1px solid #f0eadf; }
    .ox-opts b { white-space: nowrap; } .ox-opts small { font-weight: 500; color: #6b6458; }

    /* Module de réservation */
    .ox-card.tp-card { top: 140px; padding: 0; overflow: hidden; border-radius: 24px; background: #fff; border: 1px solid #e6dccb; box-shadow: none; }
    .ox-card__head { padding: 20px 22px 16px; border-bottom: 1px solid #f0eadf; }
    .ox-card__head .tp-card__price { font-size: 13.5px; }
    .ox-card__head .tp-card__price b { font-size: 30px; letter-spacing: -.01em; }
    .ox-card__badges { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .ox-card__badges span { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 999px; background: #e7f3ec; color: #1b6b3a; font-size: 12px; font-weight: 700; }
    .ox-card__badges span.is-neutral { background: #f6f3ec; color: #4a443a; }
    .ox-card__body { padding: 4px 22px 22px; }
    .ox-card .tp-submit { border-radius: 999px; padding: 15px; }

    /* Barre de réservation mobile */
    .ox-bar { position: fixed; left: 12px; right: 12px; bottom: 84px; z-index: 40; display: none; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 10px 10px 18px; border-radius: 999px; background: #1d1a16; color: #fff; transition: transform .25s ease, opacity .25s ease; }
    .ox-bar small { display: block; font-size: 11.5px; color: rgba(255,255,255,.7); }
    .ox-bar b { font-size: 17px; font-variant-numeric: tabular-nums; }
    .ox-bar a { display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 20px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 14.5px; font-weight: 700; text-decoration: none; }
    .ox-bar.is-away { transform: translateY(140%); opacity: 0; pointer-events: none; }

    @media (max-width: 1023px) {
        .ox-card.tp-card { top: auto; }
        .ox-bar { display: flex; }
        .ox-keys { grid-auto-flow: row; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ox-keys li:nth-child(odd) { border-left: 0; }
        .ox-keys li:nth-child(n+3) { border-top: 1px solid #ece5d6; }
    }
    @media (min-width: 768px) and (max-width: 1023px) { .ox-bar { bottom: 20px; left: 50%; right: auto; width: 520px; transform: translateX(-50%); } .ox-bar.is-away { transform: translate(-50%, 140%); } }
    .hx-back + .ox-head { margin-top: 14px; }
    @media (max-width: 1023px) {
        .ox-keys li:last-child:nth-child(odd) { grid-column: 1 / -1; }
    }
    @media (max-width: 767px) {
        .ox-head { position: relative; display: block; }
        .ox-head .hx-pills { padding-right: 56px; }
        .ox-head__side { position: absolute; top: 0; right: 0; }
        /* Laisse la place au bouton « Assistant » en bas à droite. */
        .ox-bar { right: 76px; }
        .ox-tabs { top: 64px; margin-left: -16px; margin-right: -16px; padding-left: 12px; padding-right: 12px; }
        .ox-hl, .ox-zones { grid-template-columns: 1fr; }
        .ox-keys li { padding: 13px 12px; gap: 10px; }
        .ox-keys i { width: 34px; height: 34px; font-size: 13px; }
        .ox-keys b { font-size: 13.5px; }
    }
    @media (prefers-reduced-motion: reduce) { .ox-bar { transition: none; } }

    /* Barre de réservation mobile aux couleurs de la marque. */
    .ox-bar { background: #b9500a; }
    .ox-bar small { color: rgba(255,255,255,.8); }
    .ox-bar a { background: #fff; color: #9a4308; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Onglets : catégorie active selon le défilement.
    const tabs = document.querySelector('[data-ox-tabs]');
    if (tabs) {
        const links = Array.from(tabs.querySelectorAll('a[href^="#"]'));
        const secs = links.map(a => document.getElementById(a.getAttribute('href').slice(1))).filter(Boolean);
        const setActive = id => links.forEach(a => {
            const on = a.getAttribute('href') === '#' + id;
            a.classList.toggle('is-active', on);
            if (on) { a.setAttribute('aria-current', 'true'); tabs.scrollTo({ left: a.offsetLeft - 12, behavior: 'smooth' }); } else a.removeAttribute('aria-current');
        });
        if ('IntersectionObserver' in window && secs.length) {
            const seen = new Map();
            const io = new IntersectionObserver(es => {
                es.forEach(e => seen.set(e.target, e.isIntersecting));
                const first = secs.find(s => seen.get(s));
                if (first) setActive(first.id);
            }, { rootMargin: '-35% 0px -55% 0px' });
            secs.forEach(s => io.observe(s));
        }
        if (secs[0]) setActive(secs[0].id);
    }
    // Barre mobile : cachée quand le module de réservation est visible.
    const bar = document.querySelector('[data-ox-bar]'), box = document.getElementById('reserver');
    if (bar && box && 'IntersectionObserver' in window) {
        new IntersectionObserver(es => es.forEach(e => bar.classList.toggle('is-away', e.isIntersecting)), { threshold: .05 }).observe(box);
    }
});
</script>
