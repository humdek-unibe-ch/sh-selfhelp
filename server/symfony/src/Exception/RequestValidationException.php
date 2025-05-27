<?php

namespace App\Exception;

/**
 * Exception thrown when request validation fails
 */
class RequestValidationException extends \Exception
{
    private array $validationErrors;
    private string $schemaName;
    private array $requestData;

    /**
     * Constructor
     *
     * @param array $validationErrors The validation errors
     * @param string $schemaName The name of the schema that failed validation
     * @param array $requestData The request data that failed validation
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        array $validationErrors, 
        string $schemaName,
        array $requestData = [],
        string $message = 'Validation failed', 
        int $code = 400, 
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->validationErrors = $validationErrors;
        $this->schemaName = $schemaName;
        $this->requestData = $requestData;
    }

    /**
     * Get the validation errors
     *
     * @return array The validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
    
    /**
     * Get the schema name
     *
     * @return string The schema name
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }
    
    /**
     * Get the request data
     *
     * @return array The request data
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }
}
