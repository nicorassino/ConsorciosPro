<?php

namespace Tests\Feature;

use App\Enums\EstadoGasto;
use App\Enums\EstadoPresupuesto;
use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use App\Livewire\Gastos\GastoIndex;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\Presupuesto;
use App\Models\User;
use App\Services\GastoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GastoIndexAjusteGeneradoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * El ajuste vive en el último borrador (p. ej. junio) aunque el mes "siguiente" al gasto (mayo) no tenga presupuesto.
     */
    public function test_columna_ajuste_generado_detecta_ajuste_en_ultimo_borrador(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $consorcio = Consorcio::query()->create([
            'nombre' => 'Edificio Listado',
            'direccion' => 'X',
            'cuit' => '20-33333333-3',
        ]);

        $presupuestoAbril = Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-04-01',
            'estado' => EstadoPresupuesto::Borrador->value,
        ]);

        $conceptoAscensor = ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuestoAbril->id,
            'nombre' => 'Ascensor',
            'rubro' => RubroConceptoPresupuesto::Mantenimiento->value,
            'monto_total' => 1000,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'orden' => 1,
        ]);

        $presupuestoJunio = Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-06-01',
            'estado' => EstadoPresupuesto::Borrador->value,
        ]);

        ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuestoJunio->id,
            'nombre' => 'Ajuste Ascensor',
            'rubro' => RubroConceptoPresupuesto::Mantenimiento->value,
            'descripcion' => GastoService::AJUSTE_DESCRIPCION_AUTO,
            'monto_total' => 200,
            'cuotas_total' => 1,
            'cuota_actual' => 1,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'aplica_cocheras' => false,
            'orden' => 5,
        ]);

        $gasto = Gasto::query()->create([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-LIST',
            'descripcion' => 'Factura',
            'importe' => 1200,
            'fecha_factura' => '2026-04-20',
            'periodo' => '2026-04-01',
            'estado' => EstadoGasto::Pendiente->value,
        ]);
        $gasto->conceptosPresupuesto()->sync([
            $conceptoAscensor->id => ['importe_asignado' => 1200],
        ]);

        $component = Livewire::test(GastoIndex::class);
        $ajustes = $component->get('ajustesMesSiguiente');

        $this->assertArrayHasKey($gasto->id, $ajustes);
        $this->assertTrue($ajustes[$gasto->id]['has']);
        $this->assertEquals(200.0, $ajustes[$gasto->id]['amount']);
    }
}
