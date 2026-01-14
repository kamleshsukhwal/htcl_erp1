<?php

use App\Http\Controllers\Api\Boq\BoqController;
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
    });
