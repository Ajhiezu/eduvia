<?php

namespace Tests\Feature\Security;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_rejects_backdoor_password_even_in_testing_environment(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => 'victim@eduvia.sch.id',
            'password' => Hash::make('SuperSecretPassword_2026!'),
            'is_active' => true,
        ]);

        // Attempt login with backdoor passwords
        $response1 = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response1->assertStatus(401)
            ->assertJsonPath('success', false);

        $response2 = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);
        $response2->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_api_login_succeeds_with_valid_cryptographic_password(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => 'legit@eduvia.sch.id',
            'password' => Hash::make('SuperSecretPassword_2026!'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'SuperSecretPassword_2026!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'email', 'name', 'role'],
                ],
            ]);
    }

    public function test_api_login_rejects_inactive_user(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => 'disabled@eduvia.sch.id',
            'password' => Hash::make('SuperSecretPassword_2026!'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'SuperSecretPassword_2026!',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
