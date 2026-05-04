<?php

namespace Tests\Feature;

use App\Enums\EstadoPresupuesto;
use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use App\Livewire\Presupuestos\PresupuestoEditor;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Presupuesto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PresupuestoAjusteFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_pending_adjustment_when_next_budget_is_created(): void
    {
        $consorcio = Consorcio::query()->create([
            'nombre' => 'Consorcio Centro',
            'direccion' => 'Av. Principal 100',
            'cuit' => '20-12345678-9',
            'dia_primer_vencimiento' => 10,
            'dia_segundo_vencimiento' => 20,
            'recargo_segundo_vto' => 10.0,
        ]);

        $presupuestoMarzo = Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-03-01',
            'estado' => EstadoPresupuesto::Finalizado->value,
        ]);

        $conceptoMarzo = ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuestoMarzo->id,
            'nombre' => 'Mantenimiento',
            'rubro' => RubroConceptoPresupuesto::Mantenimiento->value,
            'monto_total' => 30000,
            'monto_factura_real' => 40000,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'cuotas_total' => 1,
            'cuota_actual' => 1,
            'orden' => 1,
        ]);

        Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-04-01',
            'estado' => EstadoPresupuesto::Finalizado->value,
        ]);

        Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-05-01',
            'estado' => EstadoPresupuesto::Finalizado->value,
        ]);

        Livewire::test(PresupuestoEditor::class)
            ->set('consorcio_id', $consorcio->id)
            ->set('periodo', '2026-06')
            ->call('createPresupuesto');

        $presupuestoJunio = Presupuesto::query()
            ->where('consorcio_id', $consorcio->id)
            ->whereDate('periodo', '2026-06-01')
            ->firstOrFail();

        $this->assertDatabaseHas('concepto_presupuestos', [
            'presupuesto_id' => $presupuestoJunio->id,
            'nombre' => 'Ajuste '.$conceptoMarzo->nombre,
            'monto_total' => 10000.00,
            'descripcion' => 'Generado automáticamente por diferencia con factura real del período anterior.',
        ]);
    }
}
