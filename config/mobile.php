<?php

/*
| Application mobile (Flutter) : réglages lus par GET /api/v1/meta au démarrage.
| min_version : en dessous, l'application demande une mise à jour obligatoire.
*/

return [
    'android' => [
        'min_version' => env('MOBILE_ANDROID_MIN_VERSION', '1.0.0'),
        'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.0'),
        'store_url' => env('MOBILE_ANDROID_STORE_URL', 'https://play.google.com/store/apps/details?id=ci.tresorsivoire.app'),
    ],

    // Durée de vie d'un jeton de connexion (jours) ; null = jusqu'à la déconnexion.
    'token_ttl_days' => env('MOBILE_TOKEN_TTL_DAYS', 90),
];
