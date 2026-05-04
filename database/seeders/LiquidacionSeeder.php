<?php

namespace Database\Seeders;

use App\Enums\MetodoDistribucionLiquidacion;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Liquidacion;
use App\Models\LiquidacionConcepto;
use App\Models\LiquidacionDetalle;
use App\Models\Presupuesto;
use Illuminate\Database\Seeder;

class LiquidacionSeeder extends Seeder
{
    public function run(): void
    {
        $consorcio = Consorcio::query()->firstOrFail();
        $presupuesto = Presupuesto::query()->firstOrFail();
        $unidades = $consorcio->unidades()->get();
        $coefTotal = (float) $unidades->sum('coeficiente');

        $liquidacion = Liquidacion::create([
            'presupuesto_id' => $presupuesto->id,
            'consorcio_id' => $consorcio->id,
            'periodo' => $presupuesto->periodo,
            'total_ordinario' => 420000,
            'total_extraordinario' => 120000,
            'total_general' => 540000,
            'fecha_primer_vto' => now()->startOfMonth()->addDays(9)->toDateString(),
            'fecha_segundo_vto' => now()->startOfMonth()->addDays(19)->toDateString(),
            'monto_segundo_vto' => 561600,
        ]);

        ConceptoPresupuesto::query()->where('presupuesto_id', $presupuesto->id)->each(function (ConceptoPresupuesto $concepto) use ($liquidacion, $unidades, $coefTotal): void {
            $liquidacionConcepto = LiquidacionConcepto::create([
                'liquidacion_id' => $liquidacion->id,
                'concepto_presupuesto_id' => $concepto->id,
                'nombre' => $concepto->nombre,
                'monto_total' => $concepto->monto_total,
                'tipo' => $concepto->tipo->value,
                'metodo_distribucion' => MetodoDistribucionLiquidacion::Coeficiente->value,
                'solo_cocheras' => false,
            ]);

            foreach ($unidades as $unidad) {
                $ratio = $coefTotal > 0 ? ((float) $unidad->coeficiente / $coefTotal) : 0;
                LiquidacionDetalle::create([
                    'liquidacion_concepto_id' => $liquidacionConcepto->id,
                    'unidad_id' => $unidad->id,
                    'coeficiente_aplicado' => $unidad->coeficiente,
                    'monto_calculado' => round((float) $concepto->monto_total * $ratio, 2),
                    'excluido' => false,
                ]);
            }
        });
    }
}
