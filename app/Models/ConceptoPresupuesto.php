<?php

namespace App\Models;

use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptoPresupuesto extends Model
{
    protected $table = 'concepto_presupuestos';

    protected $fillable = [
        'presupuesto_id',
        'nombre',
        'rubro',
        'descripcion',
        'monto_total',
        'cuotas_total',
        'cuota_actual',
        'tipo',
        'aplica_cocheras',
        'monto_factura_real',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'rubro' => RubroConceptoPresupuesto::class,
            'monto_total' => 'decimal:2',
            'cuotas_total' => 'integer',
            'cuota_actual' => 'integer',
            'tipo' => TipoConceptoPresupuesto::class,
            'aplica_cocheras' => 'boolean',
            'monto_factura_real' => 'decimal:2',
            'orden' => 'integer',
        ];
    }

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
