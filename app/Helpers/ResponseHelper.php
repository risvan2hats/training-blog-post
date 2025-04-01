<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ResponseHelper
{
    /**
     * Successful response
     */
    public static function success(
        JsonResource|array|null $data = null,
        string $message = '',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * Error response with logging
     */
    public static function error(
        string $message = '',
        int $code = 400,
        array|null $errors = null,
        \Throwable $exception = null
    ): JsonResponse {
        // Log the error if an exception is provided
        if ($exception !== null) {
            Log::error($message, [
                'exception' => $exception->getMessage(),
                'code' => $code,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        } else {
            // Log basic error information
            Log::error($message, [
                'code' => $code,
                'errors' => $errors,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Resource created response
     */
    public static function created(
        JsonResource|array $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * Not found response with logging
     */
    public static function notFound(
        string $message = 'Resource not found',
        \Throwable $exception = null
    ): JsonResponse {
        return self::error($message, 404, null, $exception);
    }

    /**
     * Validation error response with logging
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        Log::warning($message, [
            'errors' => $errors,
        ]);

        return self::error($message, 422, $errors);
    }

    /**
     * Unauthorized response with logging
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        \Throwable $exception = null
    ): JsonResponse {
        return self::error($message, 401, null, $exception);
    }

    /**
     * Forbidden response with logging
     */
    public static function forbidden(
        string $message = 'Forbidden',
        \Throwable $exception = null
    ): JsonResponse {
        return self::error($message, 403, null, $exception);
    }

    /**
     * Server error response with logging
     */
    public static function serverError(
        string $message = 'Internal Server Error',
        \Throwable $exception = null
    ): JsonResponse {
        return self::error($message, 500, null, $exception);
    }
}
