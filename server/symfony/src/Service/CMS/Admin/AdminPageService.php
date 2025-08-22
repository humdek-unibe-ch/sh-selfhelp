<?php

namespace App\Service\CMS\Admin;

use App\Entity\Page;
use App\Entity\PagesSection;
use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\PageTypeRepository;
use App\Repository\SectionRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\CacheService;
use App\Service\CMS\Admin\PositionManagementService;
use App\Service\CMS\Admin\PageFieldService;
use App\Service\CMS\Admin\SectionRelationshipService;
use App\Service\CMS\Admin\AdminSectionUtilityService;
use App\Service\CMS\Admin\Traits\TranslationManagerTrait;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Core\BaseService;
use App\Service\CMS\Common\SectionUtilityService;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling page-related operations in the admin panel
 * ENTITY RULE
 */
class AdminPageService extends BaseService
{
    use TranslationManagerTrait;

    // ACL group name constants
    private const GROUP_ADMIN = 'admin';
    private const GROUP_SUBJECT = 'subject';
    private const GROUP_THERAPIST = 'therapist';

    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LookupService $lookupService,
        private readonly PageTypeRepository $pageTypeRepository,
        private readonly ManagerRegistry $doctrine,
        private readonly TransactionService $transactionService,
        private readonly PositionManagementService $positionManagementService,
        private readonly SectionUtilityService $sectionUtilityService,
        private readonly PageFieldService $pageFieldService,
        private readonly SectionRelationshipService $sectionRelationshipService,
        private readonly AdminSectionUtilityService $adminSectionUtilityService,
        private readonly ACLService $aclService,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly UserContextService $userContextService,
        private readonly UserContextAwareService $userContextAwareService,
        private readonly CacheService $cache
    ) {
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
        return $this->pageFieldService->getPageWithFields($pageKeyword);
    }

    /**
     * Get page sections with entity scope caching
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page sections in a hierarchical structure
     * @throws \Exception If page not found
     */
    public function getPageSections(string $pageKeyword): array
    {
        $cacheKey = "page_sections_{$pageKeyword}";
        return $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->getItem($cacheKey, function () use ($pageKeyword) {
                $this->userContextAwareService->checkAccess($pageKeyword, 'select');

                $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
                if (!$page) {
                    $this->throwNotFound('Page not found');
                }
                // Check if user has access to the page
                $this->userContextAwareService->checkAccess($pageKeyword, 'select');

                // Cache with entity scope for this specific page
                $result = $this->cache
                    ->withCategory(CacheService::CATEGORY_PAGES)
                    ->withEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId())
                    ->getItem("page_sections_scoped_{$pageKeyword}", function () use ($page) {
                        // Call stored procedure for hierarchical sections
                        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
                        return $this->sectionUtilityService->buildNestedSections($flatSections, false);
                    });

                return $result;
            });
    }

    /** Private methods */
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
        $pageAccessType = $this->lookupService->findByTypeAndCode(
            LookupService::PAGE_ACCESS_TYPES,
            $pageAccessTypeCode
        );
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
            $currentUser = $this->userContextAwareService->getCurrentUser();
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
            // Invalidate all page lists since a new page was created
            $this->cache
                ->withCategory(CacheService::CATEGORY_PAGES)
                ->invalidateAllListsInCategory();
            $this->cache
                ->withCategory(CacheService::CATEGORY_PERMISSIONS)
                ->invalidateAllListsInCategory();

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
            $this->userContextAwareService->checkAccess($pageKeyword, 'update');

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
                    $pageAccessType = $this->lookupService->findByTypeAndCode(
                        LookupService::PAGE_ACCESS_TYPES,
                        $pageData['pageAccessTypeCode']
                    );

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
                // Get the page type ID from the page entity
                $pageType = $page->getPageType();
                if (!$pageType) {
                    throw new ServiceException(
                        sprintf("Page %s does not have a page type assigned", $page->getKeyword()),
                        Response::HTTP_BAD_REQUEST
                    );
                }
                $pageTypeId = $pageType->getId();

                // Get all valid field IDs for this page type from pageType_fields
                $validFieldIds = $this->entityManager->getRepository(\App\Entity\PageTypeField::class)
                    ->createQueryBuilder('ptf')
                    ->select('f.id')
                    ->leftJoin('ptf.field', 'f')
                    ->leftJoin('ptf.pageType', 'pt')
                    ->where('pt.id = :pageTypeId')
                    ->andWhere('f.id IN (:fieldIds)')
                    ->setParameter('pageTypeId', $pageTypeId)
                    ->setParameter('fieldIds', $fieldIds)
                    ->getQuery()
                    ->getScalarResult();

                $validFieldIds = array_column($validFieldIds, 'id');
                $invalidFieldIds = array_diff($fieldIds, $validFieldIds);

                if (!empty($invalidFieldIds)) {
                    throw new ServiceException(
                        sprintf(
                            "Fields [%s] do not belong to page type %s (page %s)",
                            implode(', ', $invalidFieldIds),
                            $pageType->getName(),
                            $page->getKeyword()
                        ),
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }

            // Update field translations using dedicated service
            $this->pageFieldService->updatePageFields($page, $fields);

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

            // Invalidate entity-scoped cache for this specific page
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId());
            $this->cache
                ->withCategory(CacheService::CATEGORY_PAGES)
                ->withEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId())
                ->invalidateAllListsInCategory();
            $this->cache
                ->withCategory(CacheService::CATEGORY_PERMISSIONS)
                ->invalidateAllListsInCategory();

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
            $this->userContextAwareService->checkAccess($pageKeyword, 'delete');

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
                'DELETE FROM App\\Entity\\PagesFieldsTranslation pft WHERE pft.page = :page'
            )
                ->setParameter('page', $page)
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
            
            // Invalidate entity-scoped cache for this specific page
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $pageIdForLog);
            $this->cache
                ->withCategory(CacheService::CATEGORY_PAGES)
                ->invalidateAllListsInCategory();
            $this->cache
                ->withCategory(CacheService::CATEGORY_PERMISSIONS)
                ->invalidateAllListsInCategory();

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
        $result = $this->sectionRelationshipService->addSectionToPage($pageKeyword, $sectionId, $position, $oldParentSectionId);
        
        // Get page ID for entity scope invalidation
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if ($page) {
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId());
        }
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $sectionId);
        $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->invalidateAllListsInCategory();
        $this->cache
            ->withCategory(CacheService::CATEGORY_SECTIONS)
            ->invalidateAllListsInCategory();
        
        return $result;
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
        $this->sectionRelationshipService->removeSectionFromPage($pageKeyword, $sectionId);
        
        // Get page ID for entity scope invalidation
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if ($page) {
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId());
        }
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $sectionId);
        $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->invalidateAllListsInCategory();
        $this->cache
            ->withCategory(CacheService::CATEGORY_SECTIONS)
            ->invalidateAllListsInCategory();
    }
}
