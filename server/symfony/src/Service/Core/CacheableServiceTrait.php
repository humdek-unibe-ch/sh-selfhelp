<?php

namespace App\Service\Core;

/**
 * Trait to add caching capabilities to services
 * 
 * This trait provides common caching methods that can be used by any service
 * to implement caching with automatic invalidation strategies.
 */
trait CacheableServiceTrait
{
    private ?GlobalCacheService $globalCacheService = null;
    private ?CacheInvalidationService $cacheInvalidationService = null;

    /**
     * Set the global cache service
     */
    public function setGlobalCacheService(?GlobalCacheService $cacheService): void
    {
        $this->globalCacheService = $cacheService;
    }

    /**
     * Set the cache invalidation service
     */
    public function setCacheInvalidationService(?CacheInvalidationService $invalidationService): void
    {
        $this->cacheInvalidationService = $invalidationService;
    }

    /**
     * Get data from cache or execute callback if not cached
     */
    protected function cacheGet(string $category, string $key, callable $callback, ?int $ttl = null, ?int $userId = null): mixed
    {
        if (!$this->globalCacheService) {
            return $callback();
        }

        try {
            $cachedData = $this->globalCacheService->get($category, $key, $userId);
            
            if ($cachedData !== null) {
                return $cachedData;
            }
        } catch (\Exception $e) {
            // Log cache error but continue with callback
            error_log("Cache get error: " . $e->getMessage());
        }

        $data = $callback();
        
        if ($data !== null && $this->globalCacheService) {
            try {
                $this->globalCacheService->set($category, $key, $data, $ttl, $userId);
            } catch (\Exception $e) {
                // Log cache error but don't fail the operation
                error_log("Cache set error: " . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * Set data in cache
     */
    protected function cacheSet(string $category, string $key, mixed $data, ?int $ttl = null, ?int $userId = null): bool
    {
        if (!$this->globalCacheService) {
            return false;
        }

        return $this->globalCacheService->set($category, $key, $data, $ttl, $userId);
    }

    /**
     * Delete specific cache item
     */
    protected function cacheDelete(string $category, string $key, ?int $userId = null): bool
    {
        if (!$this->globalCacheService) {
            return false;
        }

        return $this->globalCacheService->delete($category, $key, $userId);
    }

    /**
     * Check if cache item exists
     */
    protected function cacheHas(string $category, string $key, ?int $userId = null): bool
    {
        if (!$this->globalCacheService) {
            return false;
        }

        return $this->globalCacheService->has($category, $key, $userId);
    }

    /**
     * Invalidate entire category
     */
    protected function cacheInvalidateCategory(string $category): bool
    {
        if (!$this->globalCacheService) {
            return false;
        }

        return $this->globalCacheService->invalidateCategory($category);
    }

    /**
     * Get cache key for entity
     */
    protected function getEntityCacheKey(string $entityType, int $id, ?string $suffix = null): string
    {
        $key = "{$entityType}_{$id}";
        
        if ($suffix) {
            $key .= "_{$suffix}";
        }
        
        return $key;
    }

    /**
     * Get cache key for list operations
     */
    protected function getListCacheKey(string $entityType, array $filters = [], ?string $suffix = null): string
    {
        $key = "{$entityType}_list";
        
        if (!empty($filters)) {
            ksort($filters); // Ensure consistent ordering
            $key .= '_' . md5(serialize($filters));
        }
        
        if ($suffix) {
            $key .= "_{$suffix}";
        }
        
        return $key;
    }

    /**
     * Get cache key for user-specific data
     */
    protected function getUserCacheKey(int $userId, string $dataType, ?string $suffix = null): string
    {
        $key = "user_{$userId}_{$dataType}";
        
        if ($suffix) {
            $key .= "_{$suffix}";
        }
        
        return $key;
    }

    /**
     * Cache a single entity
     */
    protected function cacheEntity(string $category, object $entity, ?int $ttl = null): bool
    {
        if (!method_exists($entity, 'getId')) {
            return false;
        }

        $entityClass = (new \ReflectionClass($entity))->getShortName();
        $key = $this->getEntityCacheKey(strtolower($entityClass), $entity->getId());
        
        return $this->cacheSet($category, $key, $entity, $ttl);
    }

    /**
     * Get cached entity
     */
    protected function getCachedEntity(string $category, string $entityType, int $id): ?object
    {
        $key = $this->getEntityCacheKey($entityType, $id);
        return $this->globalCacheService ? $this->globalCacheService->get($category, $key) : null;
    }

    /**
     * Cache a list of entities
     */
    protected function cacheEntityList(string $category, string $entityType, array $entities, array $filters = [], ?int $ttl = null): bool
    {
        $key = $this->getListCacheKey($entityType, $filters);
        return $this->cacheSet($category, $key, $entities, $ttl);
    }

    /**
     * Get cached entity list
     */
    protected function getCachedEntityList(string $category, string $entityType, array $filters = []): ?array
    {
        $key = $this->getListCacheKey($entityType, $filters);
        return $this->globalCacheService ? $this->globalCacheService->get($category, $key) : null;
    }

    /**
     * Cache user-specific data
     */
    protected function cacheUserData(int $userId, string $dataType, mixed $data, ?int $ttl = null): bool
    {
        if (!$this->globalCacheService) {
            return false;
        }
        
        try {
            $key = $this->getUserCacheKey($userId, $dataType);
            return $this->cacheSet(GlobalCacheService::CATEGORY_FRONTEND_USER, $key, $data, $ttl, $userId);
        } catch (\Exception $e) {
            error_log("Cache user data error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached user-specific data
     */
    protected function getCachedUserData(int $userId, string $dataType): mixed
    {
        if (!$this->globalCacheService) {
            return null;
        }
        
        try {
            $key = $this->getUserCacheKey($userId, $dataType);
            return $this->globalCacheService->get(GlobalCacheService::CATEGORY_FRONTEND_USER, $key, $userId);
        } catch (\Exception $e) {
            error_log("Cache get user data error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Invalidate user-specific data
     */
    protected function invalidateUserData(int $userId, ?string $dataType = null): bool
    {
        if (!$this->globalCacheService) {
            return false;
        }

        if ($dataType) {
            $key = $this->getUserCacheKey($userId, $dataType);
            return $this->globalCacheService->delete(GlobalCacheService::CATEGORY_FRONTEND_USER, $key, $userId);
        }

        return $this->globalCacheService->invalidateUserFrontend($userId);
    }

    /**
     * Get TTL for different cache categories
     */
    protected function getCacheTTL(string $category): int
    {
        return match ($category) {
            GlobalCacheService::CATEGORY_LOOKUPS => 7200, // 2 hours
            GlobalCacheService::CATEGORY_PERMISSIONS => 1800, // 30 minutes
            GlobalCacheService::CATEGORY_FRONTEND_USER => 1800, // 30 minutes
            GlobalCacheService::CATEGORY_LANGUAGES => 3600, // 1 hour
            GlobalCacheService::CATEGORY_ROLES => 1800, // 30 minutes
            GlobalCacheService::CATEGORY_GROUPS => 1800, // 30 minutes
            default => 3600 // 1 hour default
        };
    }
}
