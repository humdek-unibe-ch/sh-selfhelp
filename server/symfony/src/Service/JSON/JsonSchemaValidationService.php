<?php

namespace App\Service\JSON;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\SchemaStorage;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use RuntimeException;
use JsonSchema\Exception\ExceptionInterface;

class JsonSchemaValidationService
{
    private Validator $validator;
    private string $schemaBaseDir;
    private UriRetriever $retriever;
    private SchemaStorage $schemaStorage;

    public function __construct(KernelInterface $kernel)
    {
        
        // Set up the base directory for schemas
        $this->schemaBaseDir = $kernel->getProjectDir() . '/config/schemas/api/v1/';
        $this->schemaBaseDir = str_replace('\\', '/', $this->schemaBaseDir);
        
        // Configure the URI retriever
        $this->retriever = new UriRetriever();
        
        // Set up schema storage with proper URI resolution
        $this->schemaStorage = new SchemaStorage($this->retriever);
        
        // Create the factory with schema storage
        $factory = new Factory($this->schemaStorage);
        
        // Create the validator with the factory
        $this->validator = new Validator($factory);
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
        $schemaFilePath = str_replace('\\', '/', $schemaFilePath);

        if (!file_exists($schemaFilePath)) {
            throw new FileNotFoundException(sprintf('Schema file not found: %s. Attempted absolute path: %s', 
                $schemaName, 
                $schemaFilePath
            ));
        }

        // Create a URI for the main schema file
        $schemaUri = 'file://' . str_replace('\\', '/', realpath($schemaFilePath));
        
        // Pre-load all referenced schemas in the same directory
        $this->preLoadReferencedSchemas(dirname($schemaFilePath));
        
        try {
            // Retrieve the schema
            $schemaObject = $this->retriever->retrieve($schemaUri);
            
            if ($schemaObject === null) {
                throw new RuntimeException('Invalid or empty JSON in schema file: ' . $schemaFilePath);
            }
            
            // Validate with schema resolution enabled
            $this->validator->validate(
                $data,
                $schemaObject,
                // Do not validate schema meta to allow UI-only keywords/types in request schemas
                Constraint::CHECK_MODE_APPLY_DEFAULTS
            );
            
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
            
        } catch (ExceptionInterface $e) {
            $errorMessage = 'Error validating against schema: ' . $schemaUri;
            $previousThrowable = null;

            if ($e instanceof \Throwable) {
                $detailedMessage = $e->getMessage();
                if (is_string($detailedMessage) && !empty(trim($detailedMessage))) {
                    $errorMessage .= ' - ' . $detailedMessage;
                }
                $previousThrowable = $e;
            }

            throw new RuntimeException($errorMessage, 0, $previousThrowable);
        }
    }
    
    /**
     * Pre-loads all JSON schema files in a directory to make them available for reference resolution.
     * 
     * @param string $directory The directory containing schema files
     */
    private function preLoadReferencedSchemas(string $directory): void
    {
        // First, register the base schema directory
        $baseSchemaDir = 'file://' . str_replace('\\', '/', $this->schemaBaseDir);
        $this->schemaStorage->addSchema($baseSchemaDir, (object)["id" => $baseSchemaDir]);
        
        // Load common schemas directory
        $commonDir = $this->schemaBaseDir . 'responses/common/';
        if (is_dir($commonDir)) {
            $this->loadSchemasFromDirectory($commonDir);
        }
        
        // Load schemas from the current directory
        $this->loadSchemasFromDirectory($directory);
        
        // Also load parent directory if we're in a subdirectory
        $parentDir = dirname($directory);
        if ($parentDir !== $directory && is_dir($parentDir)) {
            $this->loadSchemasFromDirectory($parentDir);
        }
    }
    
    /**
     * Loads all JSON schema files from a directory into the schema storage.
     * 
     * @param string $directory The directory to load schemas from
     */
    private function loadSchemasFromDirectory(string $directory): void
    {
        $directory = str_replace('\\', '/', $directory);
        
        if (!is_dir($directory)) {
            return;
        }
        
        $files = glob($directory . '*.json');
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            $uri = 'file://' . str_replace('\\', '/', realpath($file));
            
            try {
                $schema = $this->retriever->retrieve($uri);
                if ($schema !== null) {
                    // Register with the full path
                    $this->schemaStorage->addSchema($uri, $schema);
                    
                    // Also register with just the filename for relative references
                    $filename = basename($file);
                    $filenameUri = 'file://' . str_replace('\\', '/', realpath($directory)) . '/' . $filename;
                    $this->schemaStorage->addSchema($filenameUri, $schema);
                }
            } catch (\Exception $e) {
                // Skip files that can't be loaded
                continue;
            }
        }
    }
}
