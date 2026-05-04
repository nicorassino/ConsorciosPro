<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'administrador@cliente.local',
        ], [
            'name' => 'administrador',
            'role' => 'admin',
            'password' => Hash::make('olivaAdmin'),
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'admin@consorciospro.local',
        ], [
            'name' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('2648'),
            'email_verified_at' => now(),
        ]);

        $this->call([
            ConsorcioSeeder::class,
            UnidadSeeder::class,
            PresupuestoSeeder::class,
            GastoSeeder::class,
            LiquidacionSeeder::class,
            CobranzaSeeder::class,
            MovimientoFondoSeeder::class,
        ]);
    }
}
