<?php

use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\SiteMaintenanceMiddleware;
use App\Http\Middleware\SubscriptionActiveMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
        ]);

        $middleware->validateCsrfTokens(except: [
            '/webhook/cynetpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
