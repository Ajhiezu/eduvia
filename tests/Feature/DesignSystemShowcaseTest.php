<?php

namespace Tests\Feature;

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
    }
}
