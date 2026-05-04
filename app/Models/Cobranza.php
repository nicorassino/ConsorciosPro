<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cobranza extends Model
{
    protected $table = 'cobranzas';

    protected $fillable = [
        'consorcio_id',
        'unidad_id',
        'fecha_pago',
        'monto_capital',
        'monto_interes',
        'total_pagado',
        'medio_pago',
        'comprobante_path',
        'liquidacion_detalle_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date',
            'monto_capital' => 'decimal:2',
            'monto_interes' => 'decimal:2',
            'total_pagado' => 'decimal:2',
        ];
    }

    public function consorcio(): BelongsTo
    {
        return $this->belongsTo(Consorcio::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function liquidacionDetalle(): BelongsTo
    {
        return $this->belongsTo(LiquidacionDetalle::class);
    }
}
