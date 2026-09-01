<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;

trait ApiResponses
{
    /**
     * Standard success response without double-wrapping 'data'.
     */
    protected function success(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data instanceof AbstractPaginator) {
            $data = $data->toArray();
        }

        if (is_array($data) && isset($data['data'], $data['current_page'])) {
            $response['data'] = $data['data'];
            $response['meta'] = [
                'current_page' => $data['meta']['current_page'] ?? $data['current_page'] ?? null,
                'last_page' => $data['meta']['last_page'] ?? $data['last_page'] ?? null,
                'per_page' => $data['meta']['per_page'] ?? $data['per_page'] ?? null,
                'total' => $data['meta']['total'] ?? $data['total'] ?? null,
            ];
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Standard error response.
     */
    protected function error(string $message, int $code = 400, mixed $data = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }
}
