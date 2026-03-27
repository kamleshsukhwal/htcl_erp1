<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HR\EmployeeController;
use App\Http\Controllers\Api\HR\EmployeeDetailController;
use App\Http\Controllers\Api\HR\EmployeeProfileController;
// use App\Http\Controllers\Api\HR\EmployeeDocumentControllerController;
use App\Http\Controllers\Api\HR\EmployeeDocumentController;
use App\Http\Controllers\Api\HR\AttendenceController;
use App\Http\Controllers\Api\HR\LeaveTypeController;
use App\Http\Controllers\Api\HR\LeaveApplicationController;
// use App\Models\Employee_document;

Route::prefix('hr')->group(function () {
    Route::post('/', [EmployeeController::class, 'store'])->middleware('auth:sanctum');

    Route::put('/{id}', [EmployeeController::class, 'update'])->middleware('auth:sanctum');

    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->middleware('auth:sanctum');

    
    Route::get('/{id}', [EmployeeController::class, 'show'])->middleware('auth:sanctum');

    Route::get('/details/{id}', [EmployeeController::class, 'show_withdetails'])->middleware('auth:sanctum');
    
    Route::get('/profile/{id}', [EmployeeController::class, 'show_profile'])->middleware('auth:sanctum');

});

Route::prefix('hr/employee_details')->group(function () {
    Route::post('/',[EmployeeDetailController::class,'store'])->middleware('auth:sanctum');

    Route::get('/{employee_id}',[EmployeeDetailController::class,'show'])->middleware('auth:sanctum');

    Route::put('/{employee_id}',[EmployeeDetailController::class,'update'])->middleware('auth:sanctum');

    Route::delete('/{employee_id}',[EmployeeDetailController::class,'destroy'])->middleware('auth:sanctum');
});

Route::prefix('hr/employee_profiles')->group(function () {
    Route::post('/',[EmployeeProfileController::class,'store'])->middleware('auth:sanctum');
    
    Route::put('/{employee_id}',[EmployeeProfileController::class,'update'])->middleware('auth:sanctum');
    
    Route::delete('/{employee_id}',[EmployeeProfileController::class,'destroy'])->middleware('auth:sanctum');
});

Route::prefix('hr/employee_documents')->group(function () {
    Route::post('/{employee_id}',[EmployeeDocumentController::class,'store'])->middleware('auth:sanctum');
    
    Route::get('/{employee_id}',[EmployeeDocumentController::class,'show'])->middleware('auth:sanctum');
    
    Route::put('/{employee_id}',[EmployeeDocumentController::class,'update'])->middleware('auth:sanctum');
    Route::delete('/{employee_id}',[EmployeeDocumentController::class,'destroy'])->middleware('auth:sanctum');
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

Route::prefix('hr/leave_application')->group(function(){
    Route::post('/',[LeaveApplicationController::class,'store'])->middleware('auth:sanctum');
    
    Route::get('/employee_detail/{employee_id}',[LeaveApplicationController::class,'mearge_employee_leave_tables'])->middleware('auth:sanctum');
    
    Route::get('/leave_type/{leave_type_id}',[LeaveApplicationController::class,'show_leave_type'])->middleware('auth:sanctum');
    
    Route::put('/accept_or_reject/{leave_id}',[LeaveApplicationController::class,'Accpect_Reject_application'])->middleware('auth:sanctum');
   
    Route::get('/pending_applications/',[LeaveApplicationController::class,'show_pending_applications'])->middleware('auth:sanctum');

    });