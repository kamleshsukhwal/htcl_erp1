<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ForceAuthorizationHeader;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceAuthorizationHeader::class);
        $middleware->alias([
            'role'       => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);
        // Apply CheckPermission globally to all API routes (auto-derives from URL segment).
        $middleware->api(append: [CheckPermission::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
