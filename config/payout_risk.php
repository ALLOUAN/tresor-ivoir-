<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seuils de décision
    |--------------------------------------------------------------------------
    |
    | score < auto_approve_below            -> approbation automatique
    | score entre les deux                  -> vérification complémentaire (code OTP)
    | score >= suspend_at_or_above           -> suspendu, admin notifié
    |
    */
    'auto_approve_below' => 25,
    'suspend_at_or_above' => 70,

    /*
    |--------------------------------------------------------------------------
    | Poids de chaque signal (0-100, cumulés puis plafonnés à 100)
    |--------------------------------------------------------------------------
    */
    'weights' => [
        'amount_near_full_balance' => 25,
        'amount_far_above_average' => 15,
        'new_account' => 15,
        'prior_rejected_payout' => 15,
        'frequency_24h' => 30,
        'frequency_7d' => 20,
        'invalid_destination_format' => 100, // toujours bloquant, jamais auto-approuvé
        'destination_changed' => 15,
        'recent_sensitive_change' => 35,
        'unusual_login_ip' => 20,
        'no_login_history' => 5,
        'duplicate_recent_request' => 40,
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres des contrôles
    |--------------------------------------------------------------------------
    */
    'amount_near_full_balance_ratio' => 0.90,
    'amount_average_multiplier' => 3,
    'new_account_days' => 7,
    'frequency_24h_max' => 2,
    'frequency_7d_max' => 5,
    'sensitive_change_window_hours' => 72,
    'duplicate_request_window_minutes' => 30,
];
