<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::with('roles')->get();
    }

    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->assignRole($request->roles);

        return response()->json(['status' => true]);
    }

    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->syncRoles($request->roles);

        return response()->json(['status' => true]);
    }
}
