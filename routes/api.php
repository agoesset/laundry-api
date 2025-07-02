<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    
    // Admin routes (restricted to admin role)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // Employee management
        Route::get('/employees', [AdminController::class, 'employees']);
        Route::post('/employees', [AdminController::class, 'storeEmployee']);
        Route::get('/employees/{employee}', [AdminController::class, 'showEmployee']);
        Route::put('/employees/{employee}', [AdminController::class, 'updateEmployee']);
        Route::delete('/employees/{employee}', [AdminController::class, 'destroyEmployee']);
        
        // Price management
        Route::get('/prices', [AdminController::class, 'prices']);
        Route::post('/prices', [AdminController::class, 'storePrice']);
        Route::get('/prices/{price}', [AdminController::class, 'showPrice']);
        Route::put('/prices/{price}', [AdminController::class, 'updatePrice']);
        Route::delete('/prices/{price}', [AdminController::class, 'destroyPrice']);
    });
    
    // Employee routes (accessible by employees and admins)
    Route::middleware(['role:employee,admin'])->prefix('employee')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'dashboard']);
        
        // Customer management
        Route::get('/customers', [EmployeeController::class, 'customers']);
        Route::post('/customers', [EmployeeController::class, 'storeCustomer']);
        Route::get('/customers/search', [EmployeeController::class, 'searchCustomers']);
        Route::get('/customers/{customer}', [EmployeeController::class, 'showCustomer']);
        Route::put('/customers/{customer}', [EmployeeController::class, 'updateCustomer']);
        Route::delete('/customers/{customer}', [EmployeeController::class, 'destroyCustomer']);
        
        // Order management
        Route::get('/orders', [EmployeeController::class, 'orders']);
        Route::post('/orders', [EmployeeController::class, 'storeOrder']);
        Route::get('/orders/{order}', [EmployeeController::class, 'showOrder']);
        Route::put('/orders/{order}/status', [EmployeeController::class, 'updateOrderStatus']);
        
        // Helper routes
        Route::get('/prices', [EmployeeController::class, 'getActivePrices']);
    });
    
    // Legacy route for testing
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

