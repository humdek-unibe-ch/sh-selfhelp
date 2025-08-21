<?php

namespace App\Service\CMS\Admin;

use App\Entity\Section;
use App\Entity\PagesSection;
use App\Entity\SectionsHierarchy;
use App\Exception\ServiceException;
use App\Service\CMS\Admin\Traits\RelationshipManagerTrait;
use App\Service\Core\BaseService;
use App\Service\Core\TransactionService;
use App\Service\ACL\ACLService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling section relationship operations
 */
class SectionRelationshipService extends BaseService
{
    use RelationshipManagerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PositionManagementService $positionManagementService,
        private readonly TransactionService $transactionService,
        private readonly ReworkedCacheService $cache,
        private readonly ACLService $aclService,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly UserContextAwareService $userContextAwareService
    ) {
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
           $this->userContextAwareService->checkAccess($pageKeyword, 'update');
            
            // Find the section
            $childSection = $this->entityManager->getRepository(Section::class)->find($sectionId);
            if (!$childSection) {
                $this->throwNotFound('Section not found');
            }
            
            // Remove old parent section relationship if needed
            $this->removeOldParentRelationships(null, $oldParentSectionId, $childSection, $this->entityManager);
            
            // Create or update page-section relationship
            $pageSection = $this->createOrUpdatePageSectionRelationship($parentPage, $childSection, $position, $this->entityManager);            
            
            $this->entityManager->flush();
            $this->positionManagementService->normalizePageSectionPositions($parentPage->getId());
            
            // Invalidate page and section caches
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $parentPage->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $childSection->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateAllListsInCategory();
            
            $this->entityManager->commit();
        
            return $pageSection;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to add section to page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Add a section to another section
     * 
     * @param string $pageKeyword The page keyword
     * @param int $parentSectionId The ID of the parent section
     * @param int $childSectionId The ID of the child section
     * @param int|null $position The desired position
     * @param string|null $oldParentPageKeyword The keyword of the old parent page to remove the relationship from (optional)
     * @param int|null $oldParentSectionId The ID of the old parent section to remove the relationship from (optional)
     * @return SectionsHierarchy The new section hierarchy relationship
     * @throws ServiceException If the relationship already exists or entities are not found
     */
    public function addSectionToSection(string $pageKeyword, int $parentSectionId, int $childSectionId, ?int $position, ?string $oldParentPageKeyword = null, ?int $oldParentSectionId = null): SectionsHierarchy
    {
        // Permission check
       $this->userContextAwareService->checkAccess($pageKeyword, 'update');
        $this->checkSectionInPage($pageKeyword, $parentSectionId);
        
        $this->entityManager->beginTransaction();
        try {
            $parentSection = $this->sectionRepository->find($parentSectionId);
            if (!$parentSection) {
                $this->throwNotFound('Parent section not found');
            }

            $childSection = $this->sectionRepository->find($childSectionId);
            if (!$childSection) {
                $this->throwNotFound('Child section not found');
            }

            // Convert page keyword to page ID if provided
            $oldParentPageId = null;
            if ($oldParentPageKeyword !== null) {
                $oldParentPage = $this->pageRepository->findOneBy(['keyword' => $oldParentPageKeyword]);
                if (!$oldParentPage) {
                    $this->throwNotFound("Old parent page with keyword '{$oldParentPageKeyword}' not found");
                }
                $oldParentPageId = $oldParentPage->getId();
            }

            // Remove old parent relationships
            $this->removeOldParentRelationships($oldParentPageId, $oldParentSectionId, $childSection, $this->entityManager);
            
            // Flush the removal of old relationships to avoid identity map conflicts
            $this->entityManager->flush();

            // Create section hierarchy relationship
            $sectionHierarchy = $this->createSectionHierarchyRelationship($parentSection, $childSection, $position, $this->entityManager);                       
            
            $this->entityManager->flush();
            $this->positionManagementService->normalizeSectionHierarchyPositions($parentSectionId, true);
            
            // Invalidate section caches
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $parentSection->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $childSection->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageKeyword);
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateAllListsInCategory();
            
            $this->entityManager->commit();
            
            return $sectionHierarchy;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to add section to section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Remove a section from a page
     * 
     * @param string $pageKeyword The keyword of the page
     * @param int $sectionId The ID of the section to remove
     * @throws ServiceException If the relationship does not exist
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
           $this->userContextAwareService->checkAccess($pageKeyword, 'update');

            // First, check if the section is directly associated with the page
            $pageSection = $this->entityManager->getRepository(PagesSection::class)->findOneBy(['page' => $page, 'section' => $sectionId]);
            
            if ($pageSection) {
                // Direct page section - just remove the association
                $this->entityManager->remove($pageSection);
                $this->entityManager->flush();
                $this->positionManagementService->normalizePageSectionPositions($page->getId());
                
                // Invalidate page cache
                $this->cache
                    ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                    ->invalidateItem("page_with_fields_{$page->getKeyword()}");
            } else {
                // Not directly associated - check if it's a child section in the page hierarchy
                $section = $this->entityManager->getRepository(Section::class)->find($sectionId);
                if (!$section) {
                    $this->throwNotFound('Section not found');
                }
                
                // Check if this section belongs to the page hierarchy
                if (!$this->sectionBelongsToPageHierarchy($page, $sectionId, $this->entityManager, $this->sectionRepository)) {
                    $this->throwNotFound('Section is not associated with this page.');
                }
                
                // This is a child section that belongs to the page hierarchy - delete it completely
                $this->removeAllSectionRelationships($section, $this->entityManager);
                $this->entityManager->remove($section);
                $this->entityManager->flush();
                
                // Invalidate page and section caches
                $this->cache
                    ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                    ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $page->getId());
                $this->cache
                    ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                    ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $section->getId());
                $this->cache
                    ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                    ->invalidateAllListsInCategory();
            }

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to remove section from page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Remove a section from another section
     * 
     * @param string $pageKeyword The page keyword
     * @param int $parentSectionId The ID of the parent section
     * @param int $childSectionId The ID of the child section
     * @throws ServiceException If the relationship does not exist
     */
    public function removeSectionFromSection(string $pageKeyword, int $parentSectionId, int $childSectionId): void
    {
        // Permission check
       $this->userContextAwareService->checkAccess($pageKeyword, 'update');
        $this->checkSectionInPage($pageKeyword, $parentSectionId);
        
        $this->entityManager->beginTransaction();
        try {
            $sectionHierarchy = $this->entityManager->getRepository(SectionsHierarchy::class)
                ->findOneBy(['parentSection' => $parentSectionId, 'childSection' => $childSectionId]);
            if (!$sectionHierarchy) {
                $this->throwNotFound('Section hierarchy relationship not found.');
            }

            $this->entityManager->remove($sectionHierarchy);
            $this->entityManager->flush();
            $this->positionManagementService->normalizeSectionHierarchyPositions($parentSectionId, true);
            
            // Invalidate section caches
            $parentSection = $this->sectionRepository->find($parentSectionId);
            $childSection = $this->sectionRepository->find($childSectionId);
            if ($parentSection) {
                $this->cache
                    ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                    ->invalidateItem("section_fields_{$parentSection->getId()}");
            }
            if ($childSection) {
                $this->cache
                    ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                    ->invalidateItem("section_fields_{$childSection->getId()}");
            }

            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageKeyword);

            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $parentSection->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $childSection->getId());

            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateAllListsInCategory();
            
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to remove section from section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Delete a section permanently
     * 
     * @param string|null $pageKeyword The page keyword
     * @param int $sectionId The ID of the section to delete
     * @throws ServiceException If the section is not found
     */
    public function deleteSection(?string $pageKeyword, int $sectionId): void
    {
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }
        
        // If page_keyword is not provided, find it from the section
        if ($pageKeyword === null) {
            $pageSection = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['section' => $sectionId]);
            
            if ($pageSection) {
                $page = $pageSection->getPage();
                if ($page) {
                    $pageKeyword = $page->getKeyword();
                }
            }
            
            if (!$pageKeyword) {
                $this->throwNotFound('Page not found for this section');
            }
        }
        
        // Permission check
       $this->userContextAwareService->checkAccess($pageKeyword, 'update');
        
        // Check if section belongs to page hierarchy
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        if (!$this->sectionBelongsToPageHierarchy($page, $sectionId, $this->entityManager, $this->sectionRepository)) {
            $this->throwForbidden("Section $sectionId is not associated with page {$page->getKeyword()}");
        }
        
        $this->entityManager->beginTransaction();
        try {
            // Remove all relationships and the section itself
            $this->removeAllSectionRelationships($section, $this->entityManager);
            $this->entityManager->remove($section);
            $this->entityManager->flush();
            
            // Invalidate page and section caches
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $page->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $section->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateAllListsInCategory();
            
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to delete section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Force delete a section permanently (always delete, never just remove from page)
     * This is different from deleteSection which might just remove from page for direct associations
     * 
     * @param string $pageKeyword The page keyword
     * @param int $sectionId The ID of the section to delete
     * @throws ServiceException If the section is not found or access denied
     */
    public function forceDeleteSection(string $pageKeyword, int $sectionId): void
    {
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }
        
        // Permission check
       $this->userContextAwareService->checkAccess($pageKeyword, 'delete');
        
        // Check if section belongs to page hierarchy
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        if (!$this->sectionBelongsToPageHierarchy($page, $sectionId, $this->entityManager, $this->sectionRepository)) {
            $this->throwForbidden("Section $sectionId is not associated with page {$page->getKeyword()}");
        }
        
        $this->entityManager->beginTransaction();
        try {
            // Store original section for transaction logging
            $originalSection = clone $section;
            
            // Always remove all relationships and delete the section completely
            $this->removeAllSectionRelationships($section, $this->entityManager);
            $this->entityManager->remove($section);
            $this->entityManager->flush();
            
            // Log the transaction
            $this->transactionService->logTransaction(
                \App\Service\Core\LookupService::TRANSACTION_TYPES_DELETE,
                \App\Service\Core\LookupService::TRANSACTION_BY_BY_USER,
                'sections',
                $section->getId(),
                (object) ["deleted_section" => $originalSection, "page_keyword" => $pageKeyword],
                'Section force deleted from page: ' . $section->getName() . ' (ID: ' . $section->getId() . ') from page: ' . $pageKeyword
            );
            
            // Invalidate page and section caches
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $page->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $section->getId());
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
                ->invalidateAllListsInCategory();
            
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to force delete section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }

        
    }

    /**
     * Check if the section is in the page
     * 
     * IMportant check for api calls in order to manipulate sections. 
     * 
     * @param string $page_keyword The page keyword
     * @param string $section_id The section ID
     * @throws ServiceException If the section is not found or access denied
     */
    public function checkSectionInPage(string $page_keyword, string $section_id): void
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        // Fetch all sections (flat) for this page
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        // Extract all section IDs
        $sectionIds = array_map(function ($section) {
            return is_array($section) && isset($section['id']) ? (string) $section['id'] : null;
        }, $flatSections);
        if (!in_array((string) $section_id, $sectionIds, true)) {
            $this->throwForbidden('Access denied: Section does not belong to page');
        }
    }
} 