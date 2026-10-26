{{-- Styles des modules IA (Pour vous, Mon séjour). Couleurs en CSS : le pont de thème clair réécrit les utilitaires Tailwind. --}}
<style>
    .ai-wrap { max-width: 72rem; margin: 0 auto; padding: 0 1rem; }
    .ai-card { background: #fff; border: 1px solid #D8E9E0; border-radius: 12px; box-shadow: 0 1px 2px rgb(14 59 42 / .06); }
    .ai-h2 { font-family: 'Montserrat', serif; font-size: 1.6rem; font-weight: 700; color: #0E3B2A; }
    .ai-muted { color: #557466; }
    .ai-kicker { font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #EB6E00; }
    .ai-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 20px; border-radius: 10px; background: #EB6E00; color: #fff; font-weight: 700; font-size: 14px; border: 0; cursor: pointer; transition: background .15s; }
    .ai-btn:hover { background: #B85600; }
    .ai-btn--ghost { background: #fff; color: #0E3B2A; border: 1px solid #8AA99B; }
    .ai-btn--ghost:hover { background: #F3F8F5; }
    .ai-link { color: #EB6E00; font-weight: 600; }
    .ai-link:hover { text-decoration: underline; text-underline-offset: 3px; }

    /* Pastilles de choix (cases et boutons radio masqués) */
    .ai-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .ai-chip { position: relative; }
    .ai-chip input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .ai-chip span { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border: 1px solid #8AA99B; border-radius: 999px; background: #fff; color: #24503D; font-size: 14px; font-weight: 500; transition: all .15s; }
    .ai-chip span i { color: #EB6E00; font-size: 12px; }
    .ai-chip input:checked + span { background: #00874F; border-color: #00874F; color: #fff; }
    .ai-chip input:checked + span i { color: #fff; }
    .ai-chip input:focus-visible + span { outline: 2px solid #EB6E00; outline-offset: 2px; }
    .ai-label { display: block; font-size: 14px; font-weight: 700; color: #0E3B2A; margin-bottom: 8px; }
    .ai-hint { font-size: 12.5px; color: #557466; margin-top: 4px; }
    .ai-input { width: 100%; height: 44px; padding: 0 12px; border: 1px solid #8AA99B; border-radius: 10px; background: #fff; color: #0E3B2A; font-size: 15px; }
    .ai-input:focus { outline: none; border-color: #EB6E00; box-shadow: 0 0 0 3px rgb(235 110 0 / .18); }

    /* Cartes de recommandation */
    .ai-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
    .ai-reco { display: flex; flex-direction: column; overflow: hidden; border: 1px solid #D8E9E0; border-radius: 12px; background: #fff; box-shadow: 0 1px 2px rgb(14 59 42 / .06); transition: box-shadow .2s, border-color .2s; }
    .ai-reco:hover { border-color: #8AA99B; box-shadow: 0 10px 24px rgb(14 59 42 / .10); }
    .ai-reco__img { position: relative; height: 150px; background: #D8E9E0; overflow: hidden; }
    .ai-reco__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ai-reco__img i { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #8AA99B; font-size: 28px; }
    .ai-reco__type { position: absolute; left: 10px; top: 10px; padding: 4px 9px; border-radius: 6px; background: rgba(255,255,255,.94); color: #00874F; font-size: 11px; font-weight: 700; }
    .ai-reco__body { display: flex; flex-direction: column; gap: 6px; padding: 14px 16px 16px; flex: 1; }
    .ai-reco__title { font-family: 'Montserrat', serif; font-size: 1.05rem; font-weight: 700; line-height: 1.3; color: #0E3B2A; }
    .ai-reco__meta { font-size: 13px; color: #557466; }
    .ai-why { margin-top: auto; padding-top: 10px; border-top: 1px dashed #D8E9E0; font-size: 12.5px; line-height: 1.5; color: #24503D; }
    .ai-why i { color: #EB6E00; margin-right: 6px; }
    .ai-badge { display: inline-block; padding: 2px 8px; border-radius: 6px; background: #F3F8F5; color: #B85600; font-size: 11px; font-weight: 700; }
    .ai-note { padding: 12px 14px; border-radius: 10px; font-size: 14px; border: 1px solid; border-left-width: 4px; }
    .ai-note--ok { background: #F3F8F5; border-color: #D8E9E0; border-left-color: #00874F; color: #00874F; }
    .ai-note--warn { background: #F3F8F5; border-color: #F7C396; border-left-color: #EB6E00; color: #B85600; }
    .ai-note--err { background: #fdeceb; border-color: #f5c2bd; border-left-color: #c0362c; color: #8a241c; }

    /* Bandeau de présentation (accueil / annuaire) */
    .ai-strip { max-width: 80rem; margin: 0 auto; padding: 0 1rem; }
    .ai-strip__head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 16px; }

    /* Séjour */
    .ai-day { overflow: hidden; }
    .ai-day__head { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 16px; padding: 14px 20px; background: #F3F8F5; border-bottom: 1px solid #D8E9E0; }
    .ai-day__no { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #00874F; color: #fff; font-weight: 800; }
    .ai-stop { display: grid; grid-template-columns: 92px 1fr auto; gap: 8px 16px; padding: 14px 20px; border-top: 1px solid #D8E9E0; }
    .ai-stop:first-of-type { border-top: 0; }
    .ai-stop__slot { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #EB6E00; padding-top: 3px; }
    .ai-stop__title { font-weight: 700; color: #0E3B2A; }
    .ai-stop__sub { font-size: 13px; color: #557466; margin-top: 2px; }
    .ai-stop__cost { text-align: right; font-weight: 700; color: #0E3B2A; white-space: nowrap; }
    .ai-stat { padding: 14px 16px; }
    .ai-stat b { display: block; font-size: 1.35rem; color: #0E3B2A; }
    .ai-stat small { color: #557466; font-size: 12.5px; }
    .ai-lodging { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px 16px; margin: 0; padding: 14px 20px; background: #F3F8F5; border-top: 1px solid #D8E9E0; }
    @media (max-width: 640px) {
        .ai-stop { grid-template-columns: 1fr; padding: 14px 16px; }
        .ai-stop__cost { text-align: left; }
        .ai-day__head { padding: 12px 16px; }
        .ai-lodging { padding: 14px 16px; }
    }
</style>
