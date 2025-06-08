<?php

namespace App\Services\Checkout;

use Illuminate\Http\Request;
use App\Models\Client\Cliente;
use App\Models\Client\ContactoEntrega;
use App\Models\Client\Direccion;
use App\Models\Shared\Location\Poblacion;
use App\Models\Shared\Cupon;
use App\Models\Client\Order\Pedido;
use App\Models\Company\PedidoEmpresa;
use App\Models\Company\Producto;
use App\Models\Client\Order\DetallePedido;
use App\Models\Client\Order\Carrito;
use App\Models\Client\Pago;
use App\Models\Client\Order\CuponUsado;

class OrderCreator
{
    /**
     * Crea todo el proceso de pedido completo: contacto, dirección,
     * pedido, subpedidos, detalles, pago, etc.
     * 
     * @param Request $request
     * @param Cliente $cliente
     * @return Pedido
     */
    public function create(Request $request, Cliente $cliente): Pedido
    {
        // 1. Crear contacto de entrega
        $contacto = ContactoEntrega::firstOrCreate(
            [
                'nombre' => $request->contacto['nombre'],
                'apellidos' => $request->contacto['apellidos'],
                'email' => $request->contacto['email'],
                'telefono' => $request->contacto['telefono'],
            ]
        );

        // 2. Crear dirección de entrega
        $direccion = Direccion::firstOrCreate(
            [
                'calle' => $request->direccion['calle'],
                'pais' => $request->direccion['pais'],
                'codigo_postal' => $request->direccion['codigo_postal'],
                'poblacion_id' => $request->direccion['poblacion_id'],
            ],
            [
                'piso' => $request->direccion['piso'] ?? null,
                'puerta' => $request->direccion['puerta'] ?? null,
            ]
        );

        // 3. Asignar dirección al cliente si lo ha solicitado
        if ($request->guardar_direccion) {
            //\Log::info("Guardar direccion", ['guardar_direccion' => $request->guardar_direccion]);
            if (is_null($direccion->cliente_id)) {
                $direccion->cliente_id = $cliente->id;
                $direccion->save();
            }
        } else {
            if ($direccion->cliente_id === $cliente->id) {
                $direccion->cliente_id = null;
                $direccion->save();
            }
        }

        // 4. Calcular total del pedido con IVA
        $total = collect($request->productos)->sum(function ($p) {
            $producto = Producto::with('categoria')->find($p['producto_id']);
            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioConIva = $p['precio_unitario'] * (1 + $iva);
            return $precioConIva * $p['cantidad'];
        });

        // 5. Aplicar descuento por cupón si existe
        if (isset($request->cupon['id'])) {
            $cupon = Cupon::find($request->cupon['id']);
            if ($cupon && $cupon->porcentaje_descuento > 0) {
                $descuento = $total * ($cupon->porcentaje_descuento / 100);
                $total -= $descuento;
            }
        }

        // 6. Crear el pedido principal
        $pedido = Pedido::create([
            'cliente_id' => $cliente->id,
            'direccion_id' => $direccion->id,
            'contacto_entrega_id' => $contacto->id,
            'total' => $total,
            'status' => 'pendiente',
        ]);

        // 7. Agrupar productos por empresa
        $productosPorEmpresa = collect($request->productos)->groupBy('empresa_id');

        $pedidosEmpresa = [];
        foreach ($productosPorEmpresa as $empresaId => $productos) {
            $totalEmpresa = collect($productos)->sum(function ($p) {
                $producto = Producto::with('categoria')->find($p['producto_id']);
                $iva = $producto->categoria->iva_porcentaje ?? 0;
                $precioConIva = $p['precio_unitario'] * (1 + $iva);
                return $precioConIva * $p['cantidad'];
            });

            $pedidosEmpresa[$empresaId] = PedidoEmpresa::create([
                'pedido_id' => $pedido->id,
                'empresa_id' => $empresaId,
                'estado_envio' => 'pendiente',
                'fecha_envio' => null,
                'precio_total' => $totalEmpresa,
            ]);
        }

        // 8. Crear detalle de cada producto del pedido
        foreach ($request->productos as $p) {
            $producto = Producto::with('categoria')->find($p['producto_id']);
            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioBase = $producto->oferta_activa && $producto->precio_oferta
                ? $producto->precio_oferta
                : $producto->precio_base;
            $precioFinal = $precioBase * (1 + $iva);

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'pedido_empresa_id' => $pedidosEmpresa[$p['empresa_id']]->id,
                'producto_id' => $p['producto_id'],
                'cantidad' => $p['cantidad'],
                'precio_unitario' => $precioFinal,
                'precio_total' => $precioFinal * $p['cantidad'],
            ]);

            $producto->stock -= $p['cantidad'];
            $producto->save();
        }

        // 9. Registrar el pago
        Pago::create([
            'pedido_id' => $pedido->id,
            'metodo_pago' => $request->metodo_pago,
            'estado' => 'pagado',
            //'fecha_pago' => now(),
            'referencia' => $request->paypal['id'] ?? null,
        ]);

        // 10. Registrar el uso del cupón si aplica
        if (!empty($request->cupon['id'])) {
            CuponUsado::create([
                'cliente_id' => $cliente->id,
                'cupon_id' => $request->cupon['id'],
                'pedido_id' => $pedido->id,
            ]);
        }

        // 11. Vaciar carrito del cliente
        Carrito::where('cliente_id', $cliente->id)->delete();

        return $pedido;
    }
}