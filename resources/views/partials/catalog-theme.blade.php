{{-- Pages catalogue des établissements (carte, activités, circuits, offres) : bandeau, filtres en pastilles, cartes d'éléments. Aplats aux couleurs du logo, aucun dégradé. --}}
<style>
        .mn-banner { position: relative; height: clamp(180px, 26vh, 280px); border-radius: 28px; overflow: hidden; background: #f3eee4; }
        .mn-banner img { width: 100%; height: 100%; object-fit: cover; }
        .mn-banner__stats { position: absolute; left: 16px; bottom: 16px; display: flex; flex-wrap: wrap; gap: 8px; }
        .mn-banner__stats span { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 14px; border-radius: 999px; background: rgba(255,255,255,.96); color: #1d1a16; font-size: 13px; font-weight: 700; box-shadow: 0 2px 8px rgba(29,26,22,.18); }
        .mn-banner__stats i { color: #b9500a; }

        .mn-filters { position: sticky; top: 76px; z-index: 20; display: flex; gap: 6px; padding: 6px; overflow-x: auto; scrollbar-width: none; background: rgba(255,255,255,.96); border: 1px solid #ece5d6; border-radius: 999px; box-shadow: 0 10px 30px -24px rgba(29,26,22,.5); }
        .mn-filters::-webkit-scrollbar { display: none; }
        .filter-btn { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 8px 0 14px; border: 0; border-radius: 999px; background: transparent; color: #4a443a; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s ease, color .15s ease; }
        .filter-btn:hover { background: #fdf4ec; color: #9a4308; }
        .filter-btn i { font-size: 13px; color: #b9500a; }
        .filter-count { min-width: 24px; height: 24px; padding: 0 7px; border-radius: 999px; background: #f6f3ec; color: #6b6458; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
        .filter-btn.is-active { background: #b9500a; color: #fff; }
        .filter-btn.is-active i { color: #fff; }
        .filter-btn.is-active .filter-count { background: rgba(255,255,255,.22); color: #fff; }
        .filter-btn:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        .menu-category-block.is-hidden { display: none; }
        .mn-cat { display: flex; align-items: center; gap: 14px; margin: 0 0 18px; }
        .mn-cat__icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: 14px; background: #eef6f0; color: #1b6b3a; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
        .mn-cat h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(22px, 1.2vw + 16px, 28px); font-weight: 700; color: #1d1a16; }
        .mn-cat small { display: block; font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b6458; }
        .mn-cat::after { content: ''; flex: 1; height: 1px; background: #e2d6c1; }

        .mn-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .mn-dish { display: flex; flex-direction: column; padding: 9px; background: #fff; border: 1px solid #ece5d6; border-radius: 28px; box-shadow: 0 16px 40px -32px rgba(29,26,22,.5); transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease; }
        .mn-dish:hover { transform: translateY(-4px); border-color: #d8cdb8; box-shadow: 0 24px 50px -30px rgba(29,26,22,.5); }
        .mn-dish__media { position: relative; aspect-ratio: 4 / 3; border-radius: 21px; overflow: hidden; background: #f3eee4; }
        .mn-dish__media[role="button"] { cursor: zoom-in; }
        .mn-dish__media img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .mn-dish:hover .mn-dish__media img { transform: scale(1.06); }
        .mn-dish__empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .mn-dish__empty i { width: 64px; height: 64px; border-radius: 999px; background: #fff; color: #ef7c2b; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; }
        .mn-dish__price { position: absolute; left: 12px; bottom: 12px; display: inline-flex; align-items: baseline; gap: 4px; height: 34px; padding: 0 13px; border-radius: 999px; background: #fff; color: #1d1a16; font-size: 16px; font-weight: 800; line-height: 34px; box-shadow: 0 2px 8px rgba(29,26,22,.18); font-variant-numeric: tabular-nums; }
        .mn-dish__price small { font-size: 11px; font-weight: 600; color: #6b6458; }
        .mn-dish__tag { position: absolute; left: 12px; top: 12px; display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: #1b6b3a; color: #fff; font-size: 11.5px; font-weight: 700; }
        .mn-dish__zoom { position: absolute; right: 12px; top: 12px; width: 34px; height: 34px; border-radius: 999px; background: rgba(255,255,255,.95); color: #1d1a16; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; opacity: 0; transition: opacity .2s ease; }
        .mn-dish:hover .mn-dish__zoom { opacity: 1; }
        .mn-dish__body { display: flex; flex-direction: column; flex: 1; padding: 14px 10px 6px; }
        .mn-dish__name { margin: 0; font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; line-height: 1.25; color: #1d1a16; }
        .mn-dish__facts { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0 0; padding: 0; list-style: none; }
        .mn-dish__facts li { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border-radius: 999px; background: #f6f3ec; color: #3a352c; font-size: 12.5px; font-weight: 600; }
        .mn-dish__facts i { font-size: 11px; color: #b9500a; }
        .mn-dish__desc { margin: 8px 0 0; font-size: 14px; line-height: 1.6; color: #5c5548; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .mn-dish__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto; padding-top: 14px; }
        .mn-dish__ask { display: inline-flex; align-items: center; gap: 10px; height: 42px; padding: 0 6px 0 16px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 13.5px; font-weight: 700; transition: background .15s ease; }
        .mn-dish__ask:hover { background: #9a4308; color: #fff; }
        .mn-dish__ask span { width: 30px; height: 30px; border-radius: 999px; background: #fff; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; transition: transform .2s ease; }
        .mn-dish__ask:hover span { transform: translateX(2px); }
        .mn-dish__ask.is-ghost { padding: 0 5px; background: #fff; border: 1.5px solid #ece5d6; }
        .mn-dish__ask.is-ghost span { background: #fdf4ec; }
        .mn-dish__ask.is-ghost:hover { border-color: #b9500a; background: #fff; }
        .mn-dish__foot { gap: 8px; justify-content: flex-start; }
        .mn-dish__ask:focus-visible, .mn-dish__media[role="button"]:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        .mn-empty { padding: 56px 20px; text-align: center; background: #fff; border: 1px solid #ece5d6; border-radius: 28px; color: #6b6458; }
        .mn-empty i { display: block; margin-bottom: 12px; font-size: 28px; color: #ef7c2b; }

        .mn-dish__nav { position: absolute; top: 50%; z-index: 2; width: 34px; height: 34px; margin-top: -17px; border: 0; border-radius: 999px; background: rgba(255,255,255,.95); color: #1d1a16; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; box-shadow: 0 2px 8px rgba(29,26,22,.2); opacity: 0; transition: opacity .2s ease, background .15s ease; }
        .mn-dish__nav.is-prev { left: 10px; } .mn-dish__nav.is-next { right: 10px; }
        .mn-dish:hover .mn-dish__nav, .mn-dish__nav:focus-visible { opacity: 1; }
        .mn-dish__nav:hover { background: #b9500a; color: #fff; }
        .mn-dish__nav:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .mn-dish__count { position: absolute; right: 12px; bottom: 12px; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 9px; border-radius: 999px; background: rgba(29,26,22,.72); color: #fff; font-size: 11.5px; font-weight: 700; }
        html body #lightbox { background: rgba(14,12,9,.94) !important; }
        .mn-lb__nav { position: absolute; top: 50%; width: 48px; height: 48px; margin-top: -24px; border: 0; border-radius: 999px; background: rgba(255,255,255,.14); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; transition: background .15s ease; }
        .mn-lb__nav:hover { background: #b9500a; }
        .mn-lb__nav.is-prev { left: 16px; } .mn-lb__nav.is-next { right: 16px; }
        .mn-lb__count { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.14); color: #fff; font-size: 13px; font-weight: 700; }
        @media (hover: none) { .mn-dish__nav { opacity: 1; } }
        @media (max-width: 1023px) { .mn-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 639px) {
            .mn-grid { grid-template-columns: 1fr; }
            .mn-filters { top: 64px; }
            .mn-dish__zoom { opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) { .mn-dish, .mn-dish__media img { transition: none; } }
</style>
