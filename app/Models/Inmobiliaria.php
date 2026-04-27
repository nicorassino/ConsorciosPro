<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inmobiliaria extends Model
{
    protected $fillable = [
        'unidad_id',
        'nombre',
        'apellido',
        'telefono',
        'email',
        'direccion',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }
}
