<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use App\Models\LoginHistory; 

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
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    // ❌ BLOCK INACTIVE USER
    if (!$user->is_active) {
        return response()->json([
            'status' => false,
            'message' => 'Your account is inactive. Contact admin.'
        ], 403);
    }

    // Password check
    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    // ✅ Create token (ONLY ONCE)
    $token = $user->createToken('erp-token');
    $plainTextToken = $token->plainTextToken;

    // ✅ Update last login
    $user->last_login_at = Carbon::now();

    // ✅ Save session token (Sanctum safe)
    $user->session_token = $user->currentAccessToken()->id ?? null;

    $user->save();

    // ✅ STORE LOGIN HISTORY
    LoginHistory::create([
        'user_id' => $user->id,
        'ip_address' => $request->ip(),
        'user_agent' => $request->header('User-Agent'),
        'login_at' => Carbon::now()
    ]);

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
            'role'  => $role,
            'last_login' => $user->last_login_at
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


public function logout(Request $request)
{
    $user = $request->user();

    // ✅ Update last login history logout time
    LoginHistory::where('user_id', $user->id)
        ->whereNull('logout_at')
        ->latest()
        ->first()
        ?->update([
            'logout_at' => now()
        ]);

    $user->tokens()->delete();

    return response()->json([
        'status' => true,
        'message' => 'Logged out successfully'
    ]);
}
}
