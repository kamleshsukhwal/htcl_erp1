<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Boq\BoqController;
use App\Http\Controllers\Api\Boq\BoqItemController;
use App\Http\Controllers\Api\Boq\BoqRevisionController;
use App\Http\Controllers\Api\Boq\BoqItemProgressController;
use App\Models\BoqItemHistory;

/*
|--------------------------------------------------------------------------
| BOQ Routes
|--------------------------------------------------------------------------
| Handles BOQ planning, revisions and quantity tracking
*/

Route::prefix('boq')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | BOQ Master
        |--------------------------------------------------------------------------
        */

        Route::get('/', [BoqController::class, 'index'])
            ->middleware('permission:boq.view');

        Route::post('/', [BoqController::class, 'store'])
            ->middleware('permission:boq.create');
  Route::get('/item-progress',[BoqItemProgressController::class, 'show']); // get data
  //Route::get('/item-progress',[BoqItemProgressController::class, 'show']); // get data
  
        Route::get('/{id}', [BoqController::class, 'show'])
            ->middleware('permission:boq.view');
            
        Route::get('/project/{project_id}', [BoqController::class, 'listByProject'])
            ->middleware('permission:boq.view');

        Route::put('/{id}', [BoqController::class, 'update'])
            ->middleware('permission:boq.update');

        Route::delete('/{id}', [BoqController::class, 'destroy'])
            ->middleware('permission:boq.delete');
            
// Below route addd for storing items
Route::post('/{boqId}/items', [BoqItemController::class, 'store']);


Route::get('/{boqId}/items', [BoqController::class, 'getItemsByBoq']);

Route::get('/{id}/items', [BoqController::class,'getBoqItems']);

/***** BOQ file upload */
Route::post('/{boqId}/upload', [BoqController::class, 'uploadFile']);
        /*
        |--------------------------------------------------------------------------
        | BOQ Revisions
        |--------------------------------------------------------------------------
        */
        Route::get('/{boq_id}/revisions', [BoqRevisionController::class, 'index'])
            ->middleware('permission:boq.view');

        Route::post('/{boq_id}/revisions', [BoqRevisionController::class, 'store'])
            ->middleware('permission:boq.revise');

        /*
        |--------------------------------------------------------------------------
        | BOQ Progress (AUTO from DC + Installation)
        |--------------------------------------------------------------------------
        */
        Route::get('/{boq_id}/progress', [BoqItemProgressController::class, 'show'])
            ->middleware('permission:boq.view');

        /*
        |--------------------------------------------------------------------------
        | BOQ Quantity Status (ERP dashboard usage)
        |--------------------------------------------------------------------------
        */
        Route::get('/{boq_id}/status', [BoqController::class, 'status'])
            ->middleware('permission:boq.view');

 

Route::get('/items/{id}/history', function ($id) {
    return BoqItemHistory::where('boq_item_id', $id)
        ->latest()
        ->get();
});

 
Route::put(
    '/{boqId}/items/bulk-update',
    [BoqItemController::class, 'bulkUpdateItems']
)->middleware('auth:sanctum');

Route::get(
    '/items/history/by-date',
    [BoqItemController::class, 'historyByDate']
);

  Route::post('/item-progress',[BoqItemProgressController::class, 'store']); //store and get data

Route::post('/items/{itemId}/uploadFile', [BoqItemController::class, 'uploadItemFile']);
  
  Route::get('/items/{itemId}/files', [BoqItemController::class, 'getItemFiles']);
  
    });