<?php

namespace App\Service\CMS;

use App\Entity\User;
use App\Service\Cache\Core\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for caching user permissions during request lifecycle
 * This prevents N+1 queries when checking permissions multiple times within a single request
 * 
 * Uses unified CacheService with permissions category for consistent caching
 */
class UserPermissionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private readonly CacheService $cache
    ) {
    }

    /**
     * Get user permissions with caching
     * Uses optimized query to avoid N+1 issues
     */
    public function getUserPermissions(User $user): array
    {

        $cacheKey = 'user_permissions_' . $user->getId();
        return $this->cache
            ->withCategory(CacheService::CATEGORY_PERMISSIONS)
            ->withUser($user->getId())
            ->getItem($cacheKey, function() use ($user) {
                return $this->fetchUserPermissionsFromDatabase($user->getId());
            });
    }

    /**
     * Get route permissions with caching
     * Uses router to get permissions from route options (already cached by ApiRouteLoader)
     */
    public function getRoutePermissions(string $routeName): array
    {
        return $this->cache
            ->withCategory(CacheService::CATEGORY_PERMISSIONS)
            ->getItem("route_permissions_{$routeName}", function() use ($routeName) {
                return $this->fetchRoutePermissionsFromRouter($routeName);
            });
    }

    /**
     * Fetch user permissions from database
     */
    private function fetchUserPermissionsFromDatabase(int $userId): array
    {
        // Optimized query to get all permissions in one go
        $sql = '
            SELECT DISTINCT p.name
            FROM permissions p
            INNER JOIN roles_permissions rp ON p.id = rp.id_permissions
            INNER JOIN users_roles ur ON rp.id_roles = ur.id_roles
            WHERE ur.id_users = :userId
            ORDER BY p.name
        ';

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $result = $stmt->executeQuery();
        
        return array_column($result->fetchAllAssociative(), 'name');
    }

    /**
     * Fetch route permissions from router
     */
    private function fetchRoutePermissionsFromRouter(string $routeName): array
    {
        // Get route from router collection (already cached by ApiRouteLoader)
        $route = $this->router->getRouteCollection()->get($routeName);
        if (!$route) {
            // Route not found, return empty permissions
            return [];
        }
        
        // Get permissions from route options
        return $route->getOption('permissions') ?? [];
    }
}
