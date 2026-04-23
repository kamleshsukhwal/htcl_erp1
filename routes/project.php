<?php

use App\Http\Controllers\Api\DcOutController;
use Illuminate\Support\Facades\Route;
// BOQ, STOCK, PO dashboard summary
use App\Http\Controllers\Api\DashboardController;

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
use App\Http\Controllers\Api\Project\ProjectAttachmentController;
// finance
use App\Http\Controllers\Api\Finance\InvoiceController;
use App\Http\Controllers\Api\Finance\PaymentController;
use App\Http\Controllers\Api\Finance\VendorPaymentController;

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

     /**************************** attachment ***********************************/
        Route::post('{project_id}/upload-file', [ProjectAttachmentController::class, 'upload']);
        Route::get('{project_id}/files', [ProjectAttachmentController::class, 'list']);

        Route::get('download-file/{id}', [ProjectAttachmentController::class, 'download']);

        Route::delete('delete-file/{id}', [ProjectAttachmentController::class, 'delete']);
    });

    /*
    |--------------------------------------------------------------------------
    | VENDOR MANAGEMENT
    |--------------------------------------------------------------------------
    */
    // Route::prefix('vendors')->middleware('permission:vendor.manage')->group(function () {
    Route::prefix('vendors')->group(function () {
        Route::post('/{id}/documents', [VendorController::class, 'uploadDocument']);
        Route::get('/{id}/documents', [VendorController::class, 'getVendorDocuments']);
        Route::delete('/document/{id}', [VendorController::class, 'deleteDocument']);
        Route::get('/document/download/{id}', [VendorController::class, 'downloadDocument']);
        Route::post('/', [VendorController::class, 'store']);   // Create vendor
        Route::get('/', [VendorController::class, 'index']);    // List vendors
        Route::get('/{id}', [VendorController::class, 'show']); // View vendor
        Route::put('/{id}', [VendorController::class, 'update']);
        Route::delete('/{id}', [VendorController::class, 'destroy']);
     Route::prefix('vendors-files')->group(function () {
        // View file (open in browser)
        Route::get('view/{id}', [VendorController::class, 'viewFile']);
        // Download file
        Route::get('download/{id}', [ VendorController::class, 'downloadFile']);

    });
    });

   

    /*
    |--------------------------------------------------------------------------
    | PURCHASE ORDER (BOQ BASED)
    |--------------------------------------------------------------------------
    */
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::post('/', [PurchaseOrderController::class, 'store']); // Create PO
        Route::get('/{id}', [PurchaseOrderController::class, 'show']);
        Route::get('/project/{project_id}', [PurchaseOrderController::class, 'byProject']);


/**** Once Purchase order create need to pay to vendor payment  */

Route::post('/vendor-payments', [VendorPaymentController::class, 'store']);
Route::post('/vendor-payments/{id}/upload', [VendorPaymentController::class, 'uploadAttachment']);
Route::get('/vendor-payments/download/{id}', [VendorPaymentController::class, 'download']);
Route::get('/vendor-payments/history/{poId}', [VendorPaymentController::class, 'history']);
    });



    /*
    |--------------------------------------------------------------------------
    | DC IN (SUPPLIED QTY)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dc-in')->group(function () {

        Route::post('/', [DcInController::class, 'store']); // DC entry
        Route::get('/', [DcInController::class, 'index']);
        Route::get('/{id}', [DcInController::class, 'show']);
Route::get('/list/dropdown', [DcInController::class, 'dropdownfordcout']); // used for DCOUt dropdown
        Route::get('/project/{project_id}', [DcInController::class, 'byProject']);
Route::get('/{id}/items-stock', [DcInController::class, 'getDcinItemsWithStock']);
        
    });
    /*
    |--------------------------------------------------------------------------
    | DC OUT (Received QTY)
    |--------------------------------------------------------------------------
    */



    Route::prefix('dc-outs')->group(function () {
        Route::post('/', [DcOutController::class, 'store']);
        Route::get('/', [DcOutController::class, 'index']);
        Route::get('/{id}', [DcOutController::class, 'show']);
        Route::get('/items/{id}', [DcOutController::class, 'items']);
    });


/*
 ----------------------------------------------------------------------------
 * After DC out Billing module started on 13-04-26   and payment            |                                                             
 ----------------------------------------------------------------------------
 */

Route::prefix('finance')->group(function () {

    Route::post('/invoices/from-dc', [InvoiceController::class,'createFromDc']);
    Route::get('/invoices', [InvoiceController::class,'index']);
    Route::get('/invoices/{id}', [InvoiceController::class,'show']);
    Route::post('/invoices', [InvoiceController::class,'store']);
    Route::get('/invoices/{id}/download', [InvoiceController::class,'download']);
    Route::post('/invoices/{id}/cancel', [InvoiceController::class,'cancel']);

    Route::post('/payments', [PaymentController::class,'store']);
    Route::get('/invoices/{id}/payments', [PaymentController::class,'list']);

  Route::post('/payments/{paymentId}/attachments', [PaymentController::class, 'paymentreceiptupload']);
Route::get('/payments/{id}/download', [PaymentController::class, 'downloadpaymentrecipt']);
    
});
 

 /*** Billing module route END */

    /*
    |--------------------------------------------------------------------------
    | INSTALLATION / WORK PROGRESS
    |--------------------------------------------------------------------------
    */
    Route::prefix('installations')->middleware('permission:execution.create')->group(function () {

        Route::post('/', [InstallationController::class, 'store']);
        Route::get('/project/{project_id}', [InstallationController::class, 'byProject']);
    });






    Route::prefix('dashboard')->group(function () {

        Route::get('/boq-summary/{project_id}', [DashboardController::class, 'boqSummary']);
        Route::get('/stock-summary/{project_id}', [DashboardController::class, 'stockSummary']);
        Route::get('/po-summary/{project_id}', [DashboardController::class, 'poSummary']);
    });
});
