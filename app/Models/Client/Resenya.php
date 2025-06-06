<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\Cliente;
use App\Models\Company\Producto;

class Resenya extends Model
{
    use HasFactory;

    protected $fillable = [
        'comentario',
        'valoracion',
        'fecha',
        'cliente_id',
        'producto_id'
    ];

    // Relación inversa con Usuario
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación inversa con Producto
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

}
