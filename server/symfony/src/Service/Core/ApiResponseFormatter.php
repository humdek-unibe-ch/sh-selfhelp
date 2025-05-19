<?php

namespace App\Service\Core;

use App\Exception\ServiceException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API response formatter service
 * 
 * Formats API responses to match the existing format
 */
class ApiResponseFormatter
{
    /**
     * Format a success response
     * 
     * @param mixed $data The response data
     * @param int $status The HTTP status code
     * @param bool $loggedIn Whether the user is logged in
     * @return JsonResponse The formatted response
     */
    public function formatSuccess($data = null, int $status = Response::HTTP_OK, bool $loggedIn = true): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'message' => Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => null,
            'logged_in' => $loggedIn,
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTime())->format('c')
            ],
            'data' => $data
        ], $status);
    }
    
    /**
     * Format an error response
     * 
     * @param string $error The error message
     * @param int $status The HTTP status code
     * @param bool $loggedIn Whether the user is logged in
     * @param mixed $data Additional error data
     * @return JsonResponse The formatted response
     */
    public function formatError(string $error, int $status = Response::HTTP_BAD_REQUEST, bool $loggedIn = true, $data = null): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'message' => Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => $error,
            'logged_in' => $loggedIn,
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTime())->format('c')
            ],
            'data' => $data
        ], $status);
    }
    
    /**
     * Format a service exception response
     * 
     * @param ServiceException $exception The exception
     * @param bool $loggedIn Whether the user is logged in
     * @return JsonResponse The formatted response
     */
    public function formatException(ServiceException $exception, bool $loggedIn = true): JsonResponse
    {
        return $this->formatError(
            $exception->getMessage(),
            $exception->getCode(),
            $loggedIn,
            $exception->getData()
        );
    }
}
