<?php

namespace App\Service\Cache\Core;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Psr\Log\LoggerInterface;

/**
 * Service dedicated to cache statistics and monitoring
 * 
 * This service handles all cache statistics, monitoring, and debugging functionality
 * separate from the core cache operations for better separation of concerns.
 * 
 * Features:
 * - Statistics tracking per category (hits, misses, sets, invalidations)
 * - Global statistics aggregation
 * - Cache health monitoring with recommendations
 * - Top performing categories analysis
 * - Statistics reset functionality
 */
class CacheStatsService extends ReworkedCacheService
{

    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly ?LoggerInterface $logger = null
    ) {
        parent::__construct($cache, $logger);
    }

    /**
     * Get cache statistics for one or all categories
     * 
     * Returns hit/miss/set/invalidate counters that are automatically tracked
     * during cache operations. Useful for monitoring cache performance.
     * 
     * @param string|null $category Specific category to get stats for, or null for all categories
     * @return array Statistics data structure following cache_stats.json schema format
     */
    public function getStats(?string $category = null): array
    {
        if ($category !== null) {
            return $this->readStatsBucket($category);
        }

        // Collect raw stats for all categories
        $categoryStats = [];
        foreach (self::ALL_CATEGORIES as $cat) {
            $rawStats = $this->readStatsBucket($cat);
            $categoryStats[$cat] = [
                'hits' => $rawStats['hit'] ?? 0,
                'misses' => $rawStats['miss'] ?? 0,
                'sets' => $rawStats['set'] ?? 0,
                'invalidations' => $rawStats['invalidate'] ?? 0,
            ];
        }

        // Calculate global statistics
        $globalStats = $this->calculateGlobalStatsFromCategories($categoryStats);

        return [
            'cache_stats' => [
                'global_stats' => $globalStats,
                'category_stats' => $categoryStats,
            ],
            'cache_categories' => self::ALL_CATEGORIES,
            'timestamp' => date('c'),
        ];
    }

    /**
     * Get raw category statistics (internal format)
     */
    public function getRawCategoryStats(): array
    {
        $out = [];
        foreach (self::ALL_CATEGORIES as $cat) {
            $out[$cat] = $this->readStatsBucket($cat);
        }
        return $out;
    }

    /**
     * Get top performing categories by hit rate
     */
    public function getTopPerformingCategories(int $limit = 5): array
    {
        $categoryStats = $this->getRawCategoryStats();
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
        if (!in_array($category, self::ALL_CATEGORIES)) {
            throw new \InvalidArgumentException("Invalid cache category: {$category}");
        }
        
        $stats = $this->readStatsBucket($category);
        
        return [
            'category' => $category,
            'hits' => $stats['hit'] ?? 0,
            'misses' => $stats['miss'] ?? 0,
            'sets' => $stats['set'] ?? 0,
            'invalidations' => $stats['invalidate'] ?? 0,
            'hit_rate' => $this->calculateHitRate($stats),
            'total_operations' => ($stats['hit'] ?? 0) + ($stats['miss'] ?? 0),            
            'last_activity' => date('c'),
        ];
    }

    /**
     * Reset all statistics
     */
    public function resetStats(): void
    {
        foreach (self::ALL_CATEGORIES as $category) {
            $this->resetCategoryStats($category);
        }
    }

    /**
     * Get cache health status with recommendations
     */
    public function getCacheHealth(): array
    {
        $allStats = $this->getRawCategoryStats();
        $globalStats = $this->calculateGlobalStats($allStats);
        
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

    // Private helper methods

    private function readStatsBucket(string $category): array
    {
        return [
            'hit' => $this->getInt($this->statKey($category, 'hit'), 0),
            'miss' => $this->getInt($this->statKey($category, 'miss'), 0),
            'set' => $this->getInt($this->statKey($category, 'set'), 0),
            'invalidate' => $this->getInt($this->statKey($category, 'invalidate'), 0),
        ];
    }

    /**
     * Format category statistics
     */
    private function formatCategoryStats(array $categoryStats): array
    {
        $formatted = [];
        
        foreach ($categoryStats as $category => $stats) {
            $formatted[$category] = [
                'hits' => $stats['hit'] ?? 0,
                'misses' => $stats['miss'] ?? 0,
                'sets' => $stats['set'] ?? 0,
                'invalidations' => $stats['invalidate'] ?? 0,
                'hit_rate' => $this->calculateHitRate($stats),
                'total_operations' => ($stats['hit'] ?? 0) + ($stats['miss'] ?? 0),
                'last_activity' => date('c')
            ];
        }
        
        return $formatted;
    }

    /**
     * Calculate the hit rate for a category
     */
    private function calculateHitRate(array $stats): float
    {
        $total = ($stats['hit'] ?? 0) + ($stats['miss'] ?? 0);
        return $total > 0 ? round(($stats['hit'] / $total) * 100, 2) : 0.0;
    }

    /**
     * Calculate global statistics from all category statistics
     */
    private function calculateGlobalStatsFromCategories(array $categoryStats): array
    {
        $totalHits = 0;
        $totalMisses = 0;
        $totalSets = 0;
        $totalInvalidations = 0;

        foreach ($categoryStats as $stats) {
            $totalHits += $stats['hits'];
            $totalMisses += $stats['misses'];
            $totalSets += $stats['sets'];
            $totalInvalidations += $stats['invalidations'];
        }

        $totalOps = $totalHits + $totalMisses;
        $hitRate = $totalOps > 0 ? round(($totalHits / $totalOps) * 100, 2) : 0.0;

        return [
            'hits' => $totalHits,
            'misses' => $totalMisses,
            'sets' => $totalSets,
            'invalidations' => $totalInvalidations,
            'hit_rate' => $hitRate,
        ];
    }

    /**
     * Calculate the global statistics (for compatibility with getCacheHealth)
     */
    private function calculateGlobalStats(array $allStats): array
    {
        $totalHits = 0;
        $totalMisses = 0;
        $totalSets = 0;
        $totalInvalidations = 0;
        
        foreach ($allStats as $stats) {
            $totalHits += $stats['hit'] ?? 0;
            $totalMisses += $stats['miss'] ?? 0;
            $totalSets += $stats['set'] ?? 0;
            $totalInvalidations += $stats['invalidate'] ?? 0;
        }
        
        $totalOps = $totalHits + $totalMisses;
        
        return [
            'hits' => $totalHits,
            'misses' => $totalMisses,
            'sets' => $totalSets,
            'invalidations' => $totalInvalidations,
            'hit_rate' => $totalOps > 0 ? round(($totalHits / $totalOps) * 100, 2) : 0.0,
            'total_operations' => $totalOps,
            'last_updated' => date('c')
        ];
    }

    /**
     * Reset the statistics for a category
     */
    private function resetCategoryStats(string $category): void
    {
        // Reset all stat keys for this category
        $statTypes = ['hit', 'miss', 'set', 'invalidate'];
        foreach ($statTypes as $statType) {
            $key = $this->statKey($category, $statType);
            $this->cache->delete($key);
        }
    }
}