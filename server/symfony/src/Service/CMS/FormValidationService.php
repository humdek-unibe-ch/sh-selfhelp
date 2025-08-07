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
     * @param string $formId The form ID (dataTable name)
     * @param array $formData The form data being submitted
     * @return array Validation result with page and dataTable info
     * @throws ServiceException If validation fails
     */
    public function validateFormSubmission(int $pageId, string $formId, array $formData): array
    {
        // Find the page
        $page = $this->pageRepository->find($pageId);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page (use 'insert' permission for form submission)
        $this->checkAccess($page->getKeyword(), 'insert');

        // Find the dataTable by name (form_id)
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $formId]);
        if (!$dataTable) {
            $this->throwNotFound('Form not found');
        }

        // Validate that the form belongs to the page
        $this->validateFormBelongsToPage($page, $dataTable);

        // Validate form data structure
        $this->validateFormData($formData);

        return [
            'page' => $page,
            'dataTable' => $dataTable,
            'validated' => true
        ];
    }

    /**
     * Validate that a form (dataTable) belongs to the specified page
     * 
     * @param Page $page The page to check
     * @param DataTable $dataTable The form's dataTable
     * @throws ServiceException If form doesn't belong to page
     */
    private function validateFormBelongsToPage(Page $page, DataTable $dataTable): void
    {
        // Find form sections on the page that match the dataTable name
        $formSection = $this->findFormSectionOnPage($page, $dataTable->getName());
        
        if (!$formSection) {
            $this->throwForbidden('Form does not belong to this page');
        }
    }

    /**
     * Find form section on page that matches the dataTable name
     * 
     * @param Page $page The page to search
     * @param string $formName The form name to match
     * @return Section|null The matching form section
     */
    private function findFormSectionOnPage(Page $page, string $formName): ?Section
    {
        // Get all sections on the page (direct and hierarchical)
        $allSections = $this->getAllPageSections($page);
        
        foreach ($allSections as $section) {
            if ($this->isFormSection($section) && $this->sectionMatchesFormName($section, $formName)) {
                return $section;
            }
        }
        
        return null;
    }

    /**
     * Get all sections on a page (direct and hierarchical)
     * 
     * @param Page $page The page
     * @return Section[] Array of sections
     */
    private function getAllPageSections(Page $page): array
    {
        $sections = [];
        
        // Get direct page sections
        $pageSections = $this->entityManager->getRepository(PagesSection::class)
            ->findBy(['page' => $page]);
        
        foreach ($pageSections as $pageSection) {
            $section = $pageSection->getSection();
            if ($section) {
                $sections[] = $section;
                // Get child sections recursively
                $childSections = $this->getChildSections($section);
                $sections = array_merge($sections, $childSections);
            }
        }
        
        return $sections;
    }

    /**
     * Get child sections recursively
     * 
     * @param Section $parentSection The parent section
     * @return Section[] Array of child sections
     */
    private function getChildSections(Section $parentSection): array
    {
        $childSections = [];
        
        $hierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)
            ->findBy(['parentSection' => $parentSection]);
        
        foreach ($hierarchies as $hierarchy) {
            $childSection = $hierarchy->getChildSection();
            if ($childSection) {
                $childSections[] = $childSection;
                // Recursively get grandchildren
                $grandChildren = $this->getChildSections($childSection);
                $childSections = array_merge($childSections, $grandChildren);
            }
        }
        
        return $childSections;
    }

    /**
     * Check if a section is a form section
     * 
     * @param Section $section The section to check
     * @return bool True if it's a form section
     */
    private function isFormSection(Section $section): bool
    {
        $style = $section->getStyle();
        if (!$style) {
            return false;
        }
        
        return in_array($style->getName(), self::FORM_STYLE_NAMES);
    }

    /**
     * Check if section matches form name
     * This will check the section's "name" field value against the form name
     * 
     * @param Section $section The section to check
     * @param string $formName The form name to match
     * @return bool True if matches
     */
    private function sectionMatchesFormName(Section $section, string $formName): bool
    {
        // Get the section's field translations to find the "name" field
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('sft')
           ->from(SectionsFieldsTranslation::class, 'sft')
           ->join('sft.field', 'f')
           ->where('sft.section = :section')
           ->andWhere('f.name = :fieldName')
           ->setParameter('section', $section)
           ->setParameter('fieldName', 'name');
        
        $translations = $qb->getQuery()->getResult();
        
        foreach ($translations as $translation) {
            // Compare the content (form name) with the dataTable name
            if ($translation->getContent() === $formName) {
                return true;
            }
        }
        
        return false;
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
     * @return Page The page if accessible
     * @throws ServiceException If page is not accessible
     */
    public function validatePublicPageAccess(int $pageId): Page
    {
        $page = $this->pageRepository->find($pageId);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // For public access, we still need to check if the page allows anonymous submissions
        // This could be extended with a page-level setting for public forms
        // For now, we'll allow all pages with form sections to accept public submissions
        
        $hasFormSections = $this->pageHasFormSections($page);
        if (!$hasFormSections) {
            $this->throwForbidden('This page does not accept form submissions');
        }

        return $page;
    }

    /**
     * Check if page has form sections
     * 
     * @param Page $page The page to check
     * @return bool True if page has form sections
     */
    private function pageHasFormSections(Page $page): bool
    {
        $allSections = $this->getAllPageSections($page);
        
        foreach ($allSections as $section) {
            if ($this->isFormSection($section)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get form sections on a page
     * 
     * @param Page $page The page
     * @return Section[] Array of form sections
     */
    public function getFormSectionsOnPage(Page $page): array
    {
        $allSections = $this->getAllPageSections($page);
        $formSections = [];
        
        foreach ($allSections as $section) {
            if ($this->isFormSection($section)) {
                $formSections[] = $section;
            }
        }
        
        return $formSections;
    }
}
