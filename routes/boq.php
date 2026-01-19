<?php

use App\Http\Controllers\Api\Boq\BoqController;
use App\Http\Controllers\Api\Boq\BoqItemProgressController;
use App\Http\Controllers\Api\Boq\BoqRevisionController;
 use App\Http\Controllers\BoqImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('boqs')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::post('/', [BoqController::class, 'store']);
        Route::get('/project/{projectId}', [BoqController::class, 'listByProject']);
        Route::post('/{boqId}/items', [BoqController::class, 'addItems']);
        Route::put('/items/{itemId}', [BoqController::class, 'updateItem']);
        Route::post('/{boqId}/upload', [BoqController::class, 'uploadFile']);
        Route::patch('/{boqId}/status', [BoqController::class, 'updateStatus']);
        Route::post('boqs/{id}/revise', [BoqItemProgressController::class, 'revise']);
        Route::post('boq-item-progress', [BoqRevisionController::class, 'store']);
       Route::get('/{boqId}/items', [BoqController::class, 'itemsByBoq']);
       Route::put('/{boqId}/items/bulk-update', [BoqController::class, 'bulkUpdateItems']);
//Route::post('/{boqId}/import-items', [BoqController::class, 'importItemsFromExcel']);
            Route::get('/{boqId}', [BoqController::class, 'getBoqById']);
Route::post('/{bodId}/import', [BoqController::class, 'importBoq']);
});


