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
        $categorias = Categoria::all()->map(function ($categoria) {
            if ($categoria->imagen) {
                $categoria->imagen = asset($categoria->imagen);
            }
            return $categoria;
        });

        return response()->json($categorias);
    }

    public function insertCategory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Solo los administradores pueden crear nuevas categorias'], 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string',
            'iva_porcentaje' => 'required|numeric',
            'imagen' => 'file|image|mimetypes:image/jpeg,image/png,image/webp/image/avif|max:2049',
        ]);

        $categoria = new Categoria;
        $categoria->nombre = $request->nombre;
        $categoria->iva_porcentaje = $request->iva_porcentaje;

        // Procesamos la imagen y la guardamos
        if ($request->file('imagen')) {
            $imagen = $request->file('imagen');
            $rutaImagenes = env('RUTA_IMAGENES_CATEGORIAS', '');

            $nombreArchivo = $categoria->nombre . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

            $imagen->move(public_path($rutaImagenes), $nombreArchivo);

            $categoria->imagen = $rutaImagenes . '/' . $nombreArchivo;
        }

        $categoria->save();

        return response()->json($categoria, 201);

    }

    public function getCategory($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoria no encontrada'], 404);
        }

        // Agregamos la URL completa de la imagen
        $categoria->imagen = url($categoria->imagen);

        return response()->json($categoria, 200);

    }

    public function getCategoryImage($id)
    {
        $categoria = Categoria::find($id);
        $rutaImagenes = env('RUTA_IMAGENES_CATEGORIAS', '');

        if ($categoria) {
            $pathToFile = public_path($categoria->imagen);

            \Log::info("Devolviendo imagen con ruta de la categoria:  {$pathToFile}");

            if (file_exists($pathToFile)) {
                $headers = ['Content-Type' => mime_content_type($pathToFile)];
                return response()->file($pathToFile, $headers);
            } else {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }
        }

        return response()->json(['error' => 'Categoria sin imagen'], 404);
    }

    public function updateCategory(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoria no encontrada'], 404);
        }

        // Si se sube una nueva imagen
        if ($request->hasFile('imagen')) {
            // Eliminamos la imagen anterior si existe
            if ($categoria->imagen && file_exists(public_path($categoria->imagen))) {
                unlink(public_path($categoria->imagen));
                \Log::info("Imagen anterior eliminada" . public_path($categoria->imagen));
            }

            // Guardamos la nueva imagen
            $file = $request->file('imagen');
            $filename = $request->nombre . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $ruta = env('RUTA_IMAGENES_CATEGORIAS');

            // Guardamos el archivo
            $file->move(public_path($ruta), $filename);

            // Actualizamos el campo imagen
            $categoria->imagen = $ruta . '/' . $filename;
        }

        // Actualizamos otros campos
        $categoria->nombre = $request->nombre;
        $categoria->iva_porcentaje = $request->iva_porcentaje;
        $categoria->save();

        return response()->json(['message' => 'Categoría actualizada correctamente', 'categoria' => $categoria], 200);

        //$categoria->update($request->all());

        //return $categoria;
    }

    public function deleteCategory($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoria no encontrada o no autorizado'], 404);
        }

        // Eliminamos la imagen asociada, si existe
        if ($categoria->imagen) {
            $pathToFile = public_path($categoria->imagen);

            if (file_exists($pathToFile)) {
                unlink($pathToFile);
                \Log::info("Imagen eliminada: {$pathToFile}");
            } else {
                \Log::warning("Imagen no encontrada: {$pathToFile}");
            }
        }

        $categoria->delete();
        
        return response()->json(['message' => 'Categoria eliminada correctamente'], 200);
    }
    

}
