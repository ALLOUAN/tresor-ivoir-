<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RolePermissionMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserRoleManagementController extends Controller
{
    public function index(): View
    {
        $allowedRoles = RolePermissionMap::roles();
        $search = trim((string) request('q', ''));
        $roleFilter = (string) request('role', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($roleFilter, $allowedRoles, true), function ($query) use ($roleFilter) {
                $query->where('role', $roleFilter);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        $roles = $allowedRoles;
        $permissionOptions = collect(Permission::cases())
            ->groupBy(fn (Permission $permission) => $permission->group());

        return view('admin.users.index', compact('users', 'roles', 'permissionOptions', 'search', 'roleFilter'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(RolePermissionMap::roles())],
        ]);

        if ((int) $request->user()->id === (int) $user->id) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $user->update([
            'role' => $validated['role'],
        ]);

        return back()->with('success', "Rôle mis à jour pour {$user->full_name}.");
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedRoles = RolePermissionMap::roles();
        $allowedPermissions = array_column(Permission::cases(), 'value');

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in($allowedRoles)],
            'granted_permissions' => ['nullable', 'array'],
            'granted_permissions.*' => ['string', Rule::in($allowedPermissions)],
        ]);

        User::withTrashed()->where('email', $validated['email'])->whereNotNull('deleted_at')->forceDelete();

        $user = User::create([
            'email' => $validated['email'],
            'password_hash' => $validated['password'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'role' => $validated['role'],
            'granted_permissions' => array_values(array_unique($validated['granted_permissions'] ?? [])),
            'is_active' => true,
            'is_verified' => true,
            'locale' => 'fr',
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "Utilisateur créé: {$user->full_name}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->full_name;
        $user->delete();

        return back()->with('success', "Utilisateur « {$name} » supprimé.");
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $allowedPermissions = array_column(Permission::cases(), 'value');

        $validated = $request->validate([
            'granted_permissions' => ['nullable', 'array'],
            'granted_permissions.*' => ['string', Rule::in($allowedPermissions)],
        ]);

        $user->update([
            'granted_permissions' => array_values(array_unique($validated['granted_permissions'] ?? [])),
        ]);

        return back()->with('success', "Permissions mises à jour pour {$user->full_name}.");
    }
}
