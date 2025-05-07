<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company\Producto;
use App\Models\Company\ProductoImagen;

class OfferController extends Controller
{
    public function index()
    {
        $rutaImagenes = env('RUTA_IMAGENES', '');

        $productos = Producto::with('imagenes', 'resenyas')
            ->where('estado', 'activo')
            ->where('oferta_activa', true) // Solo productos con oferta activa
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

                $precioOfertaConIva = round($producto->precio_oferta * (1 + $iva), 2);

                $oferta = [
                    'precio_oferta_con_iva' => $precioOfertaConIva,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'precio_original_con_iva' => $precioBaseConIva
                ];

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
}
