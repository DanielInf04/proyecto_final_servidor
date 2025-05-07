<?php

namespace App\Models\Client\Order;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company\Producto;
use App\Models\Client\Cliente;

class Carrito extends Model
{
    protected $fillable = [
        'cliente_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

}
