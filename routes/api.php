<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

//публичные пути, без авторизации
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

//только для авторизованных пользователей
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/cart', CartController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);
    Route::resource('/orders', OrderController::class);

});

//только для администратора
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    Route::resource('/products', AdminProductController::class)->only([
        'store', 'update', 'destroy'
    ]);
    Route::put('/orders/{id}', [AdminOrderController::class, 'update']);
    Route::delete('/orders/{id}', [AdminOrderController::class, 'destroy']);
});
