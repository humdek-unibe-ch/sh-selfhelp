<?php

namespace App\Tests\Controller\Api\V1\Admin;

use App\Service\Cache\Core\CacheService;
use App\Service\Cache\Core\CacheStatsService;
use App\Service\CMS\Admin\AdminGenderService;
use App\Service\Core\LookupService;
use App\Service\Core\ApiResponseFormatter;
use App\Entity\User;
use App\Entity\Section;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache Test Controller for debugging cache functionality
 * Located in tests directory as this is for testing/debugging purposes
 */
class AdminCacheTestController extends AbstractController
{
    public function __construct(
        private CacheService $cacheService,
        private CacheStatsService $cacheStatsService,
        private AdminGenderService $genderService,
        private LookupService $lookupService,
        private ApiResponseFormatter $responseFormatter
    ) {}

    /**
     * Test cache functionality
     */
    public function testCache(Request $request): Response
    {
        try {
            $testResults = [];
            
            // Test 1: Direct cache service test
            $testKey = 'test_cache_key';
            $testData = ['test' => 'data', 'timestamp' => time()];
            
            $setResult = $this->cacheService->set(
                CacheService::CATEGORY_LOOKUPS, 
                $testKey, 
                $testData, 
                300
            );
            
            $cachedData = $this->cacheService->get(
                CacheService::CATEGORY_LOOKUPS, 
                $testKey
            );
            
            $testResults['direct_cache_test'] = [
                'set_result' => $setResult,
                'cached_data' => $cachedData,
                'cache_hit' => $cachedData !== null,
                'data_matches' => $cachedData === $testData
            ];
            
            // Test 2: Gender service cache test
            $genders1 = $this->genderService->getAllGenders();
            $genders2 = $this->genderService->getAllGenders(); // Should be cached
            
            $testResults['gender_service_test'] = [
                'first_call' => count($genders1),
                'second_call' => count($genders2),
                'data_identical' => $genders1 === $genders2
            ];
            
            // Test 3: Lookup service cache test  
            $lookups1 = $this->lookupService->getLookups('userTypes');
            $lookups2 = $this->lookupService->getLookups('userTypes'); // Should be cached
            
            $testResults['lookup_service_test'] = [
                'first_call' => count($lookups1),
                'second_call' => count($lookups2),
                'data_identical' => $lookups1 === $lookups2
            ];
            
            // Test 4: Cache stats
            $stats = $this->cacheStatsService->getStats();
            $testResults['cache_stats'] = $stats;
            
            // Test 5: Cache health
            $health = $this->cacheStatsService->getCacheHealth();
            $testResults['cache_health'] = $health;
            
            return $this->responseFormatter->formatSuccess($testResults);
            
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                'Cache test failed: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Test cache invalidation
     */
    public function testCacheInvalidation(Request $request): Response
    {
        try {
            $testResults = [];
            
            // Test 1: Set cache item
            $testKey = 'invalidation_test_key';
            $testData = ['test' => 'invalidation', 'timestamp' => time()];
            
            $setResult = $this->cacheService->set(
                CacheService::CATEGORY_LOOKUPS,
                $testKey,
                $testData,
                300
            );
            
            // Verify it's cached
            $cachedBefore = $this->cacheService->get(
                CacheService::CATEGORY_LOOKUPS,
                $testKey
            );
            
            // Test 2: Invalidate specific item
            $deleteResult = $this->cacheService->delete(
                CacheService::CATEGORY_LOOKUPS,
                $testKey
            );
            
            // Verify it's gone
            $cachedAfter = $this->cacheService->get(
                CacheService::CATEGORY_LOOKUPS,
                $testKey
            );
            
            $testResults['item_invalidation_test'] = [
                'set_result' => $setResult,
                'cached_before' => $cachedBefore !== null,
                'delete_result' => $deleteResult,
                'cached_after' => $cachedAfter !== null,
                'invalidation_successful' => $cachedBefore !== null && $cachedAfter === null
            ];
            
            // Test 3: Category invalidation
            $this->cacheService->set(CacheService::CATEGORY_USERS, 'user_1', ['id' => 1], 300);
            $this->cacheService->set(CacheService::CATEGORY_USERS, 'user_2', ['id' => 2], 300);
            
            $user1Before = $this->cacheService->get(CacheService::CATEGORY_USERS, 'user_1');
            $user2Before = $this->cacheService->get(CacheService::CATEGORY_USERS, 'user_2');
            
            $categoryInvalidateResult = $this->cacheService->invalidateCategory(CacheService::CATEGORY_USERS);
            
            $user1After = $this->cacheService->get(CacheService::CATEGORY_USERS, 'user_1');
            $user2After = $this->cacheService->get(CacheService::CATEGORY_USERS, 'user_2');
            
            $testResults['category_invalidation_test'] = [
                'users_cached_before' => $user1Before !== null && $user2Before !== null,
                'category_invalidate_result' => $categoryInvalidateResult,
                'users_cached_after' => $user1After !== null || $user2After !== null,
                'category_invalidation_successful' => 
                    ($user1Before !== null && $user2Before !== null) && 
                    ($user1After === null && $user2After === null)
            ];

            // Test 4: Entity-based invalidation
            $mockUser = new \stdClass();
            $mockUser->id = 999;
            
            // Set some user cache
            $this->cacheService->set(CacheService::CATEGORY_USERS, 'user_999', ['id' => 999], 300);
            $userCachedBefore = $this->cacheService->get(CacheService::CATEGORY_USERS, 'user_999');
            
            // Test entity invalidation using mock entity
            try {
                // This will trigger invalidation even if entity class doesn't match perfectly
                $this->cacheService->invalidateUser(999, 'update');
            } catch (\Exception $e) {
                // Expected for mock entity
            }
            
            $userCachedAfter = $this->cacheService->get(CacheService::CATEGORY_USERS, 'user_999');
            
            $testResults['entity_invalidation_test'] = [
                'user_cached_before' => $userCachedBefore !== null,
                'user_cached_after' => $userCachedAfter !== null,
                'entity_invalidation_successful' => $userCachedBefore !== null && $userCachedAfter === null
            ];
            
            return $this->responseFormatter->formatSuccess($testResults);
            
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                'Cache invalidation test failed: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
