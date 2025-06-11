<?php

namespace App\Controller\Trait;

use App\Exception\RequestValidationException;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait for validating API requests against JSON schemas
 */
trait RequestValidatorTrait
{
    /**
     * Validates a request against a JSON schema
     *
     * @param Request $request The request to validate
     * @param string $schemaName The name of the schema to validate against (e.g., 'requests/auth/login')
     * @param JsonSchemaValidationService $jsonSchemaValidationService The JSON schema validation service
     * @return array The validated request data
     * @throws RequestValidationException If validation fails
     * @throws \InvalidArgumentException If the request body is not valid JSON
     */
    protected function validateRequest(
        Request $request,
        string $schemaName,
        JsonSchemaValidationService $jsonSchemaValidationService
    ): array {
        // Parse JSON request body
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON payload: ' . json_last_error_msg());
        }

        // Validate against schema
        $validationErrors = $jsonSchemaValidationService->validate($this->convertToObject($data), $schemaName);
        if (!empty($validationErrors)) {
            throw new RequestValidationException(
                $validationErrors,
                $schemaName,
                $data,
                'Request validation failed for schema: ' . $schemaName . ' with errors: ' . json_encode($validationErrors)
            );
        }

        return $data;
    }

    private function convertToObject(mixed $value): mixed
    {
        if (is_array($value)) {
            // Fix: empty array is not associative
            if ($value === []) {
                return [];
            }

            // Check if associative
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssoc) {
                return (object) array_map([$this, 'convertToObject'], $value);
            } else {
                return array_map([$this, 'convertToObject'], $value);
            }
        }
        return $value;
    }
}
