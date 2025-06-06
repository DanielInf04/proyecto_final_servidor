<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Shared\Cupon;
use App\Models\Client\Order\CuponUsado;
use App\Models\Client\ContactoEntrega;
use App\Models\Company\Producto;
use App\Models\Client\Cliente;
use App\Models\Company\PedidoEmpresa;
use App\Models\Client\Direccion;
use App\Models\Client\Order\Pedido;
use App\Models\Client\Order\DetallePedido;
use App\Models\Client\Pago;
use App\Models\Client\Order\Carrito;
use App\Models\Shared\Location\Poblacion;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // Validamos el stock antes de empezar la transacción
        $erroresStock = [];

        foreach ($request->productos as $producto) {
            $productoBD = Producto::find($producto['producto_id']);

            if (!$productoBD) {
                return response()->json([
                    'error' => 'producto_no_encontrado',
                    'message' => 'El producto con ID ' . $producto['producto_id'] . ' no existe.'
                ], 404);
            }

            if ($productoBD->stock < $producto['cantidad']) {
                $erroresStock[] = [
                    'id' => $producto['producto_id'],
                    'nombre' => $productoBD->nombre,
                    'cantidad_solicitada' => $producto['cantidad'],
                    'stock_disponible' => $productoBD->stock
                ];
            }
        }

        if (!empty($erroresStock)) {
            return response()->json([
                'error' => 'stock_insuficiente',
                'productos_con_error' => $erroresStock
            ], 400);
        }

        // Validación de si el usuario ha usado el cupón
        if (!empty($request->cupon['id'])) {
            $cliente = Cliente::where('user_id', auth()->id())->first();

            if (!$cliente) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            $yaUsado = CuponUsado::where('cliente_id', $cliente->id)
                ->where('cupon_id', $request->cupon['id'])
                ->exists();

            if ($yaUsado) {
                return response()->json([
                    'error' => 'Este cupón ya ha sido usado por este usuario'
                ], 400);
            }
        }

        return DB::transaction(function () use ($request) {

            \Log::info('Datos recibidos:', $request->all());
            \Log::info('Paypal recibido:', ['paypal' => $request->paypal]);

            // Validamos el pago de PayPal si se envía
            if ($request->metodo_pago === 'paypal' && isset($request->paypal['id'])) {
                $paypalOrderId = $request->paypal['id'];
        
                // ✅ Mover estas dos líneas dentro del closure
                $clientId = config('services.paypal.client_id');
                $secret = config('services.paypal.secret');
        
                $accessTokenResponse = Http::asForm()
                    ->withBasicAuth($clientId, $secret)
                    ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                        'grant_type' => 'client_credentials',
                    ]);
        
                if (!$accessTokenResponse->ok()) {
                    return response()->json(['error' => 'No se pudo obtener token de PayPal']);
                }
        
                $accessToken = $accessTokenResponse->json()['access_token'];
        
                $paypalResponse = Http::withToken($accessToken)
                    ->get("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paypalOrderId}");
        
                if (!$paypalResponse->ok() || $paypalResponse->json()['status'] !== 'COMPLETED') {
                    return response()->json(['error' => 'Pago de PayPal no válido o no completado'], 400);
                }
            }

            // 1. Obtenemos el cliente por user_id (JWT Auth)
            $cliente = Cliente::where('user_id', auth()->id())->first();

            if (!$cliente) {
                \Log::error('❌ Cliente no encontrado para user_id: ' . auth()->id());
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            // 2. Creamos el contacto de entrega
            //$contacto = ContactoEntrega::create($request->contacto);
            $contacto = ContactoEntrega::firstOrCreate(
                [
                    'nombre' => $request->contacto['nombre'],
                    'apellidos' => $request->contacto['apellidos'],
                    'email' => $request->contacto['email'],
                    'telefono' => $request->contacto['telefono']
                ]
            );

            $poblacion = Poblacion::find($request->direccion['poblacion_id']);

            if (!$poblacion) {
                return response()->json(['error' => 'Población no válida'], 422);
            }

            $codigoPostal = $request->direccion['codigo_postal'];

            if (!preg_match('/^\d{5}$/', $codigoPostal)) {
                return response()->json(['error' => 'El código postal debe tener exactamente 5 dígitos.'], 422);
            }

            // Comparar dos primeros digitos del codigo postal
            $provinciaId = str_pad($poblacion->provincia_id, 2, '0', STR_PAD_LEFT);

            if (substr($codigoPostal, 0, 2) !== $provinciaId) {
                return response()->json([
                    'error' => 'El código posta no pertenece a la provincia de la población seleccionada.'
                ], 422);
            }

            //\Log::info('ID Contacto:', $contacto->id);

            // 3. Creamos la dirección de entrega
            //$direccion = Direccion::create($request->direccion);
            $direccion = Direccion::firstOrCreate(
                [
                    'calle' => $request->direccion['calle'],
                    'pais' => $request->direccion['pais'],
                    'codigo_postal' => $request->direccion['codigo_postal'],
                    'poblacion_id' => $request->direccion['poblacion_id'],
                ],
                [
                    'piso' => $request->direccion['piso'] ?? null,
                    'puerta' => $request->direccion['puerta'] ?? null
                ]
            );

            if ($request->guardar_direccion) {
                if (is_null($direccion->cliente_id)) {
                    // Solo la asigno si aún no tiene cliente
                    $direccion->cliente_id = $cliente->id;
                    $direccion->save();
                } 
            } else {
                // Desvinculo solo si esta dirección pertenece al cliente actual
                if ($direccion->cliente_id === $cliente->id) {
                    $direccion->cliente_id = null;
                    $direccion->save();
                }
            }

            // Total del pedido con IVA
            $totalPedido = collect($request->productos)->sum(function ($producto) {
                $productoBD = Producto::with('categoria')->find($producto['producto_id']);
                $iva = $productoBD->categoria->iva_porcentaje ?? 0;
                $precioConIva = $producto['precio_unitario'] * (1 + $iva);
                return $precioConIva * $producto['cantidad'];
            });

            // Aplicamos descuento si existe un cupón
            if (isset($request->cupon['id'])) {
                $cupon = Cupon::find($request->cupon['id']);
                if ($cupon && $cupon->porcentaje_descuento > 0) {
                    \Log::info("Aplicando cuón '{$cupon->codigo}' con {$cupon->porcentaje_descuento}% de descuento.");
                    $descuento = $totalPedido * ($cupon->porcentaje_descuento / 100);
                    $totalPedido -= $descuento;
                } else {
                    \Log::warning("Cupón inválido o sin descuento: ID {$request->cupon['id']}");
                }
            }


            // 4. Crear pedido principal
            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'direccion_id' => $direccion->id,
                'contacto_entrega_id' => $contacto->id,
                'total' => $totalPedido,
                'status' => 'pendiente',
            ]);

            // 5. Agrupar productos por empresa
            $productosPorEmpresa = collect($request->productos)->groupBy('empresa_id');

            // 6. Crear PedidoEmpresa por cada grupo
            $pedidoEmpresa = [];

            foreach ($productosPorEmpresa as $empresaId => $productos) {
                $precioTotalEmpresa = collect($productos)->sum(function ($producto) {
                    $productoBD = Producto::with('categoria')->find($producto['producto_id']);
                    $iva = $productoBD->categoria->iva_porcentaje ?? 0;
                    $precioConIva = $producto['precio_unitario'] * (1 + $iva);
                    return $precioConIva * $producto['cantidad'];
                });
                
                $pedidoEmpresa[$empresaId] = PedidoEmpresa::create([
                    'pedido_id' => $pedido->id,
                    'empresa_id' => $empresaId,
                    'estado_envio' => 'pendiente',
                    'fecha_envio' => null,
                    'precio_total' => $precioTotalEmpresa,
                ]);
            }

            // 7. Crear DetallePedido con referencia a PedidoEmpresa
            foreach ($request->productos as $producto) {
                $productoBD = Producto::with('categoria')->find($producto['producto_id']);
                $iva = $productoBD->categoria->iva_porcentaje ?? 0;
                
                $precioBase = $productoBD->oferta_activa && $productoBD->precio_oferta
                    ? $productoBD->precio_oferta
                    : $productoBD->precio_base;

                $precioConIva = $precioBase * (1 + $iva);

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'pedido_empresa_id' => $pedidoEmpresa[$producto['empresa_id']]->id,
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $precioConIva,
                    'precio_total' => $precioConIva * $producto['cantidad'],
                ]);

                if ($productoBD) {
                    $productoBD->stock -= $producto['cantidad'];
                    $productoBD->save();
                }

            }

            // 8. Registrar pago
            Pago::create([
                'pedido_id' => $pedido->id,
                'metodo_pago' => $request->metodo_pago,
                'estado' => 'pagado',
                'fecha_pago' => now(),
                'referencia' => $request->paypal['id'] ?? null,
            ]);

            // 8.1 Cupón ha sido usado, registrarlo
            if (!empty($request->cupon['id'])) {
                CuponUsado::create([
                    'cliente_id' => $cliente->id,
                    'cupon_id' => $request->cupon['id'],
                    'pedido_id' => $pedido->id,
                ]);
            }

            // 9. Eliminamos el carrito del cliente
            Carrito::where('cliente_id', $cliente->id)->delete();

            // 10. Pedido completado, enviamos una respuesta
            return response()->json([
                'success' => true,
                'pedido_id' => $pedido->id
            ], 201);

        });

    }
    
}
