<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_access_admin_restricted_endpoint(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/restricted-admin-demo');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_school_admin_can_access_admin_restricted_endpoint(): void
    {
        $schoolAdmin = User::factory()->schoolAdmin()->create();

        $response = $this->actingAs($schoolAdmin)->get('/restricted-admin-demo');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_student_is_denied_from_admin_restricted_endpoint_with_403(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/restricted-admin-demo');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_api_request_returns_401_json(): void
    {
        $response = $this->getJson('/restricted-admin-demo');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}
