<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Shared\Location\Poblacion;
use App\Models\Shared\Location\Provincia;

class LocationController extends Controller
{
    public function getProvincias()
    {
        return response()->json(Provincia::all());
    }

    public function getPoblacionesPorProvincia($provinciaId)
    {
        $poblaciones = Poblacion::where('provincia_id', $provinciaId)->get();

        if ($poblaciones->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron poblaciones para esa provincia'
            ], 404);
        }
        return response()->json($poblaciones);
    }

}
