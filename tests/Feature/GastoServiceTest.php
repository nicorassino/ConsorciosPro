<?php

namespace Tests\Feature;

use App\Enums\EstadoGasto;
use App\Enums\EstadoPresupuesto;
use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Presupuesto;
use App\Services\GastoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GastoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_gasto_and_syncs_monto_factura_real(): void
    {
        [$consorcio, $presupuesto, $conceptoA, $conceptoB] = $this->seedBudget();

        $service = app(GastoService::class);
        $service->save([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-1',
            'descripcion' => 'Factura ascensor',
            'importe' => 1000,
            'fecha_factura' => '2026-04-20',
            'periodo' => '2026-04-01',
            'estado' => EstadoGasto::Pendiente->value,
        ], [
            ['concepto_presupuesto_id' => $conceptoA->id, 'importe_asignado' => 600],
            ['concepto_presupuesto_id' => $conceptoB->id, 'importe_asignado' => 400],
        ]);

        $this->assertDatabaseHas('gastos', [
            'consorcio_id' => $consorcio->id,
            'nro_orden' => 'ORD-1',
        ]);
        $this->assertDatabaseHas('gasto_concepto_presupuesto', [
            'concepto_presupuesto_id' => $conceptoA->id,
            'importe_asignado' => 600,
        ]);
        $this->assertSame('600.00', $conceptoA->fresh()->monto_factura_real);
        $this->assertSame('400.00', $conceptoB->fresh()->monto_factura_real);

        $this->assertEquals(2, $presupuesto->conceptos()->count());
    }

    public function test_it_rejects_when_line_items_do_not_match_total(): void
    {
        [$consorcio, , $conceptoA, $conceptoB] = $this->seedBudget();

        $service = app(GastoService::class);

        $this->expectException(ValidationException::class);
        $service->save([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-2',
            'descripcion' => 'Factura limpieza',
            'importe' => 900,
            'fecha_factura' => '2026-04-20',
            'periodo' => '2026-04-01',
            'estado' => EstadoGasto::Pendiente->value,
        ], [
            ['concepto_presupuesto_id' => $conceptoA->id, 'importe_asignado' => 600],
            ['concepto_presupuesto_id' => $conceptoB->id, 'importe_asignado' => 400],
        ]);
    }

    public function test_it_allows_concept_from_other_period_of_same_consorcio(): void
    {
        [$consorcio, , $conceptoA] = $this->seedBudget();
        $conceptoOtherPeriod = $this->seedConceptForOtherPeriod($consorcio->id);

        $service = app(GastoService::class);
        $service->save([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-3',
            'descripcion' => 'Factura gas',
            'importe' => 1000,
            'fecha_factura' => '2026-04-20',
            'periodo' => '2026-04-01',
            'estado' => EstadoGasto::Pendiente->value,
        ], [
            ['concepto_presupuesto_id' => $conceptoA->id, 'importe_asignado' => 500],
            ['concepto_presupuesto_id' => $conceptoOtherPeriod->id, 'importe_asignado' => 500],
        ]);

        $this->assertSame('500.00', $conceptoA->fresh()->monto_factura_real);
        $this->assertSame('500.00', $conceptoOtherPeriod->fresh()->monto_factura_real);
    }

    public function test_it_creates_or_updates_ajuste_on_last_pending_budget_when_user_chooses_it(): void
    {
        [$consorcio, $presupuestoAbril, $conceptoA] = $this->seedBudget();
        $presupuestoJunio = Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-06-01',
            'estado' => EstadoPresupuesto::Borrador->value,
        ]);

        $service = app(GastoService::class);
        $service->save([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-4',
            'descripcion' => 'Factura ascensor con diferencia',
            'importe' => 1200,
            'fecha_factura' => '2026-04-21',
            'periodo' => '2026-04-01',
            'estado' => EstadoGasto::Pendiente->value,
            'ajuste_destino' => 'ultimo_pendiente',
        ], [
            ['concepto_presupuesto_id' => $conceptoA->id, 'importe_asignado' => 1200],
        ]);

        $this->assertDatabaseHas('concepto_presupuestos', [
            'presupuesto_id' => $presupuestoJunio->id,
            'nombre' => 'Ajuste Ascensor',
            'monto_total' => 200.00,
        ]);

        // Si cambia el valor real, se actualiza el mismo ajuste y no duplica.
        $gasto = \App\Models\Gasto::query()->where('nro_orden', 'ORD-4')->firstOrFail();
        $service->save([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-4',
            'descripcion' => 'Factura ascensor con diferencia actualizada',
            'importe' => 1100,
            'fecha_factura' => '2026-04-21',
            'periodo' => '2026-04-01',
            'estado' => EstadoGasto::Pendiente->value,
            'ajuste_destino' => 'ultimo_pendiente',
        ], [
            ['concepto_presupuesto_id' => $conceptoA->id, 'importe_asignado' => 1100],
        ], $gasto);

        $this->assertDatabaseHas('concepto_presupuestos', [
            'presupuesto_id' => $presupuestoJunio->id,
            'nombre' => 'Ajuste Ascensor',
            'monto_total' => 100.00,
        ]);
        $this->assertSame(
            1,
            ConceptoPresupuesto::query()
                ->where('presupuesto_id', $presupuestoJunio->id)
                ->where('nombre', 'Ajuste Ascensor')
                ->count()
        );

        // Evitamos warning por variable creada y dejamos el contrato del test explícito.
        $this->assertSame($presupuestoAbril->id, $conceptoA->presupuesto_id);
    }

    /**
     * @return array{0:Consorcio,1:Presupuesto,2:ConceptoPresupuesto,3:ConceptoPresupuesto}
     */
    private function seedBudget(): array
    {
        $consorcio = Consorcio::query()->create([
            'nombre' => 'Edificio Test',
            'direccion' => 'Calle 123',
            'cuit' => '20-12345678-9',
        ]);

        $presupuesto = Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-04-01',
            'estado' => EstadoPresupuesto::Borrador->value,
        ]);

        $conceptoA = ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuesto->id,
            'nombre' => 'Ascensor',
            'rubro' => RubroConceptoPresupuesto::Mantenimiento->value,
            'monto_total' => 1000,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'orden' => 1,
        ]);

        $conceptoB = ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuesto->id,
            'nombre' => 'Limpieza',
            'rubro' => RubroConceptoPresupuesto::Servicios->value,
            'monto_total' => 1000,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'orden' => 2,
        ]);

        return [$consorcio, $presupuesto, $conceptoA, $conceptoB];
    }

    private function seedConceptForOtherPeriod(int $consorcioId): ConceptoPresupuesto
    {
        $presupuesto = Presupuesto::query()->create([
            'consorcio_id' => $consorcioId,
            'periodo' => '2026-05-01',
            'estado' => EstadoPresupuesto::Borrador->value,
        ]);

        return ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuesto->id,
            'nombre' => 'Concepto mayo',
            'rubro' => RubroConceptoPresupuesto::Otros->value,
            'monto_total' => 1000,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'orden' => 1,
        ]);
    }
}
