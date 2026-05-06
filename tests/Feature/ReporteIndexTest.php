<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_reportes_screen(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.index'));

        $response->assertOk();
        $response->assertSee('Fase 7: Informes y Conciliación');
    }

    public function test_reportes_screen_works_with_seeded_financial_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('reportes.index'));

        $response->assertOk();
        $response->assertSee('Conciliación');
    }
}
