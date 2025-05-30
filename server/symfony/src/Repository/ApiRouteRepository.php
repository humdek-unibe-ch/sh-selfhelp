<?php

namespace App\Repository;

use App\Entity\ApiRoute;
use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

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
     * @return ApiRoute[] Array of ApiRoute entities with eager-loaded permissions
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
    
    /**
     * Find all permissions required for a specific route
     *
     * @param int $routeId The API route ID
     * @return Permission[] Array of Permission entities
     */
    public function findPermissionsForRoute(int $routeId): array
    {
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\\Entity\\Permission p
            JOIN p.apiRoutes ar
            WHERE ar.id = :routeId'
        )->setParameter('routeId', $routeId);
        
        return $query->getResult();
    }
}
