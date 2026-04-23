<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ModuleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\Inventory\StockController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;


/*
|--------------------------------------------------------------------------
| Utility Routes
|--------------------------------------------------------------------------
*/

Route::get('/clear-all', function () {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return "Cleared!";
});

Route::get('/test-mail', function () {
    Mail::raw('Test mail from HTCL ERP', function ($message) {
        $message->to('kamlesh@htcl.co.in')
            ->subject('SMTP Test');
    });

    return 'Mail Sent!';
});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    // ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        Route::get('/modules', [ModuleController::class, 'index']);
        Route::put('/modules/{id}', [ModuleController::class, 'update']);
        Route::post('/modules', [ModuleController::class, 'store']);

        Route::apiResource('/roles', RoleController::class);
        Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);
        Route::get('/roles-moodules', [RoleController::class, 'rolesAndModules']);
    });


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])->name('api.login');


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('clients', ClientController::class);

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:user.view');
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{id}/roles', [UserController::class, 'assignRole']);
Route::get('/check-session', [AuthController::class, 'checkSession'])
    ->middleware('auth:sanctum');
    /*
    |--------------------------------------------------------------------------
    | Rating APIs
    |--------------------------------------------------------------------------
    */

    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings', [RatingController::class, 'index']);
    Route::get('/ratings/employee/{id}', [RatingController::class, 'getByEmployee']);
    Route::get('/ratings/average', [RatingController::class, 'average']);

    /*
    |--------------------------------------------------------------------------
    | Feedback APIs
    |--------------------------------------------------------------------------
    */

    Route::post('/email/send-with-attachment', [EmailController::class, 'sendWithAttachment']);

    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::get('/feedback/filter', [FeedbackController::class, 'filterByDate']);

Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
 /*
    |--------------------------------------------------------------------------
    | Module Stock add update while DC IN DC-OUT
    |--------------------------------------------------------------------------
    */

    Route::prefix('inventory')->group(function () {
Route::get('/stock', [StockController::class, 'index']);
Route::get('/stock/{boq_item_id}', [StockController::class, 'show']);
Route::get('/stock-ledger/{boq_item_id}', [StockController::class, 'ledger']);
Route::get('/low-stock', [StockController::class, 'lowStock']);   
    });
/*
    |--------------------------------------------------------------------------
    | Module Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__.'/hr.php';
    require __DIR__.'/finance.php';
    require __DIR__.'/project.php';
    require __DIR__.'/boq.php';
    require __DIR__.'/audit.php';
});


/*
|--------------------------------------------------------------------------
| Permission Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('/permissions', PermissionController::class)
    ->only(['index', 'store']);