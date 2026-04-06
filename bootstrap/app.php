<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ForceAuthorizationHeader;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

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

        // ── 401 Unauthenticated ──────────────────────────────────────────────
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Please login again.',
                ], 401);
            }
        });

        // ── 403 Unauthorized ────────────────────────────────────────────────
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Forbidden. You do not have permission to perform this action.',
                ], 403);
            }
        });

        // ── 422 Validation Error ─────────────────────────────────────────────
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // ── 404 Model Not Found (findOrFail) ─────────────────────────────────
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $model = class_basename($e->getModel());
                return response()->json([
                    'status'  => false,
                    'message' => "{$model} not found.",
                ], 404);
            }
        });

        // ── 404 Route Not Found ──────────────────────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'The requested endpoint does not exist.',
                ], 404);
            }
        });

        // ── 405 Method Not Allowed ───────────────────────────────────────────
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'HTTP method not allowed for this endpoint.',
                ], 405);
            }
        });

        // ── Other HTTP Exceptions (500, 503, etc.) ───────────────────────────
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                ], $e->getStatusCode());
            }
        });

        // ── 500 Unexpected / Server Error ────────────────────────────────────
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Something went wrong. Please try again later.',
                ], 500);
            }
        });

    })->create();
