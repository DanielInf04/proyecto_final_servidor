<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User;
use App\Models\Client\Resenya;
use App\Models\Client\Order\Pedido;
use App\Models\Client\Order\CuponUsado;

class Cliente extends Model
{
    use HasFactory;

    public function cuponesUsados(): HasMany
    {
        return $this->hasMany(CuponUsado::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function resenyas(): HasMany
    {
        return $this->hasMany(Resenya::class);
    }

}
