<?php

namespace App\Controller\Api\V1\Frontend;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\DataService;
use App\Service\CMS\FormValidationService;
use App\Service\CMS\FormFileUploadService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\Core\LookupService;
use App\Service\JSON\JsonSchemaValidationService;
use App\Service\Auth\UserContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for public form data submissions
 */
class FormController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly DataService $dataService,
        private readonly FormValidationService $formValidationService,
        private readonly FormFileUploadService $formFileUploadService,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Submit form data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function submitForm(Request $request): JsonResponse
    {
        try {
            // Handle both JSON and multipart form data
            if (str_starts_with($request->headers->get('Content-Type', ''), 'application/json')) {
                // JSON request
                $requestData = $this->validateRequest($request, 'requests/frontend/submit_form', $this->jsonSchemaValidationService);
                $pageId = $requestData['page_id'];
                $sectionId = $requestData['section_id'];
                $formData = $requestData['form_data'];
            } else {
                // Multipart form data request
                $pageId = (int) $request->request->get('page_id');
                $sectionId = (int) $request->request->get('section_id');
                
                // Extract form data from request parameters, excluding our control fields
                $formData = [];
                foreach ($request->request->all() as $key => $value) {
                    if (!in_array($key, ['page_id', 'section_id', '__id_sections'])) {
                        $formData[$key] = $value;
                    }
                }
                
                // Validate required fields
                if (!$pageId || !$sectionId) {
                    throw new \InvalidArgumentException('page_id and section_id are required');
                }
            }

            // Determine if user is authenticated
            $currentUser = $this->userContextService->getCurrentUser();
            $isAuthenticated = $currentUser !== null;
            $userId = $currentUser ? $currentUser->getId() : 1; // Guest user fallback

            // Validate form submission
            if ($isAuthenticated) {
                // Authenticated user - full validation
                $validationResult = $this->formValidationService->validateFormSubmission($pageId, $sectionId, $formData);
            } else {
                // Anonymous user - public validation
                $validationResult = $this->formValidationService->validatePublicPageAccess($pageId, $sectionId);
            }

            // Process file uploads if any file fields are present
            $processedFormData = $this->processFileUploads($formData, $request, $userId, $sectionId);

            // Save form data using section ID as table name
            $recordId = $this->dataService->saveData(
                (string) $sectionId,
                $processedFormData,
                LookupService::TRANSACTION_BY_BY_USER
            );

            if ($recordId === false) {
                return $this->apiResponseFormatter->formatError(
                    'Failed to save form data',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            // Prepare response data
            $responseData = [
                'record_id' => $recordId,
                'section_id' => $sectionId,
                'page_id' => $pageId,
                'submitted_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                'user_authenticated' => $isAuthenticated
            ];

            return $this->apiResponseFormatter->formatSuccess(
                $responseData,
                'responses/frontend/form_submitted'
            );

        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Process file uploads from form data
     *
     * @param array $formData The form data
     * @param Request $request The HTTP request
     * @param int $userId The user ID
     * @param int $sectionId The section ID
     * @return array The processed form data with file paths
     */
    private function processFileUploads(array $formData, Request $request, int $userId, int $sectionId): array
    {
        $processedFormData = $formData;
        $uploadedFiles = $request->files->all();

        // Check if there are any uploaded files
        if (empty($uploadedFiles)) {
            return $processedFormData;
        }

        // Check if this is a multipart request (direct file uploads)
        $isMultipart = !str_starts_with($request->headers->get('Content-Type', ''), 'application/json');

        if ($isMultipart) {
            // Handle direct file uploads from multipart form data
            foreach ($uploadedFiles as $fieldName => $uploadedFile) {
                if ($this->formFileUploadService->isFileInputField($fieldName, $sectionId)) {
                    try {
                        // Ensure we have an array of files
                        $files = is_array($uploadedFile) ? $uploadedFile : [$uploadedFile];
                        
                        // Process the uploaded files
                        $processedFiles = $this->formFileUploadService->processUploadedFiles(
                            [$fieldName => $files],
                            $userId,
                            $sectionId
                        );

                        // Update the form data with file paths
                        if (isset($processedFiles[$fieldName])) {
                            $processedFormData[$fieldName] = is_array($processedFiles[$fieldName]) 
                                ? json_encode($processedFiles[$fieldName])
                                : $processedFiles[$fieldName];
                        }
                    } catch (\Exception $e) {
                        // If file processing fails, log error but continue
                        throw $e;
                    }
                }
            }
        } else {
            // Handle JSON-based file uploads (matching by filename)
            foreach ($formData as $fieldName => $fieldValue) {
                // Check if this field contains file information (JSON array of filenames)
                if ($this->isFileField($fieldName, $fieldValue)) {
                    try {
                        // Parse the JSON array of filenames
                        $fileNames = $this->parseFileNames($fieldValue);

                        if (!empty($fileNames)) {
                            // Find matching uploaded files
                            $matchingFiles = $this->findMatchingUploadedFiles($fileNames, $uploadedFiles);

                            if (!empty($matchingFiles)) {
                                // Process the uploaded files
                                $processedFiles = $this->formFileUploadService->processUploadedFiles(
                                    $matchingFiles,
                                    $userId,
                                    $sectionId
                                );

                                // Update the form data with file paths
                                if (isset($processedFiles[$fieldName])) {
                                    $processedFormData[$fieldName] = json_encode($processedFiles[$fieldName]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // If file processing fails, keep the original value
                        // This allows the form to still be submitted even if file processing fails
                        error_log("File processing failed for field {$fieldName}: " . $e->getMessage());
                    }
                }
            }
        }

        return $processedFormData;
    }

    /**
     * Check if a field contains file information
     *
     * @param string $fieldName The field name
     * @param mixed $fieldValue The field value
     * @return bool True if this is a file field
     */
    private function isFileField(string $fieldName, mixed $fieldValue): bool
    {
        // Check if field name suggests it's a file field
        if (!$this->formFileUploadService->isFileInputField($fieldName, 0)) {
            return false;
        }

        // Allow empty objects, empty arrays, or null values (no files uploaded)
        if ($fieldValue === null || $fieldValue === '' ||
            (is_array($fieldValue) && empty($fieldValue)) ||
            (is_object($fieldValue) && empty((array)$fieldValue))) {
            return true;
        }

        // Check if the value looks like a JSON array of filenames
        if (!is_string($fieldValue)) {
            return false;
        }

        // Try to decode as JSON
        $decoded = json_decode($fieldValue, true);
        if (!is_array($decoded)) {
            return false;
        }

        // Allow empty arrays in JSON
        if (empty($decoded)) {
            return true;
        }

        // Check if array contains strings that look like filenames
        foreach ($decoded as $item) {
            if (!is_string($item) || empty($item)) {
                return false;
            }
            // Basic filename validation
            if (!preg_match('/^[a-zA-Z0-9\-_\.\s\(\)]+\.[a-zA-Z0-9]{1,10}$/', $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse file names from various formats
     *
     * @param mixed $value The value containing filenames (JSON string, array, or object)
     * @return array Array of filenames
     * @throws \Exception If parsing fails
     */
    private function parseFileNames(mixed $value): array
    {
        // Handle empty objects, arrays, or null values
        if ($value === null || $value === '' ||
            (is_array($value) && empty($value)) ||
            (is_object($value) && empty((array)$value))) {
            return [];
        }

        // Handle JSON strings
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                throw new \Exception('Invalid JSON format for file names');
            }
            return array_filter($decoded, function($name) {
                return is_string($name) && !empty(trim($name));
            });
        }

        // Handle arrays directly
        if (is_array($value)) {
            return array_filter($value, function($name) {
                return is_string($name) && !empty(trim($name));
            });
        }

        throw new \Exception('Invalid format for file names');
    }

    /**
     * Find uploaded files that match the filenames in the form data
     *
     * @param array $fileNames Array of expected filenames
     * @param array $uploadedFiles Array of uploaded files
     * @return array Array of matching UploadedFile objects keyed by field name
     */
    private function findMatchingUploadedFiles(array $fileNames, array $uploadedFiles): array
    {
        $matchingFiles = [];

        foreach ($fileNames as $fileName) {
            // Look for uploaded files with matching names
            foreach ($uploadedFiles as $fieldName => $uploadedFile) {
                if ($uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    if ($uploadedFile->getClientOriginalName() === $fileName) {
                        $matchingFiles[$fieldName] = $uploadedFile;
                        break;
                    }
                } elseif (is_array($uploadedFile)) {
                    // Handle multiple files in array
                    foreach ($uploadedFile as $file) {
                        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile &&
                            $file->getClientOriginalName() === $fileName) {
                            if (!isset($matchingFiles[$fieldName])) {
                                $matchingFiles[$fieldName] = [];
                            }
                            $matchingFiles[$fieldName][] = $file;
                            break 2;
                        }
                    }
                }
            }
        }

        return $matchingFiles;
    }

    /**
     * Update form data (for authenticated users only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateForm(Request $request): JsonResponse
    {
        try {
            // Check authentication
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->apiResponseFormatter->formatError(
                    'Authentication required',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Handle both JSON and multipart form data
            if (str_starts_with($request->headers->get('Content-Type', ''), 'application/json')) {
                // JSON request
                $requestData = $this->validateRequest($request, 'requests/frontend/update_form', $this->jsonSchemaValidationService);
                $pageId = $requestData['page_id'];
                $sectionId = $requestData['section_id'];
                $formData = $requestData['form_data'];
                $updateBasedOn = $requestData['update_based_on'] ?? null;
            } else {
                // Multipart form data request
                $pageId = (int) $request->request->get('page_id');
                $sectionId = (int) $request->request->get('section_id');
                
                // Extract form data from request parameters, excluding our control fields
                $formData = [];
                foreach ($request->request->all() as $key => $value) {
                    if (!in_array($key, ['page_id', 'section_id', 'update_based_on', '__id_sections'])) {
                        $formData[$key] = $value;
                    }
                }
                
                // Handle update_based_on for multipart requests
                $updateBasedOnJson = $request->request->get('update_based_on');
                $updateBasedOn = $updateBasedOnJson ? json_decode($updateBasedOnJson, true) : null;
                
                // Validate required fields
                if (!$pageId || !$sectionId) {
                    throw new \InvalidArgumentException('page_id and section_id are required');
                }
            }

            // Validate form submission
            $validationResult = $this->formValidationService->validateFormSubmission($pageId, $sectionId, $formData);

            // Process file uploads if any file fields are present
            $processedFormData = $this->processFileUploads($formData, $request, $currentUser->getId(), $sectionId);

            // Update form data
            $recordId = $this->dataService->saveData(
                (string) $sectionId,
                $processedFormData,
                LookupService::TRANSACTION_BY_BY_USER,
                $updateBasedOn,
                true // own entries only
            );

            if ($recordId === false) {
                return $this->apiResponseFormatter->formatError(
                    'Failed to update form data',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            // Prepare response data
            $responseData = [
                'record_id' => $recordId,
                'section_id' => $sectionId,
                'page_id' => $pageId,
                'updated_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
            ];

            return $this->apiResponseFormatter->formatSuccess(
                $responseData,
                'responses/frontend/form_updated'
            );

        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete form data (for authenticated users only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteForm(Request $request): JsonResponse
    {
        try {
            // Check authentication
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->apiResponseFormatter->formatError(
                    'Authentication required',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request schema (DELETE with JSON body)
            $requestData = $this->validateRequest($request, 'requests/frontend/delete_form', $this->jsonSchemaValidationService);
            $recordId = (int) $requestData['record_id'];
            $pageId = (int) $requestData['page_id'];
            $sectionId = (int) $requestData['section_id'];

            // Validate ACL delete access and that section belongs to page and is correct type
            $this->formValidationService->validateFormDeletion($pageId, $sectionId);

            // Delete form data
            $success = $this->dataService->deleteData($recordId, true);

            if (!$success) {
                return $this->apiResponseFormatter->formatError(
                    'Failed to delete form data or record not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return $this->apiResponseFormatter->formatSuccess(
                [
                    'record_id' => $recordId,
                    'section_id' => $sectionId,
                    'page_id' => $pageId,
                ],
                'responses/frontend/form_deleted'
            );

        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }    
}