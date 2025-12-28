<?php

use Illuminate\Support\Facades\Route;

Route::prefix('hr')->group(function () {
    Route::get('/hr', function () {
        return 'HR tested';
    });
});
