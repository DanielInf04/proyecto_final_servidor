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

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        \Log::info('✅ Cliente encontrado con ID: ' . $cliente->id);

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
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

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

    /*public function getCart()
    {
        // Obtenemos el cliente autenticado
        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        \Log::info('✅ Cliente encontrado con ID: ' . $cliente->id);

        // Traemos los items del carrito con la info del producto asociada
        $carrito = Carrito::with('producto.imagenes', 'producto.categoria')
            ->where('cliente_id', $cliente->id)
            ->get();

        $rutaImagenes = env('RUTA_IMAGENES', '');

        // Calcular el total del carrito
        $total = $carrito->sum(function ($item) {
            return $item->cantidad * $item->precio_unitario;
        });

        // Formatear respuesta
        $carritoFormateado = $carrito->map(function ($item) use ($rutaImagenes) {

            $producto = $item->producto;

            // Obtenemos la primera imagen
            $imagen = $producto->imagenes->first();

            $imagenUrl = $imagen
                ? asset($rutaImagenes . '/' . $imagen->imagen)
                : null;

            return [
                'producto_id' => $item->producto_id,
                'imagen_url' => $imagenUrl,
                'nombre' => $item->producto->nombre ?? 'Producto eliminado',
                'categoria' => $item->producto->categoria->nombre,
                'empresa_id' => $item->producto->empresa_id,
                'precio_unitario' => $item->precio_unitario,
                'iva_porcentaje' => $producto->categoria->iva_porcentaje,
                'precio_con_iva' => round($producto->precio_base * (1 + $producto->categoria->iva_porcentaje), 2),
                'cantidad' => $item->cantidad,
                'subtotal' => $item->cantidad * $item->precio_unitario,
            ];
        });

        return response()->json([
            'items' => $carritoFormateado,
            'total' => $total
        ]);
        
    }*/



    // NO TOCAR
    /*public function getCart()
    {
        // Obtenemos el cliente autenticado
        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        \Log::info('✅ Cliente encontrado con ID: ' . $cliente->id);

        // Traemos los items del carrito con la info del producto asociada
        $carrito = Carrito::with('producto.imagenes', 'producto.categoria')
            ->where('cliente_id', $cliente->id)
            ->get();

        $rutaImagenes = env('RUTA_IMAGENES', '');

        // Calcular el total del carrito
        $total = $carrito->sum(function ($item) {
            return $item->cantidad * $item->precio_unitario;
        });

        // Formatear respuesta
        $carritoFormateado = $carrito->map(function ($item) use ($rutaImagenes) {

            $producto = $item->producto;

            // Obtenemos la primera imagen
            $imagen = $producto->imagenes->first();

            $imagenUrl = $imagen
                ? asset($rutaImagenes . '/' . $imagen->imagen)
                : null;

            return [
                'producto_id' => $item->producto_id,
                'imagen_url' => $imagenUrl,
                'nombre' => $item->producto->nombre ?? 'Producto eliminado',
                'categoria' => $item->producto->categoria->nombre,
                'empresa_id' => $item->producto->empresa_id,
                'precio_unitario' => $item->precio_unitario,
                'iva_porcentaje' => $producto->categoria->iva_porcentaje,
                'precio_con_iva' => round($producto->precio_base * (1 + $producto->categoria->iva_porcentaje), 2),
                'cantidad' => $item->cantidad,
                'subtotal' => $item->cantidad * $item->precio_unitario,
            ];
        });

        return response()->json([
            'items' => $carritoFormateado,
            'total' => $total
        ]);
        
    }*/

    public function addToCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        // Obtenemos el cliente actual
        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

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

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

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

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

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

    /*public function addProduct(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $clienteId = $cliente->id;


        // Buscar o crear un pedido en estado 'carrito'
        $pedido = Pedido::firstOrCreate(
            ['cliente_id' => $clienteId, 'estado' => 'carrito'],
            ['total' => 0]
        );

        $producto = Producto::findOrFail($request->producto_id);

        // Verificar si el producto ya está en el carrito
        $detalle = DetallePedido::where('pedido_id', $pedido->id)
                        ->where('producto_id', $producto->id)
                        ->first();
        
        if ($detalle) {
            // Si ya existe, solo actualizamos la cantidad
            $detalle->cantidad += $request->cantidad;
            $detalle->save();
        } else {
            // Si no, lo añadimos
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidad,
                'precio' => $producto->precio,
            ]);
        }

        // Recalcular el total
        $pedido->total = $pedido->detalles->sum(fn($item) => $item->precio * $item->cantidad);
        $pedido->save();

        return response()->json(['message' => 'Producto agregado al carrito con éxito'], 200);
    }

    public function getCart()
    {
        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Incluir imágenes también
        $pedido = Pedido::with('detalles.producto.categoria', 'detalles.producto.imagenes')
            ->where('cliente_id', $cliente->id)
            ->where('estado', 'carrito')
            ->first();

        if (!$pedido || $pedido->detalles->isEmpty()) {
            return response()->json([
                'productos' => [],
                'subtotal' => 0,
                'envio' => 0,
                'impuestos' => 0,
                'total' => 0
            ]);
        }

        $rutaImagenes = env('RUTA_IMAGENES', '');

        $productos = $pedido->detalles->map(function ($detalle) use ($rutaImagenes) {
            $producto = $detalle->producto;
            $imagen = $producto->imagenes->first(); // Usamos la primera imagen si existe

            $imagenUrl = $imagen
                ? asset($rutaImagenes . '/' . $imagen->imagen)
                : null;

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
                'precio' => $detalle->precio,
                'cantidad' => $detalle->cantidad,
                'subtotal' => round($detalle->cantidad * $detalle->precio, 2),
                'imagen_url' => $imagenUrl
            ];
        });

        $subtotal = $productos->sum('subtotal');
        $envio = 00.00;
        $impuestos = 00.00;

        $total = $subtotal + $envio + $impuestos;

        return response()->json([
            'productos' => $productos,
            'subtotal' => round($subtotal, 2),
            'envio' => $envio,
            'impuestos' => $impuestos,
            'total' => round($total, 2)
        ]);
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $clienteId = $cliente->id;

        $pedido = Pedido::firstOrCreate(
            ['cliente_id' => $clienteId, 'estado' => 'carrito'],
            ['total' => 0]
        );

        $producto = Producto::findOrFail($request->producto_id);

        // Verificamos si el producto ya está en el carrito
        $detalle = DetallePedido::where('pedido_id', $pedido->id)
                ->where('producto_id', $producto->id)
                ->first();

        if ($detalle) {
            $detalle->cantidad = $request->cantidad;
            $detalle->save();
        }

        // Recalcular el total
        $pedido->total = $pedido->detalles->sum(fn($item) => $item->precio * $item->cantidad);
        $pedido->save();

        return response()->json(['message' => 'Producto del carrito actualizado con éxito'], 200);

    }

    public function removeProduct(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
        ]);

        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $pedido = Pedido::where('cliente_id', $cliente->id)
                    ->where('estado', 'carrito')
                    ->first();

        if (!$pedido) {
            return response()->json(['error' => 'No hay carrito activo'], 404);
        }

        $detalle = DetallePedido::where('pedido_id', $pedido->id)
                        ->where('producto_id', $request->producto_id)
                        ->first();

        if (!$detalle) {
            return response()->json(['error' => 'Producto no encontrado en el carrito'], 404);
        }

        $detalle->delete();

        // Verificar si quedan más productos en el carrito
        if ($pedido->detalles()->count() === 0) {
            $pedido->delete();
            return response()->json(['message' => 'Producto eliminado y carrito vacío']);
        }

        // Si aún hay productos, recalcular el total
        $pedido->total = $pedido->detalles->sum(fn($item) => $item->precio * $item->cantidad);
        $pedido->save();

        return response()->json(['message' => 'Producto eliminado del carrito con éxito'], 200);

    }*/

}
