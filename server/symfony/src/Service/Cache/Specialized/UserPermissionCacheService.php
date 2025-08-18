<?php

namespace App\Service\Cache\Specialized;

use App\Entity\User;
use App\Service\Cache\Core\CacheableServiceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Routing\RouterInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for caching user permissions during request lifecycle
 * This prevents N+1 queries when checking permissions multiple times within a single request
 * 
 * Uses ArrayAdapter for fast in-memory caching during request
 */
class UserPermissionCacheService
{
    use CacheableServiceTrait;
    
    private CacheItemPoolInterface $cache;
    private const CACHE_PREFIX = 'user_permissions_';
    private const ROUTE_PERMISSIONS_PREFIX = 'route_permissions_';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private ?LoggerInterface $logger = null
    ) {
        // Use ArrayAdapter for fast in-memory caching during request
        $this->cache = new ArrayAdapter(0, false);
    }

    /**
     * Get user permissions with caching
     * Uses optimized query to avoid N+1 issues
     */
    public function getUserPermissions(User $user): array
    {
        $cacheKey = self::CACHE_PREFIX . $user->getId();
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            if ($this->logger) {
                $this->logger->debug('User permissions cache hit for user {userId}', [
                    'userId' => $user->getId()
                ]);
            }
            return $cacheItem->get();
        }

        if ($this->logger) {
            $this->logger->debug('User permissions cache miss for user {userId}', [
                'userId' => $user->getId()
            ]);
        }

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
        $stmt->bindValue('userId', $user->getId());
        $result = $stmt->executeQuery();
        
        $permissions = array_column($result->fetchAllAssociative(), 'name');

        // Cache the result
        $cacheItem->set($permissions);
        $this->cache->save($cacheItem);

        return $permissions;
    }

    /**
     * Get route permissions with caching
     * Uses router to get permissions from route options (already cached by ApiRouteLoader)
     */
    public function getRoutePermissions(string $routeName): array
    {
        $cacheKey = self::ROUTE_PERMISSIONS_PREFIX . $routeName;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        // Get route from router collection (already cached by ApiRouteLoader)
        $route = $this->router->getRouteCollection()->get($routeName);
        if (!$route) {
            // Route not found, return empty permissions
            $permissions = [];
        } else {
            // Get permissions from route options
            $permissions = $route->getOption('permissions') ?? [];
        }

        // Cache the result
        $cacheItem->set($permissions);
        $this->cache->save($cacheItem);

        return $permissions;
    }

    /**
     * Clear cache for a specific user (useful when permissions change)
     */
    public function clearUserPermissions(int $userId): void
    {
        $cacheKey = self::CACHE_PREFIX . $userId;
        $this->cache->deleteItem($cacheKey);
    }

    /**
     * Clear route permissions cache (useful when route permissions change)
     */
    public function clearRoutePermissions(string $routeName): void
    {
        $cacheKey = self::ROUTE_PERMISSIONS_PREFIX . $routeName;
        $this->cache->deleteItem($cacheKey);
    }

    /**
     * Clear all cached permissions
     */
    public function clearAll(): void
    {
        $this->cache->clear();
    }
}
