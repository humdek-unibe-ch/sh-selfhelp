<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\Core\GlobalCacheService;
use App\Service\Core\CacheInvalidationService;
use App\Service\Core\ApiResponseFormatter;
use App\Controller\Trait\RequestValidatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
        private GlobalCacheService $cacheService,
        private CacheInvalidationService $invalidationService,
        private ApiResponseFormatter $responseFormatter,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Get cache statistics and monitoring data
     */
    public function getCacheStats(Request $request): Response
    {
        try {
            $stats = $this->cacheService->getStats();
            
            // Add additional monitoring data
            $monitoringData = [
                'cache_stats' => $stats,
                'cache_categories' => $this->getCacheCategories(),
                'cache_pools' => $this->getCachePoolsInfo(),
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
            $success = $this->cacheService->clearAll();
            
            if ($success) {
                $user = $this->getUser();
                $userId = $user && method_exists($user, 'getId') ? $user->getId() : null;
                $this->log('warning', 'All caches cleared by admin', ['user_id' => $userId]);
                
                return $this->responseFormatter->formatSuccess(
                    ['cleared' => true, 'timestamp' => date('c')],
                    null,
                    Response::HTTP_OK
                );
            } else {
                throw new \Exception('Failed to clear all caches');
            }

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

            $success = $this->cacheService->invalidateCategory($category);
            
            if ($success) {
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
            } else {
                throw new \Exception("Failed to clear cache category: {$category}");
            }

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

            $this->invalidationService->invalidateAllUserCaches($userId);
            
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
     * Reset cache statistics
     */
    public function resetCacheStats(Request $request): Response
    {
        try {
            $this->cacheService->resetStats();
            
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
            $stats = $this->cacheService->getStats();
            $globalStats = $stats['global_stats'];
            
            // Determine health status based on hit rate and usage
            $hitRate = $globalStats['hit_rate'];
            $totalOperations = $globalStats['hits'] + $globalStats['misses'];
            
            $healthStatus = 'unknown';
            $healthColor = 'gray';
            
            if ($totalOperations > 100) { // Only evaluate if we have enough data
                if ($hitRate >= 80) {
                    $healthStatus = 'excellent';
                    $healthColor = 'green';
                } elseif ($hitRate >= 60) {
                    $healthStatus = 'good';
                    $healthColor = 'blue';
                } elseif ($hitRate >= 40) {
                    $healthStatus = 'fair';
                    $healthColor = 'yellow';
                } else {
                    $healthStatus = 'poor';
                    $healthColor = 'red';
                }
            }

            $healthData = [
                'status' => $healthStatus,
                'color' => $healthColor,
                'hit_rate' => $hitRate,
                'total_operations' => $totalOperations,
                'recommendations' => $this->getCacheRecommendations($stats),
                'timestamp' => date('c')
            ];

            return $this->responseFormatter->formatSuccess(
                $healthData,
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
     * Get available cache categories
     */
    private function getCacheCategories(): array
    {
        return [
            GlobalCacheService::CATEGORY_PAGES,
            GlobalCacheService::CATEGORY_USERS,
            GlobalCacheService::CATEGORY_SECTIONS,
            GlobalCacheService::CATEGORY_LANGUAGES,
            GlobalCacheService::CATEGORY_GROUPS,
            GlobalCacheService::CATEGORY_ROLES,
            GlobalCacheService::CATEGORY_PERMISSIONS,
            GlobalCacheService::CATEGORY_LOOKUPS,
            GlobalCacheService::CATEGORY_ASSETS,
            GlobalCacheService::CATEGORY_FRONTEND_USER,
            GlobalCacheService::CATEGORY_CMS_PREFERENCES,
            GlobalCacheService::CATEGORY_SCHEDULED_JOBS
        ];
    }

    /**
     * Get cache pools information
     */
    private function getCachePoolsInfo(): array
    {
        return [
            'global' => [
                'name' => 'Global Cache',
                'description' => 'Main cache for entities and API responses',
                'default_ttl' => 3600
            ],
            'user_frontend' => [
                'name' => 'User Frontend Cache',
                'description' => 'User-specific frontend data cache',
                'default_ttl' => 1800
            ],
            'admin' => [
                'name' => 'Admin Cache',
                'description' => 'Admin interface data cache',
                'default_ttl' => 900
            ],
            'lookups' => [
                'name' => 'Lookups Cache',
                'description' => 'Lookup data cache (longer TTL)',
                'default_ttl' => 7200
            ],
            'permissions' => [
                'name' => 'Permissions Cache',
                'description' => 'Permissions and ACL data cache',
                'default_ttl' => 1800
            ]
        ];
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
