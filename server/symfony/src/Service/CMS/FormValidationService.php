<?php

namespace App\Service\CMS;

use App\Exception\ServiceException;
use App\Service\CMS\Common\StyleNames;
use App\Service\Core\BaseService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\CacheService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\DataTableRepository;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * Service for validating form submission permissions and relationships
 */
class FormValidationService extends BaseService
{
    

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DataTableRepository $dataTableRepository,
        private readonly ACLService $aclService,
        private readonly UserContextService $userContextService,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly UserContextAwareService $userContextAwareService,
        private readonly CacheService $cache
    ) {
    }

    /**
     * Validate form submission request
     * 
     * @param int $pageId The page ID where form is submitted from
     * @param int $sectionId The section ID (form ID)
     * @param array $formData The form data being submitted
     * @return array Validation result with page and section info
     * @throws ServiceException If validation fails
     */
    public function validateFormSubmission(int $pageId, int $sectionId, array $formData): array
    {
        // Find the page
        $page = $this->pageRepository->find($pageId);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page (use 'insert' permission for form submission)
       $this->userContextAwareService->checkAccess($page->getKeyword(), 'insert');

        // Find the section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // Ensure section style allows submissions
        $styleName = $section->getStyle()?->getName();
        if (!in_array($styleName, StyleNames::FORM_STYLE_NAMES, true)) {
            throw new ServiceException(
                'Invalid section type for submission',
                Response::HTTP_BAD_REQUEST
            );
        }

        // Validate that the section belongs to the page using stored procedure
        $this->validateSectionBelongsToPage($pageId, $sectionId);

        // Validate form data structure
        $this->validateFormData($formData);

        return [
            'page' => $page,
            'section' => $section,
            'validated' => true
        ];
    }

    /**
     * Validate delete request: ACL delete permission, section in page, and correct section type
     */
    public function validateFormDeletion(int $pageId, int $sectionId): array
    {
        // Find the page
        $page = $this->pageRepository->find($pageId);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check delete permission on page
       $this->userContextAwareService->checkAccess($page->getKeyword(), 'delete');

        // Find the section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // Ensure section belongs to the page
        $this->validateSectionBelongsToPage($pageId, $sectionId);

        // Ensure section style is the display/input form type
        $styleName = $section->getStyle()?->getName();
        if ($styleName !== StyleNames::STYLE_SHOW_USER_INPUT) {
            throw new ServiceException(
                'Invalid section type for delete operation',
                Response::HTTP_BAD_REQUEST
            );
        }

        return [
            'page' => $page,
            'section' => $section,
            'validated' => true
        ];
    }

    /**
     * Validate that a section belongs to the specified page using stored procedure
     * 
     * @param int $pageId The page ID to check
     * @param int $sectionId The section ID to validate
     * @throws ServiceException If section doesn't belong to page
     */
    private function validateSectionBelongsToPage(int $pageId, int $sectionId): void
    {
        // Use repository method to get all sections for the page
        $sections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($pageId);
        
        // Check if our section ID is in the results
        $sectionFound = false;
        foreach ($sections as $section) {
            if ((int) $section['id'] === $sectionId) {
                $sectionFound = true;
                break;
            }
        }
        
        if (!$sectionFound) {
            $this->throwForbidden('Section does not belong to this page');
        }
    }



    /**
     * Validate form data structure
     * 
     * @param array $formData The form data to validate
     * @throws ServiceException If validation fails
     */
    private function validateFormData(array $formData): void
    {
        if (empty($formData)) {
            throw new ServiceException(
                'Form data cannot be empty',
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check for potentially dangerous fields
        $forbiddenFields = ['id', 'timestamp', 'id_actionTriggerTypes'];
        foreach ($forbiddenFields as $forbiddenField) {
            if (array_key_exists($forbiddenField, $formData)) {
                throw new ServiceException(
                    "Field '{$forbiddenField}' is not allowed in form data",
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        // Validate data types and lengths
        foreach ($formData as $fieldName => $fieldValue) {
            if (!is_string($fieldName)) {
                throw new ServiceException(
                    'Field names must be strings',
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (strlen($fieldName) > 255) {
                throw new ServiceException(
                    "Field name '{$fieldName}' is too long (max 255 characters)",
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Handle file input fields specially
            if (str_contains(strtolower($fieldName), 'file')) {
                // Allow empty objects, empty arrays, or file path strings for file input fields
                if ($fieldValue === null || $fieldValue === '' ||
                    (is_array($fieldValue) && empty($fieldValue)) ||
                    (is_object($fieldValue) && empty((array)$fieldValue))) {
                    // Empty file field is valid (no files uploaded)
                    continue;
                }

                // Allow file path strings
                if (is_string($fieldValue) && str_contains($fieldValue, 'uploads/form-files/')) {
                    // File paths are allowed for file input fields
                    continue;
                }

                // Allow JSON strings containing file information
                if (is_string($fieldValue) && $this->isValidFileJson($fieldValue)) {
                    // Valid JSON string with file information
                    continue;
                }

                // If it's not one of the allowed formats, check if it's a valid scalar
                if (!is_scalar($fieldValue)) {
                    throw new ServiceException(
                        "Field '{$fieldName}' must contain a valid file value (string, empty array, or empty object)",
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }

            // Handle translation arrays (new feature)
            if (is_array($fieldValue) && !empty($fieldValue) && isset($fieldValue[0]['language_id'])) {
                // Validate translation array format
                if (!$this->isValidTranslationArray($fieldValue)) {
                    throw new ServiceException(
                        "Field '{$fieldName}' contains invalid translation data format",
                        Response::HTTP_BAD_REQUEST
                    );
                }
                // Translation array is valid, continue to next field
                continue;
            }

            // Convert values to strings and check length for non-file fields
            if ($fieldValue !== null && !is_scalar($fieldValue)) {
                throw new ServiceException(
                    "Field '{$fieldName}' must contain a scalar value",
                    Response::HTTP_BAD_REQUEST
                );
            }

            $stringValue = (string) $fieldValue;
            if (strlen($stringValue) > 65535) { // TEXT field limit
                throw new ServiceException(
                    "Field '{$fieldName}' value is too long (max 65535 characters)",
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
    }

    /**
     * Check if a string contains valid JSON with file information
     *
     * @param string $jsonString The string to validate
     * @return bool True if valid file JSON
     */
    private function isValidFileJson(string $jsonString): bool
    {
        try {
            $decoded = json_decode($jsonString, true);
            if (!is_array($decoded)) {
                return false;
            }

            // Check if array contains valid filenames or file paths
            foreach ($decoded as $item) {
                if (!is_string($item)) {
                    return false;
                }

                // Allow either filenames or file paths
                if (!preg_match('/^[a-zA-Z0-9\-_\.\s\(\)]+\.[a-zA-Z0-9]{1,10}$/', $item) &&
                    !str_contains($item, 'uploads/form-files/')) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if an array contains valid translation data
     *
     * @param array $translationArray The array to validate
     * @return bool True if valid translation array
     */
    private function isValidTranslationArray(array $translationArray): bool
    {
        if (!is_array($translationArray) || empty($translationArray)) {
            return false;
        }

        foreach ($translationArray as $translation) {
            // Each translation must be an object with language_id and value
            if (!is_array($translation) ||
                !isset($translation['language_id']) ||
                !isset($translation['value'])) {
                return false;
            }

            // language_id must be a positive integer (or numeric string that can be converted to int)
            $languageId = $translation['language_id'];
            if (is_string($languageId)) {
                $languageId = is_numeric($languageId) ? (int) $languageId : null;
            }

            if (!is_int($languageId) || $languageId < 1) {
                return false;
            }

            // value can be string, number, boolean, or null
            $value = $translation['value'];
            if ($value !== null && !is_scalar($value)) {
                return false;
            }

            // If value is string, check length
            if (is_string($value) && strlen($value) > 65535) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate page access for anonymous users
     * This is a special validation for public form submissions
     * 
     * @param int $pageId The page ID
     * @param int $sectionId The section ID to validate
     * @return array Validation result with page and section info
     * @throws ServiceException If page is not accessible
     */
    public function validatePublicPageAccess(int $pageId, int $sectionId): array
    {
        $page = $this->pageRepository->find($pageId);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        if (!$page->isOpenAccess()) {
            $this->throwForbidden('Page is not open access');
        }

        // Find the section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // Validate that the section belongs to the page using stored procedure
        $this->validateSectionBelongsToPage($pageId, $sectionId);

        // Ensure section style allows submissions (public)
        $styleName = $section->getStyle()?->getName();
        if (!in_array($styleName, StyleNames::FORM_STYLE_NAMES, true)) {
            throw new ServiceException(
                'Invalid section type for submission',
                Response::HTTP_BAD_REQUEST
            );
        }

        return [
            'page' => $page,
            'section' => $section,
            'validated' => true
        ];
    }
}
