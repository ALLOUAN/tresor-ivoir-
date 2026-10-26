{{--
    Hero d'un événement sur mobile (≤ 640 px) : le héros n'a plus de hauteur fixe (le contenu pouvait déborder sous le bouton « Agenda »
    ou sous la barre d'onglets), le contenu est dans le flux, le titre est ajusté, les actions sont en grille.
    Ne change rien au-dessus de 640 px. À inclure APRÈS les styles de la page.
--}}
<style>
    @media (max-width: 640px) {
        /* L'image s'affiche en entier en haut (4:3), sans texte par-dessus ; le titre, les infos et les
           boutons passent dessous sur un bandeau sombre : l'affiche reste visible et le texte lisible. */
        .hero-cinematic { height: auto !important; min-height: 0 !important; display: block; background: #0E3B2A; }
        .hero-cinematic > img, .hero-cinematic > .hero-fallback {
            position: relative !important; inset: auto !important; display: block;
            width: 100% !important; height: auto !important; aspect-ratio: 4 / 3; object-fit: cover; transform: none !important;
        }
        .hero-cinematic:hover > img { transform: none !important; }
        .hero-cinematic .hero-scrim { display: none; }
        .hero-cinematic .back-pill { top: 14px; left: 14px; background: rgba(14,59,42,.62) !important; }
        .hero-cinematic .hero-content { position: relative !important; inset: auto !important; padding-top: 1.4rem; min-height: 0; }
        .hero-cinematic .hero-inner { padding-bottom: 5.2rem; }
        .hero-cinematic h1 { font-size: clamp(2rem, 9.2vw, 2.7rem); line-height: 1.06; overflow-wrap: anywhere; }
        .hero-cinematic .hero-sub {
            margin-top: .8rem; font-size: 1rem; line-height: 1.5;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 4; line-clamp: 4; overflow: hidden;
        }
        .hero-cinematic .hero-meta { margin-top: 1.1rem; flex-direction: column; gap: .5rem; font-size: .92rem; }
        .hero-cinematic .hero-actions { margin-top: 1.4rem; display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
        .hero-cinematic .hero-actions > a { justify-content: center; text-align: center; white-space: nowrap; }
        .hero-cinematic .hero-actions > a:first-child { grid-column: 1 / -1; padding-top: .95rem; padding-bottom: .95rem; font-size: .95rem; }
        .hero-cinematic .hero-actions > a.btn-glass { padding-left: .5rem; padding-right: .5rem; }
        .hero-cinematic .hero-actions > form, .hero-cinematic .hero-actions > span { grid-column: 1 / -1; justify-self: start; }
        .hero-cinematic .hero-cue { display: none; }
    }
</style>
