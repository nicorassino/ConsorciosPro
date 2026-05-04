<?php

namespace Database\Seeders;

use App\Models\Consorcio;
use App\Models\Inmobiliaria;
use App\Models\Inquilino;
use App\Models\PortalUser;
use App\Models\Propietario;
use App\Models\Unidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        $consorcio = Consorcio::query()->firstOrFail();

        $data = [
            ['numero' => '1A', 'coef' => 15.200000, 'owner' => 'Ana Ríos', 'owner_email' => 'ana.rios+1a@demo.local', 'tenant' => 'Mario Díaz', 'tenant_email' => 'mario.diaz+1a@demo.local', 'rentas' => '1001'],
            ['numero' => '1B', 'coef' => 15.200000, 'owner' => 'Lucía Pérez', 'owner_email' => 'lucia.perez+1b@demo.local', 'tenant' => null, 'tenant_email' => null, 'rentas' => '1002'],
            ['numero' => '2A', 'coef' => 17.100000, 'owner' => 'Carlos Sosa', 'owner_email' => 'carlos.sosa+2a@demo.local', 'tenant' => 'Julia Martín', 'tenant_email' => 'julia.martin+2a@demo.local', 'rentas' => '1003'],
            ['numero' => '2B', 'coef' => 17.100000, 'owner' => 'Verónica Ruiz', 'owner_email' => 'veronica.ruiz+2b@demo.local', 'tenant' => null, 'tenant_email' => null, 'rentas' => '1004'],
            ['numero' => '3A', 'coef' => 17.700000, 'owner' => 'Nicolás Vega', 'owner_email' => 'nicolas.vega+3a@demo.local', 'tenant' => 'Sofía Acosta', 'tenant_email' => 'sofia.acosta+3a@demo.local', 'rentas' => '1005'],
            ['numero' => '3B', 'coef' => 17.700000, 'owner' => 'Valeria Luna', 'owner_email' => 'valeria.luna+3b@demo.local', 'tenant' => null, 'tenant_email' => null, 'rentas' => '1006'],
        ];

        foreach ($data as $index => $row) {
            $unidad = Unidad::create([
                'consorcio_id' => $consorcio->id,
                'numero' => $row['numero'],
                'coeficiente' => $row['coef'],
                'nro_cuenta_rentas' => $row['rentas'],
                'tiene_cochera' => $index % 2 === 0,
                'nro_cochera' => $index % 2 === 0 ? 'C-'.($index + 1) : null,
                'estado_ocupacion' => $row['tenant'] ? 'inquilino' : 'propietario_residente',
                'nro_cupon_siro' => 'SIRO-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'codigo_pago_electronico' => 'CPE-2026-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'recibos_a_nombre_de' => 'propietario',
                'condicion_iva' => 'consumidor_final',
                'email_expensas_ordinarias' => $row['owner_email'],
                'email_expensas_extraordinarias' => $row['owner_email'],
                'activo' => true,
            ]);

            Propietario::create([
                'unidad_id' => $unidad->id,
                'nombre' => $row['owner'],
                'dni' => '30'.($index + 1).'112233',
                'direccion_postal' => 'Córdoba',
                'email' => $row['owner_email'],
                'telefono' => '35150000'.($index + 1),
            ]);

            PortalUser::create([
                'unidad_id' => $unidad->id,
                'tipo' => 'propietario',
                'nombre' => $row['owner'],
                'email' => $row['owner_email'],
                'password' => Hash::make($row['rentas']),
                'must_change_password' => true,
            ]);

            if ($row['tenant']) {
                Inquilino::create([
                    'unidad_id' => $unidad->id,
                    'nombre' => explode(' ', $row['tenant'])[0],
                    'apellido' => explode(' ', $row['tenant'])[1] ?? '',
                    'telefono' => '35160000'.($index + 1),
                    'email' => $row['tenant_email'],
                    'direccion_postal' => 'Córdoba',
                    'fecha_fin_contrato' => now()->addMonths(8)->toDateString(),
                    'activo' => true,
                ]);

                PortalUser::create([
                    'unidad_id' => $unidad->id,
                    'tipo' => 'inquilino',
                    'nombre' => $row['tenant'],
                    'email' => $row['tenant_email'],
                    'password' => Hash::make($row['rentas']),
                    'must_change_password' => true,
                ]);
            }

            Inmobiliaria::create([
                'unidad_id' => $unidad->id,
                'nombre' => 'Inmobiliaria',
                'apellido' => 'Río',
                'telefono' => '35170000'.($index + 1),
                'email' => 'inmobiliaria+'.strtolower($row['numero']).'@demo.local',
                'direccion' => 'Av. Maipú 1200',
            ]);
        }
    }
}
