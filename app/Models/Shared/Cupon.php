<?php

namespace App\Models\Shared;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\Client\CuponUsado;

class Cupon extends Model
{

    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'porcentaje_descuento',
        'solo_nuevos_usuarios',
        'fecha_expiracion',
    ];

    public function usos(): HasMany
    {
        return $this->hasMany(CuponUsado::class);
    }

}
