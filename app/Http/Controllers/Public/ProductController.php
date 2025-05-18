<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Shared\Categoria;
use App\Models\Company\Producto;
use App\Models\Company\ProductoImagen;

class ProductController extends Controller
{
    // Retornar 4 productos sin oferta y otros 4 con oferta para página principal
    public function index()
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');

        // Productos sin oferta
        $productosSinOferta = Producto::with('imagenes', 'resenyas', 'categoria')
            ->where('estado', 'activo')
            ->where('oferta_activa', false)
            ->inRandomOrder()
            ->take(10)
            ->get();

        // Productos con oferta
        $productosConOferta = Producto::with('imagenes', 'resenyas', 'categoria')
            ->where('estado', 'activo')
            ->where('oferta_activa', true)
            ->inRandomOrder()
            ->take(8)
            ->get();

        // Función reutilizable
        $formatear = function ($producto) use ($rutaImagenes) {
            $imagenUrl = optional($producto->imagenes->first())->imagen;
            $imagenUrl = $imagenUrl ? asset($rutaImagenes . '/' . $imagenUrl) : null;

            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

            $oferta = null;
            if ($producto->oferta_activa && $producto->precio_oferta) {
                $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);
                $oferta = [
                    'precio_oferta_con_iva' => $precioOfertaConIva,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'precio_original_con_iva' => $precioBaseConIva
                ];
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio_con_iva' => $precioBaseConIva,
                'stock' => $producto->stock,
                'valoracion_promedio' => $producto->resenyas->count() > 0
                    ? round($producto->resenyas->avg('valoracion'), 1)
                    : null,
                'imagen' => $imagenUrl,
                'oferta' => $oferta
            ];
        };

        return response()->json([
            'productos_sin_oferta' => $productosSinOferta->map($formatear),
            'productos_con_oferta' => $productosConOferta->map($formatear)
        ]);
    }

    /*public function index()
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');

        $productos = Producto::with('imagenes', 'resenyas', 'categoria')
            ->where('estado', 'activo')
            ->take(12) // Limitar cantidad
            ->get()
            ->map(function ($producto) use ($rutaImagenes) {
                // Obtener solo la primera imagen
                $imagenPrincipal = $producto->imagenes->first();
                $imagenUrl = $imagenPrincipal ? asset($rutaImagenes . '/' . $imagenPrincipal->imagen) : null;

                // Calcular promedio de valoraciones
                $promedioValoracion = $producto->resenyas->count() > 0
                    ? round($producto->resenyas->avg('valoracion'), 1)
                    : null;

                $iva = $producto->categoria->iva_porcentaje ?? 0;
                $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

                $oferta = null;
                if ($producto->oferta_activa && $producto->precio_oferta) {
                    $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);
                    $oferta = [
                        'precio_oferta_con_iva' => $precioOfertaConIva,
                        'descuento_porcentaje' => $producto->descuento_porcentaje,
                        'precio_original_con_iva' => $precioBaseConIva
                    ];
                }

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio_con_iva' => $precioBaseConIva,
                    'stock' => $producto->stock,
                    'valoracion_promedio' => $promedioValoracion,
                    'imagen' => $imagenUrl, // ← solo una imagen
                    'oferta' => $oferta
                ];
            });

        return response()->json($productos);
    }*/

    // Método para sacar todos los productos
    /*public function index()
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');

        $productos = Producto::with('imagenes', 'resenyas')
            ->where('estado', 'activo')
            ->get()
            ->map(function ($producto) use ($rutaImagenes) {
                $imagenesUrls = $producto->imagenes->map(function ($imagen) use ($rutaImagenes) {
                    return asset($rutaImagenes . '/' . $imagen->imagen);
                });

                // Calculamos el promedio de valoraciones (si hay)
                $promedioValoracion = $producto->resenyas->count() > 0
                ? round($producto->resenyas->avg('valoracion'), 1)
                : null;

                $iva = $producto->categoria->iva_porcentaje ?? 0;

                // Precio base con IVA
                $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

                // Si hay oferta activa, calculamos el precio con oferta + IVA
                $oferta = null;
                if ($producto->oferta_activa && $producto->precio_oferta) {
                    $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);

                    $oferta = [
                        'precio_oferta_con_iva' => $precioOfertaConIva,
                        'descuento_porcentaje' => $producto->descuento_porcentaje,
                        'precio_original_con_iva' => $precioBaseConIva
                    ];
                }

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    //'descripcion' => $producto->descripcion,
                    //'precio_con_iva' => round($producto->precio_base * (1 + $producto->categoria->iva_porcentaje), 2),
                    'precio_con_iva' => $precioBaseConIva,
                    'stock' => $producto->stock,
                    'valoracion_promedio' => $promedioValoracion,
                    'imagenes' => $imagenesUrls,
                    'oferta' => $oferta
                ];
            });

        return response()->json($productos);
    }*/

    public function search(Request $request)
    {
        $query = $request->query('query');
        $rutaImagenes = env('RUTA_IMAGENES', '');

        $productos = Producto::with('imagenes', 'resenyas', 'categoria')
        ->where('estado', 'activo')
        ->where(function ($q) use ($query) {
            $q->where('nombre', 'like', "%$query%")
              ->orWhere('descripcion', 'like', "%$query%");
        })
        ->get()
        ->map(function ($producto) use ($rutaImagenes) {
            $imagenesUrls = $producto->imagenes->map(function ($imagen) use ($rutaImagenes) {
                return asset($rutaImagenes . '/' . $imagen->imagen);
            });

            $promedioValoracion = $producto->resenyas->count() > 0
                ? round($producto->resenyas->avg('valoracion'), 1)
                : null;

            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

            $oferta = null;
            if ($producto->oferta_activa && $producto->precio_oferta) {
                $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);
                $oferta = [
                    'precio_oferta_con_iva' => $precioOfertaConIva,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'precio_original_con_iva' => $precioBaseConIva
                ];
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_con_iva' => $precioBaseConIva,
                'stock' => $producto->stock,
                'valoracion_promedio' => $promedioValoracion,
                'imagenes' => $imagenesUrls,
                'oferta' => $oferta
            ];
        });

        return response()->json($productos);
    }

    public function recommended(Request $request)
    {
        $context = $request->query('context');
        $productoId = $request->query('id');
        $rutaImagenes = env('RUTA_IMAGENES', '');

        if ($context === 'producto' && $productoId) {
            $productoBase = Producto::with('categoria')->find($productoId);
            if (!$productoBase) return response()->json([]);

            $productos = Producto::with('imagenes', 'resenyas', 'categoria')
                ->where('estado', 'activo')
                ->where('categoria_id', $productoBase->categoria_id)
                ->where('id', '!=', $productoBase->id)
                ->inRandomOrder()
                ->limit(4)
                ->get();
        } else {
            // Default: un producto aleatorio por categoría
            $categorias = Categoria::with(['productos' => fn($q) => $q->where('estado', 'activo')->inRandomOrder()->limit(1)])->get();
            $productos = $categorias->map->productos->filter()->flatten();
        }

        $recomendados = $productos->map(function ($p) use ($rutaImagenes) {
            $iva = $p->categoria->iva_porcentaje ?? 0;
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'precio_con_iva' => round($p->precio_base * (1 + $iva), 2),
                'imagenes' => $p->imagenes->map(fn($img) => asset($rutaImagenes . '/' . $img->imagen)),
                'valoracion_promedio' => $p->resenyas->avg('valoracion')
            ];
        });

        return response()->json($recomendados);
    }

    // Ver imagen principal de un producto (la primera)
    public function getProductImage($id)
    {
        $producto = Producto::with('imagenes')->find($id);
        $rutaImagenes = env('RUTA_IMAGENES', '');

        if ($producto && $producto->imagenes->isNotEmpty()) {
            $imagen = $producto->imagenes[0]->imagen;
            $pathToFile = public_path($rutaImagenes . '/' . $imagen);

            \Log::info("Devolviendo imagen con ruta: {$pathToFile}");

            if (file_exists($pathToFile)) {
                $headers = ['Content-Type' => mime_content_type($pathToFile)];
                return response()->file($pathToFile, $headers);
            } else {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }
        }

        return response()->json(['error' => 'Producto sin imágenes'], 404);
    }

    public function getProductImages($id)
    {
        $producto = Producto::with('imagenes')->find($id);
        $rutaImagenes = env('RUTA_IMAGENES', '');

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        if ($producto->imagenes->isEmpty()) {
            return response()->json(['imagenes' => []]); // sin error, solo lista vacía
        }

        $imagenesUrls = $producto->imagenes->map(function ($imagen) use ($rutaImagenes) {
            return asset($rutaImagenes . '/' . $imagen->imagen);
        });

        return response()->json([
            'producto_id' => $producto->id,
            'imagenes' => $imagenesUrls
        ]);
    }

    public function getProduct($id) 
    {
        $producto = Producto::with('imagenes', 'resenyas.cliente.usuario')->find($id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $rutaImagenes = env('RUTA_IMAGENES', 'imagenes_productos');
        $iva = $producto->categoria->iva_porcentaje ?? 0;

        // Convertimos el producto a array manualmente
        $productoArray = $producto->toArray();

        // Precio con IVA base
        $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);
        $productoArray['precio_con_iva'] = $precioBaseConIva;

        // Si tiene oferta, añadimos info de oferta
        if ($producto->oferta_activa && $producto->precio_oferta) {
            $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);

            $productoArray['oferta'] = [
                'precio_original_con_iva' => $precioBaseConIva,
                'precio_oferta_con_iva' => $precioOfertaConIva,
                'descuento_porcentaje' => $producto->descuento_porcentaje,
            ];
        }

        // Sobrescribimos las imágenes con solo las URLs deseadas
        $productoArray['imagenes'] = $producto->imagenes->map(function ($imagen) {
            return [
                'imagen' => url('/api/product/image/' . $imagen->id)
            ];
        })->toArray();

        // Formateamos las reseñas para mostrar lo necesario
        $productoArray['resenyas'] = $producto->resenyas->map(function ($resenya) {
            return [
                'comentario' => $resenya->comentario,
                'valoracion' => $resenya->valoracion,
                'fecha' => $resenya->fecha,
                'cliente' => [
                    'nombre' => optional($resenya->cliente?->usuario)->name ?? 'Usuario Desconocido'
                ]
                ];
        })->toArray();

        return response()->json($productoArray, 200);
    }

    /*public function getByCategory(Request $request, $id)
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Obtener todos los productos sin paginar aún
        $productos = Producto::with('imagenes', 'resenyas', 'categoria')
            ->where('estado', 'activo')
            ->where('categoria_id', $id)
            ->get();

        if ($productos->isEmpty()) {
            return response()->json([]);
        }

        // Transformamos productos y calculamos el precio final con IVA
        $productos = $productos->map(function ($producto) use ($rutaImagenes) {
            $imagenUrl = $producto->imagenes->first()
                ? asset($rutaImagenes . '/' . $producto->imagenes->first()->imagen)
                : null;

            $promedioValoracion = $producto->resenyas->count() > 0
                ? round($producto->resenyas->avg('valoracion'), 1)
                : null;

            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

            if ($producto->oferta_activa && $producto->precio_oferta) {
                $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);
                $precioFinal = $precioOfertaConIva;
                $oferta = [
                    'precio_oferta_con_iva' => $precioOfertaConIva,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'precio_original_con_iva' => $precioBaseConIva
                ];
            } else {
                $precioFinal = $precioBaseConIva;
                $oferta = null;
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_con_iva' => $precioFinal,
                'precio_ordenable' => $precioFinal,
                'stock' => $producto->stock,
                'valoracion_promedio' => $promedioValoracion,
                'imagen' => $imagenUrl,
                'oferta' => $oferta
            ];
        });

        // Ordenamos por precio final con IVA (de menor a mayor)
        $productos = $productos->sortBy('precio_ordenable')->values();

        // Paginación manual

        // Extraemos de la colección los elementos que pertenecen a la página actual
        $paged = $productos->forPage($page, $perPage);

        $response = new \Illuminate\Pagination\LengthAwarePaginator(
            $paged, // Elementos de esta página
            $productos->count(), // Total de elementos
            $perPage, // Elementos por página
            $page, // Página actual
            [
                'path' => $request->url(), // Mantiene la URL base
                'query' => $request->query() // Mantiene los parámetros ?page, ?sort, etc.
            ] 
        );

        return response()->json($response);
    }*/


    // METODO QUE FUNCIONA PERFECTAMENTE
    public function getByCategory(Request $request, $id)
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');
        $perPage = $request->input('per_page', 10);

        $query = Producto::with('imagenes', 'resenyas', 'categoria')
            ->where('estado', 'activo')
            ->where('categoria_id', $id);

        $productos = $query->paginate($perPage);
        //$productos = $query->orderBy('')

        if ($productos->isEmpty()) {
            return response()->json([]);
        }

        $productos->getCollection()->transform(function ($producto) use ($rutaImagenes) {
            $imagenUrl = $producto->imagenes->first()
                ? asset($rutaImagenes . '/' . $producto->imagenes->first()->imagen)
                : null;

            $promedioValoracion = $producto->resenyas->count() > 0
                ? round($producto->resenyas->avg('valoracion'), 1)
                : null;

            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

            $oferta = null;
            if ($producto->oferta_activa && $producto->precio_oferta) {
                $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);
                $oferta = [
                    'precio_oferta_con_iva' => $precioOfertaConIva,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'precio_original_con_iva' => $precioBaseConIva
                ];
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_con_iva' => $precioBaseConIva,
                'stock' => $producto->stock,
                'valoracion_promedio' => $promedioValoracion,
                'imagen' => $imagenUrl,
                'oferta' => $oferta
            ];
        });

        return response()->json($productos);
    }

    /*public function getByCategory($id)
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');

        $productos = Producto::with('imagenes', 'resenyas', 'categoria')
            ->where('estado', 'activo')
            ->where('categoria_id', $id)
            ->get();

        if ($productos->isEmpty()) {
            return response()->json([]);
        }

        $productos = $productos->map(function ($producto) use ($rutaImagenes) {
            $imagenesUrls = $producto->imagenes->map(function ($imagen) use ($rutaImagenes) {
                return asset($rutaImagenes . '/' . $imagen->imagen);
            });

            $promedioValoracion = $producto->resenyas->count() > 0
                ? round($producto->resenyas->avg('valoracion'), 1)
                : null;

            $iva = $producto->categoria->iva_porcentaje ?? 0;
            $precioBaseConIva = round($producto->precio_base * (1 + $iva), 2);

            $oferta = null;
            if ($producto->oferta_activa && $producto->precio_oferta) {
                $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);
                $oferta = [
                    'precio_oferta_con_iva' => $precioOfertaConIva,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'precio_original_con_iva' => $precioBaseConIva
                ];
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_con_iva' => $precioBaseConIva,
                'stock' => $producto->stock,
                'valoracion_promedio' => $promedioValoracion,
                'imagenes' => $imagenesUrls,
                'oferta' => $oferta
            ];
        });

        return response()->json($productos);
    }*/

}
