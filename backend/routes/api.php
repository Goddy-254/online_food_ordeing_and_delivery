<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//categories routes
Route::apiResource('/categories', CategoryController::class);

// Menu item routes
Route::apiResource('/menu-items', MenuItemController::class);



// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/orders/{orderId}/items/{itemId}', 'App\\Http\\Controllers\\Api\\OrderItemController@update');
    Route::delete('/orders/{orderId}/items/{itemId}', 'App\\Http\\Controllers\\Api\\OrderItemController@destroy');

    Route::get('/profile', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
        ]);
    });

// Order routes

Route::apiResource('orders', 'App\\Http\\Controllers\\Api\\OrderController');


});



