<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

use App\Models\Company\PedidoEmpresa;

class OrderController extends Controller
{
    public function myOrders(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $empresa = $user->empresa;

        $rutaImagenes = env('RUTA_IMAGENES', '');
        $perPage = $request->input('per_page', 10);

        $orden = $request->input('orden', 'recientes');

        $query = $empresa->pedidosEmpresa()
            ->with([
                'pedido.contactoEntrega',
                'detalles.producto.imagenes'
            ]);

        if ($orden === 'antiguos') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $paginatedPedidos = $query->paginate($perPage);

        // Transformamos los datos paginados
        $paginatedPedidos->getCollection()->transform(function ($pedidoEmpresa) use ($rutaImagenes) {
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

                    $imagenUrl = $producto->imagenes->first()
                        ? asset($rutaImagenes . '/' . $producto->imagenes->first()->imagen)
                        : null;

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

        return response()->json($paginatedPedidos);
    }

    public function searchMyOrders(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $empresa = $user->empresa;

        $queryParam = $request->input('q', '');
        $perPage = $request->input('per_page', 10);
        $rutaImagenes = env('RUTA_IMAGENES', '');

        $query = $empresa->pedidosEmpresa()
            ->with([
                'pedido.contactoEntrega',
                'detalles.producto.imagenes'
            ]);

        if ($queryParam !== '') {
            if (is_numeric($queryParam)) {
                $query->where('pedido_id', $queryParam);
            } else {
                $query->whereHas('pedido.contactoEntrega', function ($subQuery) use ($queryParam) {
                    $subQuery->whereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%$queryParam%"]);
                });
            }
        }

        $pedidos = $query->orderByDesc('created_at')->paginate($perPage);

        $pedidos->getCollection()->transform(function ($pedidoEmpresa) use ($rutaImagenes) {
            return [
                'id' => $pedidoEmpresa->id,
                'estado_envio' => $pedidoEmpresa->estado_envio,
                'fecha_envio' => $pedidoEmpresa->fecha_envio,
                'precio_total' => $pedidoEmpresa->precio_total,
                'pedido' => [
                    'id' => $pedidoEmpresa->pedido->id,
                    'fecha_pedido' => $pedidoEmpresa->pedido->fecha_pedido,
                    'nombre_completo' => optional($pedidoEmpresa->pedido->contactoEntrega, function ($contacto) {
                        return $contacto->nombre . ' ' . $contacto->apellidos;
                    }),
                ],
                'productos' => $pedidoEmpresa->detalles->map(function ($detalle) use ($rutaImagenes) {
                    $producto = $detalle->producto;

                    $imagenUrl = $producto->imagenes->first()
                        ? asset($rutaImagenes . '/' . $producto->imagenes->first()->imagen)
                        : null;

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

        return response()->json($pedidos);
    }

    public function updateStatus(Request $request, $id)
    {
        \Log::info("Intentando actualizar el estado del pedido empresa ID: $id");

        $user = JWTAuth::parseToken()->authenticate();
        $empresa = $user->empresa;

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
