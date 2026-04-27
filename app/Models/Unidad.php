<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
    use SoftDeletes;

    protected $table = 'unidades';

    protected $fillable = [
        'consorcio_id',
        'numero',
        'nro_ph',
        'coeficiente',
        'nomenclatura_catastral',
        'nro_cuenta_rentas',
        'tiene_cochera',
        'nro_cochera',
        'estado_ocupacion',
        'nro_cupon_siro',
        'codigo_pago_electronico',
        'recibos_a_nombre_de',
        'condicion_iva',
        'email_expensas_ordinarias',
        'email_expensas_extraordinarias',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'coeficiente' => 'decimal:6',
            'tiene_cochera' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function consorcio(): BelongsTo
    {
        return $this->belongsTo(Consorcio::class);
    }
}
