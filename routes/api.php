<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SwaggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Books routes
Route::get('books', [BookController::class, 'index']);
Route::get('books/{id}', [BookController::class, 'show']);

// Search routes
Route::get('search', [SearchController::class, 'search']);
Route::get('search/suggestions', [SearchController::class, 'suggestions']);

// Analytics routes (admin only)
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('analytics', [AnalyticsController::class, 'index']);
});

// Protected book recommendations and summary routes
Route::middleware('auth:api')->group(function () {
    Route::get('books/{id}/recommendations', [BookController::class, 'recommendations']);
    Route::get('books/{id}/summary', [BookController::class, 'summary']);
});

// Protected admin routes
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{id}', [BookController::class, 'update']);
    Route::delete('books/{id}', [BookController::class, 'destroy']);
});

// Orders routes (authenticated users only)
Route::middleware('auth:api')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
});

// API Documentation
Route::get('docs', [SwaggerController::class, 'index']);

// Admin Routes (Protected by JWT and admin policies)
Route::middleware('auth:api')->prefix('admin')->group(function () {
    // Admin Books Management
    Route::apiResource('books', \App\Http\Controllers\Api\Admin\BookController::class);

    // Admin Orders Management
    Route::apiResource('orders', \App\Http\Controllers\Api\Admin\OrderController::class)->except(['store', 'destroy']);
    Route::get('orders/stats/summary', [\App\Http\Controllers\Api\Admin\OrderController::class, 'stats']);

    // Admin Users Management
    Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
    Route::get('users/stats/summary', [\App\Http\Controllers\Api\Admin\UserController::class, 'stats']);
});
