<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

use App\Models\Company\PedidoEmpresa;

class OrderController extends Controller
{
    public function myOrders()
    {
        \Log::info('Estamos obteniendo los pedidos de la empresa');
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', '');

        // Obtenemos los pedidos de la empresa
        $pedidosEmpresa = $empresa->pedidosEmpresa()
            ->with([
                'pedido.contactoEntrega',
                'detalles.producto.imagenes'
            ])
            ->get();

        // Formateamos la respuesta para traer lo importante
        $pedidosFormateados = $pedidosEmpresa->map(function ($pedidoEmpresa) use ($rutaImagenes) {
            return [
                'id' => $pedidoEmpresa->id,
                'estado_envio' => $pedidoEmpresa->estado_envio,
                'fecha_envio' => $pedidoEmpresa->fecha_envio,
                'precio_total' => $pedidoEmpresa->precio_total,
                'pedido' => [
                    'id' => $pedidoEmpresa->pedido->id,
                    'fecha_pedido' => $pedidoEmpresa->pedido->fecha_pedido,
                    'nombre_completo' => optional($pedidoEmpresa->pedido->contactoEntrega, function($contacto) {
                        return $contacto->nombre . ' ' . $contacto->apellidos;
                    }),
                ],
                'productos' => $pedidoEmpresa->detalles->map(function ($detalle) use ($rutaImagenes) {
                    $producto = $detalle->producto;

                    // Tomamos solo la primera imagen
                    $imagenUrl = null;
                    if ($producto->imagenes && $producto->imagenes->count() > 0) {
                        $imagenUrl = asset($rutaImagenes . '/' . $producto->imagenes->first()->imagen);
                    }

                    return [
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'producto' => [
                            'nombre' => $producto->nombre,
                            'precio' => $producto->precio,
                            'imagen' => $imagenUrl,
                        ]
                    ];
                }),
            ];
        });

        return response()->json([
            'pedidos' => $pedidosFormateados
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        \Log::info("Intentando actualizar el estado del pedido empresa ID: $id");

        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $pedidoEmpresa = PedidoEmpresa::where('id', $id)
            ->where('empresa_id', $empresa->id)
            ->first();

        if (!$pedidoEmpresa) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        $nuevoEstado = $request->estado_envio;

        if (!in_array($nuevoEstado, ['pendiente', 'preparando', 'enviado', 'entregado', 'cancelado'])) {
            return response()->json(['error' => 'Estado inválido'], 400);
        }

        $pedidoEmpresa->estado_envio = $nuevoEstado;
        $pedidoEmpresa->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'estado_envio' => $pedidoEmpresa->estado_envio
        ]);
    }

}
