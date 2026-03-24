<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Module;

class RoleController extends Controller
{
    public function index()
    {
        return Role::where('status',1)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|unique:roles,name',
            'status' => 'nullable|in:0,1' // or boolean
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'web',
            'status'     => $request->status ?? 1 ,
            'created_by' => auth()->id() ?? 0,
            'updated_by' => auth()->id() ?? 0,
        ]);

        return response()->json([
            'status' => true,
            'role'   => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'   => 'required|unique:roles,name,' . $id,
            'status' => 'nullable|in:0,1' // or boolean
        ]);

        $role->update([
            'name'       => $request->name,
            'status'     => $request->has('status') ? $request->status : $role->status ,
            'updated_by' => auth()->id() ?? 0
        ]);

        return response()->json(['status' => true]);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['status' => true]);
    }

    public function rolesAndModules()
    {
        return response()->json([
            'status'  => true,
            'roles'   => Role::where('status',1)->get(),
            'modules' => Module::where('status',1)->get(),
        ]);
    }

    public function assignPermissions(Request $request, $id)
    {
        $role = Role::where('status',1)->findOrFail($id);

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
