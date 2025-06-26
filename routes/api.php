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
        Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);
        Route::post('register', [App\Http\Controllers\Api\AuthController::class, 'register']);
        Route::post('check-email', [App\Http\Controllers\Api\AuthController::class, 'checkEmail']);
    });
    
    // Protected routes - memerlukan authentication
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth routes yang memerlukan authentication
        Route::prefix('auth')->group(function () {
            Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
            Route::post('logout-all', [App\Http\Controllers\Api\AuthController::class, 'logoutAll']);
            Route::get('profile', [App\Http\Controllers\Api\AuthController::class, 'profile']);
            Route::put('update-password', [App\Http\Controllers\Api\AuthController::class, 'updatePassword']);
        });
        
        // User profile routes
        Route::prefix('profile')->group(function () {
            Route::put('update', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
            Route::post('photo', [App\Http\Controllers\Api\UserController::class, 'updatePhoto']);
            Route::delete('photo', [App\Http\Controllers\Api\UserController::class, 'deletePhoto']);
            Route::get('points-history', [App\Http\Controllers\Api\UserController::class, 'getPointsHistory']);
        });
        
        // Transaction routes
        Route::prefix('transactions')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\TransactionController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\TransactionController::class, 'store']);
            Route::get('summary', [App\Http\Controllers\Api\TransactionController::class, 'summary']);
            Route::get('{id}', [App\Http\Controllers\Api\TransactionController::class, 'show']);
            Route::put('{id}', [App\Http\Controllers\Api\TransactionController::class, 'update']);
            Route::delete('{id}', [App\Http\Controllers\Api\TransactionController::class, 'destroy']);
        });
        
        // Price routes
        Route::prefix('prices')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\PriceController::class, 'index']);
            Route::get('jenis-list', [App\Http\Controllers\Api\PriceController::class, 'getJenisList']);
            Route::post('/', [App\Http\Controllers\Api\PriceController::class, 'store']);
            Route::get('{id}', [App\Http\Controllers\Api\PriceController::class, 'show']);
            Route::put('{id}', [App\Http\Controllers\Api\PriceController::class, 'update']);
            Route::delete('{id}', [App\Http\Controllers\Api\PriceController::class, 'destroy']);
        });
        
        // Customer routes (for admin/karyawan)
        Route::prefix('customers')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\UserController::class, 'getCustomers']);
            Route::post('/', [App\Http\Controllers\Api\UserController::class, 'createCustomer']);
            Route::get('{id}', [App\Http\Controllers\Api\UserController::class, 'getCustomerDetail']);
            Route::put('{id}/status', [App\Http\Controllers\Api\UserController::class, 'updateCustomerStatus']);
        });
        
    });
    
});