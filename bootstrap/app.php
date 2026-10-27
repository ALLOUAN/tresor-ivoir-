<?php

use App\Http\Middleware\OptimizeImages;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\SiteMaintenanceMiddleware;
use App\Http\Middleware\SubscriptionActiveMiddleware;
use App\Http\Middleware\TrackInteractions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'subscription.active' => SubscriptionActiveMiddleware::class,
        ]);

        $middleware->web(append: [
            SetLocaleMiddleware::class,
            SiteMaintenanceMiddleware::class,
            SecurityHeaders::class,
            OptimizeImages::class,
            TrackInteractions::class,
            \App\Http\Middleware\TrackSiteVisit::class,   // audience du site (tableau de bord admin)
            \App\Http\Middleware\ShrinkUploadedImages::class,
        ]);

        // API mobile (/api/v1) : réponses JSON et langue du téléphone.
        $middleware->api(prepend: [
            \App\Http\Middleware\Api\ForceJsonResponse::class,
            \App\Http\Middleware\Api\SetApiLocale::class,
        ]);

        // Posé en JavaScript par la fenêtre « Astuces de Mian » (déjà vue) : lu tel quel côté serveur.
        $middleware->encryptCookies(except: ['tiv_mian_seen']);

        $middleware->validateCsrfTokens(except: [
            '/webhook/cynetpay',
            '/galerie/achat/webhook',
            '/reservations/paiement/webhook',
            '/art-creations/achat/webhook',
            '/wallet/recharge/webhook',
            '/visites/webhook',
            '/circuits/paiement/webhook',
            '/loisirs/paiement/webhook',
            '/trajets/paiement/webhook',
            '/sur-mesure/paiement/webhook',
            '/demandes-reservation/paiement/webhook',
            '/bons-cadeaux/paiement/webhook',
            '/billing/webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API mobile : erreurs au format JSON avec un message lisible (FR/EN), sans détail technique.
        \App\Exceptions\ApiExceptionRenderer::register($exceptions);
    })->create();
