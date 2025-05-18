<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\Client\Order\Pedido;

class ContactoEntrega extends Model
{
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'telefono',
    ];

    public function pedido(): HasOne
    {
        return $this->hasOne(Pedido::class);
    }

}
