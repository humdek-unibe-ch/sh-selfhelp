<?php

namespace App\Service\Core;

use App\Exception\ServiceException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\JSON\JsonSchemaValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * API response formatter service
 * 
 * Formats API responses to match the existing format
 */
class ApiResponseFormatter
{
    /**
     * Whether to validate the response schema. It consumes a lot of resources and should be disabled in production.
     * 
     * @var bool
     */
    private const VALIDATE_RESPONSE_SCHEMA = false;
    
    public function __construct(
        private readonly Security $security,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Format a success response
     * 
     * @param mixed $data The response data
     * @param int $status The HTTP status code
     * @param bool $isLoggedIn Whether the user is logged in
     * @param string|null $responseSchemaName Optional name of the JSON schema to validate the response against (e.g., 'responses/auth_login_success')
     * @return JsonResponse The formatted response
     */
    public function formatSuccess($data = null, ?string $responseSchemaName = null, int $status = Response::HTTP_OK, bool $isLoggedIn = false): JsonResponse
    {
        $isLoggedIn = $isLoggedIn || $this->security->getUser() !== null;

        // Normalize any Doctrine entities in the data using Symfony Serializer
        $normalizedData = Utils::normalizeWithSymfonySerializer($data);
        
        $responseData = [
            'status' => $status,
            'message' => Response::$statusTexts[$status] ?? 'OK',
            'error' => null,
            'logged_in' => $isLoggedIn,
            'meta' => [
                'version' => 'v1', // Consider making this configurable
                'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ],
            'data' => $normalizedData,
            // validation field is not included in success responses
        ];

        // Only perform schema validation in non-production environments
        if ($responseSchemaName !== null && $this->kernel->getEnvironment() !== 'prod' && self::VALIDATE_RESPONSE_SCHEMA) {
            try {
                // Deep convert arrays to objects for proper JSON Schema validation
                $responseDataForValidation = $this->arrayToObject($responseData);
                
                // Validate the entire responseData object
                $validationErrors = $this->jsonSchemaValidationService->validate($responseDataForValidation, $responseSchemaName);

                if (!empty($validationErrors)) {
                    $this->logger->error('API Response Schema Validation Failed.', [
                        'schema' => $responseSchemaName,
                        'errors' => $validationErrors,
                        // 'data' => $responseData, // Be cautious with logging sensitive data
                    ]);

                    // Add debug info directly to the responseData for non-prod environments
                    $responseData['_debug'] = ['validation_errors' => $validationErrors];
                }
            } catch (\Exception $e) {
                $this->logger->error('Error during response schema validation.', [
                    'schema' => $responseSchemaName,
                    'exception' => $e->getMessage(),
                ]);
                $responseData['_debug'] = ['validation_exception' => $e->getMessage()];
            }
        }

        return new JsonResponse($responseData, $status);
    }
    
    /**
     * Format an error response
     * 
     * @param string $error The error message
     * @param int $status The HTTP status code
     * @param mixed $data Additional error data
     * @param array|null $validationErrors Optional validation errors
     * @return JsonResponse The formatted response
     */
    public function formatError(string $error, int $status = Response::HTTP_BAD_REQUEST, $data = null, ?array $validationErrors = null): JsonResponse
    {
        $isLoggedIn = $this->security->getUser() !== null;
        
        $responseData = [
            'status' => $status,
            'message' => Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => $error,
            'logged_in' => $isLoggedIn,
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
            ],
            'data' => $data
        ];
        
        // Add validation errors if provided
        if ($validationErrors !== null) {
            $responseData['validation'] = $validationErrors;
        }
        
        // Only perform schema validation in non-production environments
        if ($this->kernel->getEnvironment() !== 'prod') {
            try {
                // Deep convert arrays to objects for proper JSON Schema validation
                $responseDataForValidation = $this->arrayToObject($responseData);
                
                // Determine which schema to use based on status code
                $schemaName = 'responses/common/_error_response_envelope';
                
                // Use specific error schemas for common status codes
                if ($status === Response::HTTP_NOT_FOUND) {
                    $schemaName = 'responses/errors/not_found_error';
                } elseif ($status === Response::HTTP_BAD_REQUEST) {
                    $schemaName = 'responses/errors/bad_request_error';
                } elseif ($status === Response::HTTP_UNAUTHORIZED) {
                    $schemaName = 'responses/errors/unauthorized_error';
                } elseif ($status === Response::HTTP_FORBIDDEN) {
                    $schemaName = 'responses/errors/forbidden_error';
                } elseif ($status === Response::HTTP_INTERNAL_SERVER_ERROR) {
                    $schemaName = 'responses/errors/internal_server_error';
                }
                
                // Validate against the appropriate error response schema
                $validationErrors = $this->jsonSchemaValidationService->validate(
                    $responseDataForValidation, 
                    $schemaName
                );

                if (!empty($validationErrors)) {
                    $this->logger->error('API Error Response Schema Validation Failed.', [
                        'schema' => $schemaName,
                        'errors' => $validationErrors,
                    ]);

                    // Add debug info directly to the responseData for non-prod environments
                    $responseData['_debug'] = ['validation_errors' => $validationErrors];
                }
            } catch (\Exception $e) {
                $this->logger->error('Error during error response schema validation.', [
                    'exception' => $e->getMessage(),
                ]);
                $responseData['_debug'] = ['validation_exception' => $e->getMessage()];
            }
        }
        
        return new JsonResponse($responseData, $status);
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
    
    /**
     * Recursively converts arrays to objects for JSON Schema validation
     * 
     * This is necessary because PHP's json_encode treats associative arrays as JSON objects,
     * but the JsonSchema validator expects actual objects for validation against object schemas.
     * 
     * @param mixed $data The data to convert
     * @return mixed The converted data
     */
    private function arrayToObject($data)
    {
        // If it's an array, convert it
        if (is_array($data)) {
            // Check if it's an associative array (has string keys)
            $isAssoc = false;
            foreach ($data as $key => $value) {
                if (is_string($key)) {
                    $isAssoc = true;
                    break;
                }
            }
            
            // If associative, convert to object
            if ($isAssoc) {
                $obj = new \stdClass();
                foreach ($data as $key => $value) {
                    $obj->$key = $this->arrayToObject($value);
                }
                return $obj;
            } else {
                // If sequential, keep as array but convert each element
                return array_map([$this, 'arrayToObject'], $data);
            }
        }
        
        // If it's already an object, recursively convert its properties
        if (is_object($data)) {
            foreach (get_object_vars($data) as $key => $value) {
                $data->$key = $this->arrayToObject($value);
            }
        }
        
        // Otherwise, return as is
        return $data;
    }
    
   
}
