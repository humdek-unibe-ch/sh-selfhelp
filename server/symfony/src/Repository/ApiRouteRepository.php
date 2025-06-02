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
    /**
     * Find all permissions associated with a specific route
     *
     * @param int $routeId The ID of the route
     * @return array<array-key, Permission> Array of Permission entities
     */
    public function findPermissionsForRoute(int $routeId): array
    {
        $route = $this->find($routeId);
        
        if (!$route) {
            return [];
        }
        
        return $route->getPermissions()->toArray();
    }
}
