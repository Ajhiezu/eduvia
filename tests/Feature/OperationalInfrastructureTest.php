<?php

namespace Tests\Feature;

use App\Jobs\Middleware\SetTenantContext;
use App\Models\School;
use App\Services\TenantManager;
use App\Traits\TenantAwareJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DummyQueuedTask
{
    use TenantAwareJob;

    public bool $executedInTenantContext = false;
    public ?int $capturedSchoolId = null;

    public function __construct(?int $schoolId = null)
    {
        $this->withTenantContext($schoolId);
    }

    public function handle(): void
    {
        $this->capturedSchoolId = app(TenantManager::class)->getSchoolId();
        $this->executedInTenantContext = ($this->capturedSchoolId === $this->tenantSchoolId);
    }
}

class OperationalInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create(['name' => 'Sekolah Operasional', 'code' => 'SCH-OPS-01', 'is_active' => true]);
    }

    /**
     * Test 1: Job antrean (queue) merekonstruksi konteks tenant sekolah saat dieksekusi.
     */
    public function test_tenant_aware_queued_job_restores_tenant_context(): void
    {
        $task = new DummyQueuedTask($this->school->id);
        $this->assertEquals((int) $this->school->id, (int) $task->tenantSchoolId);

        // Pastikan middleware SetTenantContext dipasang
        $middlewares = $task->middleware();
        $this->assertCount(1, $middlewares);
        $this->assertInstanceOf(SetTenantContext::class, $middlewares[0]);

        // Simulasikan eksekusi middleware oleh worker antrean
        $middlewares[0]->handle($task, function ($job) {
            $job->handle();
        });

        $this->assertTrue($task->executedInTenantContext);
        $this->assertEquals((int) $this->school->id, (int) $task->capturedSchoolId);
    }

    /**
     * Test 2: Endpoint health check mengembalikan status lengkap (DB, cache, storage, multi-tenancy).
     */
    public function test_extended_health_check_endpoint(): void
    {
        $response = $this->getJson(route('api.v1.health'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'status',
                'app' => ['name', 'env', 'version', 'framework', 'php'],
                'services' => [
                    'database' => ['connection', 'status', 'latency_ms'],
                    'cache' => ['driver', 'status'],
                    'storage' => ['disk', 'status'],
                    'multi_tenancy' => ['status', 'active_schools_count'],
                ],
                'system' => ['timezone', 'server_time', 'uptime'],
            ],
            'message',
        ]);

        $this->assertEquals('healthy', $response->json('data.services.database.status'));
        $this->assertEquals('healthy', $response->json('data.services.cache.status'));
        $this->assertEquals('healthy', $response->json('data.services.storage.status'));
        $this->assertGreaterThanOrEqual(1, $response->json('data.services.multi_tenancy.active_schools_count'));
    }
}
