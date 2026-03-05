<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Quality\AuditLogController;
use App\Http\Controllers\Api\Quality\NcrController;
use App\Http\Controllers\Api\Quality\QaChecklistController;
use App\Http\Controllers\Api\Quality\QaInspectionController;
use App\Http\Controllers\Api\Quality\QaInspectionController as QualityQaInspectionController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| QA MANAGEMENT MODULE (Professional Structure)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | QA MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('qa')->group(function () {

        /*
        |----------------------------------
        | INSPECTIONS
        |----------------------------------
        */
        Route::apiResource('inspections', QaInspectionController::class);

        // Inspection Lifecycle
        Route::post('inspections/{inspection}/submit', [QaInspectionController::class, 'submit']);
        Route::post('inspections/{inspection}/approve', [QaInspectionController::class, 'approve']);
        Route::post('inspections/{inspection}/reject', [QaInspectionController::class, 'reject']);

        // Inspection Items (Results)
        Route::post('inspections/{inspection}/items', [QaInspectionController::class, 'addResult']);
        Route::put('inspection-items/{item}', [QaInspectionController::class, 'updateResult']);
        Route::get('inspections/{inspection}/items', [QaInspectionController::class, 'items']);
        // Inspection Attachment
        Route::post('inspections/{inspection}/upload', [QaInspectionController::class, 'upload']);



        /*
        |----------------------------------
        | CHECKLISTS
        |----------------------------------
        */

    Route::apiResource('checklists', QaChecklistController::class);

        // Checklist Items
        
        Route::post('checklists/{checklist}/items', [QaChecklistController::class, 'addItem']);
        Route::put('checklist-items/{item}', [QaChecklistController::class, 'updateItem']);
        Route::delete('checklist-items/{item}', [QaChecklistController::class, 'deleteItem']);
    });

    /*
    |--------------------------------------------------------------------------
    | NCR MODULE
    |--------------------------------------------------------------------------
    */

    Route::prefix('ncr')->group(function () {

        Route::apiResource('/', NcrController::class)->parameters([
            '' => 'ncr'
        ]);

        // NCR Lifecycle
        
        Route::patch('{ncr}/assign', [NcrController::class, 'assign']);
        Route::patch('{ncr}/in-progress', [NcrController::class, 'markInProgress']);
        Route::patch('{ncr}/corrected', [NcrController::class, 'markCorrected']);
        Route::patch('{ncr}/close', [NcrController::class, 'close']);

        // NCR Upload
        Route::post('{ncr}/upload', [NcrController::class, 'upload']);
        // Filter by project
        Route::get('project/{project}', [NcrController::class, 'byProject']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUDIT MODULE
    |--------------------------------------------------------------------------
    */

    Route::prefix('audit')->group(function () {

        Route::get('module/{module}', [AuditLogController::class, 'byModule']);
        Route::get('project/{project}', [AuditLogController::class, 'byProject']);
        Route::get('user/{user}', [AuditLogController::class, 'byUser']);
        
    });
    Route::get('/users', [UserController::class,'index']);

});