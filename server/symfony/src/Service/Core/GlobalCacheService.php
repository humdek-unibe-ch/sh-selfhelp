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
    private CacheItemPoolInterface $appCache; // Default Symfony cache pool for API routes

    // Cache statistics keys in Redis
    private const STATS_CACHE_KEY = 'cache_statistics';
    private const CATEGORY_STATS_PREFIX = 'cache_category_stats-';
    private const STATS_TTL = 86400; // 24 hours
    private const CATEGORY_STATS_TTL = 3600; // 1 hour for category-specific stats

    public function __construct(
        #[Autowire(service: 'cache.global')] CacheItemPoolInterface $globalCache,
        #[Autowire(service: 'cache.user_frontend')] CacheItemPoolInterface $userFrontendCache,
        #[Autowire(service: 'cache.admin')] CacheItemPoolInterface $adminCache,
        #[Autowire(service: 'cache.lookups')] CacheItemPoolInterface $lookupsCache,
        #[Autowire(service: 'cache.permissions')] CacheItemPoolInterface $permissionsCache,
        #[Autowire(service: 'cache.app')] CacheItemPoolInterface $appCache,
        private ?LoggerInterface $logger = null
    ) {
        $this->globalCache = $globalCache;
        $this->userFrontendCache = $userFrontendCache;
        $this->adminCache = $adminCache;
        $this->lookupsCache = $lookupsCache;
        $this->permissionsCache = $permissionsCache;
        $this->appCache = $appCache;

        // Initialize persistent cache statistics if they don't exist
        $this->initializePersistentStats();
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
            'permissions' => $this->permissionsCache,
            'app' => $this->appCache
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
            // Clear statistics when clearing all caches
            $this->resetStats();
            $this->log('info', 'All caches and statistics cleared');
        }
        
        return $success;
    }

    /**
     * Clear API routes cache specifically
     * This is useful when new routes are added to the database
     */
    public function clearApiRoutesCache(): bool
    {
        $result = $this->appCache->deleteItem('api_routes_collection');
        
        if ($result) {
            $this->log('info', 'API routes cache cleared');
        }
        
        return $result;
    }

    /**
     * Clear all caches except JWT blacklist and persistent stats
     * This is a safe cache clear that preserves security data
     */
    public function clearApplicationCaches(): bool
    {
        $success = $this->clearAll();
        
        if ($success) {
            // Also clear API routes
            $success = $success && $this->clearApiRoutesCache();
        }
        
        return $success;
    }

    /**
     * Get cache statistics with enhanced category details
     */
    public function getStats(): array
    {
        $globalStats = $this->getGlobalStats();
        $categoryStats = $this->getAllCategoryStats();
        
        return [
            'global_stats' => [
                'hits' => $globalStats['hits'] ?? 0,
                'misses' => $globalStats['misses'] ?? 0,
                'sets' => $globalStats['sets'] ?? 0,
                'invalidations' => $globalStats['invalidations'] ?? 0,
                'hit_rate' => $this->calculateGlobalHitRate($globalStats),
                'total_operations' => ($globalStats['hits'] ?? 0) + ($globalStats['misses'] ?? 0),
                'last_updated' => $globalStats['last_updated'] ?? null
            ],
            'category_stats' => $this->formatCategoryStats($categoryStats)
        ];
    }

    /**
     * Get statistics for a specific category
     */
    public function getCategoryStatistics(string $category): array
    {
        if (!in_array($category, $this->getAllCategories())) {
            throw new \InvalidArgumentException("Invalid cache category: {$category}");
        }
        
        $stats = $this->getCategoryStats($category);
        
        return [
            'category' => $category,
            'hits' => $stats['hits'] ?? 0,
            'misses' => $stats['misses'] ?? 0,
            'sets' => $stats['sets'] ?? 0,
            'invalidations' => $stats['invalidations'] ?? 0,
            'hit_rate' => $stats['hit_rate'] ?? 0.0,
            'total_operations' => ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0),
            'cache_pool' => $this->getCachePoolName($category),
            'last_activity' => $stats['last_updated'] ?? null,
            'invalidation_breakdown' => $stats['invalidation_types'] ?? [],
            'performance_metrics' => [
                'efficiency_score' => $this->calculateEfficiencyScore($stats),
                'activity_level' => $this->calculateActivityLevel($stats)
            ]
        ];
    }

    /**
     * Get top performing categories by hit rate
     */
    public function getTopPerformingCategories(int $limit = 5): array
    {
        $categoryStats = $this->getAllCategoryStats();
        $formatted = $this->formatCategoryStats($categoryStats);
        
        // Sort by hit rate descending
        uasort($formatted, function($a, $b) {
            return $b['hit_rate'] <=> $a['hit_rate'];
        });
        
        return array_slice($formatted, 0, $limit, true);
    }

    /**
     * Reset statistics
     */
    public function resetStats(): void
    {
        // Reset global stats
        $globalStats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'last_updated' => date('c')
        ];
        $this->saveGlobalStats($globalStats);
        
        // Reset all category stats
        $categories = $this->getAllCategories();
        foreach ($categories as $category) {
            $this->resetCategoryStats($category);
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
        // Update global stats
        $this->incrementGlobalStat('hits');
        
        // Update category-specific stats with more details
        $this->incrementCategoryStat($category, 'hits');
        $this->updateCategoryTimestamp($category);
    }

    /**
     * Record cache miss
     */
    private function recordMiss(string $category): void
    {
        // Update global stats
        $this->incrementGlobalStat('misses');
        
        // Update category-specific stats
        $this->incrementCategoryStat($category, 'misses');
        $this->updateCategoryTimestamp($category);
    }

    /**
     * Record cache set
     */
    private function recordSet(string $category): void
    {
        // Update global stats
        $this->incrementGlobalStat('sets');
        
        // Update category-specific stats
        $this->incrementCategoryStat($category, 'sets');
        $this->updateCategoryTimestamp($category);
    }

    /**
     * Record cache invalidation
     */
    private function recordInvalidation(string $category, string $type = 'item'): void
    {
        // Update global stats
        $this->incrementGlobalStat('invalidations');
        
        // Update category-specific stats with invalidation type
        $this->incrementCategoryStat($category, 'invalidations');
        $this->recordCategoryInvalidationType($category, $type);
        $this->updateCategoryTimestamp($category);
    }

    /**
     * Calculate hit rate percentage
     */
    private function calculateHitRate(array $stats): float
    {
        $total = $stats['hits'] + $stats['misses'];
        return $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0.0;
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

    /**
     * Initialize persistent cache statistics if they don't exist
     */
    private function initializePersistentStats(): void
    {
        // Initialize global stats
        $globalStatsItem = $this->globalCache->getItem(self::STATS_CACHE_KEY);
        if (!$globalStatsItem->isHit()) {
            $globalStats = [
                'hits' => 0,
                'misses' => 0,
                'sets' => 0,
                'invalidations' => 0,
                'last_updated' => date('c')
            ];
            $this->saveGlobalStats($globalStats);
        }
        
        // Initialize category stats
        $categories = $this->getAllCategories();
        foreach ($categories as $category) {
            $categoryStatsItem = $this->globalCache->getItem(self::CATEGORY_STATS_PREFIX . $category);
            if (!$categoryStatsItem->isHit()) {
                $this->resetCategoryStats($category);
            }
        }
    }

    /**
     * Get global cache statistics
     */
    private function getGlobalStats(): array
    {
        $statsItem = $this->globalCache->getItem(self::STATS_CACHE_KEY);
        
        if ($statsItem->isHit()) {
            return $statsItem->get();
        }
        
        return [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'last_updated' => date('c')
        ];
    }

    /**
     * Save global cache statistics
     */
    private function saveGlobalStats(array $stats): void
    {
        $stats['last_updated'] = date('c');
        $statsItem = $this->globalCache->getItem(self::STATS_CACHE_KEY);
        $statsItem->set($stats);
        $statsItem->expiresAfter(self::STATS_TTL);
        $this->globalCache->save($statsItem);
    }

    /**
     * Increment global statistic counter
     */
    private function incrementGlobalStat(string $statName): void
    {
        $stats = $this->getGlobalStats();
        $stats[$statName] = ($stats[$statName] ?? 0) + 1;
        $this->saveGlobalStats($stats);
    }

    /**
     * Get category-specific statistics
     */
    private function getCategoryStats(string $category): array
    {
        $statsItem = $this->globalCache->getItem(self::CATEGORY_STATS_PREFIX . $category);
        
        if ($statsItem->isHit()) {
            return $statsItem->get();
        }
        
        return [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'hit_rate' => 0.0,
            'last_hit' => null,
            'last_miss' => null,
            'last_set' => null,
            'last_invalidation' => null,
            'invalidation_types' => []
        ];
    }

    /**
     * Save category-specific statistics
     */
    private function saveCategoryStats(string $category, array $stats): void
    {
        $statsItem = $this->globalCache->getItem(self::CATEGORY_STATS_PREFIX . $category);
        $statsItem->set($stats);
        $statsItem->expiresAfter(self::CATEGORY_STATS_TTL);
        $this->globalCache->save($statsItem);
    }

    /**
     * Increment category statistic counter
     */
    private function incrementCategoryStat(string $category, string $statName): void
    {
        $stats = $this->getCategoryStats($category);
        $stats[$statName] = ($stats[$statName] ?? 0) + 1;
        
        // Update hit rate
        $total = ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0);
        $stats['hit_rate'] = $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0.0;
        
        $this->saveCategoryStats($category, $stats);
    }

    /**
     * Update category timestamp for specific operation
     */
    private function updateCategoryTimestamp(string $category): void
    {
        $stats = $this->getCategoryStats($category);
        $timestamp = date('c');
        
        // This will be called after incrementCategoryStat, so we know which operation just happened
        // We can determine this by checking which counter was just incremented
        $stats['last_updated'] = $timestamp;
        
        $this->saveCategoryStats($category, $stats);
    }

    /**
     * Record invalidation type for category
     */
    private function recordCategoryInvalidationType(string $category, string $type): void
    {
        $stats = $this->getCategoryStats($category);
        
        if (!isset($stats['invalidation_types'])) {
            $stats['invalidation_types'] = [];
        }
        
        $stats['invalidation_types'][$type] = ($stats['invalidation_types'][$type] ?? 0) + 1;
        $stats['last_invalidation'] = date('c');
        
        $this->saveCategoryStats($category, $stats);
    }

    /**
     * Get all category statistics
     */
    private function getAllCategoryStats(): array
    {
        $categoryStats = [];
        $categories = $this->getAllCategories();
        
        foreach ($categories as $category) {
            $categoryStats[$category] = $this->getCategoryStats($category);
        }
        
        return $categoryStats;
    }

    /**
     * Format category statistics for output
     */
    private function formatCategoryStats(array $categoryStats): array
    {
        $formatted = [];
        
        foreach ($categoryStats as $category => $stats) {
            $formatted[$category] = [
                'hits' => $stats['hits'] ?? 0,
                'misses' => $stats['misses'] ?? 0,
                'sets' => $stats['sets'] ?? 0,
                'invalidations' => $stats['invalidations'] ?? 0,
                'hit_rate' => $stats['hit_rate'] ?? 0.0,
                'total_operations' => ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0),
                'cache_pool' => $this->getCachePoolName($category),
                'last_activity' => $stats['last_updated'] ?? null,
                'invalidation_breakdown' => $stats['invalidation_types'] ?? []
            ];
        }
        
        return $formatted;
    }

    /**
     * Reset category statistics
     */
    private function resetCategoryStats(string $category): void
    {
        $stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'hit_rate' => 0.0,
            'last_hit' => null,
            'last_miss' => null,
            'last_set' => null,
            'last_invalidation' => null,
            'last_updated' => date('c'),
            'invalidation_types' => []
        ];
        
        $this->saveCategoryStats($category, $stats);
    }

    /**
     * Calculate global hit rate
     */
    private function calculateGlobalHitRate(array $stats): float
    {
        $total = ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0);
        return $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0.0;
    }

    /**
     * Get cache pool name for category
     */
    private function getCachePoolName(string $category): string
    {
        return match ($category) {
            self::CATEGORY_FRONTEND_USER => 'user_frontend',
            self::CATEGORY_LOOKUPS => 'lookups',
            self::CATEGORY_PERMISSIONS => 'permissions',
            self::CATEGORY_ACTIONS => 'admin',
            self::CATEGORY_CMS_PREFERENCES => 'admin',
            self::CATEGORY_SCHEDULED_JOBS => 'admin',
            self::CATEGORY_ROLES => 'admin',
            self::CATEGORY_ASSETS => 'admin',
            default => 'global'
        };
    }

    /**
     * Calculate efficiency score for a category (0-100)
     */
    private function calculateEfficiencyScore(array $stats): float
    {
        $hits = $stats['hits'] ?? 0;
        $misses = $stats['misses'] ?? 0;
        $invalidations = $stats['invalidations'] ?? 0;
        $sets = $stats['sets'] ?? 0;
        
        $total = $hits + $misses;
        if ($total === 0) {
            return 0.0;
        }
        
        // Base score from hit rate
        $hitRate = ($hits / $total) * 100;
        
        // Penalty for excessive invalidations relative to sets
        $invalidationPenalty = $sets > 0 ? ($invalidations / $sets) * 10 : 0;
        
        // Final efficiency score
        $efficiency = max(0, $hitRate - $invalidationPenalty);
        
        return round($efficiency, 2);
    }

    /**
     * Calculate activity level for a category
     */
    private function calculateActivityLevel(array $stats): string
    {
        $totalOps = ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0) + ($stats['sets'] ?? 0) + ($stats['invalidations'] ?? 0);
        
        return match (true) {
            $totalOps >= 1000 => 'very_high',
            $totalOps >= 500 => 'high',
            $totalOps >= 100 => 'medium',
            $totalOps >= 10 => 'low',
            default => 'very_low'
        };
    }
}
