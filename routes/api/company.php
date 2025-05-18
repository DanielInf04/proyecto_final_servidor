<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Company\ProductController;
use App\Http\Controllers\Company\OrderController;
use App\Http\Controllers\Company\ProfileController;

Route::middleware('auth:api')->prefix('company')->group(function () {

    // Perfil Empresa
    Route::get('/profile', [ProfileController::class, 'getCurrentCompany']);
    Route::put('/profile', [ProfileController::class, 'updateCompany']);

    // Obtener Todos Productos Empresa
    Route::get('/products', [ProductController::class, 'getMyProducts']);

    Route::get('/products/search', [ProductController::class, 'searchMyProducts']);
    
    // CRUD Productos
    Route::post('/product', [ProductController::class, 'insertProduct']);
    Route::post('/product/{id}', [ProductController::class, 'updateProduct']);
    Route::patch('/product/{id}', [ProductController::class, 'updateStatus']);
    Route::delete('/product/{id}', [ProductController::class, 'deleteProduct']);

    // Pedidos
    Route::get('/orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/search', [OrderController::class, 'searchMyOrders']);
    Route::put('/order/{id}/status', [OrderController::class, 'updateStatus']);

});