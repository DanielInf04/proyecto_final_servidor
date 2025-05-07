<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

use App\Models\Shared\Categoria;

class CategoryController extends Controller
{
    // Retorna todas las categorias
    public function categorias()
    {
        return response()->json(Categoria::all());
    }

    public function insertCategory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Solo los administradores pueden crear nuevas categorias'], 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string',
            'iva_porcentaje' => 'required|numeric'
        ]);

        $categoria = new Categoria;
        $categoria->nombre = $request->nombre;
        $categoria->iva_porcentaje = $request->iva_porcentaje;
        $categoria->save();

        return response()->json($categoria, 201);

    }

    public function getCategory($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoria no encontrada'], 404);
        }

        return response()->json($categoria, 200);

    }

    public function updateCategory(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $categoria = Categoria::find($request->id);
        $categoria->update($request->all());

        return $categoria;
    }

    public function deleteCategory($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $categoria = Categoria::find($id);
        $categoria->delete();
        
        return response()->json(['message' => 'Categoria eliminada correctamente'], 200);
    }
    

}
