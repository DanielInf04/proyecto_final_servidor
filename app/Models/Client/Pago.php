<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\Order\Pedido;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'metodo_pago',
        'referencia',
        'estado',
    ];

    // Relación inversa 1:1 con Pedido
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }
}
