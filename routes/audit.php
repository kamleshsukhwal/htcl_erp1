<?php

use App\Http\Controllers\Api\Quality\AuditLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Quality\NcrController;
use App\Http\Controllers\Api\Quality\QaChecklistController;
use App\Http\Controllers\Api\Quality\QaInspectionController;

Route::prefix('qa')->group(function () {

    Route::post('/inspection', [QaInspectionController::class, 'store']);
    Route::get('/inspection/{id}', [QaInspectionController::class, 'show']);
    Route::post('/checklist', [QaChecklistController::class, 'store']);
    Route::post('/inspection/{id}/submit', [QaInspectionController::class, 'submit']);
    Route::post('/inspection/{id}/approve', [QaInspectionController::class, 'approve']);
    Route::post('/inspection/{id}/reject', [QaInspectionController::class, 'reject']);
    Route::get('/inspection', [QaInspectionController::class, 'index']);
});

Route::prefix('ncr')->group(function () {
    Route::post('/', [NcrController::class, 'store']);
    Route::get('/project/{project_id}', [NcrController::class, 'byProject']);
    Route::patch('/{id}/assign', [NcrController::class, 'assign']);
    Route::patch('/{id}/in-progress', [NcrController::class, 'markInProgress']);
    Route::patch('/{id}/corrected', [NcrController::class, 'markCorrected']);
    Route::patch('/{id}/close', [NcrController::class, 'close']);
    Route::get('/', [NcrController::class, 'index']);
    Route::get('/{id}', [NcrController::class, 'show']);
});

Route::prefix('audit')->group(function () {
    Route::get('/module/{module}', [AuditLogController::class, 'byModule']);
});
