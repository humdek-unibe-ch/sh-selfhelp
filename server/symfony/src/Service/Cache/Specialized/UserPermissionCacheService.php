<?php

namespace App\Service\Cache\Specialized;

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
class UserPermissionCacheService
{
    private ?CacheService $cacheService = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Set the cache service (injected via services.yaml)
     */
    public function setCacheService(?CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get user permissions with caching
     * Uses optimized query to avoid N+1 issues
     */
    public function getUserPermissions(User $user): array
    {
        if (!$this->cacheService) {
            return $this->fetchUserPermissionsFromDatabase($user->getId());
        }

        $cacheKey = 'user_permissions_' . $user->getId();
        $cachedPermissions = $this->cacheService->get(CacheService::CATEGORY_PERMISSIONS, $cacheKey, $user->getId());

        if ($cachedPermissions !== null) {
            if ($this->logger) {
                $this->logger->debug('User permissions cache hit for user {userId}', [
                    'userId' => $user->getId()
                ]);
            }
            return $cachedPermissions;
        }

        if ($this->logger) {
            $this->logger->debug('User permissions cache miss for user {userId}', [
                'userId' => $user->getId()
            ]);
        }

        $permissions = $this->fetchUserPermissionsFromDatabase($user->getId());

        // Cache the result
        $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_PERMISSIONS);
        $this->cacheService->set(CacheService::CATEGORY_PERMISSIONS, $cacheKey, $permissions, $ttl, $user->getId());

        return $permissions;
    }

    /**
     * Get route permissions with caching
     * Uses router to get permissions from route options (already cached by ApiRouteLoader)
     */
    public function getRoutePermissions(string $routeName): array
    {
        return $this->fetchRoutePermissionsFromRouter($routeName);
        if (!$this->cacheService) {
            return $this->fetchRoutePermissionsFromRouter($routeName);
        }

        $cacheKey = 'route_permissions_' . $routeName;
        $cachedPermissions = $this->cacheService->get(CacheService::CATEGORY_PERMISSIONS, $cacheKey);

        if ($cachedPermissions !== null) {
            return $cachedPermissions;
        }

        $permissions = $this->fetchRoutePermissionsFromRouter($routeName);

        // Cache the result
        $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_PERMISSIONS);
        $this->cacheService->set(CacheService::CATEGORY_PERMISSIONS, $cacheKey, $permissions, $ttl);

        return $permissions;
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

    /**
     * Clear cache for a specific user (useful when permissions change)
     */
    public function clearUserPermissions(int $userId): void
    {
        if (!$this->cacheService) {
            return;
        }

        $cacheKey = 'user_permissions_' . $userId;
        $this->cacheService->delete(CacheService::CATEGORY_PERMISSIONS, $cacheKey, $userId);
    }

    /**
     * Clear route permissions cache (useful when route permissions change)
     */
    public function clearRoutePermissions(string $routeName): void
    {
        if (!$this->cacheService) {
            return;
        }

        $cacheKey = 'route_permissions_' . $routeName;
        $this->cacheService->delete(CacheService::CATEGORY_PERMISSIONS, $cacheKey);
    }

    /**
     * Clear all cached permissions
     */
    public function clearAll(): void
    {
        if (!$this->cacheService) {
            return;
        }

        $this->cacheService->invalidateCategory(CacheService::CATEGORY_PERMISSIONS);
    }
}
