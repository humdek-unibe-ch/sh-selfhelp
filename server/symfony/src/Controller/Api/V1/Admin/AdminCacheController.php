<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\Cache\Core\CacheStatsService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Service\Core\ApiResponseFormatter;
use App\Controller\Trait\RequestValidatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * Admin Cache Management Controller
 * 
 * Provides endpoints for cache monitoring and management:
 * - Get cache statistics and usage
 * - Clear specific cache categories
 * - Clear all caches
 * - Monitor cache effectiveness
 */
class AdminCacheController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private ReworkedCacheService $cacheService,
        private CacheStatsService $cacheStatsService,
        private ApiResponseFormatter $responseFormatter,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Get cache statistics and monitoring data
     */
    public function getCacheStats(Request $request): Response
    {
        try {
            $stats = $this->cacheStatsService->getStats();

            // Add additional monitoring data
            $monitoringData = [
                'cache_stats' => $stats,
                'cache_categories' => ReworkedCacheService::ALL_CATEGORIES,
                'top_performing_categories' => $this->cacheStatsService->getTopPerformingCategories(5),
                'timestamp' => date('c')
            ];

            $this->log('info', 'Cache statistics retrieved');

            return $this->responseFormatter->formatSuccess(
                $monitoringData,
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to get cache statistics', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to retrieve cache statistics',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Clear all caches
     */
    public function clearAllCaches(Request $request): Response
    {
        try {
            
            foreach (ReworkedCacheService::ALL_CATEGORIES as $category) {
                $this->cacheService->withCategory($category)->invalidateCategory();
            }

            $user = $this->getUser();
            $userId = $user && method_exists($user, 'getId') ? $user->getId() : null;
            $this->log('warning', 'All caches cleared by admin', ['user_id' => $userId]);

            return $this->responseFormatter->formatSuccess(
                ['cleared' => true, 'timestamp' => date('c')],
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to clear all caches', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to clear all caches',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Clear specific cache category
     */
    public function clearCacheCategory(Request $request): Response
    {
        try {
            // Get request data
            $requestData = json_decode($request->getContent(), true);
            if (!$requestData || !isset($requestData['category'])) {
                return $this->responseFormatter->formatError(
                    'Validation failed: category is required',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $category = $requestData['category'];

            // Validate category exists
            if (!in_array($category, $this->getCacheCategories())) {
                return $this->responseFormatter->formatError(
                    'Invalid cache category',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $this->cacheService->withCategory($category)->invalidateCategory();

            $user = $this->getUser();
            $userId = $user && method_exists($user, 'getId') ? $user->getId() : null;
            $this->log('warning', 'Cache category cleared by admin', [
                'category' => $category,
                'user_id' => $userId
            ]);

            return $this->responseFormatter->formatSuccess(
                [
                    'category' => $category,
                    'cleared' => true,
                    'timestamp' => date('c')
                ],
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to clear cache category', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to clear cache category',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Clear cache for specific user
     */
    public function clearUserCache(Request $request): Response
    {
        try {
            // Get request data
            $requestData = json_decode($request->getContent(), true);
            if (!$requestData || !isset($requestData['user_id']) || !is_int($requestData['user_id']) || $requestData['user_id'] < 1) {
                return $this->responseFormatter->formatError(
                    'Validation failed: user_id must be a positive integer',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = $requestData['user_id'];

            foreach (ReworkedCacheService::ALL_CATEGORIES as $category) {
                $this->cacheService->withCategory($category)->invalidateUser($userId);
            }

            $user = $this->getUser();
            $adminUserId = $user && method_exists($user, 'getId') ? $user->getId() : null;
            $this->log('warning', 'User cache cleared by admin', [
                'target_user_id' => $userId,
                'admin_user_id' => $adminUserId
            ]);

            return $this->responseFormatter->formatSuccess(
                [
                    'user_id' => $userId,
                    'cleared' => true,
                    'timestamp' => date('c')
                ],
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to clear user cache', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to clear user cache',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Clear API routes cache specifically
     * This is useful when new routes are added to the database
     */
    public function clearApiRoutesCache(Request $request): Response
    {
        try {
            $this->cacheService->withCategory(ReworkedCacheService::CATEGORY_API_ROUTES)->invalidateCategory();

            $user = $this->getUser();
            $adminUserId = $user && method_exists($user, 'getId') ? $user->getId() : null;
            $this->log('info', 'API routes cache cleared by admin', [
                'admin_user_id' => $adminUserId
            ]);

            return $this->responseFormatter->formatSuccess(
                [
                    'cleared' => true,
                    'cache_type' => 'api_routes',
                    'message' => 'API routes cache cleared successfully',
                    'timestamp' => date('c')
                ],
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to clear API routes cache', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to clear API routes cache',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get statistics for a specific cache category
     */
    public function getCategoryStats(Request $request, string $category): Response
    {
        try {
            $categoryStats = $this->cacheStatsService->getCategoryStatistics($category);

            $this->log('info', 'Category cache statistics retrieved', ['category' => $category]);

            return $this->responseFormatter->formatSuccess(
                $categoryStats,
                null,
                Response::HTTP_OK
            );

        } catch (\InvalidArgumentException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->log('error', 'Failed to get category cache statistics', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return $this->responseFormatter->formatError(
                'Failed to retrieve category cache statistics',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Reset cache statistics
     */
    public function resetCacheStats(Request $request): Response
    {
        try {
            $this->cacheStatsService->resetStats();

            $user = $this->getUser();
            $userId = $user && method_exists($user, 'getId') ? $user->getId() : null;
            $this->log('info', 'Cache statistics reset by admin', ['user_id' => $userId]);

            return $this->responseFormatter->formatSuccess(
                ['reset' => true, 'timestamp' => date('c')],
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to reset cache statistics', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to reset cache statistics',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get cache health status
     */
    public function getCacheHealth(Request $request): Response
    {
        try {
            $health = $this->cacheStatsService->getCacheHealth();

            $this->log('info', 'Cache health status retrieved');

            return $this->responseFormatter->formatSuccess(
                $health,
                null,
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to get cache health status', ['error' => $e->getMessage()]);

            return $this->responseFormatter->formatError(
                'Failed to retrieve cache health status',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get cache recommendations based on statistics
     */
    private function getCacheRecommendations(array $stats): array
    {
        $recommendations = [];
        $globalStats = $stats['global_stats'];
        $categoryStats = $stats['category_stats'];

        // Check overall hit rate
        if ($globalStats['hit_rate'] < 60) {
            $recommendations[] = [
                'type' => 'performance',
                'message' => 'Overall cache hit rate is below 60%. Consider increasing TTL values or reviewing cache invalidation strategies.',
                'priority' => 'high'
            ];
        }

        // Check for categories with low hit rates
        foreach ($categoryStats as $category => $catStats) {
            $categoryTotal = $catStats['hits'] + $catStats['misses'];
            if ($categoryTotal > 50) { // Only check categories with enough data
                $categoryHitRate = $categoryTotal > 0 ? ($catStats['hits'] / $categoryTotal) * 100 : 0;
                if ($categoryHitRate < 40) {
                    $recommendations[] = [
                        'type' => 'category',
                        'message' => "Category '{$category}' has low hit rate ({$categoryHitRate}%). Review caching strategy for this category.",
                        'priority' => 'medium',
                        'category' => $category
                    ];
                }
            }
        }

        // Check for excessive invalidations
        if ($globalStats['invalidations'] > ($globalStats['sets'] * 0.5)) {
            $recommendations[] = [
                'type' => 'invalidation',
                'message' => 'High invalidation rate detected. Review invalidation strategies to reduce unnecessary cache clearing.',
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    /**
     * Log cache operations
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, "[AdminCache] {$message}", $context);
        }
    }
}
