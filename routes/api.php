<?php

use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;





Route::middleware(['session'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::resource('/product', ProductController::class)->only([
    'index', 'show'
]);

//authorisation
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');//короче, все будут перенаправляться сюда

//только для авторизованных пользователей
Route::middleware('auth:api')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/cart', CartController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);
    Route::resource('/orders', OrderController::class);

});

//только для администратора
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
});



//jwt.auth, auth:api
