<?php

namespace App\Services\Checkout;

use App\Models\Company\Producto;

class StockValidator
{
    /**
     * Valida el stock de los productos
     * 
     * @param array $productos
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validate(array $productos)
    {
        $erroresStock = [];

        foreach ($productos as $producto) {
            $productoDB = Producto::find($producto['producto_id']);

            if (!$productoDB) {
                return response()->json([
                    'error' => 'producto_no_encontrado',
                    'message' => 'El producto con ID ' . $producto['producto_id'] . ' no existe.'
                ], 404);
            }

            if ($productoDB->stock < $producto['cantidad']) {
                $erroresStock[] = [
                    'id' => $producto['producto_id'],
                    'nombre' => $productoDB->nombre,
                    'cantidad_solicitada' => $producto['cantidad'],
                    'stock_disponible' => $productoDB->stock
                ];
            }
        }

        if (!empty($erroresStock)) {
            return response()->json([
                'error' => 'stock_insuficiente',
                'productos' => $erroresStock
            ], 400);
        }

        return null;
    }
}