<?php

namespace App\Repository;

use App\Entity\PagesField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PagesField>
 */
class PagesFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagesField::class);
    }
    
    /**
     * Find all fields for a specific page with their translations
     *
     * @param int $pageId The page ID
     * @return array Array of page fields with translations
     */
    public function findFieldsWithTranslationsByPageId(int $pageId): array
    {
        $qb = $this->createQueryBuilder('pf')
            ->select('pf', 'f', 'pft', 'l')
            ->leftJoin('pf.field', 'f')
            ->leftJoin('App\Entity\PagesFieldsTranslation', 'pft', 'WITH', 
                'pft.idPages = pf.idPages AND pft.idFields = pf.idFields')
            ->leftJoin('pft.language', 'l')
            ->where('pf.idPages = :pageId')
            ->setParameter('pageId', $pageId)
            ->orderBy('f.name', 'ASC');
            
        return $qb->getQuery()->getResult();
    }
}
