<?php

namespace App\Traits;

use App\Enums\Permission;
use App\Services\RolePermissionMap;

trait HasPermissions
{
    protected function extraPermissions(): array
    {
        $extra = $this->granted_permissions ?? [];

        return is_array($extra) ? $extra : [];
    }

    public function hasPermission(Permission|string $permission): bool
    {
        $value = $permission instanceof Permission ? $permission->value : $permission;

        if ($this->role === 'admin') {
            return true;
        }

        if (RolePermissionMap::roleHas($this->role, $value)) {
            return true;
        }

        return in_array($value, $this->extraPermissions(), true);
    }

    public function hasAnyPermission(Permission|string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(Permission|string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function permissions(): array
    {
        return array_values(array_unique(array_merge(
            RolePermissionMap::forRole($this->role),
            $this->extraPermissions()
        )));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    public function isVisitor(): bool
    {
        return $this->role === 'visitor';
    }
}
