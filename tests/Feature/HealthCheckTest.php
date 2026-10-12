<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that API health check endpoint returns 200 with standard schema.
     */
    public function test_api_v1_health_check_returns_healthy_status_and_valid_json_structure(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'status',
                    'app' => ['name', 'env', 'debug', 'version', 'framework', 'php'],
                    'services' => [
                        'database' => ['connection', 'status', 'latency_ms'],
                    ],
                    'system' => ['timezone', 'server_time', 'uptime'],
                ],
                'errors',
                'timestamp',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'healthy')
            ->assertJsonPath('data.services.database.status', 'healthy');
    }

    /**
     * Test that web health endpoint responds successfully.
     */
    public function test_web_health_check_endpoint_returns_success(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
