<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API Routes untuk Laundry Application
 * 
 * Routes ini akan di-prefix dengan '/api' dan memiliki throttling
 * untuk membatasi request per menit dari setiap user.
 */

// Route untuk mendapatkan informasi user yang sedang login
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Route Group untuk API versi 1
 * Semua route API akan menggunakan prefix '/api/v1'
 */
Route::prefix('v1')->group(function () {
    
    // Public routes - tidak memerlukan authentication
    Route::prefix('auth')->group(function () {
        // Route akan ditambahkan pada step selanjutnya
    });
    
    // Protected routes - memerlukan authentication
    Route::middleware('auth:sanctum')->group(function () {
        
        // User profile routes
        Route::prefix('profile')->group(function () {
            // Route akan ditambahkan pada step selanjutnya
        });
        
        // Transaction routes
        Route::prefix('transactions')->group(function () {
            // Route akan ditambahkan pada step selanjutnya
        });
        
        // Price routes (admin only)
        Route::prefix('prices')->group(function () {
            // Route akan ditambahkan pada step selanjutnya
        });
        
        // Customer routes (admin only)
        Route::prefix('customers')->group(function () {
            // Route akan ditambahkan pada step selanjutnya
        });
        
    });
    
});