<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HR\EmployeeController;
use App\Http\Controllers\Api\HR\EmployeeDetailController;
use App\Http\Controllers\Api\HR\EmployeeProfile;
// use App\Http\Controllers\Api\HR\EmployeeDocumentController;
use App\Http\Controllers\Api\HR\EmployeeDocument;
use App\Http\Controllers\Api\HR\AttendenceController;
use App\Http\Controllers\Api\HR\LeaveTypeController;

// use App\Models\Employee_document;

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

Route::prefix('hr/employee_documents')->group(function () {
    Route::post('/{employee_id}',[EmployeeDocument::class,'store'])->middleware('auth:sanctum');
    
    Route::get('/{employee_id}',[EmployeeDocument::class,'show'])->middleware('auth:sanctum');
    
    Route::put('/{employee_id}',[EmployeeDocument::class,'update'])->middleware('auth:sanctum');
    Route::delete('/{employee_id}',[EmployeeDocument::class,'destroy'])->middleware('auth:sanctum');
});

Route::prefix('hr/Attendence/')->group(function(){
    Route::post('/check_in/{employee_id}',[AttendenceController::class,'check_in'])->middleware('auth:sanctum');

    Route::post('/check_out/{employee_id}',[AttendenceController::class,'check_out'])->middleware('auth:sanctum');

    Route::get('/present',[AttendenceController::class,'index'])->middleware('auth:sanctum');

});
 Route::prefix('hr/leave_type')->group(function(){
    Route::post('/',[LeaveTypeController::class,'store'])->middleware('auth:sanctum');
    Route::put('/{id}',[LeaveTypeController::class,'update'])->middleware('auth:sanctum');
    Route::delete('/{id}',[LeaveTypeController::class,'destroy'])->middleware('auth:sanctum');
    Route::get('/{id}',[LeaveTypeController::class,'show'])->middleware('auth:sanctum');
 });