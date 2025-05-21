<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Catches all exceptions for API routes and returns JSON responses.
 */
class ApiExceptionListener
{
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

        // Standard error structure
        $data = [
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message,
        ];

        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }
}
