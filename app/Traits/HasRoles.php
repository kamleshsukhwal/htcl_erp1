<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles', 'model_id', 'role_id');
    }

    public function getRoleNames()
    {
        return $this->roles()->pluck('name');
    }

    public function hasRole($roles): bool
    {
        $names = is_array($roles) ? $roles : [$roles];
        return $this->roles()->whereIn('name', $names)->exists();
    }

    public function assignRole($role): static
    {
        $names = is_array($role) ? $role : [$role];
        $ids = Role::whereIn('name', $names)->pluck('id')->toArray();
        $existing = $this->roles()->pluck('roles.id')->toArray();
        $toAttach = array_diff($ids, $existing);
        if ($toAttach) {
            $this->roles()->attach($toAttach);
        }
        return $this;
    }

    public function syncRoles($roles): static
    {
        $names = is_array($roles) ? $roles : [$roles];
        $ids = Role::whereIn('name', $names)->pluck('id')->toArray();
        $this->roles()->sync($ids);
        return $this;
    }

    public function getAllPermissions()
    {
        
        $roleIds = $this->roles()->pluck('roles.id');
      
        return Permission::whereIn('id', function ($query) use ($roleIds) {
            $query->select('permission_id')
                ->from('role_has_permissions')
                ->whereIn('role_id', $roleIds);
        })->get();
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->getAllPermissions()->pluck('name')->contains($permission);
    }
}
