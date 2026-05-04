<?php

namespace Database\Seeders;

use App\Models\Cobranza;
use App\Models\Consorcio;
use App\Models\LiquidacionDetalle;
use Illuminate\Database\Seeder;

class CobranzaSeeder extends Seeder
{
    public function run(): void
    {
        $consorcio = Consorcio::query()->firstOrFail();

        $detalles = LiquidacionDetalle::query()
            ->with('unidad')
            ->orderBy('id')
            ->limit(4)
            ->get();

        foreach ($detalles as $index => $detalle) {
            $capital = round((float) $detalle->monto_calculado, 2);
            $interes = $index % 2 === 0 ? 0 : round($capital * 0.02, 2);

            Cobranza::create([
                'consorcio_id' => $consorcio->id,
                'unidad_id' => $detalle->unidad_id,
                'fecha_pago' => now()->startOfMonth()->addDays(9 + $index)->toDateString(),
                'monto_capital' => $capital,
                'monto_interes' => $interes,
                'total_pagado' => $capital + $interes,
                'medio_pago' => 'siro',
                'comprobante_path' => 'cobranzas/comprobante-'.$detalle->id.'.pdf',
                'liquidacion_detalle_id' => $detalle->id,
            ]);
        }
    }
}
