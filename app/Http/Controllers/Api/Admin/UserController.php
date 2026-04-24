<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
class UserController extends Controller
{
    use SendEmail;
   /* public function index()
    {
        return User::with('roles')->get();

   // dd($users);
    }*/
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
        $roleNames = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();

        $user->assignRole($roleNames);
        
         // Fetch assigned roles
        $roles = $user->roles()->pluck('name'); // returns collection of role names


       $this->sendMail(
            $user->email,
            'Welcome! Your Account Has Been Created',
            "Hello {$user->name},<br><br>
            Welcome! Your account has been successfully created.<br><br>
            
            <b>Account Details:</b><br>
            Email: {$user->email}<br><br>
            
            You can now log in and start using our services.<br><br>
            
            If you did not create this account, please contact our support team immediately.<br><br>
            
            Regards,<br>
            Support Team"
        );
        

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => [
                'user' => $user,
                'roles' => $roles
            ]
        ]);
    }

    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->syncRoles($request->roles);

        return response()->json(['status' => true]);
    }
}
