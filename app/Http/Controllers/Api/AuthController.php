<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
   
public function profile(Request $request)
{
    return response()->json([
        'status' => true,
        'data' => $request->user()
    ]);
}
public function login(Request $request) 
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'status'  => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    // Delete old tokens (optional but recommended for ERP)
    $user->tokens()->delete();

    // Create new token
    $token = $user->createToken('erp-token')->plainTextToken;

    // Roles & Permissions
    $roles       = $user->getRoleNames();                // ['admin']
    $role        = $roles->first();                      // 'admin'
    $permissions = $user->getAllPermissions()->pluck('name'); 

    return response()->json([
        'status' => true,
        'token'  => $token,
        'user'   => [
        'id'    => $user->id,
        'name'  => $user->name,
        'email' => $user->email,
        'role'  => $role
        ],
        'roles'       => $roles,
        'permissions' => $permissions
    ]);
}
}


