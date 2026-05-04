<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoFondo extends Model
{
    protected $table = 'movimientos_fondos';

    protected $fillable = [
        'cuenta_id',
        'fecha',
        'tipo',
        'monto',
        'descripcion',
        'referencia_type',
        'referencia_id',
        'saldo_resultante',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
            'saldo_resultante' => 'decimal:2',
        ];
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_id');
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }
}
