<?php

namespace App\Service\Cache\Core;

/**
 * Trait to add high-level caching convenience methods to services
 * 
 * This trait provides common caching PATTERNS that simplify cache usage
 * for business services. It focuses on convenience methods and delegates
 * all actual cache operations to CacheService.
 * 
 * USE THIS WHEN:
 * - You need simple "cache-or-callback" patterns
 * - You want standardized cache key generation
 * - You need basic entity/list caching patterns
 * 
 * USE CacheService DIRECTLY WHEN:
 * - You need fine-grained cache control
 * - You need custom invalidation logic
 * - You're building cache infrastructure
 * - You need direct pool access
 */
trait CacheableServiceTrait
{
    private ?CacheService $cacheService = null;

    /**
     * Set the cache service (automatically injected via services.yaml)
     */
    public function setCacheService(?CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    /**
     * MAIN CACHING METHOD: Get from cache or execute callback if not cached
     * This is the primary convenience method - handles the common pattern:
     * "check cache -> if miss, execute callback -> store result -> return"
     */
    protected function getCache(string $category, string $key, callable $callback, ?int $ttl = null, ?int $userId = null): mixed
    {
        if (!$this->cacheService) {
            return $callback();
        }

        try {
            $cachedData = $this->cacheService->get($category, $key, $userId);
            
            if ($cachedData !== null) {
                return $cachedData;
            }
        } catch (\Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
        }

        $data = $callback();
        
        if ($data !== null && $this->cacheService) {
            try {
                $effectiveTtl = $ttl ?? $this->cacheService->getCacheTTL($category);
                $this->cacheService->set($category, $key, $data, $effectiveTtl, $userId);
            } catch (\Exception $e) {
                error_log("Cache set error: " . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * CONVENIENCE: Invalidate cache after CUD operations
     * This provides a simple way to trigger cache invalidation
     * For complex invalidation logic, use CacheService directly
     */
    protected function invalidateAfterChange(string $operation, string $category, ?object $entity = null, ?int $userId = null): void
    {
        if (!$this->cacheService) {
            return;
        }

        try {
            // Delegate to CacheService for actual invalidation logic
            switch ($operation) {
                case 'create':
                case 'update':
                case 'delete':
                    // Always invalidate the category
                    $this->cacheService->invalidateCategory($category);
                    
                    // If user specified, also invalidate user-specific caches
                    if ($userId) {
                        $this->cacheService->invalidateUserCategory($userId);
                    }
                    
                    // Use specific invalidation methods if entity is provided
                    if ($entity) {
                        $this->cacheService->invalidateForEntity($entity, $operation);
                    }
                    break;
            }
        } catch (\Exception $e) {
            error_log("Cache invalidation error: " . $e->getMessage());
        }
    }

    /**
     * CONVENIENCE: Cache key generation helpers for common patterns
     */
    
    /**
     * Get cache key for entity (standardized format)
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
     * Get cache key for list operations (standardized format)
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
     * Get cache key for user-specific data (standardized format)
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
     * CONVENIENCE: High-level caching patterns for entities and lists
     */
    
    /**
     * Cache an entity using standard key format
     */
    protected function cacheEntity(string $category, object $entity, ?int $ttl = null): bool
    {
        if (!method_exists($entity, 'getId') || !$this->cacheService) {
            return false;
        }

        $entityClass = (new \ReflectionClass($entity))->getShortName();
        $key = $this->getEntityCacheKey(strtolower($entityClass), $entity->getId());
        
        try {
            $effectiveTtl = $ttl ?? $this->cacheService->getCacheTTL($category);
            return $this->cacheService->set($category, $key, $entity, $effectiveTtl);
        } catch (\Exception $e) {
            error_log("Cache entity error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached entity using standard key format
     */
    protected function getCachedEntity(string $category, string $entityType, int $id): ?object
    {
        if (!$this->cacheService) {
            return null;
        }
        
        $key = $this->getEntityCacheKey($entityType, $id);
        
        try {
            return $this->cacheService->get($category, $key);
        } catch (\Exception $e) {
            error_log("Get cached entity error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cache a list using standard key format
     */
    protected function cacheList(string $category, string $entityType, array $data, array $filters = [], ?int $ttl = null): bool
    {
        if (!$this->cacheService) {
            return false;
        }
        
        $key = $this->getListCacheKey($entityType, $filters);
        
        try {
            $effectiveTtl = $ttl ?? $this->cacheService->getCacheTTL($category);
            return $this->cacheService->set($category, $key, $data, $effectiveTtl);
        } catch (\Exception $e) {
            error_log("Cache list error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached list using standard key format
     */
    protected function getCachedList(string $category, string $entityType, array $filters = []): ?array
    {
        if (!$this->cacheService) {
            return null;
        }
        
        $key = $this->getListCacheKey($entityType, $filters);
        
        try {
            return $this->cacheService->get($category, $key);
        } catch (\Exception $e) {
            error_log("Get cached list error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * CONVENIENCE: User-specific data caching pattern
     */
    
    /**
     * Cache user-specific data (automatically uses frontend_user category)
     */
    protected function cacheUserData(int $userId, string $dataType, mixed $data, ?int $ttl = null): bool
    {
        if (!$this->cacheService) {
            return false;
        }
        
        try {
            $key = $this->getUserCacheKey($userId, $dataType);
            $effectiveTtl = $ttl ?? $this->cacheService->getCacheTTL(CacheService::CATEGORY_FRONTEND_USER);
            return $this->cacheService->set(CacheService::CATEGORY_FRONTEND_USER, $key, $data, $effectiveTtl, $userId);
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
        if (!$this->cacheService) {
            return null;
        }
        
        try {
            $key = $this->getUserCacheKey($userId, $dataType);
            return $this->cacheService->get(CacheService::CATEGORY_FRONTEND_USER, $key, $userId);
        } catch (\Exception $e) {
            error_log("Get cached user data error: " . $e->getMessage());
            return null;
        }
    }
}
