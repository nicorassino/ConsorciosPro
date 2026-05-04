<?php

namespace Database\Seeders;

use App\Enums\EstadoGasto;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class GastoSeeder extends Seeder
{
    public function run(): void
    {
        $consorcio = Consorcio::query()->firstOrFail();
        $conceptos = ConceptoPresupuesto::query()->orderBy('id')->get();

        $proveedor = Proveedor::create([
            'nombre' => 'Servicios Integrales SRL',
            'cuit' => '30-70999888-1',
            'telefono' => '3514111111',
            'email' => 'facturacion@serviciosintegrales.local',
            'direccion' => 'Bv. San Juan 888',
            'activo' => true,
        ]);

        $gasto = Gasto::create([
            'consorcio_id' => $consorcio->id,
            'proveedor_id' => $proveedor->id,
            'nro_orden' => 'G-0001',
            'descripcion' => 'Facturación mensual de limpieza y mantenimiento',
            'importe' => 330000,
            'fecha_factura' => now()->startOfMonth()->addDays(2)->toDateString(),
            'periodo' => now()->startOfMonth()->toDateString(),
            'estado' => EstadoGasto::Pagado->value,
            'fecha_pago' => now()->startOfMonth()->addDays(7)->toDateString(),
            'comprobante_pago' => 'transferencia-001',
            'notas' => 'Pago acreditado vía banco',
        ]);

        $gasto->conceptosPresupuesto()->attach([
            $conceptos[0]->id => ['importe_asignado' => 180000],
            $conceptos[1]->id => ['importe_asignado' => 150000],
        ]);
    }
}
