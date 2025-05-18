<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company\Producto;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';

    protected $fillable = [
        'imagen',
        'producto_id'
    ];

    public function producto(): BelongsTo {
        return $this->belongsTo(Producto::class);
    }
}
