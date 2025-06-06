<?php

namespace App\Models\Client\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company\Producto;
use App\Models\Company\PedidoEmpresa;

class DetallePedido extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_empresa_id', 'producto_id', 'cantidad', 'precio_unitario'];

    // Relación inversa con Pedido
    public function pedidoEmpresa(): BelongsTo
    {
        return $this->belongsTo(PedidoEmpresa::class);
    }

    // Relación inversa con Producto
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

}
