<?php 

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null,array $meta = [],int $status = 200): JsonResponse {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ], $status);
    }

    protected function error(mixed $errors,int $status = 400,array $meta = []): JsonResponse {
        return response()->json([
            'data' => null,
            'meta' => $meta,
            'errors' => $errors,
        ], $status);
    }
}