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
use App\Exception\ServiceException;
use App\Repository\LookupRepository;
use App\Repository\PageRepository;
use App\Repository\PageTypeRepository;
use App\Repository\SectionRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
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
    private const CMS_SELECT_PAGE_KEYWORD = 'cmsSelect';

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
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly LookupRepository $lookupRepository,
        private readonly PageTypeRepository $pageTypeRepository,
        private readonly ManagerRegistry $doctrine,
        private readonly TransactionService $transactionService,
        ACLService $aclService,
        UserContextService $userContextService
    ) {
        parent::__construct($userContextService, $aclService);
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
        if (!$this->hasAccess($page->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

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
        // Check if user has admin access to cms
        if (!$this->hasAccess($this->pageRepository->findOneBy(['keyword' => self::CMS_SELECT_PAGE_KEYWORD])->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        if (!$this->hasAccess($page->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

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
                $this->reorderPagePositions($page->getId(), $parentId, 'nav');
            }

            if ($footerPosition !== null) {
                $this->reorderPagePositions($page->getId(), $parentId, 'footer');
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
     * @param int $pageId ID of the page being added/modified
     * @param int|null $parentId ID of the parent page or null for root pages
     * @param string $positionType 'nav' or 'footer'
     * @return void
     */
    private function reorderPagePositions(int $pageId, ?int $parentId, string $positionType): void
    {
        // Get all pages with positions for the given parent and position type
        $pages = $positionType === 'nav'
            ? $this->pageRepository->findPagesWithNavPosition($parentId)
            : $this->pageRepository->findPagesWithFooterPosition($parentId);

        // Get the current page and its position
        $currentPage = $this->pageRepository->find($pageId);
        $currentPosition = $positionType === 'nav'
            ? $currentPage->getNavPosition()
            : $currentPage->getFooterPosition();

        // Create a map of all pages (excluding current page) with their positions
        $pageMap = [];
        foreach ($pages as $page) {
            if ($page->getId() !== $pageId) {
                $position = $positionType === 'nav'
                    ? $page->getNavPosition()
                    : $page->getFooterPosition();

                if ($position !== null) {
                    $pageMap[] = [
                        'id' => $page->getId(),
                        'position' => $position
                    ];
                }
            }
        }

        // Add the current page to the map
        $pageMap[] = [
            'id' => $pageId,
            'position' => $currentPosition ?? PHP_INT_MAX // If null, place at the end
        ];

        // Sort pages by position
        usort($pageMap, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        // Reassign positions in multiples of 10
        $finalPositions = [];
        $position = 10;

        foreach ($pageMap as $page) {
            $finalPositions[$page['id']] = $position;
            $position += 10;
        }

        // Update all positions in the database
        $this->pageRepository->updatePagePositions($finalPositions, $positionType);
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
            if (!$this->hasAccess($page->getId(), 'update')) {
                $this->throwForbidden('Access denied: You do not have permission to update this page');
            }

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
                // Only reorder positions if setting to a non-null value
                if ($pageData['navPosition'] !== null) {
                    // Reorder nav positions if needed
                    $this->reorderPagePositions(
                        $page->getId(),
                        $page->getParentPage() ? $page->getParentPage()->getId() : null,
                        'nav'
                    );
                }
            }

            if (array_key_exists('footerPosition', $pageData)) {
                $page->setFooterPosition($pageData['footerPosition']);
                // Only reorder positions if setting to a non-null value
                if ($pageData['footerPosition'] !== null) {
                    // Reorder footer positions if needed
                    $this->reorderPagePositions(
                        $page->getId(),
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
            if (!$this->hasAccess($page->getId(), 'delete')) {
                $this->throwForbidden('Access denied: You do not have permission to delete this page');
            }

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

    /************************* START PAGE & SECTION RELATIONSHIPS *************************/

    /**
     * Adds a section to a page with a specific position.
     *
     * @param string $pageKeyword The keyword of the page.
     * @param int $sectionId The ID of the section to add.
     * @param int|null $position The desired position (e.g., 5, 15, 25). This will be normalized.
     * @return PagesSection The new page-section relationship.
     * @throws ServiceException If the relationship already exists, or if entities are not found.
     */
    public function addSectionToPage(string $pageKeyword, int $sectionId, ?int $position): PagesSection
    {
        $this->entityManager->beginTransaction();
        try {
            $parentPage = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
            if (!$parentPage) {
                $this->throwNotFound('Page not found');
            }

            if (!$this->hasAccess($parentPage->getId(), 'update')) {
                $this->throwForbidden('Access denied to modify this page');
            }

            $childSection = $this->sectionRepository->find($sectionId);
            if (!$childSection) {
                $this->throwNotFound('Section not found');
            }


            // Remove from SectionsHierarchy if this section is currently a child in any section
            $sectionsHierarchyRepo = $this->entityManager->getRepository(SectionsHierarchy::class);
            $attachedHierarchies = $sectionsHierarchyRepo->findBy(['childSection' => $childSection]);
            foreach ($attachedHierarchies as $attached) {
                $this->entityManager->remove($attached);
            }
            if (count($attachedHierarchies) > 0) {
                $this->entityManager->flush();
            }


            // For PagesSection, check for existing relationship
            $existing = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['page' => $parentPage, 'section' => $childSection]);
            if ($existing) {
                // Just update the position and normalize
                $existing->setPosition($position);
                // Do NOT flush yet, normalize first
                $this->normalizePageSectionPositions($parentPage->getId());
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
            $this->normalizePageSectionPositions($parentPage->getId());
            $this->entityManager->commit();
            return $pageSection;

            $this->normalizePageSectionPositions($parentPage->getId());

            $this->entityManager->commit();
            return $pageHierarchy;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to add section to page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Removes a section from a page.
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

            $pageSection = $this->entityManager->getRepository(PagesSection::class)->findOneBy(['page' => $page, 'section' => $sectionId]);
            if (!$pageSection) {
                $this->throwNotFound('Section is not associated with this page.');
            }

            $this->entityManager->remove($pageSection);
            $this->entityManager->flush();

            $this->normalizePageSectionPositions($page->getId());

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to remove section from page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Normalizes the positions of all sections within a specific page.
     */
    private function normalizePageSectionPositions(int $pageId): void
    {
        // Accept an optional $movedSection, so we can ensure its new position is considered
        $pageSections = $this->entityManager->getRepository(PagesSection::class)->findBy(
            ['page' => $pageId],
            ['position' => 'ASC', 'idSections' => 'ASC']
        );

        // Sort by position, but if a section was just moved, use its new position
        usort($pageSections, function ($a, $b) {
            return ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0);
        });

        $currentPosition = 0;
        foreach ($pageSections as $pageSection) {
            $pageSection->setPosition($currentPosition);
            $currentPosition += 10;
        }
        // Do NOT flush here; let the caller flush after normalization
    }

    /************************* END PAGE & SECTION RELATIONSHIPS *************************/
}
