<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiquidacionDetalle extends Model
{
    protected $table = 'liquidacion_detalles';

    protected $fillable = [
        'liquidacion_concepto_id',
        'unidad_id',
        'coeficiente_aplicado',
        'monto_calculado',
        'excluido',
        'porcentaje_manual',
    ];

    protected function casts(): array
    {
        return [
            'coeficiente_aplicado' => 'decimal:6',
            'monto_calculado' => 'decimal:2',
            'excluido' => 'boolean',
            'porcentaje_manual' => 'decimal:6',
        ];
    }

    public function liquidacionConcepto(): BelongsTo
    {
        return $this->belongsTo(LiquidacionConcepto::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function cobranzas(): HasMany
    {
        return $this->hasMany(Cobranza::class);
    }
}
