<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TelegramConfigBotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/config', [\App\Http\Controllers\Api\ConfigController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/customer/login', [\App\Http\Controllers\Api\CustomerAuthController::class, 'login']);
Route::post('/telegram/config/webhook/{secret}', [TelegramConfigBotController::class, 'handle']);

// Protected routes (require Sanctum authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Auth (available to all authenticated users)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin APIs (require role:admin — strict DB role check, not Sanctum ability)
    Route::middleware('api.role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::apiResource('customers', CustomerController::class);
    });

    // Customer Portal APIs (require role:customer)
    Route::middleware('api.role:customer')->prefix('customer')->group(function () {
        // Invoices
        Route::get('/invoices', [\App\Http\Controllers\Api\CustomerInvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\CustomerInvoiceController::class, 'show']);
        Route::post('/invoices/{invoice}/payment-proof', [\App\Http\Controllers\Api\CustomerInvoiceController::class, 'submitPaymentProof']);
        Route::get('/payment-settings', [\App\Http\Controllers\Api\CustomerInvoiceController::class, 'paymentSettings']);

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Api\CustomerProfileController::class, 'index']);
        Route::put('/profile/password', [\App\Http\Controllers\Api\CustomerProfileController::class, 'updatePassword']);
        Route::put('/profile/contact', [\App\Http\Controllers\Api\CustomerProfileController::class, 'updateContact']);

    });
});
