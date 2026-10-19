<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMA Eduvia Test',
            'npsn' => '12345678',
            'education_stage' => 'SMA',
            'is_active' => true,
        ]);
    }

    public function test_parent_user_can_login_with_valid_credentials_and_receive_token(): void
    {
        $parent = User::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.parent@eduvia.test',
            'password' => Hash::make('password123'),
            'role' => UserRole::PARENT,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'budi.parent@eduvia.test',
            'password' => 'password123',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'school_id',
                    ],
                    'school' => [
                        'id',
                        'name',
                    ],
                ],
                'timestamp',
            ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertEquals('parent', $response->json('data.user.role'));
    }

    public function test_student_user_can_login_with_valid_credentials(): void
    {
        $student = User::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Citra Dewi',
            'email' => 'citra.student@eduvia.test',
            'password' => Hash::make('secretpass'),
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'citra.student@eduvia.test',
            'password' => 'secretpass',
            'device_name' => 'Samsung Galaxy S24',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'student');
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@eduvia.test',
            'password' => Hash::make('correct_password'),
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'test@eduvia.test',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Email atau kata sandi tidak sesuai.');
    }

    public function test_inactive_user_is_forbidden_from_logging_in(): void
    {
        User::factory()->create([
            'email' => 'inactive@eduvia.test',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'inactive@eduvia.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Akun Anda sedang dinonaktifkan. Silakan hubungi pihak sekolah.');
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Ahmad Fauzi',
            'email' => 'fauzi@eduvia.test',
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        $token = $user->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.v1.auth.me'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'fauzi@eduvia.test')
            ->assertJsonPath('data.role', 'student');
    }

    public function test_unauthenticated_request_to_me_returns_401(): void
    {
        $response = $this->getJson(route('api.v1.auth.me'));

        $response->assertStatus(401);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create([
            'email' => 'user.logout@eduvia.test',
            'role' => UserRole::PARENT,
            'is_active' => true,
        ]);

        $token = $user->createToken('MobileApp')->plainTextToken;

        $this->assertCount(1, $user->tokens);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(route('api.v1.auth.logout'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Sesi berhasil diakhiri.');

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
