<?php

use Illuminate\Support\Facades\Route;

Route::prefix('project')->group(function () {
    Route::get('/project', function () {
        return 'project tested';
    });
});
