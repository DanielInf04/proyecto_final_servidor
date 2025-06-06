<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company\Producto;
use App\Models\Client\Order\Carrito;
use App\Models\Client\Cliente;
use App\Models\Client\Order\Pedido;
use App\Models\Client\Order\DetallePedido;


class CartController extends Controller
{
    public function getCart()
    {
        $cliente = Cliente::where('user_id', auth()->id())->first();

        $carrito = Carrito::with('producto.imagenes', 'producto.categoria')
            ->where('cliente_id', $cliente->id)
            ->get();

        $rutaImagenes = env('RUTA_IMAGENES', '');

        // Calcular el total del carrito correctamente
        $total = 0;

        $carritoFormateado = $carrito->map(function ($item) use ($rutaImagenes, &$total) {
            $producto = $item->producto;

            // Verificamos si hay oferta activa
            $precioUnitario = ($producto->oferta_activa && $producto->precio_oferta)
                ? $producto->precio_oferta
                : $producto->precio_base;

            $ivaDecimal = floatval($producto->categoria->iva_porcentaje); // ejemplo: 0.21
            $precioConIVA = round($precioUnitario * (1 + $ivaDecimal), 2);

            $subtotal = round($precioUnitario * $item->cantidad, 2);
            $total += $subtotal;

            $imagen = $producto->imagenes->first();
            $imagenUrl = $imagen ? asset($rutaImagenes . '/' . $imagen->imagen) : null;

            return [
                'producto_id' => $item->producto_id,
                'imagen_url' => $imagenUrl,
                'nombre' => $producto->nombre ?? 'Producto eliminado',
                'categoria' => $producto->categoria->nombre,
                'empresa_id' => $producto->empresa_id,
                'precio_unitario' => round($precioUnitario, 2),
                'iva_porcentaje' => $ivaDecimal,
                'precio_con_iva' => $precioConIVA,
                'cantidad' => $item->cantidad,
                'stock' =>  $producto->stock,
                'subtotal' => $subtotal,
            ];
        });

        return response()->json([
            'items' => $carritoFormateado,
            'total' => round($total, 2)
        ]);
    }

    public function mergeAnonCart(Request $request)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|integer|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        foreach ($request->productos as $item) {
            $producto = Producto::with('categoria')->find($item['producto_id']);
            if (!$producto) {
                continue;
            }

            $precioUnitario = ($producto->oferta_activa && $producto->precio_oferta)
                ? $producto->precio_oferta
                : $producto->precio_base;

            $carritoItem = Carrito::where('cliente_id', $cliente->id)
                ->where('producto_id', $item['producto_id'])
                ->first();

            if ($carritoItem) {
                // Si existe, sumamos la cantidad
                $carritoItem->cantidad += $item['cantidad'];
                $carritoItem->save();
            } else {
                // Si no existe, lo creamos con el precio unitario
                Carrito::create([
                    'cliente_id' => $cliente->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                ]);
            }
        }

        \Log::info('✅ Carrito fusionado correctamente');

        return response()->json(['message' => 'Carrito fusionado correctamente'], 200);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        // Obtenemos el cliente actual
        $cliente = Cliente::where('user_id', auth()->id())->first();

        // Obtenemos el producto
        $producto = Producto::find($request->producto_id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Buscar si existe en el carrito
        $carritoItem = Carrito::where('cliente_id', $cliente->id)
            ->where('producto_id', $producto->id)
            ->first();

        if ($carritoItem) {
            // Ya existe, asi que actualizamos la cantidad
            $carritoItem->cantidad += $request->cantidad;
            $carritoItem->save();
        } else {
            // No existe, creamos uno nuevo
            Carrito::create([
                'cliente_id' => $cliente->id,
                'producto_id' => $producto->id,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio_base
            ]);
        }

        response()->json(['message', 'Producto añadido al carrito correctamente']);
        
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        $carritoItem = Carrito::where('cliente_id', $cliente->id)
            ->where('producto_id', $request->producto_id)
            ->first();
        
        if (!$carritoItem) {
            return response()->json(['error' => 'Producto no encontrado en el carrito'], 404);
        }

        $carritoItem->cantidad = $request->cantidad;
        $carritoItem->save();

        return response()->json(['message' => 'Cantidad actualizada con éxito']);

    }

    public function removeProduct(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        $carritoItem = Carrito::where('cliente_id', $cliente->id)
            ->where('producto_id', $request->producto_id)
            ->first();

        if (!$carritoItem) {
            return response()->json(['error' => 'Producto no encontrado en el carrito']);
        }

        $carritoItem->delete();

        // Verificar si el carrito quedó vacío
        $quedanProductos = Carrito::where('cliente_id', $cliente->id)->exists();

        if (!$quedanProductos) {
            return response()->json(['message' => 'Producto eliminado y carrito vacío']);
        }

        return response()->json(['message' => 'Producto eliminado del carrito con éxito']);

    }

}
