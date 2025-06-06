<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Models\Client\Cliente;
use App\Models\Client\Resenya;
use App\Models\Client\Order\DetallePedido;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'valoracion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        // Validar que el usuario haya comprado el producto
        $haComprado = DetallePedido::whereHas('pedidoEmpresa.pedido', function ($query) use ($cliente) {
            $query->where('cliente_id', $cliente->id);
        })
        ->where('producto_id', $request->producto_id)
        ->exists();

        if (!$haComprado) {
            return response()->json([
                'message' => 'Solo puedes valorar productos que hayas comprado.'
            ], 403);
        }

        // Validar que no haya ya dejado una reseña del mismo producto
        $yaValorado = Resenya::where('cliente_id', $cliente->id)
            ->where('producto_id', $request->producto_id)
            ->exists();

        if ($yaValorado) {
            return response()->json([
                'message' => 'Ya has valorado este producto.'
            ], 409);
        }

        $resenya = Resenya::create([
            'cliente_id' => $cliente->id,
            'producto_id' => $request->producto_id,
            'valoracion' => $request->valoracion,
            'comentario' => $request->comentario,
            'fecha' => now(),
        ]);

        return response()->json($resenya, 201);
    }

}
