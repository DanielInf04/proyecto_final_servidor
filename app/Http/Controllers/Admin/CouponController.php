<?php

namespace App\Http\Controllers\Admin;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Shared\Cupon;

class CouponController extends Controller
{
    public function getMyCoupons()
    {
        return response()->json(Cupon::all());
    }

    public function insertCoupon(Request $request)
    {

        $validated = $request->validate([
            'codigo' => 'required|string|unique:cupones,codigo',
            'porcentaje_descuento' => 'required|integer|min:1|max:100',
            'solo_nuevos_usuarios' => 'nullable|boolean',
            'fecha_expiracion' => 'nullable|date|after_or_equal:today',
        ]);

        $cupon = Cupon::create([
            'codigo' => $validated['codigo'],
            'porcentaje_descuento' => $validated['porcentaje_descuento'],
            'solo_nuevos_usuarios' => $validated['solo_nuevos_usuarios'] ?? false,
            'fecha_expiracion' => $validated['fecha_expiracion'] ?? null,
        ]);

        return response()->json([
            'mensaje' => 'Cupón creado con éxito',
            'cupon' => $cupon
        ], 201);
    }

    public function deleteCoupon($id)
    {
        $cupon = Cupon::find($id);
        $cupon->delete();

        return response()->json(['message' => 'Cupón eliminado correctamente'], 200);
    }

}
