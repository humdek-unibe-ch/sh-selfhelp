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
     * Find all routes for a specific version with eager-loaded permissions
     * 
     * @param string $version The API version
     * @return ApiRoute[] Array of ApiRoute entities with eager-loaded permissions
     */
    public function findAllRoutesByVersion(string $version): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.permissions', 'p')
            ->addSelect('p')
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
     * @deprecated Use findAllRoutesByVersion() with eager loading instead
     */
    public function findPermissionsForRoute(int $routeId): array
    {
        $route = $this->find($routeId);
        
        if (!$route) {
            return [];
        }
        
        return $route->getPermissions()->toArray();
    }

    /**
     * Find all routes for all versions with eager-loaded permissions - optimized for bulk loading
     * 
     * @return array<string, ApiRoute[]> Array of routes grouped by version
     */
    public function findAllRoutesGroupedByVersionWithPermissions(): array
    {
        $routes = $this->createQueryBuilder('r')
            ->leftJoin('r.permissions', 'p')
            ->addSelect('p')
            ->orderBy('r.version', 'ASC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Group routes by version
        $groupedRoutes = [];
        foreach ($routes as $route) {
            $version = $route->getVersion();
            if (!isset($groupedRoutes[$version])) {
                $groupedRoutes[$version] = [];
            }
            $groupedRoutes[$version][] = $route;
        }
        
        return $groupedRoutes;
    }

    /**
     * Optimized method to find routes with permissions as array data (no entities)
     * Returns plain arrays to avoid Doctrine proxy/lazy loading overhead
     * 
     * @return array Array of route data with permissions
     */
    public function findAllRoutesWithPermissionsAsArray(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                r.id,
                r.route_name,
                r.path,
                r.controller,
                r.methods,
                r.requirements,
                r.params,
                r.version,
                GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ",") as permission_names
            FROM api_routes r
            LEFT JOIN api_routes_permissions arp ON r.id = arp.id_api_routes
            LEFT JOIN permissions p ON arp.id_permissions = p.id
            GROUP BY r.id, r.route_name, r.path, r.controller, r.methods, r.requirements, r.params, r.version
            ORDER BY r.version ASC, r.id ASC
        ';
        
        $result = $conn->executeQuery($sql);
        $routes = $result->fetchAllAssociative();
        
        // Process the results to parse JSON fields and permission names
        foreach ($routes as &$route) {
            // Parse JSON fields
            $route['requirements'] = $route['requirements'] ? json_decode($route['requirements'], true) : [];
            $route['params'] = $route['params'] ? json_decode($route['params'], true) : [];
            
            // Parse permission names
            $route['permission_names'] = $route['permission_names'] ? explode(',', $route['permission_names']) : [];
        }
        
        return $routes;
    }
}
