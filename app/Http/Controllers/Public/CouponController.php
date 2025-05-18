<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

use App\Models\Shared\Cupon;
use App\Models\Client\Cliente;
use App\Models\Client\Order\CuponUsado;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
        ]);

        $cupon = Cupon::where('codigo', $request->codigo)
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                ->orWhere('fecha_expiracion', '>=', now());
            })
            ->first();

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón inválido o expirado'
            ], 404);
        }

        if (auth()->check()) {
            $cliente = Cliente::where('user_id', auth()->id())->first();

            if (!$cliente) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            $yaUsado = CuponUsado::where('cliente_id', $cliente->id)
                ->where('cupon_id', $cupon->id)
                ->exists();

            if ($yaUsado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este cupón ya ha sido utilizado'
                ], 422); // ← mejor que 409 para errores de validación
            }
        }

        return response()->json([
            'success' => true,
            'data' => $cupon
        ]);
    }

    public function estadoCuponBienvenida(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            // No logueado, mostramos el banner (usuario nuevo)
            $cupon = $this->getCuponBienvenida();

            if (!$cupon) {
                return response()->json([
                    'mostrarBanner' => false,
                    'cupon' => null,
                    'porcentaje_descuento' => null
                ]);
            }

            return response()->json([
                'mostrarBanner' => true,
                'cupon' => $cupon->codigo,
                'porcentaje_descuento' => $cupon->porcentaje_descuento,
            ]);
        }

        if ($user->role !== 'cliente') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cliente = $user->cliente;

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Buscar cupón de bienvenida activo
        $cupon = Cupon::where('solo_nuevos_usuarios', true)
            ->whereNull('fecha_expiracion')
            ->first();
        
        if (!$cupon) {
            return response()->json([
                'mostrarBanner' => false,
                'cupon' => null,
                'porcentaje_descuento' => null
            ]);
        }

        // Revisamos si el cliente ya usó ese cupón
        $yaUso = CuponUsado::where('cliente_id', $cliente->id)
            ->where('cupon_id', $cupon->id)
            ->exists();
        
        return response()->json([
            'mostrarBanner' => !$yaUso,
            'cupon' => $cupon->codigo,
            'porcentaje_descuento' => $cupon->porcentaje_descuento,
        ]);

    }

    private function getCuponBienvenida(): ?Cupon
    {
        return Cupon::where('solo_nuevos_usuarios', true)
            ->whereNull('fecha_expiracion')
            ->first();
    }

}
