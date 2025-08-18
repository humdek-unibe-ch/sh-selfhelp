<?php

namespace App\Service\Cache\Core;

use Psr\Log\LoggerInterface;

/**
 * Service dedicated to cache statistics and monitoring
 * 
 * This service handles all cache statistics, monitoring, and debugging functionality
 * separate from the core cache operations for better separation of concerns.
 */
class CacheStatsService
{
    // Cache statistics keys in Redis
    private const STATS_CACHE_KEY = 'cache_statistics';
    private const CATEGORY_STATS_PREFIX = 'cache_category_stats-';
    private const STATS_TTL = 86400; // 24 hours
    private const CATEGORY_STATS_TTL = 3600; // 1 hour

    public function __construct(
        private CacheService $cacheService,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Get comprehensive cache statistics
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
        ];
    }

    /**
     * Reset all statistics
     */
    public function resetStats(): void
    {
        $globalStats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'last_updated' => date('c')
        ];
        $this->saveGlobalStats($globalStats);
        
        $categories = $this->getAllCategories();
        foreach ($categories as $category) {
            $this->resetCategoryStats($category);
        }
        
        $this->log('info', 'Cache statistics reset');
    }

    /**
     * Get cache health status with recommendations
     */
    public function getCacheHealth(): array
    {
        $stats = $this->getStats();
        $globalStats = $stats['global_stats'];
        
        $hitRate = $globalStats['hit_rate'];
        $totalOps = $globalStats['total_operations'];
        
        // Determine health status
        $status = match (true) {
            $hitRate >= 80 => 'excellent',
            $hitRate >= 60 => 'good',
            $hitRate >= 40 => 'fair',
            default => 'poor'
        };
        
        $color = match ($status) {
            'excellent' => 'green',
            'good' => 'blue',
            'fair' => 'yellow',
            default => 'red'
        };
        
        // Generate recommendations
        $recommendations = [];
        if ($hitRate < 60) {
            $recommendations[] = [
                'type' => 'performance',
                'message' => 'Cache hit rate is below optimal. Consider reviewing cache TTL settings.',
                'priority' => 'high'
            ];
        }
        
        if ($totalOps < 100) {
            $recommendations[] = [
                'type' => 'usage',
                'message' => 'Low cache usage detected. Ensure caching is implemented in critical paths.',
                'priority' => 'medium'
            ];
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'performance',
                'message' => 'Cache performance is optimal',
                'priority' => 'low'
            ];
        }
        
        return [
            'status' => $status,
            'color' => $color,
            'hit_rate' => $hitRate,
            'total_operations' => $totalOps,
            'recommendations' => $recommendations,
            'timestamp' => date('c')
        ];
    }

    /**
     * Record cache hit for statistics
     */
    public function recordHit(string $category): void
    {
        $this->incrementGlobalStat('hits');
        $this->incrementCategoryStat($category, 'hits');
    }

    /**
     * Record cache miss for statistics
     */
    public function recordMiss(string $category): void
    {
        $this->incrementGlobalStat('misses');
        $this->incrementCategoryStat($category, 'misses');
    }

    /**
     * Record cache set for statistics
     */
    public function recordSet(string $category): void
    {
        $this->incrementGlobalStat('sets');
        $this->incrementCategoryStat($category, 'sets');
    }

    /**
     * Record cache invalidation for statistics
     */
    public function recordInvalidation(string $category, string $type = 'item'): void
    {
        $this->incrementGlobalStat('invalidations');
        $this->incrementCategoryStat($category, 'invalidations');
    }

    // Private helper methods
    private function getGlobalStats(): array
    {
        try {
            $statsItem = $this->cacheService->getCachePool('stats')->getItem(self::STATS_CACHE_KEY);
            
            if ($statsItem->isHit()) {
                return $statsItem->get();
            }
        } catch (\Exception $e) {
            $this->log('error', 'Failed to get global stats: ' . $e->getMessage());
        }
        
        return [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'last_updated' => date('c')
        ];
    }

    private function saveGlobalStats(array $stats): void
    {
        try {
            $stats['last_updated'] = date('c');
            $pool = $this->cacheService->getCachePool('stats');
            $statsItem = $pool->getItem(self::STATS_CACHE_KEY);
            $statsItem->set($stats);
            $statsItem->expiresAfter(self::STATS_TTL);
            $pool->save($statsItem);
        } catch (\Exception $e) {
            $this->log('error', 'Failed to save global stats: ' . $e->getMessage());
        }
    }

    private function incrementGlobalStat(string $statName): void
    {
        $stats = $this->getGlobalStats();
        $stats[$statName] = ($stats[$statName] ?? 0) + 1;
        $this->saveGlobalStats($stats);
    }

    private function getCategoryStats(string $category): array
    {
        try {
            $pool = $this->cacheService->getCachePool('stats');
            $statsItem = $pool->getItem(self::CATEGORY_STATS_PREFIX . $category);
            
            if ($statsItem->isHit()) {
                return $statsItem->get();
            }
        } catch (\Exception $e) {
            $this->log('error', 'Failed to get category stats: ' . $e->getMessage());
        }
        
        return [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'hit_rate' => 0.0,
            'last_updated' => date('c')
        ];
    }

    private function saveCategoryStats(string $category, array $stats): void
    {
        try {
            $pool = $this->cacheService->getCachePool('stats');
            $statsItem = $pool->getItem(self::CATEGORY_STATS_PREFIX . $category);
            $statsItem->set($stats);
            $statsItem->expiresAfter(self::CATEGORY_STATS_TTL);
            $pool->save($statsItem);
        } catch (\Exception $e) {
            $this->log('error', 'Failed to save category stats: ' . $e->getMessage());
        }
    }

    private function incrementCategoryStat(string $category, string $statName): void
    {
        $stats = $this->getCategoryStats($category);
        $stats[$statName] = ($stats[$statName] ?? 0) + 1;
        
        $total = ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0);
        $stats['hit_rate'] = $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0.0;
        $stats['last_updated'] = date('c');
        
        $this->saveCategoryStats($category, $stats);
    }

    private function resetCategoryStats(string $category): void
    {
        $stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'invalidations' => 0,
            'hit_rate' => 0.0,
            'last_updated' => date('c')
        ];
        
        $this->saveCategoryStats($category, $stats);
    }

    private function getAllCategoryStats(): array
    {
        $categoryStats = [];
        $categories = $this->getAllCategories();
        
        foreach ($categories as $category) {
            $categoryStats[$category] = $this->getCategoryStats($category);
        }
        
        return $categoryStats;
    }

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
                'last_activity' => $stats['last_updated'] ?? null
            ];
        }
        
        return $formatted;
    }

    private function calculateGlobalHitRate(array $stats): float
    {
        $total = ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0);
        return $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0.0;
    }

    private function getAllCategories(): array
    {
        return [
            CacheService::CATEGORY_PAGES,
            CacheService::CATEGORY_USERS,
            CacheService::CATEGORY_SECTIONS,
            CacheService::CATEGORY_LANGUAGES,
            CacheService::CATEGORY_GROUPS,
            CacheService::CATEGORY_ROLES,
            CacheService::CATEGORY_PERMISSIONS,
            CacheService::CATEGORY_LOOKUPS,
            CacheService::CATEGORY_ASSETS,
            CacheService::CATEGORY_FRONTEND_USER,
            CacheService::CATEGORY_CMS_PREFERENCES,
            CacheService::CATEGORY_SCHEDULED_JOBS,
            CacheService::CATEGORY_ACTIONS
        ];
    }

    private function getCachePoolName(string $category): string
    {
        return match ($category) {
            CacheService::CATEGORY_FRONTEND_USER => 'user_frontend',
            CacheService::CATEGORY_LOOKUPS => 'lookups',
            CacheService::CATEGORY_PERMISSIONS => 'permissions',
            CacheService::CATEGORY_ACTIONS => 'admin',
            CacheService::CATEGORY_CMS_PREFERENCES => 'admin',
            CacheService::CATEGORY_SCHEDULED_JOBS => 'admin',
            CacheService::CATEGORY_ROLES => 'admin',
            CacheService::CATEGORY_ASSETS => 'admin',
            default => 'global'
        };
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, "[CacheStats] {$message}", $context);
        }
    }
}
