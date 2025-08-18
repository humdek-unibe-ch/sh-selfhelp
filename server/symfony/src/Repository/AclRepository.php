<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Page;
use App\Service\Cache\Core\CacheService;
use Psr\Log\LoggerInterface;

/**
 * Repository for ACL operations
 * Uses the unified CacheService for caching ACL data
 */
class AclRepository extends ServiceEntityRepository
{
    private ?CacheService $cacheService = null;
    private ?LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $registry, 
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($registry, Page::class);
        $this->logger = $logger;
    }

    /**
     * Set the cache service (injected via services.yaml)
     */
    public function setCacheService(?CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get all ACLs for a user, optionally filtered by page
     * 
     * Uses unified CacheService for caching ACL data within permissions category
     * 
     * @param int $userId User ID to get ACLs for
     * @param int $pageId Optional page ID to filter by (-1 for all pages)
     * @return array Array of page ACLs with access rights
     */
    public function getUserAcl(int $userId, ?int $pageId = -1): array
    {
        if (!$this->cacheService) {
            // If no cache service, fetch directly
            return $this->fetchUserAclFromDatabase($userId, $pageId);
        }

        // Generate a cache key based on parameters
        $cacheKey = "user_acl_{$userId}_{$pageId}";

        // Try to get from cache first
        $cachedResult = $this->cacheService->get(CacheService::CATEGORY_PERMISSIONS, $cacheKey, $userId);

        if ($cachedResult !== null) {
            if ($this->logger) {
                $this->logger->debug('ACL cache hit for user {userId}, page {pageId}', [
                    'userId' => $userId,
                    'pageId' => $pageId,
                ]);
            }
            return $cachedResult;
        }

        if ($this->logger) {
            $this->logger->debug('ACL cache miss for user {userId}, page {pageId}', [
                'userId' => $userId,
                'pageId' => $pageId,
            ]);
        }

        // Cache miss - fetch from database
        $result = $this->fetchUserAclFromDatabase($userId, $pageId);

        // Store in cache with permissions TTL
        $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_PERMISSIONS);
        $this->cacheService->set(CacheService::CATEGORY_PERMISSIONS, $cacheKey, $result, $ttl, $userId);

        return $result;
    }

    /**
     * Fetch user ACL data from database using stored procedure
     */
    private function fetchUserAclFromDatabase(int $userId, int $pageId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Call the stored procedure directly
        $sql = 'CALL get_user_acl(:userId, :pageId)';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('pageId', $pageId, \PDO::PARAM_INT);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }
   
    /**
     * Clear ACL cache for a specific user
     * 
     * @param int $userId The user ID to clear cache for
     * @return bool True if the cache was cleared successfully
     */
    public function clearUserAclCache(int $userId): bool
    {
        if (!$this->cacheService) {
            return false;
        }

        // Use CacheService's user invalidation method
        $this->cacheService->invalidatePermissions($userId);
        $success = true;

        if ($this->logger) {
            $this->logger->debug('Cleared ACL cache for user {userId}', [
                'userId' => $userId,
            ]);
        }

        return $success;
    }

    /**
     * Clear all ACL cache
     * 
     * @return bool True if the cache was cleared successfully
     */
    public function clearAllAclCache(): bool
    {
        if (!$this->cacheService) {
            return false;
        }

        // Use CacheService's permission invalidation method
        $this->cacheService->invalidatePermissions();
        $success = true;
        
        if ($this->logger) {
            $this->logger->debug('Cleared all ACL cache');
        }

        return $success;
    }
}
