<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeAdminBypassTest extends TestCase
{
    use RefreshDatabase;

    private function enableMaintenance(): void
    {
        SiteSetting::singleton()->update([
            'maintenance_mode' => true,
            'maintenance_message' => 'Test maintenance',
        ]);
    }

    public function test_guest_sees_maintenance_page(): void
    {
        $this->enableMaintenance();

        $response = $this->get('/');

        $response->assertStatus(503);
        $response->assertViewIs('errors.maintenance-site');
    }

    public function test_authenticated_non_admin_sees_maintenance_page(): void
    {
        $this->enableMaintenance();
        $visitor = User::factory()->create(['role' => 'visitor']);

        $response = $this->actingAs($visitor)->get('/');

        $response->assertStatus(503);
        $response->assertViewIs('errors.maintenance-site');
    }

    public function test_authenticated_admin_bypasses_maintenance(): void
    {
        $this->enableMaintenance();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/');

        $response->assertStatus(200);
    }

    public function test_maintenance_disabled_shows_normal_site_to_everyone(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
