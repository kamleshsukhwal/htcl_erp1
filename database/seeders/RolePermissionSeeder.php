<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // All modules from CheckPermission middleware prefixMap
        $modules = [
            'project', 'vendor', 'purchase-orders', 'dc-in', 'dc-outs',
            'execution', 'boq', 'hr', 'clients', 'user', 'ratings',
            'feedback', 'qa', 'ncr', 'audit', 'finance', 'dashboard','admin',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        // Generate and insert all module.action permissions
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}"],
                    ['guard_name' => 'web']
                );
            }
        }

        // Create admin role and assign all permissions
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web', 'status' => 1, 'created_by' => 0, 'updated_by' => 0]
        );

        // Ensure status is set for existing seeded roles
        $admin->status = 1;
        $admin->save();
        $allPermissionIds = Permission::pluck('id')->toArray();
        $admin->syncPermissions($allPermissionIds);

        // Create admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('123456')]
        );

        $user->assignRole('admin');
    }
}
