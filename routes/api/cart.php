<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\CouponController as PublicCouponController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\DireccionController;

Route::middleware('auth:api')->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'getCart']);
    Route::post('/add', [CartController::class, 'addTocart']);
    Route::put('/update', [CartController::class, 'updateCart']);
    Route::delete('/remove', [CartController::class, 'removeProduct']);

    // Validar cupones´
    //Route::post('/validate-coupon', [PublicCouponController::class, 'validateCoupon']);
});

// Checkout y dirección
Route::middleware('auth:api')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/address-default', [DireccionController::class, 'defaultAddress']);
});