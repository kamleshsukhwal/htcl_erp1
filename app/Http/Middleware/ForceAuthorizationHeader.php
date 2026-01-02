<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceAuthorizationHeader
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->header('Authorization')) {

            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
            }
            elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $request->headers->set('Authorization', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            }
            elseif (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                if (isset($headers['Authorization'])) {
                    $request->headers->set('Authorization', $headers['Authorization']);
                }
            }
        }

        return $next($request);
    }
}