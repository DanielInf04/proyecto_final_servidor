<?php

namespace App\Models\Shared;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company\Producto;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'icono'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

}
