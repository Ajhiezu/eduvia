<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class DesignSystemShowcaseTest extends TestCase
{
    public function test_design_system_showcase_page_returns_successful_response(): void
    {
        $response = $this->get('/design-system');

        $response->assertStatus(200);
        $response->assertSee('Eduvia UI Design System');
        $response->assertSee('Reusable Buttons');
    }

    public function test_welcome_landing_page_renders_cleanly(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Eduvia');
        $response->assertSee('SD, SMP, SMA, hingga SMK');
        $response->assertSee('Learn · Grow · Build Your Future');
    }

    public function test_authenticated_user_can_view_dashboard_with_brand_layout(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Eduvia');
        $response->assertSee('Dashboard');
        $response->assertSee($user->name);
    }

    public function test_official_brand_assets_exist(): void
    {
        $this->assertFileExists(public_path('images/brand/eduvia-logo-horizontal-transparent.png'));
        $this->assertFileExists(public_path('images/brand/eduvia-logo-vertical-transparent.png'));
        $this->assertFileExists(public_path('images/brand/eduvia-icon-mark-transparent.png'));
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('favicon.png'));
    }
}
