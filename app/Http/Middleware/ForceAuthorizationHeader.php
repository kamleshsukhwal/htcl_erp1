<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceAuthorizationHeader
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->header('Authorization') && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
        }

        return $next($request);
    }
}
