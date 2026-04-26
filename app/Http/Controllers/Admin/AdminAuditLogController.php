<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        if (! Schema::hasTable('admin_audit_logs')) {
            return view('admin.audit.index', [
                'logs' => null,
                'missingTable' => true,
            ]);
        }

        $query = AdminAuditLog::query()->with('user:id,first_name,last_name,email')->latest('created_at');

        if ($request->filled('method')) {
            $query->where('method', strtoupper((string) $request->query('method')));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->query('user_id'));
        }

        if ($request->filled('route')) {
            $query->where('route_name', 'like', '%'.$request->query('route').'%');
        }

        $logs = $query->paginate(40)->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'missingTable' => false,
        ]);
    }
}
