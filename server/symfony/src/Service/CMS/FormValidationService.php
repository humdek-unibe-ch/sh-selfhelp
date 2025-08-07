<?php

namespace App\Service\CMS;

use App\Entity\Page;
use App\Entity\Section;
use App\Entity\PagesSection;
use App\Entity\SectionsFieldsTranslation;
use App\Entity\SectionsHierarchy;
use App\Entity\DataTable;
use App\Exception\ServiceException;
use App\Service\Core\UserContextAwareService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\DataTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for validating form submission permissions and relationships
 */
class FormValidationService extends UserContextAwareService
{
    private const FORM_STYLE_NAMES = [
        'formUserInput',
        'formUserInputLog'
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DataTableRepository $dataTableRepository,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
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
        $this->checkAccess($page->getKeyword(), 'insert');

        // Find the section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
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

            // Convert values to strings and check length
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

        // Find the section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // Validate that the section belongs to the page using stored procedure
        $this->validateSectionBelongsToPage($pageId, $sectionId);

        return [
            'page' => $page,
            'section' => $section,
            'validated' => true
        ];
    }
}
