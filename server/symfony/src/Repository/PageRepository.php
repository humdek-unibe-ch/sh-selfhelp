<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }
    
    /**
     * Find pages with nav position by parent page ID
     *
     * @param int|null $parentId Parent page ID or null for root pages
     * @return array Array of pages with nav_position not null, ordered by nav_position
     */
    public function findPagesWithNavPosition(?int $parentId = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.nav_position IS NOT NULL')
            ->andWhere('p.is_headless = :isHeadless')
            ->setParameter('isHeadless', false)
            ->orderBy('p.nav_position', 'ASC');
            
        if ($parentId !== null) {
            $qb->andWhere('p.parentPage = :parentId')
               ->setParameter('parentId', $parentId);
        } else {
            $qb->andWhere('p.parentPage IS NULL');
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Find pages with footer position by parent page ID
     *
     * @param int|null $parentId Parent page ID or null for root pages
     * @return array Array of pages with footer_position not null, ordered by footer_position
     */
    public function findPagesWithFooterPosition(?int $parentId = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.footer_position IS NOT NULL')
            ->andWhere('p.is_headless = :isHeadless')
            ->setParameter('isHeadless', false)
            ->orderBy('p.footer_position', 'ASC');
            
        if ($parentId !== null) {
            $qb->andWhere('p.parentPage = :parentId')
               ->setParameter('parentId', $parentId);
        } else {
            $qb->andWhere('p.parentPage IS NULL');
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Update page positions in a batch using Doctrine ORM.
     *
     * @param array $pagePositions Array of [pageId => position]
     * @param string $positionType 'nav' or 'footer'
     * @return bool Success status
     */
    public function updatePagePositions(array $pagePositions, string $positionType): bool
    {
        $em = $this->getEntityManager();
        $pages = $em->getRepository(Page::class)->findBy([
            'id' => array_keys($pagePositions)
        ]);
        $fieldSetter = $positionType === 'nav' ? 'setNavPosition' : 'setFooterPosition';
        try {
            foreach ($pages as $page) {
                $pageId = $page->getId();
                if (array_key_exists($pageId, $pagePositions)) {
                    $page->$fieldSetter($pagePositions[$pageId]);
                }
            }
            $em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}