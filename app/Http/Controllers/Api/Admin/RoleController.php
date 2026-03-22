<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return Role::all();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);

        $role = Role::create(['name' => $request->name , 'guard_name' => 'web']);

        return response()->json([
            'status' => true,
            'role' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return response()->json(['status' => true]);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['status' => true]);
    }

    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $incoming = $request->permissions;

        // Show all permissions in DB to verify seeder ran
        $allPerms = \Illuminate\Support\Facades\DB::table('permissions')->get(['id','name','guard_name']);

        // Try to find matching ones manually
        $items = is_array($incoming) ? $incoming : [$incoming];
        $matched = \App\Models\Permission::whereIn('name', $items)->get(['id','name','guard_name']);

        $role->syncPermissions($incoming);

        $saved = \Illuminate\Support\Facades\DB::table('role_has_permissions')
            ->where('role_id', $role->id)
            ->pluck('permission_id');

        return response()->json([
            'status'                  => true,
            'debug_role_id'           => $role->id,
            'debug_incoming'          => $incoming,
            'debug_all_permissions'   => $allPerms,
            'debug_matched'           => $matched,
            'debug_saved_ids'         => $saved,
        ]);
    }
}
