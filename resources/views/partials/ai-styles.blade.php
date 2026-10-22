{{-- Styles des modules IA (Pour vous, Mon séjour). Couleurs en CSS : le pont de thème clair réécrit les utilitaires Tailwind. --}}
<style>
    .ai-wrap { max-width: 72rem; margin: 0 auto; padding: 0 1rem; }
    .ai-card { background: #fff; border: 1px solid #e8e1d3; border-radius: 12px; box-shadow: 0 1px 2px rgb(29 26 22 / .06); }
    .ai-h2 { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1d1a16; }
    .ai-muted { color: #6b6458; }
    .ai-kicker { font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #b9500a; }
    .ai-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 20px; border-radius: 10px; background: #b9500a; color: #fff; font-weight: 700; font-size: 14px; border: 0; cursor: pointer; transition: background .15s; }
    .ai-btn:hover { background: #9a4308; }
    .ai-btn--ghost { background: #fff; color: #1d1a16; border: 1px solid #d8cdb8; }
    .ai-btn--ghost:hover { background: #f3eee4; }
    .ai-link { color: #b9500a; font-weight: 600; }
    .ai-link:hover { text-decoration: underline; text-underline-offset: 3px; }

    /* Pastilles de choix (cases et boutons radio masqués) */
    .ai-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .ai-chip { position: relative; }
    .ai-chip input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .ai-chip span { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border: 1px solid #d8cdb8; border-radius: 999px; background: #fff; color: #4a443a; font-size: 14px; font-weight: 500; transition: all .15s; }
    .ai-chip span i { color: #b9500a; font-size: 12px; }
    .ai-chip input:checked + span { background: #1b6b3a; border-color: #1b6b3a; color: #fff; }
    .ai-chip input:checked + span i { color: #fff; }
    .ai-chip input:focus-visible + span { outline: 2px solid #b9500a; outline-offset: 2px; }
    .ai-label { display: block; font-size: 14px; font-weight: 700; color: #1d1a16; margin-bottom: 8px; }
    .ai-hint { font-size: 12.5px; color: #6b6458; margin-top: 4px; }
    .ai-input { width: 100%; height: 44px; padding: 0 12px; border: 1px solid #d8cdb8; border-radius: 10px; background: #fff; color: #1d1a16; font-size: 15px; }
    .ai-input:focus { outline: none; border-color: #b9500a; box-shadow: 0 0 0 3px rgb(185 80 10 / .18); }

    /* Cartes de recommandation */
    .ai-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
    .ai-reco { display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e8e1d3; border-radius: 12px; background: #fff; box-shadow: 0 1px 2px rgb(29 26 22 / .06); transition: box-shadow .2s, border-color .2s; }
    .ai-reco:hover { border-color: #d8cdb8; box-shadow: 0 10px 24px rgb(29 26 22 / .10); }
    .ai-reco__img { position: relative; height: 150px; background: #efe8d9; overflow: hidden; }
    .ai-reco__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ai-reco__img i { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #b3a88f; font-size: 28px; }
    .ai-reco__type { position: absolute; left: 10px; top: 10px; padding: 4px 9px; border-radius: 6px; background: rgba(255,255,255,.94); color: #14532d; font-size: 11px; font-weight: 700; }
    .ai-reco__body { display: flex; flex-direction: column; gap: 6px; padding: 14px 16px 16px; flex: 1; }
    .ai-reco__title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; line-height: 1.3; color: #1d1a16; }
    .ai-reco__meta { font-size: 13px; color: #6b6458; }
    .ai-why { margin-top: auto; padding-top: 10px; border-top: 1px dashed #ece4d4; font-size: 12.5px; line-height: 1.5; color: #4a443a; }
    .ai-why i { color: #b8892b; margin-right: 6px; }
    .ai-badge { display: inline-block; padding: 2px 8px; border-radius: 6px; background: #fdf1e3; color: #9a4308; font-size: 11px; font-weight: 700; }
    .ai-note { padding: 12px 14px; border-radius: 10px; font-size: 14px; border: 1px solid; border-left-width: 4px; }
    .ai-note--ok { background: #eef6f0; border-color: #d9ecdf; border-left-color: #1b6b3a; color: #14532d; }
    .ai-note--warn { background: #fdf7e7; border-color: #f0e2b8; border-left-color: #b8892b; color: #6b4e0e; }
    .ai-note--err { background: #fdeceb; border-color: #f5c2bd; border-left-color: #c0362c; color: #8a241c; }

    /* Bandeau de présentation (accueil / annuaire) */
    .ai-strip { max-width: 80rem; margin: 0 auto; padding: 0 1rem; }
    .ai-strip__head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 16px; }

    /* Séjour */
    .ai-day { overflow: hidden; }
    .ai-day__head { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 16px; padding: 14px 20px; background: #f6f1e6; border-bottom: 1px solid #e8e1d3; }
    .ai-day__no { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #1b6b3a; color: #fff; font-weight: 800; }
    .ai-stop { display: grid; grid-template-columns: 92px 1fr auto; gap: 8px 16px; padding: 14px 20px; border-top: 1px solid #f0e9da; }
    .ai-stop:first-of-type { border-top: 0; }
    .ai-stop__slot { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #b9500a; padding-top: 3px; }
    .ai-stop__title { font-weight: 700; color: #1d1a16; }
    .ai-stop__sub { font-size: 13px; color: #6b6458; margin-top: 2px; }
    .ai-stop__cost { text-align: right; font-weight: 700; color: #1d1a16; white-space: nowrap; }
    .ai-stat { padding: 14px 16px; }
    .ai-stat b { display: block; font-size: 1.35rem; color: #1d1a16; }
    .ai-stat small { color: #6b6458; font-size: 12.5px; }
    .ai-lodging { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px 16px; margin: 0; padding: 14px 20px; background: #eef6f0; border-top: 1px solid #d9ecdf; }
    @media (max-width: 640px) {
        .ai-stop { grid-template-columns: 1fr; padding: 14px 16px; }
        .ai-stop__cost { text-align: left; }
        .ai-day__head { padding: 12px 16px; }
        .ai-lodging { padding: 14px 16px; }
    }
</style>
