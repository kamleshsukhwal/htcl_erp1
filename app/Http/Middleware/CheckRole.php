<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole($roles)) {
            return response()->json(['message' => 'Forbidden: insufficient role.'], 403);
        }

        return $next($request);
    }
}
