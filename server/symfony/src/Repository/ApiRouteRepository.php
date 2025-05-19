<?php

namespace App\Repository;

use App\Entity\ApiRoute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiRoute>
 */
class ApiRouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiRoute::class);
    }

    /**
     * Find all routes for a specific version
     * 
     * @param string $version The API version
     * @return ApiRoute[] Array of ApiRoute entities
     */
    public function findAllRoutesByVersion(string $version): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.version = :version')
            ->setParameter('version', $version)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find all available API versions
     * 
     * @return string[] Array of version strings
     */
    public function findAllVersions(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('DISTINCT r.version')
            ->orderBy('r.version', 'ASC')
            ->getQuery()
            ->getScalarResult();
            
        // Extract version strings from result
        return array_map(function($item) {
            return $item['version'];
        }, $result);
    }
}
