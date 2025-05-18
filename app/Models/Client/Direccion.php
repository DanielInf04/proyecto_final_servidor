<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\Order\Pedido;
use App\Models\Shared\Location\Poblacion;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'cliente_id',
        'calle',
        'puerta',
        'piso',
        'codigo_postal',
        //'ciudad',
        //'provincia',
        'poblacion_id',
        'pais',
    ];

    // Relación Inversa 1:N con Pedido
    public function pedidos(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function poblacion(): BelongsTo
    {
        return $this->belongsTo(Poblacion::class);
    }

}
