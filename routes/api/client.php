<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\DireccionController;
use App\Http\Controllers\Client\OrderController as UserOrderController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\Public\CouponController;

Route::middleware('auth:api')->prefix('user')->group(function () {

    Route::get('/orders', [UserOrderController::class, 'userOrders']);
    Route::post('/review', [ReviewController::class, 'store']);

    // Validar cupón usuario
    Route::post('/validate-coupon', [CouponController::class, 'validateCoupon']);

    // Fusionar carrito
    Route::post('/cart/merge', [CartController::class, 'mergeAnonCart']);

});