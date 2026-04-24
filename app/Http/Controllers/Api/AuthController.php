<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon; 

class AuthController extends Controller
{

    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ]);
    }
   /* public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json([
                'status'  => false,
                'message' => 'User not found'
            ], 404);
        }
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
    }*/

        public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'status'  => false,
            'message' => 'User not found'
        ], 404);
    }

if (!Hash::check($request->password, $user->password)) {
    return response()->json([
        'status'  => false,
        'message' => 'Invalid credentials'
    ], 401);
}

// ✅ ADD THIS LINE (important)
$user->last_login_at = Carbon::now();
$user->save();

    // Create new token
    $token = $user->createToken('erp-token');

    $plainTextToken = $token->plainTextToken;

    // Save token in users table
    $user->session_token = $token->accessToken->id;
    $user->save();

    // Roles & Permissions
    $roles       = $user->getRoleNames();
    $role        = $roles->first();
    $permissions = $user->getAllPermissions()->pluck('name');

    return response()->json([
        'status' => true,
        'token'  => $plainTextToken,
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


public function checkSession(Request $request)
{
    $user = $request->user();

    $currentTokenId = $request->user()->currentAccessToken()->id;

    if ($user->session_token != $currentTokenId) {

        return response()->json([
            'status' => false,
            'message' => 'Same user already logged in another browser/device'
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Session valid'
    ]);
}



public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return response()->json([
        'status' => true,
        'message' => __($status)
    ]);
}


public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        }
    );

    return response()->json([
        'status' => $status === Password::PASSWORD_RESET,
        'message' => __($status)
    ]);
}



public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed'
    ]);

    $user = auth()->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Current password is incorrect'
        ], 400);
    }

    $user->update([
        'password' => Hash::make($request->new_password)
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Password changed successfully'
    ]);
}
}
