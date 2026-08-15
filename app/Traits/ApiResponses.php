<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    /**
     * Standard success response without double-wrapping 'data'.
     */
    protected function success(mixed $data = null, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data instanceof LengthAwarePaginator || $data instanceof \Illuminate\Pagination\Paginator) {
            $arr = $data->toArray();
            $response['data'] = $arr['data'];
            $response['meta'] = [
                'current_page' => $arr['current_page'] ?? null,
                'last_page'    => $arr['last_page'] ?? null,
                'per_page'     => $arr['per_page'] ?? null,
                'total'        => $arr['total'] ?? null,
            ];
        } elseif (is_array($data)) {
            // spatie/laravel-data paginator shape
            if (isset($data['data'], $data['meta'])) {
                $response['data'] = $data['data'];
                $response['meta'] = [
                    'current_page' => $data['meta']['current_page'] ?? null,
                    'last_page'    => $data['meta']['last_page'] ?? null,
                    'per_page'     => $data['meta']['per_page'] ?? null,
                    'total'        => $data['meta']['total'] ?? null,
                ];
            } 
            // raw laravel paginator toArray shape
            elseif (isset($data['data'], $data['current_page'], $data['last_page'])) {
                $response['data'] = $data['data'];
                $response['meta'] = [
                    'current_page' => $data['current_page'],
                    'last_page'    => $data['last_page'],
                    'per_page'     => $data['per_page'],
                    'total'        => $data['total'],
                ];
            } 
            else {
                $response['data'] = $data;
            }
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
