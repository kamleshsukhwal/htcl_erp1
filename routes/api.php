<?php

<<<<<<< Updated upstream
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
=======
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ModuleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use Illuminate\Support\Facades\Route;




Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        Route::get('/modules', [ModuleController::class, 'index']);
        Route::put('/modules/{id}', [ModuleController::class, 'update']);
        Route::apiResource('/roles', RoleController::class);
        Route::apiResource('/permissions', PermissionController::class)->only(['index', 'store']);
    Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);

    Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/users/{id}/roles', [UserController::class, 'assignRole']);
});

    Route::get('/me/access', [AccessController::class, 'access'])
    ->middleware('auth:sanctum');


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:user.view');

    // MODULE ROUTES
    require __DIR__.'/hr.php';
    require __DIR__.'/finance.php';
    require __DIR__.'/project.php';
    require __DIR__.'/boq.php';
});