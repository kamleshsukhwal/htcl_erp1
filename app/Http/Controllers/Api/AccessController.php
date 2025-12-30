<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessController extends Controller
{
   public function access()
{
    $user = auth()->user();

    return response()->json([
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'modules' => \App\Models\Module::pluck('is_enabled', 'name')
    ]);
}

}
