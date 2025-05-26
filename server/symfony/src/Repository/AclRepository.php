<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Page;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Repository for ACL operations
 */
class AclRepository extends ServiceEntityRepository
{
    /**
     * In-memory cache adapter using PSR-6 standard
     */
    private CacheItemPoolInterface $cache;

    /**
     * Optional logger for cache operations
     */
    private ?LoggerInterface $logger;

    /**
     * Prefix for all cache keys to prevent collisions
     */
    private const CACHE_PREFIX = 'acl_user_';

    /**
     * Cache lifetime for request-scoped cache (in seconds)
     * A negative value means the cache is only valid for the current request
     */
    private const CACHE_TTL = -1;

    public function __construct(
        ManagerRegistry $registry, 
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($registry, Page::class);
        // Use ArrayAdapter for fast in-memory caching
        $this->cache = new ArrayAdapter(0, false);
        $this->logger = $logger;
    }

    /**
     * Get all ACLs for a user, optionally filtered by page
     * 
     * This method uses in-memory caching to ensure it's only executed once per request
     * even if called multiple times. It closely mirrors the get_user_acl stored procedure.
     * 
     * @param int $userId User ID to get ACLs for
     * @param int $pageId Optional page ID to filter by (-1 for all pages)
     * @return array Array of page ACLs with access rights
     */
    public function getUserAcl(int $userId, ?int $pageId = -1): array
    {
        // Generate a cache key based on parameters
        $cacheKey = $this->generateCacheKey($userId, $pageId);

        // Try to get from cache first
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            if ($this->logger) {
                $this->logger->debug('ACL cache hit for user {userId}, page {pageId}', [
                    'userId' => $userId,
                    'pageId' => $pageId,
                ]);
            }
            return $cacheItem->get();
        }

        if ($this->logger) {
            $this->logger->debug('ACL cache miss for user {userId}, page {pageId}', [
                'userId' => $userId,
                'pageId' => $pageId,
            ]);
        }

        // Cache miss - fetch from database
        $conn = $this->getEntityManager()->getConnection();

        // Call the stored procedure directly
        $sql = 'CALL get_user_acl(:userId, :pageId)';
        $params = [
            'userId' => $userId,
            'pageId' => $pageId
        ];
        $types = [
            'userId' => \PDO::PARAM_INT,
            'pageId' => \PDO::PARAM_INT
        ];

        $result = $conn->executeQuery($sql, $params, $types)->fetchAllAssociative();

        // Store in cache
        $cacheItem->set($result);
        // Set TTL if needed (-1 means valid for current request only)
        if (self::CACHE_TTL > 0) {
            $cacheItem->expiresAfter(self::CACHE_TTL);
        }
        $this->cache->save($cacheItem);

        return $result;
    }
   
    /**
     * Clear the in-memory ACL cache for a specific user
     * 
     * @param int $userId The user ID to clear cache for
     * @return bool True if the cache was cleared successfully
     */
    public function clearUserAclCache(int $userId): bool
    {
        $cacheKey = self::CACHE_PREFIX . $userId . '_*';
        $success = $this->cache->deleteItem($this->generateCacheKey($userId, -1));
        
        // Also clear any page-specific caches for this user
        // (Since ArrayAdapter doesn't support deleteItems with wildcards)
        $allItems = $this->cache->getItems([]);
        foreach ($allItems as $key => $item) {
            if (strpos($key, self::CACHE_PREFIX . $userId . '_') === 0) {
                $this->cache->deleteItem($key);
            }
        }

        if ($this->logger) {
            $this->logger->debug('Cleared ACL cache for user {userId}', [
                'userId' => $userId,
            ]);
        }

        return $success;
    }

    /**
     * Clear the entire in-memory ACL cache
     * 
     * @return bool True if the cache was cleared successfully
     */
    public function clearAllAclCache(): bool
    {
        $success = $this->cache->clear();
        
        if ($this->logger) {
            $this->logger->debug('Cleared all ACL cache');
        }

        return $success;
    }

    /**
     * Generates a standardized cache key for ACL items
     *
     * @param int $userId The user ID
     * @param int $pageId The page ID (-1 for all pages)
     * @return string The formatted cache key
     */
    private function generateCacheKey(int $userId, int $pageId): string
    {
        return self::CACHE_PREFIX . $userId . '_' . $pageId;
    }
}
