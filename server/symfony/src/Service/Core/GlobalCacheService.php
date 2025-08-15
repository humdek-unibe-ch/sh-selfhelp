<?php

namespace App\Service\Core;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Entity\User;

/**
 * Global Cache Service for managing application-wide caching with category-based invalidation
 * 
 * This service provides:
 * - Category-based cache management with prefixes
 * - User-specific cache invalidation for frontend calls
 * - Cache statistics and monitoring
 * - Bulk invalidation strategies
 * 
 * Cache Categories:
 * - pages: Page entities and their data
 * - users: User entities and profiles
 * - sections: Section entities and hierarchies
 * - languages: Language entities and translations
 * - groups: Group entities and memberships
 * - roles: Role entities and permissions
 * - permissions: Permission entities and ACLs
 * - lookups: Lookup data and constants
 * - assets: Asset entities and metadata
 * - frontend_user: User-specific frontend data (pages, sections, languages per user)
 */
class GlobalCacheService
{
    // Cache category prefixes
    public const CATEGORY_PAGES = 'pages';
    public const CATEGORY_USERS = 'users';
    public const CATEGORY_SECTIONS = 'sections';
    public const CATEGORY_LANGUAGES = 'languages';
    public const CATEGORY_GROUPS = 'groups';
    public const CATEGORY_ROLES = 'roles';
    public const CATEGORY_PERMISSIONS = 'permissions';
    public const CATEGORY_LOOKUPS = 'lookups';
    public const CATEGORY_ASSETS = 'assets';
    public const CATEGORY_FRONTEND_USER = 'frontend_user';
    public const CATEGORY_CMS_PREFERENCES = 'cms_preferences';
    public const CATEGORY_SCHEDULED_JOBS = 'scheduled_jobs';
    public const CATEGORY_ACTIONS = 'actions';

    // Cache pools
    private CacheItemPoolInterface $globalCache;
    private CacheItemPoolInterface $userFrontendCache;
    private CacheItemPoolInterface $adminCache;
    private CacheItemPoolInterface $lookupsCache;
    private CacheItemPoolInterface $permissionsCache;

    // Cache statistics
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'invalidations' => 0,
        'category_stats' => []
    ];

    public function __construct(
        #[Autowire(service: 'cache.global')] CacheItemPoolInterface $globalCache,
        #[Autowire(service: 'cache.user_frontend')] CacheItemPoolInterface $userFrontendCache,
        #[Autowire(service: 'cache.admin')] CacheItemPoolInterface $adminCache,
        #[Autowire(service: 'cache.lookups')] CacheItemPoolInterface $lookupsCache,
        #[Autowire(service: 'cache.permissions')] CacheItemPoolInterface $permissionsCache,
        private ?LoggerInterface $logger = null
    ) {
        $this->globalCache = $globalCache;
        $this->userFrontendCache = $userFrontendCache;
        $this->adminCache = $adminCache;
        $this->lookupsCache = $lookupsCache;
        $this->permissionsCache = $permissionsCache;

        // Initialize category stats
        $categories = $this->getAllCategories();
        foreach ($categories as $category) {
            $this->stats['category_stats'][$category] = [
                'hits' => 0,
                'misses' => 0,
                'sets' => 0,
                'invalidations' => 0
            ];
        }
    }

    /**
     * Get data from cache
     */
    public function get(string $category, string $key, ?int $userId = null): mixed
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        $item = $pool->getItem($fullKey);
        
        if ($item->isHit()) {
            $this->recordHit($category);
            $this->log('debug', 'Cache hit', ['category' => $category, 'key' => $key, 'user_id' => $userId]);
            return $item->get();
        }
        
        $this->recordMiss($category);
        $this->log('debug', 'Cache miss', ['category' => $category, 'key' => $key, 'user_id' => $userId]);
        return null;
    }

    /**
     * Set data in cache
     */
    public function set(string $category, string $key, mixed $data, ?int $ttl = null, ?int $userId = null): bool
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        $item = $pool->getItem($fullKey);
        $item->set($data);
        
        if ($ttl !== null) {
            $item->expiresAfter($ttl);
        }
        
        $success = $pool->save($item);
        
        if ($success) {
            $this->recordSet($category);
            $this->log('debug', 'Cache set', ['category' => $category, 'key' => $key, 'user_id' => $userId, 'ttl' => $ttl]);
        }
        
        return $success;
    }

    /**
     * Check if cache item exists
     */
    public function has(string $category, string $key, ?int $userId = null): bool
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        return $pool->hasItem($fullKey);
    }

    /**
     * Delete specific cache item
     */
    public function delete(string $category, string $key, ?int $userId = null): bool
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        $success = $pool->deleteItem($fullKey);
        
        if ($success) {
            $this->recordInvalidation($category);
            $this->log('debug', 'Cache item deleted', ['category' => $category, 'key' => $key, 'user_id' => $userId]);
        }
        
        return $success;
    }

    /**
     * Invalidate entire category
     */
    public function invalidateCategory(string $category): bool
    {
        $pool = $this->getCachePool($category);
        $success = $pool->clear();
        
        if ($success) {
            $this->recordInvalidation($category, 'category');
            $this->log('info', 'Cache category invalidated', ['category' => $category]);
        }
        
        return $success;
    }

    /**
     * Invalidate all user-specific frontend caches for a user
     */
    public function invalidateUserFrontend(int $userId): bool
    {
        $pattern = $this->generateCacheKey(self::CATEGORY_FRONTEND_USER, '*', $userId);
        return $this->invalidateByPattern($pattern);
    }

    /**
     * Invalidate cache by pattern (Redis-specific)
     */
    public function invalidateByPattern(string $pattern): bool
    {
        // This is a Redis-specific implementation
        // For other cache adapters, this would need to be implemented differently
        try {
            // Get all pools that might contain the pattern
            $pools = [
                $this->globalCache,
                $this->userFrontendCache,
                $this->adminCache,
                $this->lookupsCache,
                $this->permissionsCache
            ];
            
            $success = true;
            foreach ($pools as $pool) {
                // Try to clear items matching pattern
                // Note: This is a simplified approach - in production, you might want to use Redis SCAN
                if (method_exists($pool, 'clear')) {
                    // For now, we clear the entire pool if pattern matching is not available
                    // In a real Redis implementation, you would use SCAN with pattern matching
                    $success = $success && $pool->clear();
                }
            }
            
            if ($success) {
                $this->recordInvalidation('pattern', 'pattern');
                $this->log('info', 'Cache invalidated by pattern', ['pattern' => $pattern]);
            }
            
            return $success;
        } catch (\Exception $e) {
            $this->log('error', 'Failed to invalidate cache by pattern', ['pattern' => $pattern, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear all caches
     */
    public function clearAll(): bool
    {
        $pools = [
            'global' => $this->globalCache,
            'user_frontend' => $this->userFrontendCache,
            'admin' => $this->adminCache,
            'lookups' => $this->lookupsCache,
            'permissions' => $this->permissionsCache
        ];
        
        $success = true;
        foreach ($pools as $name => $pool) {
            $result = $pool->clear();
            $success = $success && $result;
            
            if ($result) {
                $this->log('info', 'Cache pool cleared', ['pool' => $name]);
            }
        }
        
        if ($success) {
            $this->recordInvalidation('all', 'all');
            $this->log('info', 'All caches cleared');
        }
        
        return $success;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'global_stats' => [
                'hits' => $this->stats['hits'],
                'misses' => $this->stats['misses'],
                'sets' => $this->stats['sets'],
                'invalidations' => $this->stats['invalidations'],
                'hit_rate' => $this->calculateHitRate()
            ],
            'category_stats' => $this->stats['category_stats']
        ];
    }

    /**
     * Reset statistics
     */
    public function resetStats(): void
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'category_stats' => []
        ];
        
        $categories = $this->getAllCategories();
        foreach ($categories as $category) {
            $this->stats['category_stats'][$category] = [
                'hits' => 0,
                'misses' => 0,
                'sets' => 0,
                'invalidations' => 0
            ];
        }
        
        $this->log('info', 'Cache statistics reset');
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey(string $category, string $key, ?int $userId = null): string
    {
        $parts = [$category, $key];
        
        if ($userId !== null) {
            $parts[] = "user_{$userId}";
        }
        
        return implode('-', $parts);
    }

    /**
     * Get appropriate cache pool for category
     */
    private function getCachePool(string $category): CacheItemPoolInterface
    {
        return match ($category) {
            self::CATEGORY_FRONTEND_USER => $this->userFrontendCache,
            self::CATEGORY_LOOKUPS => $this->lookupsCache,
            self::CATEGORY_PERMISSIONS => $this->permissionsCache,            
            self::CATEGORY_ACTIONS => $this->adminCache,
            self::CATEGORY_CMS_PREFERENCES => $this->adminCache,
            self::CATEGORY_SCHEDULED_JOBS => $this->adminCache,            
            self::CATEGORY_ROLES => $this->adminCache,
            self::CATEGORY_ASSETS => $this->adminCache,
            default => $this->globalCache
        };
    }

    /**
     * Get all available categories
     */
    private function getAllCategories(): array
    {
        return [
            self::CATEGORY_PAGES,
            self::CATEGORY_USERS,
            self::CATEGORY_SECTIONS,
            self::CATEGORY_LANGUAGES,
            self::CATEGORY_GROUPS,
            self::CATEGORY_ROLES,
            self::CATEGORY_PERMISSIONS,
            self::CATEGORY_LOOKUPS,
            self::CATEGORY_ASSETS,
            self::CATEGORY_FRONTEND_USER,
            self::CATEGORY_CMS_PREFERENCES,
            self::CATEGORY_SCHEDULED_JOBS,
            self::CATEGORY_ACTIONS
        ];
    }

    /**
     * Record cache hit
     */
    private function recordHit(string $category): void
    {
        $this->stats['hits']++;
        $this->stats['category_stats'][$category]['hits']++;
    }

    /**
     * Record cache miss
     */
    private function recordMiss(string $category): void
    {
        $this->stats['misses']++;
        $this->stats['category_stats'][$category]['misses']++;
    }

    /**
     * Record cache set
     */
    private function recordSet(string $category): void
    {
        $this->stats['sets']++;
        $this->stats['category_stats'][$category]['sets']++;
    }

    /**
     * Record cache invalidation
     */
    private function recordInvalidation(string $category, string $type = 'item'): void
    {
        $this->stats['invalidations']++;
        if (isset($this->stats['category_stats'][$category])) {
            $this->stats['category_stats'][$category]['invalidations']++;
        }
    }

    /**
     * Calculate hit rate percentage
     */
    private function calculateHitRate(): float
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        return $total > 0 ? round(($this->stats['hits'] / $total) * 100, 2) : 0.0;
    }

    /**
     * Log cache operations
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, "[GlobalCache] {$message}", $context);
        }
    }
}
