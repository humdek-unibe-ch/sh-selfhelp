<?php

namespace App\Service\CMS;

use App\Exception\ServiceException;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling form file uploads with proper validation and storage
 * ENTITY RULE - Uses association objects instead of primitive foreign keys
 */
class FormFileUploadService extends BaseService
{
    private const UPLOAD_DIR = 'uploads/form-files/';
    private const ALLOWED_EXTENSIONS = [
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff', 'ico',
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf',
        // Archives
        'zip', 'rar', '7z', 'tar', 'gz',
        // Other
        'csv', 'json', 'xml'
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    private const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total per form submission

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly UserContextService $userContextService,
        private readonly CacheService $cache,
        private readonly string $projectDir
    ) {
    }

    /**
     * Process uploaded files from form data
     *
     * @param array $uploadedFiles Array of UploadedFile objects keyed by field name
     * @param int $userId The user ID
     * @param int $sectionId The section ID
     * @return array Array with processed file data keyed by field name
     * @throws ServiceException
     */
    public function processUploadedFiles(array $uploadedFiles, int $userId, int $sectionId): array
    {
        $processedFiles = [];
        $totalSize = 0;

        foreach ($uploadedFiles as $fieldName => $files) {
            if (!is_array($files)) {
                $files = [$files];
            }

            $filePaths = [];
            foreach ($files as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                // Validate file
                $this->validateFile($file);

                // Check total size
                $totalSize += $file->getSize();
                if ($totalSize > self::MAX_TOTAL_SIZE) {
                    throw new ServiceException(
                        'Total file size exceeds limit of ' . (self::MAX_TOTAL_SIZE / 1024 / 1024) . 'MB',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                // Generate secure filename and store file
                $filePath = $this->storeFile($file, $userId, $sectionId, $fieldName);
                $filePaths[] = $filePath;
            }

            if (!empty($filePaths)) {
                $processedFiles[$fieldName] = count($filePaths) === 1 ? $filePaths[0] : $filePaths;
            }
        }

        return $processedFiles;
    }

    /**
     * Store a single uploaded file
     *
     * @param UploadedFile $file The uploaded file
     * @param int $userId The user ID
     * @param int $sectionId The section ID
     * @param string $fieldName The field name
     * @return string The stored file path
     * @throws ServiceException
     */
    private function storeFile(UploadedFile $file, int $userId, int $sectionId, string $fieldName): string
    {
        // Generate secure filename
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $safeName = $this->generateSafeFilename($originalName, $extension);

        // Create directory structure: uploads/form-files/user_{userId}/section_{sectionId}/field_{fieldName}/
        $directory = sprintf(
            '%s/public/%suser_%d/section_%d/field_%s/',
            $this->projectDir,
            self::UPLOAD_DIR,
            $userId,
            $sectionId,
            $this->sanitizeFieldName($fieldName)
        );

        // Create directory if it doesn't exist
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new ServiceException(
                    'Failed to create upload directory',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        }

        // Move file to final location
        $fullPath = $directory . $safeName;
        try {
            $file->move($directory, $safeName);
        } catch (\Exception $e) {
            throw new ServiceException(
                'Failed to save uploaded file: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Return relative path from public directory
        return sprintf(
            '%suser_%d/section_%d/field_%s/%s',
            self::UPLOAD_DIR,
            $userId,
            $sectionId,
            $this->sanitizeFieldName($fieldName),
            $safeName
        );
    }

    /**
     * Validate an uploaded file
     *
     * @param UploadedFile $file The file to validate
     * @throws ServiceException
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            throw new ServiceException(
                'File upload failed: ' . $file->getErrorMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new ServiceException(
                'File size exceeds limit of ' . (self::MAX_FILE_SIZE / 1024 / 1024) . 'MB',
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new ServiceException(
                'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Additional security check: verify file content matches extension
        $this->validateFileContent($file, $extension);
    }

    /**
     * Validate file content matches the extension
     *
     * @param UploadedFile $file The file to validate
     * @param string $extension The file extension
     * @throws ServiceException
     */
    private function validateFileContent(UploadedFile $file, string $extension): void
    {
        $mimeType = $file->getMimeType();

        // Basic MIME type validation based on extension
        $allowedMimeTypes = [
            // Images
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml', 'text/plain'],
            'bmp' => ['image/bmp'],
            'tiff' => ['image/tiff'],
            'ico' => ['image/x-icon', 'image/vnd.microsoft.icon'],
            // Documents
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'rtf' => ['application/rtf', 'text/rtf'],
            // Archives
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/x-rar-compressed', 'application/x-rar'],
            '7z' => ['application/x-7z-compressed'],
            'tar' => ['application/x-tar'],
            'gz' => ['application/gzip', 'application/x-gzip'],
            // Other
            'csv' => ['text/csv', 'application/csv', 'text/plain'],
            'json' => ['application/json', 'text/plain'],
            'xml' => ['application/xml', 'text/xml', 'text/plain']
        ];

        if (!isset($allowedMimeTypes[$extension]) || !in_array($mimeType, $allowedMimeTypes[$extension])) {
            throw new ServiceException(
                'File content does not match the file extension',
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Generate a safe filename
     *
     * @param string $originalName The original filename
     * @param string $extension The file extension
     * @return string The safe filename
     */
    private function generateSafeFilename(string $originalName, string $extension): string
    {
        // Remove path components and dangerous characters
        $safeName = basename($originalName);
        $safeName = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $safeName);

        // Generate unique filename to prevent conflicts
        $timestamp = time();
        $random = bin2hex(random_bytes(4));

        return sprintf('%d_%s_%s.%s', $timestamp, $random, $safeName, $extension);
    }

    /**
     * Sanitize field name for use in directory structure
     *
     * @param string $fieldName The field name
     * @return string The sanitized field name
     */
    private function sanitizeFieldName(string $fieldName): string
    {
        // Replace non-alphanumeric characters with underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $fieldName);
        // Remove multiple consecutive underscores
        $sanitized = preg_replace('/_+/', '_', $sanitized);
        // Remove leading/trailing underscores
        return trim($sanitized, '_');
    }

    /**
     * Delete files associated with a record
     *
     * @param array $fileData Array of file paths or arrays of file paths
     * @throws ServiceException
     */
    public function deleteFiles(array $fileData): void
    {
        foreach ($fileData as $fieldName => $filePaths) {
            if (!is_array($filePaths)) {
                $filePaths = [$filePaths];
            }

            foreach ($filePaths as $filePath) {
                if (is_string($filePath) && !empty($filePath)) {
                    $fullPath = $this->projectDir . '/public/' . $filePath;
                    if (file_exists($fullPath)) {
                        if (!unlink($fullPath)) {
                            // Log warning but don't throw exception for cleanup failures
                            error_log("Failed to delete file: {$fullPath}");
                        }
                    }
                }
            }
        }
    }

    /**
     * Extract file data from form data for cleanup
     *
     * @param array $formData The form data containing file information
     * @return array Array of file paths keyed by field name
     */
    public function extractFileData(array $formData): array
    {
        $fileData = [];

        foreach ($formData as $fieldName => $fieldValue) {
            // Check if field value looks like a file path (contains upload directory)
            if (is_string($fieldValue) && str_contains($fieldValue, self::UPLOAD_DIR)) {
                $fileData[$fieldName] = $fieldValue;
            } elseif (is_array($fieldValue)) {
                // Check if array contains file paths
                $hasFilePaths = false;
                foreach ($fieldValue as $value) {
                    if (is_string($value) && str_contains($value, self::UPLOAD_DIR)) {
                        $hasFilePaths = true;
                        break;
                    }
                }
                if ($hasFilePaths) {
                    $fileData[$fieldName] = $fieldValue;
                }
            }
        }

        return $fileData;
    }

    /**
     * Check if a field name corresponds to a file input field
     *
     * @param string $fieldName The field name to check
     * @param int $sectionId The section ID
     * @return bool True if the field is a file input
     */
    public function isFileInputField(string $fieldName, int $sectionId): bool
    {
        // For now, we'll use a simple heuristic: if the field name contains 'file'
        // In a more sophisticated implementation, we would check the actual field style
        return str_contains(strtolower($fieldName), 'file');
    }
}
