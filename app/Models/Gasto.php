<?php

namespace App\Models;

use App\Enums\EstadoGasto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gasto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'consorcio_id',
        'proveedor_id',
        'nro_orden',
        'descripcion',
        'importe',
        'fecha_factura',
        'periodo',
        'estado',
        'fecha_pago',
        'comprobante_pago',
        'factura_archivo',
        'factura_nombre_sistema',
        'archivo_disponible_online',
        'fecha_archivado_local',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'fecha_factura' => 'date',
            'periodo' => 'date',
            'estado' => EstadoGasto::class,
            'fecha_pago' => 'date',
            'archivo_disponible_online' => 'boolean',
            'fecha_archivado_local' => 'date',
        ];
    }

    public function getArchivoProximoVencerAttribute(): bool
    {
        if (! $this->archivo_disponible_online || ! $this->fecha_factura) {
            return false;
        }

        $months = Carbon::parse($this->fecha_factura)->diffInMonths(now());

        return $months >= 11 && $months < 12;
    }

    public function consorcio(): BelongsTo
    {
        return $this->belongsTo(Consorcio::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function conceptosPresupuesto(): BelongsToMany
    {
        return $this->belongsToMany(ConceptoPresupuesto::class, 'gasto_concepto_presupuesto')
            ->withPivot('importe_asignado')
            ->withTimestamps();
    }
}
