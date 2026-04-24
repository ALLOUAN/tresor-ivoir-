<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SiteMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('site_settings')) {
            return $next($request);
        }

        $settings = SiteSetting::query()->find(1);
        if (! $settings || ! $settings->maintenance_mode) {
            return $next($request);
        }

        if ($this->mayPassDuringMaintenance($request)) {
            return $next($request);
        }

        return response()
            ->view('errors.maintenance-site', [], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function mayPassDuringMaintenance(Request $request): bool
    {
        if ($request->routeIs('login', 'login.post')) {
            return true;
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return true;
        }

        return false;
    }
}
