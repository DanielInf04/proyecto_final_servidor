<?php

namespace App\Services\Checkout;

use App\Models\Client\Cliente;
use App\Models\Client\Order\CuponUsado;

class CouponValidator
{
    /**
     * Valida si el cupón fue usado anteriormente por el cliente
     * 
     * @param array|null $couponData
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validate(?array $couponData)
    {
        if (empty($couponData['id'])) {
            return null; // No hay cupón, nada que validar
        }

        // Buscar cliente autenticado
        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json([
                'error' => 'cliente_no_encontrado',
                'message' => 'No se encontró el cliente autenticado.'
            ], 404);
        }

        // Verificar si el cupón ya fue usado
        $alreadyUsed = CuponUsado::where('cliente_id', $cliente->id)
            ->where('cupon_id', $couponData['id'])
            ->exists();

        if ($alreadyUsed) {
            return response()->json([
                'error' => 'cupon_ya_usado',
                'messsage' => 'Este cupón ya ha sido usado por el cliente.'
            ], 400);
        }

        return null;
    }
}