<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;
use App\Models\DireccionEmpresa;
use App\Models\Empresa;
use App\Models\User;

class ApiController extends Controller
{

    /*function getUsers() {
        return User::all();
    }*/

    /*function insertUser(Request $request)
    {
        
    }

    function getEmpresas() {
        return Empresa::with('direccionEmpresa')->get();
        //return Empresa::all();
    }

    function insertEmpresa(Request $request) {
        
        // Crear la empresa
        $empresa = Empresa::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'telefono'    => $request->telefono,
            'nif'         => $request->nif,
            'user_id'     => $request->user_id,
        ]);

        // Crear la dirección asociada usando el id de la empresa requién creada
        DireccionEmpresa::create([
            'calle'          => $request->calle,
            'codigo_postal'  => $request->codigo_postal,
            'ciudad'         => $request->ciudad,
            'provincia'      => $request->provincia,
            'pais'           => $request->pais,
            'empresa_id'     => $empresa->id,
        ]);

        return $empresa->load('direccionEmpresa');

    }

    function deleteEmpresa($id) {

        // Buscar la empresa por su ID
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        try {
            DB::beginTransaction();

            // Eliminar la dirección asociada (si existe)
            DireccionEmpresa::where('empresa_id', $id)->delete();

            // Eliminar la empresa
            $empresa->delete();

            DB::commit();

            return response()->json(['message' => 'Empresa eliminada correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la empresa: ' . $e->getMessage()], 500);
        }

    }*/

}
