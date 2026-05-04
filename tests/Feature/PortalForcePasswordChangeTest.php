<?php

namespace Tests\Feature;

use App\Models\PortalUser;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_to_password_change_when_user_must_change_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $portalUser = PortalUser::query()->firstOrFail();

        $response = $this->actingAs($portalUser, 'portal')
            ->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.password.edit'));
    }
}
