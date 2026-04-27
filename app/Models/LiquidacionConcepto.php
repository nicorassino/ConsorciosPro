<?php

namespace App\Models;

use App\Enums\MetodoDistribucionLiquidacion;
use App\Enums\TipoConceptoPresupuesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiquidacionConcepto extends Model
{
    protected $table = 'liquidacion_conceptos';

    protected $fillable = [
        'liquidacion_id',
        'concepto_presupuesto_id',
        'nombre',
        'monto_total',
        'tipo',
        'metodo_distribucion',
        'solo_cocheras',
    ];

    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'tipo' => TipoConceptoPresupuesto::class,
            'metodo_distribucion' => MetodoDistribucionLiquidacion::class,
            'solo_cocheras' => 'boolean',
        ];
    }

    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(Liquidacion::class);
    }

    public function conceptoPresupuesto(): BelongsTo
    {
        return $this->belongsTo(ConceptoPresupuesto::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(LiquidacionDetalle::class);
    }
}
