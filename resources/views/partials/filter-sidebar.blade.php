{{--
    Filtres de page en barre latérale (≥ 1024 px) / rangée défilante au doigt (mobile et tablette).
    Structure attendue :
        <div class="filter-layout">
            <section class="filter-aside"> <div class="filter-row" data-filter-row> <p class="filter-title">Titre</p> <a>…</a> … </div> </section>
            <section class="filter-main"> … contenu (.grid de cartes) … </section>
        </div>
    La pastille active porte la classe bg-orange-500. À inclure AVANT le lien vers le CSS Tailwind compilé.
--}}
<style>
    .filter-title { display: none; }
    /* Mobile / tablette : une seule ligne défilante au lieu de pastilles empilées sur plusieurs rangées. */
    @media (max-width: 1023px) {
        .filter-row {
            flex-wrap: nowrap !important; justify-content: flex-start !important; overflow-x: auto; overscroll-behavior-x: contain;
            scroll-snap-type: x proximity; -webkit-overflow-scrolling: touch; scrollbar-width: none;
            margin-left: -1.5rem; margin-right: -1.5rem; padding: .3rem 1.5rem .7rem;
        }
        .filter-row::-webkit-scrollbar { display: none; }
        .filter-row > a { flex: none; white-space: nowrap; scroll-snap-align: center; padding-top: .6rem !important; padding-bottom: .6rem !important; }
    }
    /* Ordinateur : barre latérale collante à gauche, contenu à droite. */
    @media (min-width: 1024px) {
        .filter-layout { display: grid; grid-template-columns: 17.5rem minmax(0, 1fr); gap: 2rem; align-items: start; max-width: 72rem; margin: 0 auto; padding: 0 1.5rem; }
        .filter-layout:not(:has(.filter-aside)) { grid-template-columns: minmax(0, 1fr); }
        .filter-layout > section { max-width: none; margin: 0; padding-left: 0; padding-right: 0; }
        .filter-aside { position: sticky; top: 6.5rem; padding-bottom: 0 !important; }
        .filter-aside .filter-row {
            display: flex; flex-direction: column; flex-wrap: nowrap !important; justify-content: flex-start !important; align-items: stretch; gap: .2rem;
            max-height: calc(100vh - 8.5rem); overflow-y: auto; padding: 1rem .75rem; background: #ffffff; border: 1px solid rgba(14,59,42, .1);
            border-radius: 22px; box-shadow: 0 14px 34px -24px rgba(14,59,42, .4); scrollbar-width: thin;
        }
        .filter-title { display: block; margin: .1rem .6rem .6rem; color: #0E3B2A; font: 800 1.05rem/1.2 var(--font-serif, 'Montserrat', serif); }
        html:not(.dark) .filter-aside .filter-row > a {
            display: flex; align-items: center; gap: .65rem; width: 100%; border-radius: 12px !important; border-color: transparent !important; background: transparent !important;
            color: #0E3B2A !important; padding: .6rem .7rem !important; font-size: .9rem; line-height: 1.3; box-shadow: none !important; border-width: 0 !important;
            transition: background .15s, color .15s;
        }
        html:not(.dark) .filter-aside .filter-row > a:hover { background: #F3F8F5 !important; color: #B85600 !important; }
        html:not(.dark) .filter-aside .filter-row > a.bg-orange-500 { background: #EB6E00 !important; color: #ffffff !important; }
        .filter-aside .filter-row > a > i { width: 1.4rem; text-align: center; font-size: .9rem !important; }
        .filter-aside .filter-row > a.bg-orange-500 > i { color: #ffffff !important; }
        .filter-main .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ── Habillage aux couleurs du logo (coucher de soleil, palmier, lagune, soleil, mer, or, brun) ── */
    .filter-row > a { --fc: 235,110,0; }
    .filter-row > a:nth-of-type(7n+2) { --fc: 0,135,79; }
    .filter-row > a:nth-of-type(7n+3) { --fc: 0,135,79; }
    .filter-row > a:nth-of-type(7n+4) { --fc: 235,110,0; }
    .filter-row > a:nth-of-type(7n+5) { --fc: 0,135,79; }
    .filter-row > a:nth-of-type(7n+6) { --fc: 235,110,0; }
    .filter-row > a:nth-of-type(7n+7) { --fc: 184,86,0; }
    .filter-row > a > i:first-child {
        flex: none; display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 10px;
        background: rgba(var(--fc), .13); color: rgb(var(--fc)) !important; font-size: .82rem !important;
        transition: background .15s ease, color .15s ease;
    }
    .filter-row > a.bg-orange-500 > i:first-child { background: rgb(var(--fc)); color: #fff !important; }

    /* Mobile / tablette : pastilles blanches, icône colorée ; active = couleur du domaine */
    @media (max-width: 1023px) {
        html:not(.dark) .filter-row > a { background: #fff !important; border: 1px solid #D8E9E0 !important; color: #0E3B2A !important; padding-left: .4rem !important; gap: .55rem !important; }
        html:not(.dark) .filter-row > a.bg-orange-500 { background: rgb(var(--fc)) !important; border-color: rgb(var(--fc)) !important; color: #fff !important; }
        html:not(.dark) .filter-row > a.bg-orange-500 > i:first-child { background: rgba(255,255,255,.22); }
    }

    /* Ordinateur : carte blanche, lisière tissée en haut, lignes colorées */
    @media (min-width: 1024px) {
        .filter-aside .filter-row { position: relative; padding: 1.2rem .7rem .8rem !important; border-color: #D8E9E0 !important; box-shadow: none !important; }
        .filter-aside .filter-row::before {
            content: ''; position: absolute; left: 0; right: 0; top: 0; height: 5px; border-radius: 22px 22px 0 0;
            background-color: #EB6E00;
            background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='48' height='5'><rect width='16' height='5' fill='%23EB6E00'/><rect x='16' width='6' height='5' fill='%23EB6E00'/><rect x='22' width='14' height='5' fill='%2300874F'/><rect x='36' width='4' height='5' fill='%23D8E9E0'/><rect x='40' width='8' height='5' fill='%2300874F'/></svg>");
            background-size: 48px 5px;
        }
        .filter-title { margin: .35rem .6rem .15rem !important; font-size: 1.15rem !important; }
        .filter-title::after { content: attr(data-sub); display: block; margin-top: .2rem; font: 600 .74rem/1.3 'Montserrat', sans-serif; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .filter-title { margin-bottom: .7rem !important; }
        html:not(.dark) .filter-aside .filter-row > a { position: relative; padding: .5rem .7rem .5rem .55rem !important; font-weight: 600; color: #0E3B2A !important; }
        html:not(.dark) .filter-aside .filter-row > a:hover { background: rgba(var(--fc), .08) !important; color: #0E3B2A !important; }
        html:not(.dark) .filter-aside .filter-row > a.bg-orange-500 { background: rgba(var(--fc), .12) !important; color: #0E3B2A !important; }
        html:not(.dark) .filter-aside .filter-row > a.bg-orange-500::before { content: ''; position: absolute; left: -.7rem; top: 18%; bottom: 18%; width: 4px; border-radius: 0 4px 4px 0; background: rgb(var(--fc)); }
        html:not(.dark) .filter-aside .filter-row > a.bg-orange-500::after { content: '›'; margin-left: auto; font-size: 1.2rem; line-height: 1; color: rgb(var(--fc)); }
        .filter-aside .filter-row > a > i:first-child { width: 34px !important; height: 34px; border-radius: 11px; text-align: center; }
        .filter-aside .filter-row > a.bg-orange-500 > i:first-child { color: #fff !important; }
    }
</style>
<script>
    (function () {
        // Sur mobile, le filtre choisi est ramené au centre de la rangée (le script est chargé dans <head> : on attend le DOM,
        // puis on refait au chargement complet car les icônes changent les largeurs).
        function centerActive() {
            var row = document.querySelector('[data-filter-row]');
            if (!row || row.scrollWidth <= row.clientWidth) return;
            var active = row.querySelector('.bg-orange-500');
            if (!active) return;
            var x = active.getBoundingClientRect().left - row.getBoundingClientRect().left + row.scrollLeft;
            row.scrollLeft = Math.max(0, x - (row.clientWidth - active.offsetWidth) / 2);
        }
        document.addEventListener('DOMContentLoaded', centerActive);
        window.addEventListener('load', centerActive);
    })();
</script>
