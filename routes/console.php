<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:notify-expiring')->dailyAt('08:00');
Schedule::command('articles:publish-scheduled')->everyFiveMinutes();
Schedule::command('wallet:release-pending')->dailyAt('06:00');
Schedule::command('reservations:expire-stale')->hourly();
Schedule::command('art:release-pending')->dailyAt('06:30');

// Sauvegarde (audit DB-06) : dump base de données + fichiers uploadés (storage/app/public)
// + .env, purge des sauvegardes selon la stratégie de rétention configurée, puis contrôle
// de santé (âge/poids) qui notifie par e-mail uniquement en cas d'anomalie. Stockée sur le
// disque local par défaut faute de stockage distant configuré (cf. audit ARCH-05) — à
// pointer vers un disque S3-compatible dès qu'un tel stockage sera disponible, sans quoi
// une panne serveur emporte à la fois les données et leur sauvegarde.
Schedule::command('backup:run')->dailyAt('03:00')->onOneServer();
Schedule::command('backup:clean')->dailyAt('03:45')->onOneServer();
Schedule::command('backup:monitor')->dailyAt('04:00')->onOneServer();
