<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'cynetpay' => [
        'api_key'      => env('CYNETPAY_API_KEY'),
        'api_password' => env('CYNETPAY_API_PASSWORD'),
        'base_url'     => env('CYNETPAY_BASE_URL', 'https://api.cinetpay.co/'),
        'notify_url'   => env('CYNETPAY_NOTIFY_URL'),
        'return_url'   => env('CYNETPAY_RETURN_URL'),
        'cancel_url'   => env('CYNETPAY_CANCEL_URL'),
        'currency'     => env('CYNETPAY_CURRENCY', 'XOF'),
        'mode'         => env('CYNETPAY_MODE', 'PRODUCTION'),
    ],

    // Virements sortants (versements aux prestataires) — API de transfert CinetPay.
    // Désactivé tant que CINETPAY_TRANSFER_ENABLED n'est pas à true et que les identifiants manquent.
    'cinetpay_transfer' => [
        'enabled'  => (bool) env('CINETPAY_TRANSFER_ENABLED', false),
        'api_key'  => env('CINETPAY_TRANSFER_API_KEY', env('CYNETPAY_API_KEY')),
        'password' => env('CINETPAY_TRANSFER_PASSWORD'),
        'base_url' => env('CINETPAY_TRANSFER_BASE_URL', 'https://client.cinetpay.com/v1/'),
        'max_auto_amount_xof' => (int) env('CINETPAY_TRANSFER_MAX_AUTO_XOF', 500000),
    ],

    // SMS / WhatsApp (événements clés). driver : off | log | twilio ; channel : sms | whatsapp.
    'text_messages' => [
        'driver' => env('TEXT_MESSAGES_DRIVER', 'off'),
        'channel' => env('TEXT_MESSAGES_CHANNEL', 'sms'),
        'from' => env('TEXT_MESSAGES_FROM'),
        'twilio_sid' => env('TWILIO_SID'),
        'twilio_token' => env('TWILIO_TOKEN'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    // Google Maps : la clé navigateur (Maps JavaScript API) doit être restreinte
    // aux domaines du site ; la clé serveur (Routes API) n'est jamais exposée.
    'google_maps' => [
        'browser_key' => env('GOOGLE_MAPS_BROWSER_KEY'),
        'server_key' => env('GOOGLE_MAPS_SERVER_KEY'),
        'map_id' => env('GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID'),
        'route_cache_minutes' => (int) env('GOOGLE_MAPS_ROUTE_CACHE_MINUTES', 10),
    ],


    // Images publiques : variantes WebP redimensionnées + chargement différé (middleware OptimizeImages).
    'images' => [
        'optimize' => env('IMAGE_OPTIMIZE', true),
    ],

];
