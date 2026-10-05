{{--
    Charte typographique du site — point d'entrée unique.

    Inclus depuis partials.theme-init (chargé sur toutes les pages publiques,
    prestataire, visiteur et admin) : toute modification de police se fait ici,
    sans avoir à toucher chaque vue individuellement.

    Familles retenues (déjà majoritaires avant cette uniformisation — Inter sur
    47 vues, Playfair Display sur 46) :
      --font-sans    Inter             texte courant, navigation, boutons, formulaires, admin
      --font-serif   Playfair Display  titres éditoriaux (via class="font-serif", jamais imposé
                                        aux h1-h6 bruts : l'admin utilise volontairement des
                                        titres sans-serif — cf. resources/views/admin/**)
      --font-elegant Cormorant Garamond accroches ("kickers") et intros en italique légère
      --font-reading Lora              corps des articles longs / pages d'information
                                        (police « lecture » dédiée — choix éditorial volontaire,
                                        pas une police concurrente : réservée aux zones .prose-*)

    Tailwind est chargé par CDN sur chaque page (pas de build), donc ses utilitaires
    .font-sans/.font-serif ne sont pas configurés avec nos polices : on les redéfinit
    ici avec !important pour qu'ils gagnent quel que soit l'ordre d'injection du CDN.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Cormorant+Garamond:wght@300;400;500;600&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
    :root {
        --font-sans: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        --font-serif: 'Playfair Display', Georgia, serif;
        --font-elegant: 'Cormorant Garamond', Georgia, serif;
        --font-reading: 'Lora', Georgia, serif;
        --leading-body: 1.65;
    }

    html, body { font-family: var(--font-sans); }
    .font-sans { font-family: var(--font-sans) !important; }
    .font-serif { font-family: var(--font-serif) !important; }
    .font-elegant { font-family: var(--font-elegant) !important; }
    /* Alias historiques (Plus Jakarta Sans, Instrument Serif) : convergent désormais
       vers les polices communes plutôt que vers leur police d'origine. */
    .font-plus { font-family: var(--font-sans) !important; }
    .font-display { font-family: var(--font-serif) !important; }
    .font-reading, .prose-content, .prose-info { font-family: var(--font-reading); line-height: var(--leading-body); }

    /* Filet par défaut : ne s'applique que si aucune classe Tailwind (leading-*) n'est déjà posée. */
    p { line-height: var(--leading-body); }
</style>
