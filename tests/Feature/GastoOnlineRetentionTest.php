<?php

namespace Tests\Feature;

use App\Enums\EstadoGasto;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Services\GastoOnlineRetention;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GastoOnlineRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_past_deadline_when_factura_mas_de_un_ano(): void
    {
        Storage::fake('local');

        $consorcio = Consorcio::query()->create([
            'nombre' => 'Edificio',
            'direccion' => 'X',
            'cuit' => '20-11111111-1',
        ]);

        $path = 'gastos/facturas/old.pdf';
        Storage::disk('local')->put($path, 'x');

        Gasto::query()->create([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'O1',
            'descripcion' => 'Viejo',
            'importe' => 100,
            'fecha_factura' => '2020-01-10',
            'periodo' => '2020-01-01',
            'estado' => EstadoGasto::Pendiente->value,
            'factura_archivo' => $path,
            'archivo_disponible_online' => true,
        ]);

        $this->assertTrue(GastoOnlineRetention::anyPastDeadlineBlocking());
    }

    public function test_urgent_window_includes_gasto_con_deadline_dentro_de_30_dias(): void
    {
        Storage::fake('local');

        Carbon::setTestNow(Carbon::parse('2026-05-04'));

        $consorcio = Consorcio::query()->create([
            'nombre' => 'Torre',
            'direccion' => 'Y',
            'cuit' => '20-22222222-2',
        ]);

        $path = 'gastos/facturas/u.pdf';
        Storage::disk('local')->put($path, 'z');

        // deadline = 2026-05-19 => falta 15 días desde "hoy" 2026-05-04
        Gasto::query()->create([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => null,
            'nro_orden' => 'U1',
            'descripcion' => 'Urgente',
            'importe' => 50,
            'fecha_factura' => '2025-05-19',
            'periodo' => '2025-05-01',
            'estado' => EstadoGasto::Pendiente->value,
            'factura_archivo' => $path,
            'archivo_disponible_online' => true,
        ]);

        $urgentes = GastoOnlineRetention::urgentWarningGastos();

        $this->assertCount(1, $urgentes);

        Carbon::setTestNow();
    }
}
