<?php

namespace Database\Seeders;

use App\Enums\EstadoPresupuesto;
use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Presupuesto;
use Illuminate\Database\Seeder;

class PresupuestoSeeder extends Seeder
{
    public function run(): void
    {
        $consorcio = Consorcio::query()->firstOrFail();

        $presupuesto = Presupuesto::create([
            'consorcio_id' => $consorcio->id,
            'periodo' => now()->startOfMonth()->toDateString(),
            'estado' => EstadoPresupuesto::Liquidado->value,
            'dia_primer_vencimiento_real' => 10,
            'dia_segundo_vencimiento_real' => 20,
            'recargo_segundo_vto_real' => 12.00,
            'notas' => 'Presupuesto sembrado para circuito E2E de fase 6.',
        ]);

        $conceptos = [
            ['nombre' => 'Limpieza', 'rubro' => RubroConceptoPresupuesto::Servicios, 'monto' => 180000, 'tipo' => TipoConceptoPresupuesto::Ordinario],
            ['nombre' => 'Ascensor', 'rubro' => RubroConceptoPresupuesto::Mantenimiento, 'monto' => 150000, 'tipo' => TipoConceptoPresupuesto::Ordinario],
            ['nombre' => 'Seguro integral', 'rubro' => RubroConceptoPresupuesto::Seguros, 'monto' => 90000, 'tipo' => TipoConceptoPresupuesto::Ordinario],
            ['nombre' => 'Fondo pintura', 'rubro' => RubroConceptoPresupuesto::Otros, 'monto' => 120000, 'tipo' => TipoConceptoPresupuesto::Extraordinario],
        ];

        foreach ($conceptos as $index => $concepto) {
            ConceptoPresupuesto::create([
                'presupuesto_id' => $presupuesto->id,
                'nombre' => $concepto['nombre'],
                'rubro' => $concepto['rubro']->value,
                'monto_total' => $concepto['monto'],
                'cuotas_total' => 1,
                'cuota_actual' => 1,
                'tipo' => $concepto['tipo']->value,
                'aplica_cocheras' => false,
                'monto_factura_real' => null,
                'orden' => $index + 1,
            ]);
        }
    }
}
