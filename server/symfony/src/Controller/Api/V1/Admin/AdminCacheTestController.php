<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\Core\GlobalCacheService;
use App\Service\CMS\Admin\AdminGenderService;
use App\Service\Core\LookupService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache Test Controller for debugging cache functionality
 */
class AdminCacheTestController extends AbstractController
{
    public function __construct(
        private GlobalCacheService $cacheService,
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
                GlobalCacheService::CATEGORY_LOOKUPS, 
                $testKey, 
                $testData, 
                300
            );
            
            $cachedData = $this->cacheService->get(
                GlobalCacheService::CATEGORY_LOOKUPS, 
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
            $stats = $this->cacheService->getStats();
            $testResults['cache_stats'] = $stats;
            
            return $this->responseFormatter->formatSuccess($testResults);
            
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                'Cache test failed: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
