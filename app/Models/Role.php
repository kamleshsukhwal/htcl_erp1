<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'guard_name'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    public function syncPermissions($permissions): static
    {
        // Always delete existing permissions for this role first
        DB::table('role_has_permissions')->where('role_id', $this->id)->delete();

        if (empty($permissions)) {
            return $this;
        }

        $items = is_array($permissions) ? $permissions : [$permissions];

        // Support both IDs (integers) and names (strings)
        if (is_numeric($items[0])) {
            $permissionIds = Permission::whereIn('id', $items)->pluck('id')->toArray();
        } else {
            $permissionIds = Permission::whereIn('name', $items)->pluck('id')->toArray();
        }

        if (!empty($permissionIds)) {
            $rows = array_map(fn($pid) => ['role_id' => $this->id, 'permission_id' => $pid], $permissionIds);
            DB::table('role_has_permissions')->insert($rows);
        } else {
            \Log::warning('syncPermissions: no matching permissions found', [
                'role_id' => $this->id,
                'input'   => $permissions,
            ]);
        }

        return $this;
    }
}
