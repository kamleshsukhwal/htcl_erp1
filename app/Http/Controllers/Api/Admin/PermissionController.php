<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::all();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);

        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'web',
        ]);

        return response()->json($permission);
    }
}
