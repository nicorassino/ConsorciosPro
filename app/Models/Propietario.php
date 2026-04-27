<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Propietario extends Model
{
    protected $fillable = [
        'unidad_id',
        'nombre',
        'dni',
        'direccion_postal',
        'email',
        'telefono',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function contactosAlternativos(): HasMany
    {
        return $this->hasMany(ContactoAlternativo::class, 'contactable_id')
            ->where('contactable_type', 'propietario');
    }
}
