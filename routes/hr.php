<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HR\EmployeeController;
use App\Http\Controllers\Api\HR\EmployeeDetailController;
use App\Http\Controllers\Api\HR\EmployeeProfile;
use App\Http\Controllers\Api\HR\EmployeeDocumentController;
Route::prefix('hr')->group(function () {
    Route::post('/', [EmployeeController::class, 'store'])->middleware('auth:sanctum');

    Route::put('/{id}', [EmployeeController::class, 'update'])->middleware('auth:sanctum');

    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->middleware('auth:sanctum');

    
    Route::get('/{id}', [EmployeeController::class, 'show'])->middleware('auth:sanctum');

    Route::get('/{id}/details', [EmployeeController::class, 'show_withdetails'])->middleware('auth:sanctum');
    
    Route::get('/{id}/profile', [EmployeeController::class, 'show_profile'])->middleware('auth:sanctum');

});

Route::prefix('hr/employee_details')->group(function () {
    Route::post('/',[EmployeeDetailController::class,'store'])->middleware('auth:sanctum');

    Route::get('/{employee_id}',[EmployeeDetailController::class,'show'])->middleware('auth:sanctum');

    Route::put('/{employee_id}',[EmployeeDetailController::class,'update'])->middleware('auth:sanctum');

    Route::delete('/{employee_id}',[EmployeeDetailController::class,'destroy'])->middleware('auth:sanctum');
});

Route::prefix('hr/employee_profiles')->group(function () {
    Route::post('/',[EmployeeProfile::class,'store'])->middleware('auth:sanctum');
    
    Route::put('/{employee_id}',[EmployeeProfile::class,'update'])->middleware('auth:sanctum');
    
    Route::delete('/{employee_id}',[EmployeeProfile::class,'destroy'])->middleware('auth:sanctum');
});



