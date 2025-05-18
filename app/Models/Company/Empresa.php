<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company\PedidoEmpresa;
use App\Models\Company\Producto;

class Empresa extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'telefono',
        'nif',
        'user_id',
    ];

    // Relación inversa 1:1 con User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación 1:N con Producto
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function pedidosEmpresa(): HasMany
    {
        return $this->hasMany(PedidoEmpresa::class);
    }

}
