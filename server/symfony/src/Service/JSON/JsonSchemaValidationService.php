<?php

namespace App\Service\JSON; // Updated namespace

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Uri\UriRetriever;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use RuntimeException;

class JsonSchemaValidationService
{
    private Validator $validator;
    private string $schemaBaseDir;
    private UriRetriever $retriever;

    public function __construct(KernelInterface $kernel)
    {
        $this->validator = new Validator();
        // schemaBaseDir remains the same, pointing to config/schemas
        $this->schemaBaseDir = str_replace('\\', '/', $kernel->getProjectDir()) . '/config/schemas/api/v1/';
        $this->retriever = new UriRetriever();
    }

    /**
     * Validates data against a given JSON schema.
     *
     * @param object|array $data The data to validate (decoded JSON).
     * @param string $schemaName The relative path of the schema file from schemaBaseDir (e.g., "requests/user_create").
     * @return array An array of validation error messages. Empty if valid.
     */
    public function validate(object|array $data, string $schemaName): array
    {
        $schemaFilePath = $this->schemaBaseDir . $schemaName . '.json';

        if (!file_exists($schemaFilePath)) {
            throw new FileNotFoundException(sprintf('Schema file not found: %s. Attempted absolute path: %s', $schemaName, realpath($this->schemaBaseDir) . DIRECTORY_SEPARATOR . $schemaName . '.json'));
        }

        $schemaUri = 'file://' . realpath($schemaFilePath);

        try {
            $schemaObject = $this->retriever->retrieve($schemaUri);
        } catch (\JsonSchema\Exception\ExceptionInterface $e) {
            throw new RuntimeException('Error retrieving schema: ' . $schemaUri . ' - ' . $e->getMessage(), 0, $e);
        }
        
        if ($schemaObject === null) {
             throw new RuntimeException('Invalid or empty JSON in schema file: ' . $schemaFilePath . ' (resolved as: ' . $schemaUri . ')');
        }

        $this->validator->validate($data, $schemaObject, Constraint::CHECK_MODE_VALIDATE_SCHEMA | Constraint::CHECK_MODE_APPLY_DEFAULTS);

        $errors = [];
        if (!$this->validator->isValid()) {
            foreach ($this->validator->getErrors() as $error) {
                $pointer = $error['property'] ?? $error['pointer'] ?? 'object';
                $message = $error['message'] ?? 'Unknown validation error';
                $errors[] = sprintf("Field '%s': %s", str_replace('/', '.', ltrim($pointer, '/')), $message);
            }
        }
        
        $this->validator->reset();
        return $errors;
    }
}
