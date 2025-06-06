<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

use App\Models\Company\PedidoEmpresa;

class OrderController extends Controller
{
    public function userOrders(Request $request)
    {
        \Log::info('Estamos obteniendo los pedidos del usuario');

        $user = JWTAuth::parseToken()->authenticate();
        $cliente = $user->cliente;

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', '');
        $perPage = $request->input('per_page', 5);

        $pedidosPaginator = $cliente->pedidos()
            ->with([
                'contactoEntrega',
                'direccion',
                'pedidoEmpresas.detalles.producto.imagenes',
                'pedidoEmpresas.detalles.producto.resenyas',
                'pedidoEmpresas.detalles.producto.empresa',
                'pedidoEmpresas.empresa',
                'pago'
            ])
            ->orderByDesc('fecha_pedido')
            ->paginate($perPage);

        $pedidosFormateados = $pedidosPaginator->getCollection()->map(function ($pedido) use ($rutaImagenes) {
            return [
                'id' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'fecha_pedido' => $pedido->fecha_pedido,
                'status' => $pedido->status,
                'estado_pago' => optional($pedido->pago)?->estado,
                'total' => $pedido->total,
                //'cupon_usado' => optional($pedido->cuponUsado)?->cupon?->porcentaje_descuento ?? null,
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

        $pedidosPaginator->setCollection($pedidosFormateados);

        return response()->json($pedidosPaginator);
    }


    /*public function userOrders()
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
                'pedidoEmpresas.empresa',
                'pago'
            ])->get();

        // Formateamos la respuesta
        $pedidosFormateados = $pedidosCliente->map(function ($pedido) use ($rutaImagenes) {
            return [
                'id' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'fecha_pedido' => $pedido->fecha_pedido,
                'status' => $pedido->status,
                'estado_pago' => optional($pedido->pago)?->estado,
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

    }*/

    public function getOrderById($id)
    {
        \Log::info("Obteniendo detalles del pedido con ID: $id");

        $user = JWTAuth::parseToken()->authenticate();

        $cliente = $user->cliente;

        $pedido = $cliente->pedidos()
            ->where('id', $id)
            ->with([
                'direccion.poblacion.provincia',
                'contactoEntrega',
                'pedidoEmpresas.detalles.producto.imagenes',
                'pedidoEmpresas.empresa',
                'pago',
                'cuponUsado.cupon'
            ])
            ->first();

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', '');

        $cupon = optional($pedido->cuponUsado)?->cupon;

        $pedidoFormateado = [
            'id' => $pedido->id,
            'cliente_id' => $pedido->cliente_id,
            'fecha_pedido' => $pedido->fecha_pedido,
            'status' => $pedido->status,
            'total' => $pedido->total,
            'metodo_pago' => optional($pedido->pago)?->metodo_pago,
            'estado_pago' => optional($pedido->pago)?->estado,
            'descuento_aplicado' => $cupon ? round(($pedido->total / (1 - $cupon->porcentaje_descuento / 100)) - $pedido->total, 2) : null,
            'cupon_usado' => $pedido->cuponUsado && $pedido->cuponUsado->cupon
                    ? [
                        'codigo' => $pedido->cuponUsado->cupon->codigo,
                        'porcentaje_descuento' => $pedido->cuponUsado->cupon->porcentaje_descuento
                    ]
                    : null,
            'direccion' => [
                'calle' => $pedido->direccion?->calle,
                'puerta' => $pedido->direccion?->puerta,
                'piso' => $pedido->direccion?->piso,
                'pais' => $pedido->direccion?->pais,
                'codigo_postal' => $pedido->direccion?->codigo_postal,
                'poblacion' => $pedido->direccion?->poblacion?->nombre,
                'provincia' => $pedido->direccion?->poblacion?->provincia?->nombre,
            ],
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

        return response()->json(['pedido' => $pedidoFormateado]);
    }

}
