<?php

namespace App\Models\Client\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\Order\CuponUsado;
use App\Models\Client\Cliente;
use App\Models\Client\Order\DetallePedido;
use App\Models\Client\Direccion;
use App\Models\Company\PedidoEmpresa;
use App\Models\Client\Pago;
use App\Models\Client\ContactoEntrega;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'direccion_id',
        'contacto_entrega_id',
        'estado',
        'total'
    ];

    public function cuponUsado(): HasOne
    {
        return $this->hasOne(CuponUsado::class);
    }

    // Relación inversa 1:N con User
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relación 1:N con DetallePedido
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    // Relación 1:1 con Dirección
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class);
    }

    public function pedidoEmpresas(): HasMany
    {
        return $this->hasMany(PedidoEmpresa::class);
    }

    // Relación 1:1 con Pago
    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class);
    }

    public function contactoEntrega(): BelongsTo
    {
        return $this->belongsTo(ContactoEntrega::class);
    }

}
