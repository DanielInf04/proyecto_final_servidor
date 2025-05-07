<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

use App\Models\Company\PedidoEmpresa;

class OrderController extends Controller
{
    public function userOrders()
    {
        \Log::info('Estamos obteniendo los pedidos del usuario');
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'cliente') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cliente = $user->cliente;

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', '');

        // Obtenemos los pedidos del cliente
        $pedidosCliente = $cliente->pedidos()
            ->with([
                'contactoEntrega',
                'direccion',
                'pedidoEmpresas.detalles.producto.imagenes',
                'pedidoEmpresas.empresa'
            ])->get();

        // Formateamos la respuesta
        $pedidosFormateados = $pedidosCliente->map(function ($pedido) use ($rutaImagenes) {
            return [
                'id' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'fecha_pedido' => $pedido->fecha_pedido,
                'status' => $pedido->status,
                'total' => $pedido->total,
                'direccion' => optional($pedido->direccion)?->calle . ', ' .
                            optional($pedido->direccion)?->ciudad . ', ' .
                            optional($pedido->direccion)?->provincia,
                'nombre_completo' => optional($pedido->contactoEntrega)?->nombre . ' ' .
                                      optional($pedido->contactoEntrega)?->apellidos,
                'empresas' => $pedido->pedidoEmpresas->map(function ($pe) use ($rutaImagenes, $pedido) {
                    return [
                        'nombre' => optional($pe->empresa)?->nombre ?? 'Empresa desconocida',
                        'estado_envio' => $pe->estado_envio,
                        'fecha_envio' => $pe->fecha_envio,
                        'productos' => $pe->detalles->map(function ($detalle) use ($rutaImagenes, $pedido) {
                            $producto = $detalle->producto;
                            $imagenUrl = null;

                            if ($producto->imagenes && $producto->imagenes->count() > 0) {
                                $imagenUrl = asset($rutaImagenes . '/' . $producto->imagenes->first()->imagen);
                            }

                            $resenyaCliente = optional($producto->resenyas)
                                ->where('cliente_id', $pedido->cliente_id)
                                ->first();

                            return [
                                'producto_id' => $producto->id,
                                'nombre' => $producto->nombre,
                                'precio_unitario' => $detalle->precio_unitario,
                                'cantidad' => $detalle->cantidad,
                                'imagen' => $imagenUrl,
                                'empresa' => optional($producto->empresa)?->nombre,
                                'valoracion_cliente' => optional($resenyaCliente)?->valoracion,
                            ];
                        })
                    ];
                })
            ];
        });
        \Log::info('Pedidos formateados:', ['pedidos' => $pedidosFormateados->toArray()]);

        return response()->json(['pedidos' => $pedidosFormateados]);

    }

}
