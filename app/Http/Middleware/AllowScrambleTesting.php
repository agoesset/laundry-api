<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowScrambleTesting
{
    public function handle(Request $request, Closure $next)
    {
        // Only for local/testing environment
        if (app()->environment('local') && $request->header('X-Scramble-Testing')) {
            // Auto login as admin for testing
            auth()->loginUsingId(1); // Admin user ID
        }
        
        return $next($request);
    }
}