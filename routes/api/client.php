<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\DireccionController;
use App\Http\Controllers\Client\OrderController as UserOrderController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\Public\CouponController;

Route::middleware(['auth:api', 'is_client'])->prefix('user')->group(function () {

    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);

    Route::get('/orders', [UserOrderController::class, 'userOrders']);
    Route::get('/orders/{id}', [UserOrderController::class, 'getOrderById']);
    
    Route::post('/review', [ReviewController::class, 'store']);

    // Validar cupón usuario
    Route::post('/validate-coupon', [CouponController::class, 'validateCoupon']);

    // Fusionar carrito
    Route::post('/cart/merge', [CartController::class, 'mergeAnonCart']);

});

