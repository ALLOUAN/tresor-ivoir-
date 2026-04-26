<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActions
{
    private const MUTABLE = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /** @var array<int, string> */
    private const SENSITIVE_KEYS = [
        'password', 'password_hash', 'password_confirmation', 'current_password',
        '_token', '_method',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! Schema::hasTable('admin_audit_logs')) {
            return;
        }

        $user = $request->user();
        if (! $user || $user->role !== 'admin') {
            return;
        }

        if (! $request->is('admin') && ! $request->is('admin/*')) {
            return;
        }

        if (! in_array($request->method(), self::MUTABLE, true)) {
            return;
        }

        $keys = array_keys($request->except(self::SENSITIVE_KEYS));
        $meta = ['input_keys' => array_slice($keys, 0, 80)];

        try {
            AdminAuditLog::query()->create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
                'method' => $request->method(),
                'path' => mb_substr((string) $request->path(), 0, 500),
                'route_name' => $request->route()?->getName(),
                'status_code' => $response->getStatusCode(),
                'meta' => $meta,
            ]);
        } catch (\Throwable) {
            // évite de casser la requête si la table n'est pas migrée
        }
    }
}
