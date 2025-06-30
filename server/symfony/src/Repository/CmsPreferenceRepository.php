<?php

namespace App\Repository;

use App\Entity\CmsPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CmsPreference>
 */
class CmsPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmsPreference::class);
    }

    /**
     * Get the single CMS preferences record
     * 
     * @return CmsPreference|null
     */
    public function getCmsPreferences(): ?CmsPreference
    {
        return $this->createQueryBuilder('c')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 