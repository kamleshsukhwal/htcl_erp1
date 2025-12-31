<?php

use Illuminate\Support\Facades\Route;

Route::prefix('hr')->group(function () {
    Route::get('/test', function () {
        return 'HR tested';
    });
});
