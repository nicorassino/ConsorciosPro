<?php

namespace Tests\Feature;

use App\Enums\EstadoGasto;
use App\Livewire\Gastos\GastoIndex;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GastoIndexMassArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_descargar_paquete_y_archiva_solo_gastos_con_fecha_factura_antes_del_corte(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $consorcio = Consorcio::query()->create([
            'nombre' => 'Edificio Archivo',
            'direccion' => 'Calle 1',
            'cuit' => '20-11111111-1',
        ]);

        $pathViejo = 'gastos/facturas/viejo.pdf';
        Storage::disk('local')->put($pathViejo, 'contenido factura vieja');

        $gastoViejo = Gasto::query()->create([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-V',
            'descripcion' => 'Gasto viejo',
            'importe' => 100,
            'fecha_factura' => '2025-06-15',
            'periodo' => '2025-06-01',
            'estado' => EstadoGasto::Pendiente->value,
            'factura_archivo' => $pathViejo,
            'factura_nombre_sistema' => 'luz_2025-06_edificio-archivo.pdf',
            'archivo_disponible_online' => true,
            'comprobante_pago' => null,
        ]);

        $pathNuevo = 'gastos/facturas/nuevo.pdf';
        Storage::disk('local')->put($pathNuevo, 'contenido factura nueva');

        $gastoNuevo = Gasto::query()->create([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'ORD-N',
            'descripcion' => 'Gasto nuevo',
            'importe' => 200,
            'fecha_factura' => '2026-05-10',
            'periodo' => '2026-05-01',
            'estado' => EstadoGasto::Pendiente->value,
            'factura_archivo' => $pathNuevo,
            'factura_nombre_sistema' => 'agua_2026-05_edificio-archivo.pdf',
            'archivo_disponible_online' => true,
            'comprobante_pago' => null,
        ]);

        Livewire::test(GastoIndex::class)
            ->set('archivoCorte', '2026-01')
            ->call('descargarPaqueteYArchivarEnServidor')
            ->assertFileDownloaded();

        $gastoViejo->refresh();
        $this->assertNull($gastoViejo->factura_archivo);
        $this->assertFalse($gastoViejo->archivo_disponible_online);
        $this->assertNotNull($gastoViejo->fecha_archivado_local);

        $gastoNuevo->refresh();
        $this->assertSame($pathNuevo, $gastoNuevo->factura_archivo);
        $this->assertTrue($gastoNuevo->archivo_disponible_online);

        Storage::disk('local')->assertMissing($pathViejo);
        Storage::disk('local')->assertExists($pathNuevo);
    }

    public function test_sin_corte_no_descarga_ni_archiva(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        Livewire::test(GastoIndex::class)
            ->set('archivoCorte', '')
            ->call('descargarPaqueteYArchivarEnServidor')
            ->assertNoFileDownloaded();
    }
}
