<?php

namespace App\Models;

use App\Enums\EstadoPresupuesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presupuesto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'consorcio_id',
        'periodo',
        'estado',
        'presupuesto_anterior_id',
        'dia_primer_vencimiento_real',
        'dia_segundo_vencimiento_real',
        'recargo_segundo_vto_real',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'date',
            'estado' => EstadoPresupuesto::class,
            'dia_primer_vencimiento_real' => 'integer',
            'dia_segundo_vencimiento_real' => 'integer',
            'recargo_segundo_vto_real' => 'decimal:2',
        ];
    }

    public function consorcio(): BelongsTo
    {
        return $this->belongsTo(Consorcio::class);
    }

    public function presupuestoAnterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'presupuesto_anterior_id');
    }

    public function conceptos(): HasMany
    {
        return $this->hasMany(ConceptoPresupuesto::class);
    }

    public function liquidacion(): HasOne
    {
        return $this->hasOne(Liquidacion::class);
    }
}
