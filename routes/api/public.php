<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Public\CouponController as PublicCouponController;
use App\Http\Controllers\Public\ProductController as PublicProductController;
use App\Http\Controllers\Public\OfferController;
use App\Http\Controllers\Public\LocationController;

// Ver lista de productos
Route::get('/products', [PublicProductController::class, 'index']);

// Retornar productos por búsqueda
Route::get('/products/search', [PublicProductController::class, 'search']);

// Retornar productos recomendados
Route::get('/products/recommended', [PublicProductController::class, 'recommended']);

// Retornar productos en oferta
Route::get('/products-offer', [OfferController::class, 'index']);

// Retornar productos de una categoria
Route::get('/categories/{id}/products', [PublicProductController::class, 'getByCategory']);

// Obtener un producto
Route::get('/product/{id}', [PublicProductController::class, 'getProduct']);

// Obtener imagenes de un producto
Route::get('/product/image/{id}', [PublicProductController::class, 'getProductImage']);
Route::get('/products/images/{id}', [PublicProductController::class, 'getProductImages']);

// Obtenemos una categoria
Route::get('/category/{id}', [CategoryController::class, 'getCategory']);

// Obtener categorias
Route::get('/categories', [CategoryController::class, 'categorias']);

// Obtener imagen de una categoria
Route::get('/category/image/{id}', [CategoryController::class, 'getCategoryImage']);

// Estado cupón bienvenida
Route::get('/cupon-bienvenida', [PublicCouponController::class, 'estadoCuponBienvenida']);

// Validar cupones´
Route::post('/validate-coupon', [PublicCouponController::class, 'validateCoupon']);

// Validación de cupones
//Route::post('/validate-coupon', [PublicCouponController::class, 'validateCoupon']);

// Obtenener provincias
Route::get('/provincias', [LocationController::class, 'getProvincias']);

// Obtener poblaciones de una provincia
Route::get('/poblaciones/{provinciaId}', [LocationController::class, 'getPoblacionesPorProvincia']);

// Obtener datos de una empresa
//Route::get('/company/{id}', [CompanyProfileController::class, 'getCompany']);