<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Aktifkan API routing
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware untuk API authentication dengan Sanctum
        $middleware->api([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom API error handling untuk konsistensi response
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Hanya handle request ke API routes
            if ($request->is('api/*')) {
                // Handle ValidationException dengan format yang konsisten
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data yang dikirim tidak valid',
                        'errors' => $e->errors(),
                    ], 422);
                }
                
                // Handle ModelNotFoundException (404)
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data tidak ditemukan',
                    ], 404);
                }
                
                // Handle AuthenticationException (401)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Token tidak valid atau sudah expired',
                    ], 401);
                }
                
                // Handle AuthorizationException (403)
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk melakukan aksi ini',
                    ], 403);
                }
                
                // Handle QueryException (Database errors)
                if ($e instanceof \Illuminate\Database\QueryException) {
                    // Jangan expose database error di production
                    $message = app()->environment('production') 
                        ? 'Terjadi kesalahan pada sistem' 
                        : 'Database error: ' . $e->getMessage();
                        
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 500);
                }
                
                // Handle MethodNotAllowedHttpException (405)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'HTTP method tidak diizinkan untuk endpoint ini',
                    ], 405);
                }
                
                // Handle NotFoundHttpException (404)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Endpoint tidak ditemukan',
                    ], 404);
                }
                
                // Handle TooManyRequestsHttpException (429)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak request. Silakan coba lagi nanti.',
                    ], 429);
                }
                
                // Handle general HttpException
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Terjadi kesalahan HTTP',
                    ], $e->getStatusCode());
                }
                
                // Handle PDOException (Database connection errors)
                if ($e instanceof \PDOException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat terhubung ke database',
                    ], 500);
                }
                
                // Handle generic Exception (500)
                if (!app()->environment('production')) {
                    // Development: show detailed error
                    return response()->json([
                        'success' => false,
                        'message' => 'Server error: ' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ], 500);
                } else {
                    // Production: hide sensitive details
                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan pada server',
                    ], 500);
                }
            }
            
            // Return null untuk non-API requests (akan di-handle oleh default Laravel)
            return null;
        });
    })->create();
