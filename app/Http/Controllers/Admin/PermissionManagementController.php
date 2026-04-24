<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Services\RolePermissionMap;

class PermissionManagementController extends Controller
{
    public function index()
    {
        $permissions = collect(Permission::cases())
            ->groupBy(fn (Permission $p) => $p->group());

        $roles = RolePermissionMap::roles();

        $map = RolePermissionMap::get();

        return view('admin.permissions.index', compact('permissions', 'roles', 'map'));
    }
}
