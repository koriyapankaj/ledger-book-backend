<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Account routes
    Route::apiResource('accounts', AccountController::class);
    Route::get('/accounts-summary', [AccountController::class, 'summary']);

    // Category routes
    Route::apiResource('categories', CategoryController::class);

    // Contact routes
    Route::apiResource('contacts', ContactController::class);
    Route::get('/contacts-summary', [ContactController::class, 'summary']);

    // Transaction routes
    Route::apiResource('transactions', TransactionController::class);
    Route::get('/transactions-statistics', [TransactionController::class, 'statistics']);
    Route::get('/transactions-spending-by-category', [TransactionController::class, 'spendingByCategory']);
});