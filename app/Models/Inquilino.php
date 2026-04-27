<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inquilino extends Model
{
    protected $fillable = [
        'unidad_id',
        'nombre',
        'apellido',
        'telefono',
        'email',
        'direccion_postal',
        'fecha_fin_contrato',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_fin_contrato' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function contactosAlternativos(): HasMany
    {
        return $this->hasMany(ContactoAlternativo::class, 'contactable_id')
            ->where('contactable_type', 'inquilino');
    }
}
