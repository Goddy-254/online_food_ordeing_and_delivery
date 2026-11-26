<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\MpesaController;
use App\Http\Controllers\Api\Admin\AdminCustomerController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'role:customer']);

//categories routes
Route::get('/categories', [CategoryController::class, 'index']);

// Menu item routes
Route::get('/menu-items', [MenuItemController::class, 'index']);



// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:5,1');
Route::get('/store-info', [AdminSettingsController::class, 'storeInfo']);

// Safaricom calls this public API endpoint after the customer responds to the STK prompt.
Route::post('/mpesa/callback', [MpesaController::class, 'callback']);

// Protected routes
// Customer operations require a customer account, even if an admin token is supplied.
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/orders/{orderId}/mpesa', [MpesaController::class, 'initiate']);
    Route::get('/orders/{orderId}/payment-status', [MpesaController::class, 'status']);
    Route::patch('/orders/{orderId}/items/{itemId}', [OrderItemController::class, 'update']);
    Route::delete('/orders/{orderId}/items/{itemId}', [OrderItemController::class, 'destroy']);

    Route::get('/profile', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
        ]);
    });

// Order routes

Route::apiResource('orders', OrderController::class);

});

// Admin APIs are protected by both token authentication and the server-side role check.
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

    Route::get('/menu-items', [MenuItemController::class, 'adminIndex']);
    Route::post('/menu-items', [MenuItemController::class, 'store']);
    Route::get('/menu-items/{menu_item}', [MenuItemController::class, 'show']);
    Route::put('/menu-items/{menu_item}', [MenuItemController::class, 'update']);
    Route::patch('/menu-items/{menu_item}', [MenuItemController::class, 'update']);
    Route::delete('/menu-items/{menu_item}', [MenuItemController::class, 'destroy']);
    Route::apiResource('/categories', CategoryController::class);

    Route::get('/customers', [AdminCustomerController::class, 'index']);
    Route::get('/customers/{user}', [AdminCustomerController::class, 'show']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);

    Route::get('/settings', [AdminSettingsController::class, 'show']);
    Route::put('/settings', [AdminSettingsController::class, 'update']);
});



