<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Models\Client\Cliente;
use App\Models\Client\Direccion;
use App\Models\Poblacion;

class DireccionController extends Controller
{
    public function defaultAddress()
    {
        $user = JWTAuth::parseToken()->authenticate();
        //\Log::info("Usuario autenticado", ['user_id' => $user->id, 'email' => $user->email ?? 'no-email']);

        $cliente = $user->cliente;
        \Log::info("ID Cliente: " . $cliente->id);

        /*$cliente = Cliente::where('user_id', auth()->id())->first();
        \Log::info("ID Cliente: " . $cliente->id);*/

        // Cogemos la última dirección guardada
        $direccion = Direccion::with(['poblacion.provincia']) // Carga la población y la provincia asociada
            ->where('cliente_id', $cliente->id)
            ->latest()
            ->first();

        if (!$direccion) {
            return response()->json(['mensaje' => 'No hay dirección guardada'], 404);
        }

        // Construimos una respuesta más completa
        return response()->json([
            'id' => $direccion->id,
            'calle' => $direccion->calle,
            'piso' => $direccion->piso,
            'puerta' => $direccion->puerta,
            'pais' => $direccion->pais,
            'codigo_postal' => $direccion->codigo_postal,
            'poblacion_id' => $direccion->poblacion_id,
            'provincia_id' => $direccion->poblacion->provincia_id ?? null
        ]);
    }

}
