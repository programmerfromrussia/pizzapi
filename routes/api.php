<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    return $request->user();
});


Route::resource('/product', ProductController::class)->only([
    'index', 'show' // allowing only viewing the products
]);
//authorisation
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);


