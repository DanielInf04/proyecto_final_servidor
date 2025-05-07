<?php

namespace App\Models\Shared\Location;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Shared\Location\Poblacion;

class Provincia extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function poblaciones(): HasMany
    {
        return $this->hasMany(Poblacion::class);
    }

}
