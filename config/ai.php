<?php

/*
 |--------------------------------------------------------------------------
 | Modules d'intelligence artificielle
 |--------------------------------------------------------------------------
 | Module 1 : recommandation hybride (contenu + règles métier + collaboratif)
 | Module 2 : planificateur d'itinéraires « Mon séjour »
 | Module 3 : recherche intelligente (option)
 | Tout peut être coupé avec AI_ENABLED=false : le site retrouve alors son
 | comportement d'origine (aucune section « pour vous », aucun suivi).
 */
return [
    'enabled' => env('AI_ENABLED', true),

    // Suivi des consultations (vues de fiches) pour alimenter le filtrage collaboratif.
    'tracking' => env('AI_TRACKING', true),

    'cache_minutes' => 10,

    'recommender' => [
        // Poids des signaux d'un même acteur (visiteur connecté ou session anonyme).
        'action_weights' => ['view' => 1.0, 'favorite' => 3.0, 'reserve' => 5.0],
        // Part maximale du filtrage collaboratif dans le score final ; elle monte
        // avec l'historique du visiteur et le volume d'acteurs actifs.
        'collab_max_share' => 0.6,
        'collab_full_history' => 6,   // nombre d'items consultés pour atteindre la part maximale
        'collab_full_actors' => 15,   // nombre d'acteurs actifs pour atteindre la part maximale
        // Fusion contenu / règles quand aucun historique collaboratif n'est disponible.
        'content_weight' => 0.55,
        'rules_weight' => 0.45,
        // Diversification (MMR) : 1 = pertinence seule, 0 = diversité seule.
        'mmr_lambda' => 0.75,
        'max_per_type' => 3,
        'popularity_prior_rating' => 3.8,
        'popularity_prior_count' => 5,
    ],

    'planner' => [
        'default_meal_xof' => 3500,        // repas estimé par personne quand la carte du restaurant est vide
        'lodging_budget_share' => 0.45,    // part maximale du budget consacrée à l'hébergement
        'pace' => [
            'calme' => ['activities' => 2, 'max_km' => 30],
            'modere' => ['activities' => 3, 'max_km' => 60],
            'soutenu' => ['activities' => 4, 'max_km' => 100],
        ],
        'max_days' => 14,
        'max_cities' => 3,
    ],

    /*
     |--------------------------------------------------------------------------
     | Modération assistée (module 4)
     |--------------------------------------------------------------------------
     | L'IA ne publie, ne refuse et ne supprime rien : elle calcule un score de risque et des
     | raisons, et classe les contenus dans une file que l'administrateur traite (priorité aux
     | cas douteux). Les listes de mots sont à enrichir sans toucher au code.
     */
    'moderation' => [
        'auto' => env('AI_MODERATION_AUTO', true),        // analyse à chaque enregistrement d'un contenu
        'review_threshold' => 25,                          // à partir de ce score : à vérifier
        'likely_threshold' => 60,                          // à partir de ce score : problème probable
        'profanity' => [
            'connard', 'connasse', 'salope', 'putain', 'merde', 'encule', 'enculer', 'pute', 'batard', 'ordure', 'fdp', 'ntm',
            'nique', 'niquer', 'bouffon', 'abruti', 'debile', 'degenere', 'sale negre', 'sale blanc', 'racaille',
        ],
        'spam_phrases' => [
            'casino en ligne', 'casino gratuit', 'pari sportif', 'paris sportifs', 'viagra', 'bitcoin', 'crypto', 'investissement garanti', 'gagnez', 'gagner de l argent',
            'cliquez ici', 'cliquez-ici', '100% gratuit', 'offre exclusive', 'pret rapide', 'porno', 'escort', 'rencontre coquine', 'xxx',
            'travail a domicile', 'devenez riche', 'followers', 'abonnez-vous a ma chaine',
        ],
        'placeholders' => ['lorem ipsum', 'dolor sit amet', 'texte de test', 'test test', 'azerty', 'qwerty', 'blablabla', 'xxxxx', 'a completer', 'todo'],
        // Contrôle de conformité du contenu des photos par un modèle de vision (optionnel).
        // 'none' : seules les mesures techniques (résolution, netteté, lumière, doublons) sont faites.
        'vision' => [
            'driver' => env('AI_VISION_DRIVER', 'none'),   // none | anthropic
            'model' => env('AI_VISION_MODEL', 'claude-haiku-4-5-20251001'),
        ],
        'image' => [
            'min_width' => 640, 'min_height' => 400, 'blur_relative' => 0.05, 'very_blur_relative' => 0.02,
            'dark_mean' => 40, 'bright_mean' => 220, 'low_contrast_std' => 20, 'duplicate_distance' => 4,
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Analytique prédictive pour les prestataires (forfaits Silver et Gold)
     |--------------------------------------------------------------------------
     | Prévision de fréquentation (vues de la fiche) à partir de l'historique quotidien de
     | provider_analytics, comparaison à la catégorie, suggestions d'amélioration.
     | Silver (stats « advanced ») : prévision 30 jours, comparaison, 3 suggestions.
     | Gold (stats « full ») : horizon 90 jours, comparaison complète, plan d'amélioration, export.
     */
    'analytics' => [
        'collect' => env('AI_ANALYTICS_COLLECT', true),   // compte les vues des fiches dans provider_analytics
        'min_days' => 14,          // en dessous : pas de prévision (historique insuffisant)
        'backtest_days' => 28,     // historique minimal pour mesurer la précision par rétro-test
        'min_peers' => 3,          // taille minimale d'une catégorie pour s'y comparer
        // HYPOTHÈSE de départ, remplacée par la saisonnalité apprise dès 12 mois d'historique :
        // indice mensuel de fréquentation touristique en Côte d'Ivoire (saison sèche et fêtes de fin d'année en hausse,
        // saison des pluies en baisse). À ajuster avec vos propres données.
        'seasonal_prior' => [1 => 1.20, 2 => 1.15, 3 => 1.05, 4 => 0.95, 5 => 0.85, 6 => 0.80, 7 => 0.85, 8 => 0.90, 9 => 0.85, 10 => 0.95, 11 => 1.10, 12 => 1.30],
    ],

    /*
     |--------------------------------------------------------------------------
     | Assistant conversationnel (RAG)
     |--------------------------------------------------------------------------
     | L'assistant retrouve d'abord les passages utiles du site (fiches, articles, FAQ, cultures),
     | puis répond uniquement à partir d'eux, en citant ses sources. Sans source suffisante, il le dit
     | au lieu d'inventer. 'extractive' = réponse composée des phrases du site, sans service externe ;
     | 'anthropic' = reformulation par un modèle de langage, vérifiée avant affichage.
     */
    // Assistant ancré sur Google Maps (API Gemini, outil googleMaps) : sert de relais quand le site
    // ne contient pas la réponse. Désactivé par défaut ; facturé à l'usage par Google.
    'maps_grounding' => [
        'enabled' => env('AI_MAPS_GROUNDING', false),
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('AI_MAPS_MODEL', 'gemini-2.5-flash'),
    ],

    'chat' => [
        'enabled' => env('AI_CHAT_ENABLED', true),
        'driver' => env('AI_CHAT_DRIVER', 'extractive'),   // extractive | anthropic
        'model' => env('AI_CHAT_MODEL', 'claude-haiku-4-5-20251001'),
        'min_score' => 0.2,        // en dessous : « je n'ai pas trouvé » (pas de réponse inventée)
        'max_question' => 300,
        'sources' => 5,
        'passage_words' => 70,
    ],

    /*
     |--------------------------------------------------------------------------
     | Aide à la rédaction et à la traduction (éditeurs, prestataires)
     |--------------------------------------------------------------------------
     | Résumé et métadonnées SEO fonctionnent sans service externe (extraction). Les titres
     | alternatifs et la traduction FR/EN exigent un modèle de langage (ANTHROPIC_API_KEY).
     | L'utilisateur relit et applique chaque suggestion : rien n'est enregistré automatiquement.
     */
    'writing' => [
        'enabled' => env('AI_WRITING_ENABLED', true),
        'model' => env('AI_WRITING_MODEL', 'claude-sonnet-5-5'),
        'daily_cap' => 100,        // actions par utilisateur et par jour
        'max_chars' => 20000,
    ],

    // Modèle de langage pour la rédaction du programme (optionnel).
    // 'template' = rédaction déterministe sans appel externe (valeur par défaut).
    'llm' => [
        'driver' => env('AI_LLM_DRIVER', 'template'),   // template | anthropic
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('AI_LLM_MODEL', 'claude-haiku-4-5-20251001'),
        'timeout' => 25,
    ],
];
