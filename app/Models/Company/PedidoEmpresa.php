<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\Order\Pedido;
use App\Models\Company\Empresa;
use App\Models\Client\Order\DetallePedido;

class PedidoEmpresa extends Model
{
    public $fillable = [
        'pedido_id',
        'empresa_id',
        'estado_envio',
        'fecha_envio',
        'precio_total'
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_empresa_id');
    }

}
