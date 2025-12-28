<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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
