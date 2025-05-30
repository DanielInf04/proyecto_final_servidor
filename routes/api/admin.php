<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\UserController;

Route::middleware('auth:api')->prefix('admin')->group(function () {

    // Usuarios
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::patch('/user/{id}', [UserController::class, 'updateStatus']);
    Route::delete('/user/{id}', [UserController::class, 'deleteUser']);

    // Categorias
    Route::get('/category/{id}', [CategoryController::class, 'getCategory']);
    Route::post('/category', [CategoryController::class, 'insertCategory']);
    Route::post('/category/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/category/{id}', [CategoryController::class, 'deleteCategory']);

    // Cupones
    Route::get('/coupons', [AdminCouponController::class, 'getMyCoupons']);
    Route::post('/coupon', [AdminCouponController::class, 'insertCoupon']);
    Route::delete('/coupon/{id}', [AdminCouponController::class, 'deleteCoupon']);

});