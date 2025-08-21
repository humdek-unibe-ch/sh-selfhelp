<?php

namespace App\Service\CMS\Admin;

use App\Entity\Page;
use App\Entity\PagesSection;
use App\Entity\SectionsHierarchy;
use App\Service\Cache\Core\CacheService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for managing position-related operations for pages and sections
 */
class PositionManagementService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cache
    ) {
    }

    /**
     * Normalizes the positions of all sections within a specific page.
     * 
     * @param int $pageId The ID of the page to normalize section positions for
     * @param bool $flush Whether to flush changes to the database
     */
    public function normalizePageSectionPositions(int $pageId, bool $flush = false): void
    {
        // Get all page sections ordered by position
        $page = $this->entityManager->getRepository(Page::class)->find($pageId);
        $pageSections = $this->entityManager->getRepository(PagesSection::class)->findBy(
            ['page' => $page],
            ['position' => 'ASC']
        );

        // Sort by position to ensure correct ordering
        usort($pageSections, function ($a, $b) {
            return ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0);
        });

        // Reindex positions starting from 0 with increments of 10
        $currentPosition = 0;
        foreach ($pageSections as $pageSection) {
            $pageSection->setPosition($currentPosition);
            $currentPosition += 10;
        }
        
        // Only flush if requested (allows caller to control transaction)
        if ($flush) {
            $this->entityManager->flush();
            
            // Invalidate page cache when positions are normalized
            $page = $this->entityManager->getRepository(Page::class)->find($pageId);
            if ($page) {
                $this->cache
                    ->withCategory(CacheService::CATEGORY_PAGES)
                    ->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId());
                $this->cache
                    ->withCategory(CacheService::CATEGORY_PAGES)
                    ->invalidateAllListsInCategory();
            }
        }
    }
    
    /**
     * Normalizes the positions of all child sections within a specific parent section.
     * 
     * @param int $parentSectionId The ID of the parent section
     * @param bool $flush Whether to flush changes to the database
     */
    public function normalizeSectionHierarchyPositions(int $parentSectionId, bool $flush = false): void
    {
        // Get all section hierarchies for this parent
        $sectionHierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(
            ['parentSection' => $parentSectionId],
            ['position' => 'ASC', 'childSection' => 'ASC']
        );

        // Sort by position to ensure correct ordering
        usort($sectionHierarchies, function ($a, $b) {
            return ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0);
        });

        // Reindex positions starting from 0 with increments of 10
        $currentPosition = 0;
        foreach ($sectionHierarchies as $sectionHierarchy) {
            $sectionHierarchy->setPosition($currentPosition);
            $currentPosition += 10;
        }
        
        // Only flush if requested (allows caller to control transaction)
        if ($flush) {
            $this->entityManager->flush();
            
            // Invalidate section cache when positions are normalized
            $parentSection = $this->entityManager->getRepository(\App\Entity\Section::class)->find($parentSectionId);
            if ($parentSection) {
                $this->cache
                    ->withCategory(CacheService::CATEGORY_SECTIONS)
                    ->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $parentSection->getId());

                $this->cache
                    ->withCategory(CacheService::CATEGORY_SECTIONS)
                    ->invalidateAllListsInCategory();
            }
        }
    }
    
    /**
     * Reorders page positions when a new page is added or an existing page position is changed
     * This function ensures all pages have positions in multiples of 10 (10, 20, 30...)
     * 
     * @param int|null $parentId ID of the parent page or null for root pages
     * @param string $positionType 'nav' or 'footer'
     * @param bool $flush Whether to flush changes to the database
     */
    public function reorderPagePositions(?int $parentId, string $positionType, bool $flush = false): void
    {
        // Validate position type
        if (!in_array($positionType, ['nav', 'footer'])) {
            throw new \InvalidArgumentException('Invalid position type. Must be "nav" or "footer".');
        }

        // Use the correct property name as defined in the Page entity
        $positionField = $positionType === 'nav' ? 'nav_position' : 'footer_position';

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from(Page::class, 'p');

        if ($parentId !== null) {
            $qb->andWhere('p.parentPage = :parentId')
                ->setParameter('parentId', $parentId);
        } else {
            $qb->andWhere('p.parentPage IS NULL');
        }

        if ($positionType === 'nav') {
            $qb->andWhere('p.nav_position IS NOT NULL');
        } else {
            $qb->andWhere('p.footer_position IS NOT NULL');
        }

        $qb->orderBy("p.$positionField", 'ASC')
            ->addOrderBy('p.id', 'ASC');

        $pages = $qb->getQuery()->getResult();

        // Reindex positions starting from 10 with increments of 10
        $position = 10;
        foreach ($pages as $page) {
            $setter = $positionField === 'nav_position' ? 'setNavPosition' : 'setFooterPosition';
            $page->$setter($position);
            $position += 10;
        }

        // Only flush if requested (allows caller to control transaction)
        if ($flush) {
            $this->entityManager->flush();
        }
        $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $parentId);
        $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->invalidateAllListsInCategory();
    }
}
