<?php

namespace App\Models\Client\Order;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Client\Order\Pedido;
use App\Models\Client\Cliente;
use App\Models\Shared\Cupon;

class CuponUsado extends Model
{
    public $fillable = [
        'cliente_id',
        'cupon_id',
        'pedido_id',
    ];

    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

}
