<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Liquidacion extends Model
{
    use SoftDeletes;

    protected $table = 'liquidaciones';

    protected $fillable = [
        'presupuesto_id',
        'consorcio_id',
        'periodo',
        'total_ordinario',
        'total_extraordinario',
        'total_general',
        'fecha_primer_vto',
        'fecha_segundo_vto',
        'monto_segundo_vto',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'date',
            'total_ordinario' => 'decimal:2',
            'total_extraordinario' => 'decimal:2',
            'total_general' => 'decimal:2',
            'fecha_primer_vto' => 'date',
            'fecha_segundo_vto' => 'date',
            'monto_segundo_vto' => 'decimal:2',
        ];
    }

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function consorcio(): BelongsTo
    {
        return $this->belongsTo(Consorcio::class);
    }

    public function conceptos(): HasMany
    {
        return $this->hasMany(LiquidacionConcepto::class);
    }
}
