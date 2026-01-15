<?php

use App\Http\Controllers\Api\Project\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('projects')
    ->middleware(['auth:sanctum', 'permission:project.create'])
    ->group(function () {



    
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/{id}', [ProjectController::class, 'show']);

        Route::post('/', [ProjectController::class, 'store'])
            ->middleware('permission:project.create');

        Route::put('/{id}', [ProjectController::class, 'update'])
            ->middleware('permission:project.update');

        Route::delete('/{id}', [ProjectController::class, 'destroy'])
            ->middleware('permission:project.delete');
    });