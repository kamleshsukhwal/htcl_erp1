<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'system' => [
                'users' => \App\Models\User::count(),
                'roles' => \Spatie\Permission\Models\Role::count(),
            ],
            'modules' => \App\Models\Module::pluck('is_enabled', 'name'),
            'summary' => [
                'employees' => null,
                'projects' => null,
                'invoices' => null,
                'boqs' => null,
            ]
        ]);
    }
}
