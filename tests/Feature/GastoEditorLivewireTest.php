<?php

namespace Tests\Feature;

use App\Enums\EstadoGasto;
use App\Enums\EstadoPresupuesto;
use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use App\Livewire\Gastos\GastoEditor;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Presupuesto;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GastoEditorLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_from_livewire_with_valid_line_items(): void
    {
        $consorcio = Consorcio::query()->create([
            'nombre' => 'Consorcio Test',
            'direccion' => 'Calle 123',
            'cuit' => '20-12345678-9',
        ]);

        $proveedor = Proveedor::query()->create([
            'nombre' => 'Proveedor Test',
            'activo' => true,
        ]);

        $presupuesto = Presupuesto::query()->create([
            'consorcio_id' => $consorcio->id,
            'periodo' => '2026-03-01',
            'estado' => EstadoPresupuesto::Borrador->value,
        ]);

        $concepto = ConceptoPresupuesto::query()->create([
            'presupuesto_id' => $presupuesto->id,
            'nombre' => 'Admin',
            'rubro' => RubroConceptoPresupuesto::Mantenimiento->value,
            'monto_total' => 40000,
            'tipo' => TipoConceptoPresupuesto::Ordinario->value,
            'orden' => 1,
        ]);

        $component = Livewire::test(GastoEditor::class)
            ->set('consorcio_id', $consorcio->id)
            ->set('proveedor_id', $proveedor->id)
            ->set('nro_orden', 'ORD-TEST-1')
            ->set('descripcion', 'Factura prueba')
            ->set('importe', '40000')
            ->set('fecha_factura', '2026-05-04')
            ->set('periodo', '2026-03')
            ->set('estado', EstadoGasto::Pendiente->value)
            ->set('lineItems', [[
                'concepto_presupuesto_id' => (string) $concepto->id,
                'importe_asignado' => '40000',
            ]])
            ->call('save');

        $this->assertTrue(
            $component->errors()->isEmpty(),
            'Errores: '.json_encode($component->errors()->toArray()).' | State: '.json_encode([
                'consorcio_id' => $component->get('consorcio_id'),
                'proveedor_id' => $component->get('proveedor_id'),
                'nro_orden' => $component->get('nro_orden'),
                'descripcion' => $component->get('descripcion'),
                'importe' => $component->get('importe'),
                'fecha_factura' => $component->get('fecha_factura'),
                'periodo' => $component->get('periodo'),
                'estado' => $component->get('estado'),
                'lineItems' => $component->get('lineItems'),
            ])
        );
    }
}

