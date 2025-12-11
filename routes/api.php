<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoryController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// CSRF cookie endpoint (frontend calls this first)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf' => csrf_token()]);
});

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Profile
    Route::post('/profile/update', [ProfileController::class, 'update']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Products (all users can view)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/products/reorder', [ProductController::class, 'reorderList']);

    // Deduct stock (all users)
    Route::post('/products/{product}/deduct', [ProductController::class, 'deductStock']);

    // Stock movements (all users can view)
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::get('/stock-movements/{product_id}', [StockMovementController::class, 'getMovements']);
    Route::get('/stock-history', [StockMovementController::class, 'history']);

    // User-only history
    Route::middleware('user')->group(function () {
        Route::get('/histories/my', [HistoryController::class, 'myHistory']);
    });

    // Admin routes
    Route::middleware('admin')->group(function () {
        // Product management
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Stock adjustments
        Route::post('/products/add-stock', [ProductController::class, 'addStock']);
        Route::post('/stock-movements/reduce', [StockMovementController::class, 'reduceStock']);

        // Users endpoint
        Route::get('/users', function () {
            return User::select('id', 'name', 'email', 'role')->get();
        });
    });
});

/*
|--------------------------------------------------------------------------
| Root Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json(['message' => 'Laravel API is running']);
});
