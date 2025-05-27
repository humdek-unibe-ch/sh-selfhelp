<?php
namespace App\EventListener;

use App\Service\Core\ApiResponseFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Catches all exceptions for API routes and returns JSON responses using the standard API response envelope.
 */
class ApiExceptionListener
{
    public function __construct(
        private readonly ApiResponseFormatter $apiResponseFormatter
    ) {}
    
    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        // Check if it's an API route (adjust as needed)
        if (strpos($request->getPathInfo(), '/cms-api/') !== 0) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $message = $exception->getMessage() ?: 'An error occurred';

        // Use the ApiResponseFormatter for consistent error responses
        $response = $this->apiResponseFormatter->formatError($message, $statusCode);
        $event->setResponse($response);
    }
}
