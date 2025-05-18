<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

require __DIR__ . '/api/public.php';
require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/client.php';
require __DIR__ . '/api/cart.php';
require __DIR__ . '/api/company.php';
require __DIR__ . '/api/admin.php';

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/





// Productos del marketplace (sin autenticacion)
//Route::get('/products', [PublicProductController::class, 'index']);

// Ver un producto
//Route::get('/product/{id}', [PublicProductController::class, 'getProduct']);

// Ver imagenes de los productos
/*Route::get('/product/image/{id}', [PublicProductController::class, 'getProductImage']);
Route::get('/products/images/{id}', [PublicProductController::class, 'getProductImages']);*/

// Obtener categorias de productos
//Route::get('/categories', [CategoryController::class, 'categorias']);





// Cupon Bienvenida
//Route::get('/cupon-bienvenida', [PublicCouponController::class, 'estadoCuponBienvenida']);

// Validación de cupones
//Route::post('/validate-coupon', [PublicCouponController::class, 'validateCoupon']);

// Obtener datos de una empresa
//Route::get('/company/{id}', [CompanyProfileController::class, 'getCompany']);





// Carrito de compra
//Route::middleware('auth:api')->get('/cart', [CartController::class, 'getCart']);
//Route::middleware('auth:api')->post('/cart/add', [CartController::class, 'addToCart']);
//Route::middleware('auth:api')->post('/cart/add', [CartController::class, 'addProduct']);
//Route::middleware('auth:api')->put('/cart/update', [CartController::class, 'updateCart']);
//Route::middleware('auth:api')->delete('/cart/remove', [CartController::class, 'removeProduct']);

//Ruta para finalizar pago
//Route::middleware('auth:api')->post('/checkout', [CheckoutController::class, 'store']);
//Route::middleware('auth:api')->get('/direccion-default', [DireccionController::class, 'direccionPorDefecto']);



/*Route::middleware('auth:sanctum')->get('/usuario', function (Request $request) {
    return response()->json([
        'nombre' => $request->user()->name,
        // o cualquier otro campo: email, id, etc.
    ]);
});*/

/*Route::middleware('auth:api')->get('/user', function () {
    return response()->json(auth()->user());
});*/