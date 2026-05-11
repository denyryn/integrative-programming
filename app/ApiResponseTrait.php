<?php

namespace App;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse(
        $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    protected function errorResponse(
        string $message = 'Something went wrong',
        $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }
}
