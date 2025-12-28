<?php

use Illuminate\Support\Facades\Route;

Route::prefix('boq')->group(function () {
    Route::get('/boq', function () {
        return 'boq tested';
    });
});
