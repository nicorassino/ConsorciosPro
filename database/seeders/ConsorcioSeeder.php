<?php

namespace Database\Seeders;

use App\Models\Consorcio;
use Illuminate\Database\Seeder;

class ConsorcioSeeder extends Seeder
{
    public function run(): void
    {
        Consorcio::create([
            'nombre' => 'Consorcio Torre Oliva',
            'direccion' => 'Av. Colón 1450, Córdoba',
            'cuit' => '30-71234567-8',
            'banco' => 'Banco Córdoba',
            'nro_cuenta_bancaria' => '001-000123/4',
            'convenio' => 'SIRO-CBA-001',
            'sucursal' => 'Centro',
            'digito_verificador' => '8',
            'cbu' => '2850590940090418135201',
            'condicion_iva' => 'no_alcanzado',
            'nro_cuenta_rentas' => 'RNT-110045',
            'nomenclatura_catastral' => 'CBA-01-02-3456',
            'nro_matricula' => 'MAT-2026-88',
            'fecha_inscripcion_reglamento' => now()->subYears(5)->toDateString(),
            'unidad_facturacion_aguas' => 'AC-99911',
            'tiene_cocheras' => true,
            'encargado_nombre' => 'Pablo',
            'encargado_apellido' => 'Gómez',
            'encargado_telefono' => '3514000000',
            'encargado_horarios' => 'Lunes a viernes de 08:00 a 16:00',
            'encargado_dias' => 'Lunes a viernes',
            'encargado_empresa_servicio' => 'Servicios Torre SRL',
            'nombre_administracion' => 'Oliva Administraciones',
            'texto_medios_pago' => 'Pagá por SIRO, homebanking o débito automático.',
            'dia_primer_vencimiento' => 10,
            'dia_segundo_vencimiento' => 20,
            'recargo_segundo_vto' => 12.00,
            'nota' => 'Recordatorio: mantener matafuegos al día y actualizar datos de contacto.',
            'activo' => true,
        ]);
    }
}
