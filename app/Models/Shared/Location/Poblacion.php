<?php

namespace App\Models\Shared\Location;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Cliente\Direccion;
use App\Models\Shared\Location\Provincia;

class Poblacion extends Model
{
    use HasFactory;

    protected $table = 'poblaciones';

    protected $fillable = ['nombre', 'provincia_id'];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(Direccion::class);
    }

}
