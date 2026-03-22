<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Map HTTP methods to permission action suffixes.
     */
    private array $methodMap = [
        'GET'    => 'view',
        'POST'   => 'create',
        'PUT'    => 'update',
        'PATCH'  => 'update',
        'DELETE' => 'delete',
    ];

    /**
     * Map URL segment (route prefix) → permission module name.
     *
     * URL segment      Permission module
     * ─────────────    ─────────────────
     * projects      →  project
     * vendors       →  vendor
     * purchase-orders → purchase-orders
     * dc-in         →  dc-in
     * dc-outs       →  dc-outs
     * installations →  execution
     * boq           →  boq
     * hr            →  hr
     * clients       →  clients
     * users         →  user
     * ratings       →  ratings
     * feedback      →  feedback
     * qa            →  qa
     * ncr           →  ncr
     * audit         →  audit
     * finance       →  finance
     * dashboard     →  dashboard
     */
    private array $prefixMap = [
        'projects'       => 'project',
        'vendors'        => 'vendor',
        'purchase-orders'=> 'purchase-orders',
        'dc-in'          => 'dc-in',
        'dc-outs'        => 'dc-outs',
        'installations'  => 'execution',
        'boq'            => 'boq',
        'hr'             => 'hr',
        'clients'        => 'clients',
        'users'          => 'user',
        'ratings'        => 'ratings',
        'feedback'       => 'feedback',
        'qa'             => 'qa',
        'ncr'            => 'ncr',
        'audit'          => 'audit',
        'finance'        => 'finance',
        'dashboard'      => 'dashboard',
    ];

    /**
     * Handle an incoming request.
     *
     * Explicit:  middleware('permission:project.view')  → checks that exact permission.
     * Auto-derive: no argument → resolves module from $prefixMap + action from HTTP method.
     *   GET  /api/projects/1  → project.view
     *   POST /api/projects    → project.create
     *   PUT  /api/projects/1  → project.update
     *   DELETE /api/projects/1 → project.delete
     */
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // If permission name(s) supplied explicitly, check those.
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if ($user->hasPermissionTo($permission)) {
                    return $next($request);
                }
            }
            return response()->json(['message' => 'Forbidden: permission denied.'], 403);
        }

        // Auto-derive permission from route prefix map + HTTP method.
        $segment = $request->segment(2); // e.g. "projects" from /api/projects/1
        $module  = $this->prefixMap[$segment] ?? $segment;
        $action  = $this->methodMap[strtoupper($request->method())] ?? 'view';

        if (!$module) {
            return $next($request);
        }

        $derived = $module . '.' . $action; // e.g. "project.view"

        if (!$user->hasPermissionTo($derived)) {
            return response()->json([
                'message'    => 'Forbidden: permission denied.',
                'permission' => $derived,
            ], 403);
        }

        return $next($request);
    }
}
