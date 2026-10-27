<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Erreurs de l'API mobile (/api/*) : toujours { "message": "…" } (+ "errors" pour la validation),
 * avec un message traduit que l'application peut afficher tel quel.
 */
class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => self::isApi($request) || $request->expectsJson());

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return self::isApi($request) ? self::json(__('Connexion requise.'), 401) : null;
        });

        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            return self::isApi($request) ? self::json(__('Accès non autorisé.'), 403) : null;
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            return self::isApi($request) ? self::json(__('Ressource introuvable.'), 404) : null;
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::json(__('Trop de tentatives. Réessayez dans quelques instants.'), 429)
                ->withHeaders($e->getHeaders());
        });
    }

    private static function isApi(Request $request): bool
    {
        return $request->is('api/*');
    }

    private static function json(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
