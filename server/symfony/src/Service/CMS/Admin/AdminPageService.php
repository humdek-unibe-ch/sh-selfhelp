<?php

namespace App\Service\CMS\Admin;

use App\Entity\Field;
use App\Entity\Language;
use App\Entity\Page;
use App\Entity\PageTypeField;
use App\Entity\PagesFieldsTranslation;
use App\Entity\User;
use App\Entity\PagesSection;
use App\Entity\Section;
use App\Entity\SectionsHierarchy;
use App\Entity\PageType;
use App\Exception\ServiceException;
use App\Repository\LookupRepository;
use App\Repository\PageRepository;
use App\Repository\PageTypeRepository;
use App\Repository\SectionRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\CMS\Admin\PositionManagementService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling page-related operations in the admin panel
 */
class AdminPageService extends UserContextAwareService
{
    /************************* START ADMIN PAGES *************************/
    /**
     * CMS select page keyword
     */

    // ACL group name constants
    private const GROUP_ADMIN = 'admin';
    private const GROUP_SUBJECT = 'subject';
    private const GROUP_THERAPIST = 'therapist';

    /************************* END ADMIN PAGES *************************/

    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LookupRepository $lookupRepository,
        private readonly PageTypeRepository $pageTypeRepository,
        private readonly ManagerRegistry $doctrine,
        private readonly TransactionService $transactionService,
        private readonly PositionManagementService $positionManagementService,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository); 
    }

    /**
     * Get page with its fields and translations
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page with its fields and translations
     * @throws ServiceException If page not found or access denied
     */
    public function getPageWithFields(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);

        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        $this->checkAccess($pageKeyword, 'select');

        // Get page type fields based on the page's type
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('ptf', 'f', 'ft')
            ->from('App\Entity\PageTypeField', 'ptf')
            ->innerJoin('ptf.field', 'f')
            ->innerJoin('f.type', 'ft')
            ->where('ptf.pageType = :pageTypeId')
            ->setParameter('pageTypeId', $page->getPageType()->getId())
            ->orderBy('f.name', 'ASC');

        $pageTypeFields = $qb->getQuery()->getResult();

        // Get page fields associated with this page
        $pageFieldsMap = [];
        $pageFields = $this->entityManager->getRepository(PageTypeField::class)->findBy(['pageType' => $page->getPageType()->getId()]);
        foreach ($pageFields as $pageField) {
            $pageFieldsMap[$pageField->getField()->getId()] = $pageField;
        }

        // Get all translations for this page's fields
        $translationsMap = [];
        $translations = $this->entityManager->getRepository(PagesFieldsTranslation::class)
            ->findBy(['idPages' => $page->getId()]);

        foreach ($translations as $translation) {
            $fieldId = $translation->getIdFields();
            $langId = $translation->getIdLanguages();
            if (!isset($translationsMap[$fieldId])) {
                $translationsMap[$fieldId] = [];
            }
            $translationsMap[$fieldId][$langId] = $translation;
        }

        // Format fields with translations
        $formattedFields = [];
        foreach ($pageTypeFields as $pageTypeField) {
            $field = $pageTypeField->getField();
            $fieldId = $field->getId();

            // Get the pageField if it exists for this field
            $pageField = $pageFieldsMap[$fieldId] ?? null;

            $fieldData = [
                'id' => $fieldId,
                'name' => $field->getName(),
                'type' => $field->getType() ? $field->getType()->getName() : null,
                'default_value' => $pageField ? $pageField->getDefaultValue() : null,
                'help' => $pageField ? $pageField->getHelp() : null,
                'display' => $field->isDisplay(),  // Whether it's a content field (1) or property field (0)
                'translations' => []
            ];

            // Handle translations based on display flag
            if ($field->isDisplay()) {
                // Content field (display=1) - can have translations for each language
                if (isset($translationsMap[$fieldId])) {
                    foreach ($translationsMap[$fieldId] as $translation) {
                        $language = $translation->getLanguage();
                        $fieldData['translations'][] = [
                            'language_id' => $language->getId(),
                            'language_code' => $language->getLocale(),
                            'content' => $translation->getContent()
                        ];
                    }
                }
            } else {
                // Property field (display=0) - use language_id = 1 only
                $propertyTranslation = $translationsMap[$fieldId][1] ?? null;
                if ($propertyTranslation) {
                    $fieldData['translations'][] = [
                        'language_id' => 1,
                        'language_code' => 'property',  // This is a property, not actually language-specific
                        'content' => $propertyTranslation->getContent()
                    ];
                }
            }

            $formattedFields[] = $fieldData;
        }

        // Return page data with fields and their translations
        return [
            'page' => $page,
            'fields' => $formattedFields
        ];
    }

    /**
     * Get page sections
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page sections in a hierarchical structure
     * @throws \Exception If page not found
     */
    public function getPageSections(string $pageKeyword): array
    {
        $this->checkAccess($pageKeyword, 'select');

        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        // Check if user has access to the page
        $this->checkAccess($pageKeyword, 'select');

        // Call stored procedure for hierarchical sections
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        return $this->buildNestedSections($flatSections);
    }

    /** Private methods */
    /**
     * @brief Transforms a flat array of sections into a nested hierarchical structure
     * 
     * @param array $sections Flat array of section objects with level and path properties
     * @return array Nested array with children properly nested under their parents
     */
    private function buildNestedSections(array $sections): array
    {
        // Create a map of sections by ID for quick lookup
        $sectionsById = [];
        $rootSections = [];

        // First pass: index all sections by ID
        foreach ($sections as $section) {
            $section['children'] = [];
            $sectionsById[$section['id']] = $section;
        }

        // Second pass: build the hierarchy
        foreach ($sections as $section) {
            $id = $section['id'];

            // If it's a root section (level 0), add to root array
            if ($section['level'] === 0) {
                $rootSections[] = &$sectionsById[$id];
            } else {
                // Find parent using the path
                $pathParts = explode(',', $section['path']);
                if (count($pathParts) >= 2) {
                    $parentId = (int)$pathParts[count($pathParts) - 2];

                    // If parent exists, add this as its child
                    if (isset($sectionsById[$parentId])) {
                        $sectionsById[$parentId]['children'][] = &$sectionsById[$id];
                    }
                }
            }
        }

        // Recursively sort children by position
        $sortChildren = function (&$nodes) use (&$sortChildren) {
            usort($nodes, function ($a, $b) {
                return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
            });
            foreach ($nodes as &$node) {
                if (!empty($node['children'])) {
                    $sortChildren($node['children']);
                }
            }
        };
        $sortChildren($rootSections);
        return $rootSections;
    }

    /**
     * Create a new page
     * 
     * @param string $keyword Unique keyword for the page
     * @param string $pageAccessTypeCode Code of the page access type lookup
     * @param bool $isHeadless Whether the page is headless
     * @param bool $isOpenAccess Whether the page has open access
     * @param string|null $url URL for the page
     * @param int|null $navPosition Navigation position
     * @param int|null $footerPosition Footer position
     * @param int|null $parentId ID of the parent page
     * 
     * @return Page The created page entity
     * @throws ServiceException If validation fails or required entities not found
     */
    public function createPage(
        string $keyword,
        string $pageAccessTypeCode,
        bool $isHeadless = false,
        bool $isOpenAccess = false,
        ?string $url = null,
        ?int $navPosition = null,
        ?int $footerPosition = null,
        ?int $parentId = null,
    ): Page {

        // Check if keyword already exists
        if ($this->pageRepository->findOneBy(['keyword' => $keyword])) {
            $this->throwConflict("Page with keyword '{$keyword}' already exists");
        }

        // Check if url already exists
        if ($this->pageRepository->findOneBy(['url' => $url])) {
            $this->throwConflict("Page with url '{$url}' already exists");
        }

        // Get page access type by code
        $pageAccessType = $this->lookupRepository->findOneBy([
            'typeCode' => 'pageAccessTypes',
            'lookupCode' => $pageAccessTypeCode
        ]);
        if (!$pageAccessType) {
            $this->throwNotFound("Page access type with code '{$pageAccessTypeCode}' not found");
        }

        // Get parent page if provided
        $parentPage = null;
        if ($parentId) {
            $parentPage = $this->pageRepository->find($parentId);
            if (!$parentPage) {
                $this->throwNotFound("Parent page with ID {$parentId} not found");
            }
        }

        // Get action if provided
        $actionCode = LookupService::PAGE_ACTIONS_SECTIONS;
        $action = $this->lookupRepository->findOneBy([
            'typeCode' => 'pageActions',
            'lookupCode' => $actionCode
        ]);
        if (!$action) {
            $this->throwNotFound("Action with code '{$actionCode}' not found");
        }

        // Get default page type (experiment)
        $pageType = $this->pageTypeRepository->findOneBy(['name' => 'experiment']);
        if (!$pageType) {
            $this->throwNotFound("Default page type 'experiment' not found");
        }

        // Create new page entity
        $page = new Page();
        $page->setKeyword($keyword);
        $page->setPageAccessType($pageAccessType);
        $page->setIsHeadless($isHeadless);
        $page->setIsOpenAccess($isOpenAccess);
        $page->setUrl($url);
        $page->setNavPosition($navPosition);
        $page->setFooterPosition($footerPosition);
        $page->setParentPage($parentPage);
        $page->setPageType($pageType);
        $page->setIsSystem(false);
        $page->setAction($action);
        $page->setProtocol('GET');

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($page);
            $this->entityManager->flush(); // To get the page ID

            // Fetch groups by name
            $groupRepo = $this->entityManager->getRepository(\App\Entity\Group::class);
            $adminGroup = $groupRepo->findOneBy(['name' => self::GROUP_ADMIN]);
            $subjectGroup = $groupRepo->findOneBy(['name' => self::GROUP_SUBJECT]);
            $therapistGroup = $groupRepo->findOneBy(['name' => self::GROUP_THERAPIST]);
            if (!$adminGroup || !$subjectGroup || !$therapistGroup) {
                throw new ServiceException('One or more required groups not found.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // ACL for admin group (full access)
            $this->aclService->addGroupAcl($page, $adminGroup, true, true, true, true, $this->entityManager);

            // ACL for subject group (select only)
            $this->aclService->addGroupAcl($page, $subjectGroup, true, false, false, false, $this->entityManager);

            // ACL for therapist group (select only)
            $this->aclService->addGroupAcl($page, $therapistGroup, true, false, false, false, $this->entityManager);

            // ACL for creating user (full access)
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                throw new ServiceException('Current user not found.', Response::HTTP_UNAUTHORIZED);
            }
            $this->aclService->addUserAcl($page, $currentUser, true, true, true, true, $this->entityManager);

            // Reorder page positions if needed
            if ($navPosition !== null) {
                $this->reorderPagePositions($parentId, 'nav');
            }

            if ($footerPosition !== null) {
                $this->reorderPagePositions($parentId, 'footer');
            }

            $this->entityManager->flush();

            // Log the page creation transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'pages',
                $page->getId(),
                true,
                'Page created with keyword: ' . $keyword
            );

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to create page and assign ACLs: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
        return $page;
    }

    /**
     * Reorder page positions when a new page is added or an existing page position is changed
     * This function ensures all pages have positions in multiples of 10 (10, 20, 30...)
     *
     * @param int|null $pageId ID of the page
     * @param string $positionType 'nav' or 'footer'
     * @return void
     */
    private function reorderPagePositions(?int $pageId, string $positionType): void
    {
        $this->positionManagementService->reorderPagePositions($pageId, $positionType);
    }

    /**
     * Update an existing page and its field translations
     * 
     * @param string $pageKeyword The keyword of the page to update
     * @param array $pageData The page data to update
     * @param array $fields The fields to update
     * @return Page The updated page
     * @throws ServiceException If page not found or access denied
     */
    public function updatePage(string $pageKeyword, array $pageData, array $fields): Page
    {
        $this->entityManager->beginTransaction();

        try {
            // Find the page
            $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
            if (!$page) {
                $this->throwNotFound('Page not found');
            }

            // Check if user has update access to the page
            $this->checkAccess($pageKeyword, 'update');

            // Store original page for transaction logging
            $originalPage = clone $page;

            // Update page properties
            // Use array_key_exists instead of isset to handle explicit null values
            if (array_key_exists('url', $pageData)) {
                $page->setUrl($pageData['url']);
            }

            if (array_key_exists('headless', $pageData)) {
                $page->setIsHeadless($pageData['headless']);
            }

            if (array_key_exists('navPosition', $pageData)) {
                $page->setNavPosition($pageData['navPosition']);
                $this->entityManager->flush();
                // Only reorder positions if setting to a non-null value
                if ($pageData['navPosition'] !== null) {
                    // Reorder nav positions if needed
                    $this->reorderPagePositions(
                        $page->getParentPage() ? $page->getParentPage()->getId() : null,
                        'nav'
                    );
                }
            }

            if (array_key_exists('footerPosition', $pageData)) {
                $page->setFooterPosition($pageData['footerPosition']);
                $this->entityManager->flush();
                // Only reorder positions if setting to a non-null value
                if ($pageData['footerPosition'] !== null) {
                    // Reorder footer positions if needed
                    $this->reorderPagePositions(
                        $page->getParentPage() ? $page->getParentPage()->getId() : null,
                        'footer'
                    );
                }
            }

            if (array_key_exists('openAccess', $pageData)) {
                $page->setIsOpenAccess($pageData['openAccess']);
            }

            if (array_key_exists('pageAccessTypeCode', $pageData)) {
                if ($pageData['pageAccessTypeCode'] === null) {
                    // Set to null if explicitly provided as null
                    $page->setPageAccessType(null);
                } else {
                    // Find the page access type lookup
                    $pageAccessType = $this->lookupRepository->findOneBy([
                        'typeCode' => LookupService::PAGE_ACCESS_TYPES,
                        'lookupCode' => $pageData['pageAccessTypeCode']
                    ]);

                    if (!$pageAccessType) {
                        throw new ServiceException(
                            'Invalid page access type',
                            Response::HTTP_BAD_REQUEST
                        );
                    }
                    
                    $page->setPageAccessType($pageAccessType);
                }
            }

            // Flush page changes first to ensure we have a valid page ID
            $this->entityManager->flush();

            // Validate that all fields belong to the page's page type
            if (!empty($fields)) {
                $fieldIds = array_column($fields, 'fieldId');
                $validFieldIds = $this->entityManager->getRepository(\App\Entity\PagesField::class)
                    ->createQueryBuilder('pf')
                    ->select('pf.idFields')
                    ->where('pf.idPages = :pageId')
                    ->andWhere('pf.idFields IN (:fieldIds)')
                    ->setParameter('pageId', $page->getId())
                    ->setParameter('fieldIds', $fieldIds)
                    ->getQuery()
                    ->getScalarResult();
                
                $validFieldIds = array_column($validFieldIds, 'idFields');
                $invalidFieldIds = array_diff($fieldIds, $validFieldIds);
                
                if (!empty($invalidFieldIds)) {
                    throw new ServiceException(
                        sprintf("Fields [%s] do not belong to page %s", 
                            implode(', ', $invalidFieldIds), 
                            $page->getKeyword()
                        ),
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }

            // Update field translations
            foreach ($fields as $field) {
                $fieldId = $field['fieldId'];
                $languageId = $field['languageId'];
                $content = $field['content'];

                // Check if translation exists
                $existingTranslation = $this->entityManager->getRepository(PagesFieldsTranslation::class)
                    ->findOneBy([
                        'idPages' => $page->getId(),
                        'idFields' => $fieldId,
                        'idLanguages' => $languageId
                    ]);

                if ($existingTranslation) {
                    // Update existing translation
                    $existingTranslation->setContent($content);
                } else {
                    // Create new translation
                    $newTranslation = new PagesFieldsTranslation();
                    $newTranslation->setIdPages($page->getId());
                    $newTranslation->setIdFields($fieldId);
                    $newTranslation->setIdLanguages($languageId);
                    $newTranslation->setContent($content);

                    // Also set the entity relationships
                    $newTranslation->setPage($page);

                    // Get the Field entity
                    $field = $this->entityManager->getRepository(\App\Entity\Field::class)->find($fieldId);
                    if ($field) {
                        $newTranslation->setField($field);
                    }

                    // Get the Language entity
                    $language = $this->entityManager->getRepository(\App\Entity\Language::class)->find($languageId);
                    if ($language) {
                        $newTranslation->setLanguage($language);
                    }

                    $this->entityManager->persist($newTranslation);
                }
            }

            // Flush all changes again
            $this->entityManager->flush();

            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'pages',
                $page->getId(),
                (object) array("old_page" => $originalPage, "new_page" => $page),
                'Page updated: ' . $page->getKeyword() . ' (ID: ' . $page->getId() . ')'
            );

            $this->entityManager->commit();
            return $page;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to update page: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete a page by its keyword
     * 
     * @param string $pageKeyword The keyword of the page to delete
     * @return Page
     * @throws ServiceException If page not found or access denied
     */
    public function deletePage(string $pageKeyword): Page
    {
        $this->entityManager->beginTransaction();

        try {
            $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
            $deleted_page = clone $page;

            if (!$page) {
                $this->throwNotFound('Page not found');
            }

            // Check if user has delete access to the page
            $this->checkAccess($pageKeyword, 'delete');

            // Check if the page has children
            $children = $this->pageRepository->findBy(['parentPage' => $page->getId()]);
            if (count($children) > 0) {
                throw new ServiceException(
                    'Cannot delete page with children. Remove child pages first.',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // ACL entries will be automatically deleted via foreign key constraints with cascade on delete

            // Delete page fields translations
            $this->entityManager->createQuery(
                'DELETE FROM App\\Entity\\PagesFieldsTranslation pft WHERE pft.idPages = :pageId'
            )
                ->setParameter('pageId', $page->getId())
                ->execute();

            // Store page keyword for logging before deletion
            $pageKeywordForLog = $page->getKeyword();
            $pageIdForLog = $page->getId();

            // Delete the page
            $this->entityManager->remove($page);
            $this->entityManager->flush();

            // Log the page deletion transaction with the deleted page object
            // This ensures we capture the page data even after it's removed from the database
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'pages',
                $pageIdForLog,
                $deleted_page, // Pass the page object directly instead of a boolean
                'Page deleted with keyword: ' . $pageKeywordForLog
            );

            $this->entityManager->commit();
            return $deleted_page;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to delete page: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }
    
    /**
     * Add a section to a page
     * 
     * @param string $pageKeyword The keyword of the page
     * @param int $sectionId The ID of the section to add
     * @param int|null $position The position of the section on the page
     * @param int|null $oldParentSectionId The ID of the old parent section if moving from a section hierarchy
     * @return PagesSection The created or updated page section relationship
     * @throws ServiceException If page or section not found or access denied
     */
    public function addSectionToPage(string $pageKeyword, int $sectionId, ?int $position = null, ?int $oldParentSectionId = null): PagesSection
    {
        $this->entityManager->beginTransaction();
        try {
            // Find the page
            $parentPage = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
            if (!$parentPage) {
                $this->throwNotFound('Page not found');
            }
            
            // Check if user has update access to the page
            $this->checkAccess($pageKeyword, 'update');
            
            // Find the section
            $childSection = $this->entityManager->getRepository(Section::class)->find($sectionId);
            if (!$childSection) {
                $this->throwNotFound('Section not found');
            }
            
            // Remove old parent section relationship if needed
            if ($oldParentSectionId !== null) {
                $oldParentSection = $this->entityManager->getRepository(Section::class)->find($oldParentSectionId);
                if ($oldParentSection) {
                    $oldRelationship = $this->entityManager->getRepository(SectionsHierarchy::class)->findOneBy([
                        'parentSection' => $oldParentSection,
                        'childSection' => $childSection
                    ]);
                    if ($oldRelationship) {
                        $this->entityManager->remove($oldRelationship);
                        $this->entityManager->flush();
                    }
                }
            }
            
            // For PagesSection, check for existing relationship
            $existing = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['page' => $parentPage, 'section' => $childSection]);
            if ($existing) {
                // Just update the position and normalize
                $existing->setPosition($position);
                // Do NOT flush yet, normalize first
                $this->positionManagementService->normalizePageSectionPositions($parentPage->getId());
                $this->entityManager->flush();
                $this->entityManager->commit();
                return $existing;
            }
            
            $pageSection = new PagesSection();
            $pageSection->setPage($parentPage);
            $pageSection->setIdPages($parentPage->getId());
            $pageSection->setSection($childSection);
            $pageSection->setIdSections($childSection->getId());
            $pageSection->setPosition($position);
            $this->entityManager->persist($pageSection);
            $this->entityManager->flush();
            $this->positionManagementService->normalizePageSectionPositions($parentPage->getId());
            $this->entityManager->commit();
            return $pageSection;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to add section to page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Removes a section from a page.
     * If the section is directly associated with the page, it removes the association.
     * If the section is a child section in the page hierarchy, it deletes the section completely.
     *
     * @param string $pageKeyword The keyword of the page.
     * @param int $sectionId The ID of the section to remove.
     * @throws ServiceException If the relationship does not exist.
     */
    public function removeSectionFromPage(string $pageKeyword, int $sectionId): void
    {
        $this->entityManager->beginTransaction();
        try {
            $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
            if (!$page) {
                $this->throwNotFound('Page not found');
            }

            // Check if user has update access to the page
            $this->checkAccess($pageKeyword, 'update');

            // First, check if the section is directly associated with the page
            $pageSection = $this->entityManager->getRepository(PagesSection::class)->findOneBy(['page' => $page, 'section' => $sectionId]);
            
            if ($pageSection) {
                // Direct page section - just remove the association
                $this->entityManager->remove($pageSection);
                $this->entityManager->flush();
                $this->positionManagementService->normalizePageSectionPositions($page->getId());
            } else {
                // Not directly associated - check if it's a child section in the page hierarchy
                $section = $this->entityManager->getRepository(Section::class)->find($sectionId);
                if (!$section) {
                    $this->throwNotFound('Section not found');
                }
                
                // Check if this section belongs to the page hierarchy (either directly or as a child)
                $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
                $sectionIds = array_map(function($s) {
                    return is_array($s) && isset($s['id']) ? (string)$s['id'] : null;
                }, $flatSections);
                
                if (!in_array((string)$sectionId, $sectionIds, true)) {
                    $this->throwNotFound('Section is not associated with this page.');
                }
                
                // This is a child section that belongs to the page hierarchy - delete it completely
                // Remove from pages_sections (if any)
                $pagesSections = $this->entityManager->getRepository(PagesSection::class)->findBy(['section' => $section]);
                foreach ($pagesSections as $ps) {
                    $this->entityManager->remove($ps);
                }

                // Remove from sections_hierarchy as parent
                $hierarchiesAsParent = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(['parentSection' => $section]);
                foreach ($hierarchiesAsParent as $hierarchy) {
                    $this->entityManager->remove($hierarchy);
                }

                // Remove from sections_hierarchy as child
                $hierarchiesAsChild = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(['childSection' => $section]);
                foreach ($hierarchiesAsChild as $hierarchy) {
                    $this->entityManager->remove($hierarchy);
                }

                // Finally remove the section itself
                $this->entityManager->remove($section);
                $this->entityManager->flush();
            }

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to remove section from page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }
}
