<?php

namespace App\Services\Checkout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client\Cliente;

class CheckoutService
{
    /**
     * Procesa el flujo completo del checkout
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // 1. Validar pago PayPal
        if ($request->metodo_pago === 'paypal' && isset($request->paypal['id'])) {
            $paypalValidator = new PaypalValidator();
            if ($error = $paypalValidator->validate($request->paypal)) {
                return $error;
            }
        }

        // 1. Transacción para crear el pedido completo
        return DB::transaction(function () use ($request) {
            $cliente = Cliente::where('user_id', auth()->id())->firstOrFail();

            $orderCreator = new OrderCreator();
            $pedido = $orderCreator->create($request, $cliente);

            return response()->json([
                'success' => true,
                'pedido_id' => $pedido->id,
            ], 201);
        });
    }

    public function validateCheckoutData(Request $request)
    {
        // 1. Validar stock
        $stockValidator = new StockValidator();
        if ($error = $stockValidator->validate($request->productos)) {
            return $error;
        }

        // 2. Validar cupón
        $couponValidator = new CouponValidator();
        if ($error = $couponValidator->validate($request->cupon ?? null)) {
            return $error;
        }

        // 3. Validar dirección
        $addressValidator = new AddressValidator();
        if ($error = $addressValidator->validate($request->direccion)) {
            return $error;
        }

        // 4. Validar campos básicos (Laravel Request validation)
        $request->validate([
            'contacto.nombre' => 'required|string|max:100',
            'contacto.apellidos' => 'required|string|max:100',
            'contacto.telefono' => 'required|string|max:20',
            'direccion.calle' => 'required|string|max:255',
            'direccion.codigo_postal' => 'required|digits:5',
            'direccion.poblacion_id' => 'required|integer|exists:poblaciones,id',
            'metodo_pago' => 'required|in:paypal,tarjeta',
            'productos' => 'required|array|min:1',
        ]);

        return null; // Todo OK
    }

}



