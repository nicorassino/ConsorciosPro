<?php

namespace App\Models;

use App\Enums\CondicionIvaConsorcio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consorcio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'direccion',
        'cuit',
        'banco',
        'nro_cuenta_bancaria',
        'convenio',
        'sucursal',
        'digito_verificador',
        'cbu',
        'condicion_iva',
        'nro_cuenta_rentas',
        'nomenclatura_catastral',
        'nro_matricula',
        'fecha_inscripcion_reglamento',
        'unidad_facturacion_aguas',
        'tiene_cocheras',
        'encargado_nombre',
        'encargado_apellido',
        'encargado_telefono',
        'encargado_horarios',
        'encargado_dias',
        'encargado_empresa_servicio',
        'nombre_administracion',
        'logo_administracion',
        'texto_medios_pago',
        'dia_primer_vencimiento',
        'dia_segundo_vencimiento',
        'recargo_segundo_vto',
        'nota',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'condicion_iva' => CondicionIvaConsorcio::class,
            'fecha_inscripcion_reglamento' => 'date',
            'tiene_cocheras' => 'boolean',
            'dia_primer_vencimiento' => 'integer',
            'dia_segundo_vencimiento' => 'integer',
            'recargo_segundo_vto' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(Unidad::class);
    }

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function cuentasBancarias(): HasMany
    {
        return $this->hasMany(CuentaBancaria::class);
    }

    public function cobranzas(): HasMany
    {
        return $this->hasMany(Cobranza::class);
    }
}
