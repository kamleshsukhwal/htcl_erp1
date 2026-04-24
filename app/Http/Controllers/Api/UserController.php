<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
{
    $users = User::with('roles')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,

                // roles as array
                'roles' => $user->roles->pluck('name'),

                // last login
                'last_login' => $user->last_login_at,
            ];
        });

    return response()->json([
        'status' => true,
        'data' => $users
    ]);
}
}