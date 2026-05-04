<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaBancaria extends Model
{
    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'consorcio_id',
        'nombre',
        'cbu',
        'saldo_actual',
    ];

    protected function casts(): array
    {
        return [
            'saldo_actual' => 'decimal:2',
        ];
    }

    public function consorcio(): BelongsTo
    {
        return $this->belongsTo(Consorcio::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoFondo::class, 'cuenta_id');
    }
}
