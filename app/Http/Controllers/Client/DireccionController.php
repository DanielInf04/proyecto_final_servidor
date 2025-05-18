<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Client\Cliente;
use App\Models\Client\Direccion;
use App\Models\Poblacion;

class DireccionController extends Controller
{
    public function defaultAddress()
    {
        $cliente = Cliente::where('user_id', auth()->id())->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Cogemos la última dirección guardada
        $direccion = Direccion::with(['poblacion.provincia']) // Carga la población y la provincia asociada
            ->where('cliente_id', $cliente->id)
            ->latest()
            ->first();

        if (!$direccion) {
            return response()->json(['mensaje' => 'No hay dirección guardada'], 204);
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
