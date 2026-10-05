<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mots-clés de contournement de paiement / coordonnées externes
    |--------------------------------------------------------------------------
    |
    | Détectés dans les messages de la messagerie client-prestataire pour
    | signaler (sans bloquer l'envoi) une tentative probable de sortir la
    | transaction ou l'échange de la plateforme.
    |
    */
    'payment_bypass_keywords' => [
        'whatsapp', 'telegram', 'western union', 'moneygram', 'virement',
        'iban', 'numéro de compte', 'numero de compte', 'compte bancaire',
        'hors plateforme', 'hors de la plateforme', 'en dehors de la plateforme',
        'espèces', 'especes', 'cash', 'mobile money direct', 'paiement direct',
        'contact direct', 'appelle-moi', 'appelle moi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mots interdits (contenu abusif)
    |--------------------------------------------------------------------------
    |
    | Liste volontairement courte et prudente — à enrichir selon les besoins
    | réels de modération, sans avoir à toucher au code.
    |
    */
    'prohibited_words' => [
        'connard', 'connasse', 'salope', 'pute', 'enculé', 'enculer',
        'nique', 'batard', 'bâtard',
    ],
];
