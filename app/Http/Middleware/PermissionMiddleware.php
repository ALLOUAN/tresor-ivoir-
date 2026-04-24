<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        foreach ($permissions as $permission) {
            if (Auth::user()->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Vous n\'avez pas la permission d\'accéder à cette page.');
    }
}
