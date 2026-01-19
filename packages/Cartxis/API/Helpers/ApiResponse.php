<?php

namespace Cartxis\API\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => 'v1',
            ],
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @param string|null $errorCode
     * @param mixed $data
     * @return JsonResponse
     */
    public static function error(
        string $message,
        $errors = null,
        int $code = 400,
        ?string $errorCode = null,
        $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => 'v1',
            ],
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a paginated success response.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string $message
     * @return JsonResponse
     */
    public static function paginated($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'timestamp' => now()->toIso8601String(),
                'version' => 'v1',
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Return a validation error response.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return JsonResponse
     */
    public static function validationError($validator): JsonResponse
    {
        return self::error(
            'Validation failed',
            $validator->errors()->toArray(),
            422,
            'VALIDATION_ERROR'
        );
    }

    /**
     * Return an unauthorized response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, null, 401, 'UNAUTHORIZED');
    }

    /**
     * Return a not found response.
     *
     * @param string $message
     * @param string|null $errorCode
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found', ?string $errorCode = null): JsonResponse
    {
        return self::error($message, null, 404, $errorCode ?? 'NOT_FOUND');
    }

    /**
     * Return a server error response.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Internal server error', $data = null): JsonResponse
    {
        return self::error($message, null, 500, 'SERVER_ERROR', $data);
    }
}
