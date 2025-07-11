<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check if user has required role
        if ($request->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required role: ' . $role
            ], 403);
        }

        // Check if user is active
        if (!$request->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.'
            ], 403);
        }

        return $next($request);
    }
}
