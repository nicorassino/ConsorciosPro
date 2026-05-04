<?php

namespace Database\Seeders;

use App\Models\Cobranza;
use App\Models\Consorcio;
use App\Models\CuentaBancaria;
use App\Models\Gasto;
use App\Models\MovimientoFondo;
use Illuminate\Database\Seeder;

class MovimientoFondoSeeder extends Seeder
{
    public function run(): void
    {
        $consorcio = Consorcio::query()->firstOrFail();

        $cuenta = CuentaBancaria::create([
            'consorcio_id' => $consorcio->id,
            'nombre' => 'Cuenta Recaudadora SIRO',
            'cbu' => '2850590940090418135201',
            'saldo_actual' => 0,
        ]);

        $saldo = 0.0;

        Cobranza::query()->orderBy('fecha_pago')->each(function (Cobranza $cobranza) use ($cuenta, &$saldo): void {
            $saldo += (float) $cobranza->total_pagado;

            MovimientoFondo::create([
                'cuenta_id' => $cuenta->id,
                'fecha' => $cobranza->fecha_pago,
                'tipo' => 'ingreso',
                'monto' => $cobranza->total_pagado,
                'descripcion' => 'Acreditación SIRO unidad '.$cobranza->unidad_id,
                'referencia_type' => Cobranza::class,
                'referencia_id' => $cobranza->id,
                'saldo_resultante' => $saldo,
            ]);
        });

        Gasto::query()->where('estado', 'pagado')->orderBy('fecha_pago')->each(function (Gasto $gasto) use ($cuenta, &$saldo): void {
            $saldo -= (float) $gasto->importe;

            MovimientoFondo::create([
                'cuenta_id' => $cuenta->id,
                'fecha' => $gasto->fecha_pago ?? now()->toDateString(),
                'tipo' => 'egreso',
                'monto' => $gasto->importe,
                'descripcion' => 'Pago gasto '.$gasto->nro_orden,
                'referencia_type' => Gasto::class,
                'referencia_id' => $gasto->id,
                'saldo_resultante' => $saldo,
            ]);
        });

        $cuenta->update(['saldo_actual' => $saldo]);
    }
}
