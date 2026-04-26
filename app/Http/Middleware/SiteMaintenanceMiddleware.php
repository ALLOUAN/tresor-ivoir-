<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
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

        return response()->view('errors.maintenance-site', [
            'maintenanceMessage' => $this->maintenanceMessage($settings),
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function maintenanceMessage(?SiteSetting $settings): string
    {
        if (! $settings || ! Schema::hasColumn('site_settings', 'maintenance_message')) {
            return 'Nous effectuons une mise à jour. Merci de revenir un peu plus tard.';
        }

        return trim((string) $settings->maintenance_message) !== ''
            ? (string) $settings->maintenance_message
            : 'Nous effectuons une mise à jour. Merci de revenir un peu plus tard.';
    }

    private function mayPassDuringMaintenance(Request $request): bool
    {
        if ($request->routeIs('login', 'login.post')) {
            return true;
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $settings = SiteSetting::query()->find(1);
        if ($settings && Schema::hasColumn('site_settings', 'maintenance_allowed_ips')) {
            $allowedIps = $settings->allowedIpsList();
            if ($allowedIps !== [] && in_array((string) $request->ip(), $allowedIps, true)) {
                return true;
            }
        }

        return false;
    }
}
