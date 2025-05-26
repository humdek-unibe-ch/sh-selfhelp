<?php

namespace App\Service\Core;

use App\Exception\ServiceException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * API response formatter service
 * 
 * Formats API responses to match the existing format
 */
class ApiResponseFormatter
{
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Format a success response
     * 
     * @param mixed $data The response data
     * @param int $status The HTTP status code
     * @return JsonResponse The formatted response
     */
    public function formatSuccess($data = null, int $status = Response::HTTP_OK, bool $isLoggedIn = false): JsonResponse
    {
        $isLoggedIn = $isLoggedIn || $this->security->getUser() !== null;
        return new JsonResponse([
            'status' => $status,
            'message' => Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => null,
            'logged_in' => $isLoggedIn,
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
     * @param mixed $data Additional error data
     * @return JsonResponse The formatted response
     */
    public function formatError(string $error, int $status = Response::HTTP_BAD_REQUEST, $data = null): JsonResponse
    {
        $isLoggedIn = $this->security->getUser() !== null;
        return new JsonResponse([
            'status' => $status,
            'message' => Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => $error,
            'logged_in' => $isLoggedIn,
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
     * @return JsonResponse The formatted response
     */
    public function formatException(ServiceException $exception): JsonResponse
    {
        // The logged_in status will be determined by formatError using $this->security->getUser()
        return $this->formatError(
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getData()
        );
    }
}
