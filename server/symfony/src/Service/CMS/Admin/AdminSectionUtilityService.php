<?php

namespace App\Service\CMS\Admin;

use App\Entity\Section;
use App\Repository\SectionRepository;
use App\Service\Core\BaseService;
use App\Service\Core\GlobalCacheService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for section utility operations like finding unused sections and refContainers
 */
class AdminSectionUtilityService extends BaseService
{
    private const CACHE_TTL = 1800; // 30 minutes
    
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SectionRepository $sectionRepository,
        private readonly GlobalCacheService $globalCacheService
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
