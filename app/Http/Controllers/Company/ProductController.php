<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Company\Producto;
use App\Models\Company\ProductoImagen;

class ProductController extends Controller
{
    // 🔒 Obtener los productos de la empresa autenticada (requiere JWT)
    public function getMyProducts(Request $request)
    {
        \Log::info("Estamos en el método getMyProducts");
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', '');

        $perPage = $request->input('per_page', 10);
        $productos = $empresa->productos()->with('imagenes')->paginate($perPage);

        // 🔁 Transformamos los productos dentro de la paginación
        $productos->getCollection()->transform(function ($producto) use ($rutaImagenes) {
            $imagenesUrls = $producto->imagenes->map(function ($imagen) use ($rutaImagenes) {
                return asset($rutaImagenes . '/' . $imagen->imagen);
            });

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => $producto->precio_base,
                'iva_porcentaje' => $producto->categoria->iva_porcentaje,
                'precio_con_iva' => round($producto->precio_base * (1 + $producto->categoria->iva_porcentaje), 2),
                'stock' => $producto->stock,
                'estado' => $producto->estado,
                'imagenes' => $imagenesUrls
            ];
        });

        return response()->json($productos);
    }

    // Añadir nuevos productos a la empresa autenticada
    public function insertProduct(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
    
        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'Solo empresas pueden crear productos'], 403);
        }
    
        $empresa = $user->empresa;
        
        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }
    
        $validated = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric',
            'stock' => 'required|integer',
            //'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048', // O manejar upload real
            'imagenes.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'categoria_id' => 'nullable|integer',
            'precio_oferta' => 'nullable|numeric',
            'oferta_activa' => 'nullable|boolean',
        ]);
    
        $producto = new Producto;
        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->precio_base = $request->precio_base;
        $producto->stock = $request->stock;
        $producto->categoria_id = $request->categoria_id;
        $producto->empresa_id = $empresa->id;

        // Si hay oferta activa y precio_ofeta
        if ($request->has('oferta_activa') && $request->oferta_activa && $request->precio_oferta) {
            $producto->oferta_activa = true;
            $producto->precio_oferta = $request->precio_oferta;
            $producto->descuento_porcentaje = round((1 - $request->precio_oferta / $request->precio_base) * 100);
        } else {
            $producto->oferta_activa = false;
            $producto->precio_oferta = null;
            $producto->descuento_porcentaje = null;
        }

        $producto->save();

        // Procesar imágenes y guardarlas
        if ($request->file('imagenes')) {
            $rutaImagenes = env('RUTA_IMAGENES', '');

            foreach ($request->file('imagenes') as $imagen) {
                $filename = $empresa->nombre . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
                $imagen->move(public_path($rutaImagenes), $filename);

                // Guardar la imagen asociada al producto
                ProductoImagen::create([
                    'producto_id' => $producto->id,
                    'imagen' => $filename,
                ]);
            }
        }

        $producto->save();

        //return response()->json($producto, 201);
        return response()->json($producto->load('imagenes'), 201);
    }



    /* function updateProduct(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        $producto = Producto::where('id', $id)
            ->where('empresa_id', $empresa->id)
            ->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado o no autorizado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'categoria_id' => 'nullable|integer'
        ]);

        // Si viene una nueva imagen, eliminamos la anterior
        if ($request->file('imagen')) {
            $rutaImagenes = env('RUTA_IMAGENES', '');
            $imagenAntigua = $producto->imagen;

            if ($imagenAntigua && file_exists(public_path($rutaImagenes . '/' . $imagenAntigua))) {
                unlink(public_path($rutaImagenes . '/' . $imagenAntigua));
            }

            // Guardamos la nueva imagen
            $file = $request->file('imagen');
            $filename = $empresa->nombre . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($rutaImagenes), $filename);
            $producto->imagen = $filename;
        }

        // Actualizamos los demás campos
        $producto->fill($validated);
        $producto->save();

        return response()->json($producto, 200);
    }*/

    public function updateProduct(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        $producto = Producto::where('id', $id)
            ->where('empresa_id', $empresa->id)
            ->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado o no autorizado'], 404);
        }

        // ✅ Validación de los campos, incluyendo oferta
        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'precio_base' => 'sometimes|numeric|min:0.01',
            'precio_oferta' => 'nullable|numeric|min:0.01',
            'oferta_activa' => 'nullable|boolean',
            'stock' => 'sometimes|integer|min:0',
            'categoria_id' => 'nullable|integer',
            'imagenes.*' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // ✅ Actualizar campos normales
        $producto->fill($validated);

        // ✅ Lógica de oferta
        if ($request->has('oferta_activa') && $request->boolean('oferta_activa') && $request->filled('precio_oferta') && $request->precio_base > 0) {
            $producto->oferta_activa = true;
            $producto->precio_oferta = $request->precio_oferta;
            $producto->descuento_porcentaje = round((1 - $request->precio_oferta / $request->precio_base) * 100);
        } else {
            $producto->oferta_activa = false;
            $producto->precio_oferta = null;
            $producto->descuento_porcentaje = null;
        }

        $producto->save();

        // ✅ Procesar imágenes nuevas (añadirlas sin eliminar las anteriores)
        if ($request->hasFile('imagenes')) {
            $rutaImagenes = env('RUTA_IMAGENES', 'imagenes_productos');

            foreach ($request->file('imagenes') as $imagen) {
                $filename = $empresa->nombre . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
                $imagen->move(public_path($rutaImagenes), $filename);

                ProductoImagen::create([
                    'producto_id' => $producto->id,
                    'imagen' => $filename
                ]);
            }
        }

        return response()->json($producto->load('imagenes'), 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        $producto = Producto::where('id', $id)
            ->where('empresa_id', $empresa->id)
            ->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado o no pertenece a la empresa'], 404);
        }

        $validated = $request->validate([
            'estado' => 'required|in:activo,inactivo'
        ]);

        $producto->estado = $request->estado;
        $producto->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'producto' => $producto
        ], 200);
    }

    public function deleteProduct($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = $user->empresa;

        $producto = Producto::with('imagenes')->where('id', $id)->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado o no autorizado'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', 'imagenes_productos');

        // Eliminar imágenes físicas y registros
        foreach ($producto->imagenes as $imagen) {
            $pathToFile = public_path($rutaImagenes . '/' . $imagen->imagen);

            if (file_exists($pathToFile)) {
                unlink($pathToFile);
                \Log::info("Imagen eliminada: {$pathToFile}");
            } else {
                \Log::warning("Imagen no encontrada: {$pathToFile}");
            }

            // Eliminar el registro de imagen
            $imagen->delete();
        }

        // Eliminar el producto en sí
        $producto->delete();

        return response()->json(['message' => 'Producto e imágenes eliminados correctamente'], 200);
    }

}
