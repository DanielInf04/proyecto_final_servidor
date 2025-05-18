<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\Resenya;
use App\Models\Client\DetallePedido;
use App\Models\Company\ProductoImagen;
use App\Models\Shared\Categoria;
use App\Models\Company\Empresa;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'stock',
        'imagen',
        'categoria_id',
        'empresa_id',
    ];

    // Relación inversa 1:N con Empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con tabla ProductoImagen
    public function imagenes(): HasMany
    {
        return $this->hasMany(ProductoImagen::class);
    }

    // Relación 1:N con DetallePedido
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    // Relación 1:N con Reseña
    public function resenyas(): HasMany
    {
        return $this->hasMany(Resenya::class);
    }


}
