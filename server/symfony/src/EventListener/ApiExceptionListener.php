<?php
namespace App\EventListener;

use App\Exception\RequestValidationException;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Catches all exceptions for API routes and returns JSON responses using the standard API response envelope.
 */
class ApiExceptionListener
{
    public function __construct(
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly KernelInterface $kernel
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
        
        // Handle specific exception types
        if ($exception instanceof RequestValidationException) {
            // Create a detailed validation error response
            $validationData = [
                'schema' => $exception->getSchemaName(),
                'errors' => $exception->getValidationErrors(),
                'missing_fields' => $this->extractMissingFields($exception->getValidationErrors())
            ];
            
            // In non-production environments, include the request data for debugging
            if ($this->kernel->getEnvironment() !== 'prod') {
                $validationData['request_data'] = $exception->getRequestData();
            }
            
            $response = $this->apiResponseFormatter->formatError(
                'Request validation failed',
                Response::HTTP_BAD_REQUEST,
                null, // No data field needed for validation errors
                $validationData // Pass validation errors to the dedicated field
            );
            $event->setResponse($response);
            return;
        }
        
        // Handle InvalidArgumentException (often used for JSON parsing errors)
        if ($exception instanceof \InvalidArgumentException) {
            $response = $this->apiResponseFormatter->formatError(
                $exception->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
            $event->setResponse($response);
            return;
        }
        
        // Default exception handling
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $message = $exception->getMessage() ?: 'An error occurred';

        // Use the ApiResponseFormatter for consistent error responses
        $response = $this->apiResponseFormatter->formatError($message, $statusCode);
        $event->setResponse($response);
    }
    
    /**
     * Extract missing fields from validation errors
     *
     * @param array $validationErrors The validation errors
     * @return array The missing fields
     */
    private function extractMissingFields(array $validationErrors): array
    {
        $missingFields = [];
        
        foreach ($validationErrors as $error) {
            // Check for required property errors
            if (strpos($error, 'required property') !== false) {
                // Extract the property name from the error message
                // Example error: "The property field1 is required"
                preg_match('/property ([\w.]+)/', $error, $matches);
                if (isset($matches[1])) {
                    $missingFields[] = $matches[1];
                }
            }
        }
        
        return $missingFields;
    }
}
