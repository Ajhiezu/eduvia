<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('test@example.com|127.0.0.1');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertHeaderContains('Cache-Control', 'no-store');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'valid.user@eduvia.test',
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'valid.user@eduvia.test',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'valid.user@eduvia.test',
            'password' => 'Password123!',
        ]);

        $response = $this->post('/login', [
            'email' => 'valid.user@eduvia.test',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive.user@eduvia.test',
            'password' => 'Password123!',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive.user@eduvia.test',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $response->assertHeaderContains('Cache-Control', 'no-store');
        $this->assertGuest();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('students.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_pages_cannot_be_cached_and_revalidate_browser_history(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertHeaderContains('Cache-Control', 'no-store');
        $response->assertHeaderContains('Cache-Control', 'private');
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', '0');
        $response->assertSee('data-authenticated-page', false);
    }

    public function test_login_request_is_throttled_after_consecutive_failed_attempts(): void
    {
        $email = 'throttle.target@eduvia.test';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt must be throttled with HTTP 429 Too Many Requests
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(429);
    }
}
