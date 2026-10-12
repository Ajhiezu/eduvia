<?php

namespace Tests\Unit;

use App\Traits\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ApiResponseTraitTest extends TestCase
{
    private object $classUsingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classUsingTrait = new class
        {
            use ApiResponse;

            public function success(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = [])
            {
                return $this->apiSuccess($data, $message, $status, $meta);
            }

            public function error(string $message = 'Error', int $status = 400, mixed $errors = null)
            {
                return $this->apiError($message, $status, $errors);
            }

            public function paginated(LengthAwarePaginator $paginator, string $message = 'Retrieved')
            {
                return $this->apiPaginated($paginator, $message);
            }
        };
    }

    public function test_api_success_formats_payload_correctly(): void
    {
        $response = $this->classUsingTrait->success(['id' => 1], 'Data berhasil disimpan', 201);
        $data = $response->getData(true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertEquals('Data berhasil disimpan', $data['message']);
        $this->assertEquals(['id' => 1], $data['data']);
        $this->assertNull($data['errors']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function test_api_error_formats_payload_correctly(): void
    {
        $response = $this->classUsingTrait->error('Validasi gagal', 422, ['field' => ['Wajib diisi']]);
        $data = $response->getData(true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertFalse($data['success']);
        $this->assertEquals('Validasi gagal', $data['message']);
        $this->assertNull($data['data']);
        $this->assertEquals(['field' => ['Wajib diisi']], $data['errors']);
    }

    public function test_api_paginated_formats_meta_and_items_correctly(): void
    {
        $paginator = new LengthAwarePaginator(['item1', 'item2'], 20, 10, 1);
        $response = $this->classUsingTrait->paginated($paginator, 'Daftar item');
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals(['item1', 'item2'], $data['data']);
        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertEquals(20, $data['meta']['total']);
        $this->assertTrue($data['meta']['has_more_pages']);
    }
}
