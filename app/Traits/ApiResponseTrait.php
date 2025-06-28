<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success response with data
     */
    public function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Error response
     */
    public function errorResponse(string $message = 'Error occurred', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public function validationErrorResponse($validator): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            422,
            $validator->errors()
        );
    }

    /**
     * Resource not found response
     */
    public function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse(
            "{$resource} not found",
            404
        );
    }

    /**
     * Unauthorized response
     */
    public function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response
     */
    public function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Paginated response
     */
    public function paginatedResponse($data, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
