<?php

use Illuminate\Support\Facades\Route;

// Project
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\Project\ProjectDashboardController;

// Vendor
use App\Http\Controllers\Api\Vendor\VendorController;

// Purchase
use App\Http\Controllers\Api\Purchase\PurchaseOrderController;

// Inventory
use App\Http\Controllers\Api\Inventory\DcInController;

// Execution
use App\Http\Controllers\Api\Execution\InstallationController;

/*
|--------------------------------------------------------------------------
| PROJECT MODULE ROUTES
|--------------------------------------------------------------------------
| Project → Vendor → Purchase → DC IN → Installation
*/

Route::middleware(['auth:sanctum'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PROJECT
    |--------------------------------------------------------------------------
    */
    Route::prefix('projects')->group(function () {

        Route::get('/', [ProjectController::class, 'index'])
            ->middleware('permission:project.view');

        Route::get('/{id}', [ProjectController::class, 'show'])
            ->middleware('permission:project.view');

        Route::post('/', [ProjectController::class, 'store'])
            ->middleware('permission:project.create');

        Route::put('/{id}', [ProjectController::class, 'update'])
            ->middleware('permission:project.update');

        Route::delete('/{id}', [ProjectController::class, 'destroy'])
            ->middleware('permission:project.delete');

        // 📊 Dashboard
        Route::get('/{id}/progress', [ProjectDashboardController::class, 'projectProgress'])
            ->middleware('permission:project.view');

        Route::get('/{id}/boq-graph', [ProjectDashboardController::class, 'boqWiseValue'])
            ->middleware('permission:project.view');
    });

    /*
    |--------------------------------------------------------------------------
    | VENDOR MANAGEMENT
    |--------------------------------------------------------------------------
    */
   // Route::prefix('vendors')->middleware('permission:vendor.manage')->group(function () {
Route::prefix('vendors')->group(function () {

        Route::post('/', [VendorController::class, 'store']);   // Create vendor
        Route::get('/', [VendorController::class, 'index']);    // List vendors
        Route::get('/{id}', [VendorController::class, 'show']); // View vendor
        Route::put('/{id}', [VendorController::class, 'update']);
          Route::delete('/{id}', [VendorController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PURCHASE ORDER (BOQ BASED)
    |--------------------------------------------------------------------------
    */
    Route::prefix('purchase-orders')->group(function () {

        Route::post('/', [PurchaseOrderController::class, 'store']); // Create PO
        Route::get('/{id}', [PurchaseOrderController::class, 'show']);
        Route::get('/project/{project_id}', [PurchaseOrderController::class, 'byProject']);
    });

    /*
    |--------------------------------------------------------------------------
    | DC IN (SUPPLIED QTY)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dc-in')->middleware('permission:inventory.create')->group(function () {

        Route::post('/', [DcInController::class, 'store']); // DC entry
        Route::get('/{id}', [DcInController::class, 'show']);
        Route::get('/project/{project_id}', [DcInController::class, 'byProject']);
    });

    /*
    |--------------------------------------------------------------------------
    | INSTALLATION / WORK PROGRESS
    |--------------------------------------------------------------------------
    */
    Route::prefix('installations')->middleware('permission:execution.create')->group(function () {

        Route::post('/', [InstallationController::class, 'store']);
        Route::get('/project/{project_id}', [InstallationController::class, 'byProject']);
    });

});
