<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Return a standardized success JSON response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function apiSuccess(
        mixed $data = null,
        string $message = 'Permintaan berhasil diproses.',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a standardized error JSON response.
     */
    protected function apiError(
        string $message = 'Terjadi kesalahan saat memproses permintaan.',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }

    /**
     * Return a standardized paginated JSON response.
     */
    protected function apiPaginated(
        LengthAwarePaginator $paginator,
        string $message = 'Data berhasil diambil.'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'errors' => null,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
