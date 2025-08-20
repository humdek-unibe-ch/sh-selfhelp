<?php

namespace App\Repository;

use App\Entity\CmsPreference;
use App\Service\Cache\Core\ReworkedCacheService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CmsPreference>
 */
class CmsPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ReworkedCacheService $cache)
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
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_CMS_PREFERENCES)
            ->getItem("cms_preferences", function () {

                return $this->createQueryBuilder('c')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
            });
    }
}