<?php

namespace App\Service\CMS\Admin;

use App\Entity\Section;
use App\Repository\SectionRepository;
use App\Service\Core\BaseService;
use App\Service\Core\GlobalCacheService;
use App\Service\Core\TransactionService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for section utility operations like finding unused sections and refContainers
 */
class AdminSectionUtilityService extends BaseService
{
    private const CACHE_TTL = 1800; // 30 minutes
    
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SectionRepository $sectionRepository,
        private readonly GlobalCacheService $globalCacheService,
        private readonly TransactionService $transactionService
    ) {
        $this->setGlobalCacheService($globalCacheService);
    }

    /**
     * Get all unused sections (not in hierarchy and not in pages_sections)
     * 
     * @return array
     */
    public function getUnusedSections(): array
    {
        $cacheKey = 'unused_sections';
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_SECTIONS,
            $cacheKey,
            function() {
                $qb = $this->entityManager->createQueryBuilder();
                
                return $qb->select('s.id', 's.name', 's.idStyles', 'st.name as styleName')
                    ->from(Section::class, 's')
                    ->leftJoin('s.style', 'st')
                    ->leftJoin('App\Entity\SectionsHierarchy', 'sh', 'WITH', 's.id = sh.childSection')
                    ->leftJoin('App\Entity\PagesSection', 'ps', 'WITH', 's.id = ps.section')
                    ->where('sh.childSection IS NULL')
                    ->andWhere('ps.section IS NULL')
                    ->orderBy('s.name', 'ASC')
                    ->getQuery()
                    ->getArrayResult();
            },
            self::CACHE_TTL
        );
    }

    /**
     * Get all refContainer sections
     * 
     * @return array
     */
    public function getRefContainers(): array
    {
        $cacheKey = 'ref_containers';
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_SECTIONS,
            $cacheKey,
            function() {
                $qb = $this->entityManager->createQueryBuilder();
                
                return $qb->select('s.id', 's.name', 's.idStyles', 'st.name as styleName')
                    ->from(Section::class, 's')
                    ->innerJoin('s.style', 'st')
                    ->where('st.name = :styleName')
                    ->setParameter('styleName', 'refContainer')
                    ->orderBy('s.name', 'ASC')
                    ->getQuery()
                    ->getArrayResult();
            },
            self::CACHE_TTL
        );
    }

    /**
     * Invalidate utility caches
     */
    public function invalidateUtilityCache(): void
    {
        if ($this->globalCacheService) {
            $this->globalCacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, 'unused_sections');
            $this->globalCacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, 'ref_containers');
        }
    }

    /**
     * Delete a single unused section by ID
     * 
     * @param int $sectionId
     * @throws ServiceException
     */
    public function deleteUnusedSection(int $sectionId): void
    {
        $this->entityManager->beginTransaction();
        
        try {
            $section = $this->sectionRepository->find($sectionId);
            if (!$section) {
                throw new ServiceException('Section not found', Response::HTTP_NOT_FOUND);
            }
            
            // Verify the section is actually unused
            if (!$this->isSectionUnused($sectionId)) {
                throw new ServiceException('Section is not unused and cannot be deleted', Response::HTTP_BAD_REQUEST);
            }
            
            // Store original section for transaction logging
            $originalSection = clone $section;
            
            // Remove all section relationships and the section itself
            $this->removeAllSectionRelationships($section);
            $this->entityManager->remove($section);
            $this->entityManager->flush();
            
            // Log the transaction
            $this->transactionService->logTransaction(
                \App\Service\Core\LookupService::TRANSACTION_TYPES_DELETE,
                \App\Service\Core\LookupService::TRANSACTION_BY_BY_USER,
                'sections',
                $section->getId(),
                (object) ["deleted_section" => $originalSection],
                'Unused section deleted: ' . $section->getName() . ' (ID: ' . $section->getId() . ')'
            );
            
            $this->entityManager->commit();
            
            // Invalidate utility cache
            $this->invalidateUtilityCache();
            
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to delete unused section: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }
    


    /**
     * Delete all unused sections
     * 
     * @return int Number of sections deleted
     * @throws ServiceException
     */
    public function deleteAllUnusedSections(): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            $unusedSections = $this->getUnusedSections();
            $deletedCount = 0;
            
            foreach ($unusedSections as $sectionData) {
                $section = $this->sectionRepository->find($sectionData['id']);
                if ($section && $this->isSectionUnused($section->getId())) {
                    // Store original section for transaction logging
                    $originalSection = clone $section;
                    
                    // Remove all section relationships and the section itself
                    $this->removeAllSectionRelationships($section);
                    $this->entityManager->remove($section);
                    
                    // Log the transaction
                    $this->transactionService->logTransaction(
                        \App\Service\Core\LookupService::TRANSACTION_TYPES_DELETE,
                        \App\Service\Core\LookupService::TRANSACTION_BY_BY_USER,
                        'sections',
                        $section->getId(),
                        (object) ["deleted_section" => $originalSection],
                        'Unused section deleted (bulk): ' . $section->getName() . ' (ID: ' . $section->getId() . ')'
                    );
                    
                    $deletedCount++;
                }
            }
            
            $this->entityManager->flush();
            
            // Log bulk operation
            if ($deletedCount > 0) {
                $this->transactionService->logTransaction(
                    \App\Service\Core\LookupService::TRANSACTION_TYPES_DELETE,
                    \App\Service\Core\LookupService::TRANSACTION_BY_BY_USER,
                    'sections',
                    null,
                    (object) ["deleted_count" => $deletedCount],
                    'Bulk delete of unused sections: ' . $deletedCount . ' sections deleted'
                );
            }
            
            $this->entityManager->commit();
            
            // Invalidate utility cache
            $this->invalidateUtilityCache();
            
            return $deletedCount;
            
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to delete unused sections: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }
    
    /**
     * Check if a section is unused (not in hierarchy and not in pages_sections)
     * 
     * @param int $sectionId
     * @return bool
     */
    private function isSectionUnused(int $sectionId): bool
    {
        // Check if section is in hierarchy
        $hierarchyCount = $this->entityManager->createQueryBuilder()
            ->select('COUNT(sh.childSection)')
            ->from('App\Entity\SectionsHierarchy', 'sh')
            ->where('sh.childSection = :sectionId')
            ->setParameter('sectionId', $sectionId)
            ->getQuery()
            ->getSingleScalarResult();
            
        if ($hierarchyCount > 0) {
            return false;
        }
        
        // Check if section is in pages_sections
        $pageSectionCount = $this->entityManager->createQueryBuilder()
            ->select('COUNT(ps.section)')
            ->from('App\Entity\PagesSection', 'ps')
            ->where('ps.section = :sectionId')
            ->setParameter('sectionId', $sectionId)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $pageSectionCount === 0;
    }
    
    /**
     * Remove all relationships for a section before deletion
     * 
     * @param Section $section
     */
    private function removeAllSectionRelationships(Section $section): void
    {
        // Remove from sections hierarchy (as child)
        $childHierarchies = $this->entityManager->getRepository('App\Entity\SectionsHierarchy')
            ->findBy(['childSection' => $section]);
        foreach ($childHierarchies as $hierarchy) {
            $this->entityManager->remove($hierarchy);
        }
        
        // Remove from sections hierarchy (as parent)
        $parentHierarchies = $this->entityManager->getRepository('App\Entity\SectionsHierarchy')
            ->findBy(['parentSection' => $section]);
        foreach ($parentHierarchies as $hierarchy) {
            $this->entityManager->remove($hierarchy);
        }
        
        // Remove from pages_sections
        $pageSections = $this->entityManager->getRepository('App\Entity\PagesSection')
            ->findBy(['section' => $section]);
        foreach ($pageSections as $pageSection) {
            $this->entityManager->remove($pageSection);
        }
        
        // Remove field translations
        $fieldTranslations = $this->entityManager->getRepository('App\Entity\SectionsFieldsTranslation')
            ->findBy(['section' => $section]);
        foreach ($fieldTranslations as $translation) {
            $this->entityManager->remove($translation);
        }
    }

    /**
     * Get cache TTL for utility operations
     */
    protected function getCacheTTL(string $category): int
    {
        return match($category) {
            GlobalCacheService::CATEGORY_SECTIONS => self::CACHE_TTL,
            default => 3600
        };
    }
}
