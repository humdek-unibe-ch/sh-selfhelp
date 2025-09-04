<?php

namespace App\Repository;

use App\Service\Cache\Core\CacheService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Page;

/**
 * Repository for ACL operations
 * Uses the unified CacheService for caching ACL data
 */
class AclRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry,
        private readonly CacheService $cache
    ) {
        parent::__construct($registry, Page::class);
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
        $aclCache = $this->cache
            ->withCategory(CacheService::CATEGORY_PERMISSIONS)
            ->withEntityScope(CacheService::ENTITY_SCOPE_USER, $userId);
        if ($pageId > 0) {
            $aclCache->withEntityScope(CacheService::ENTITY_SCOPE_PAGE, $pageId);
        }
        return $aclCache
            ->withEntityScope(CacheService::ENTITY_SCOPE_USER, $userId)
            ->getList("user_acl_{$pageId}", function () use ($userId, $pageId) {
                $conn = $this->getEntityManager()->getConnection();

                // Call the stored procedure directly
                $sql = 'CALL get_user_acl(:userId, :pageId)';
                $stmt = $conn->prepare($sql);
                $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
                $stmt->bindValue('pageId', $pageId, \PDO::PARAM_INT);

                return $stmt->executeQuery()->fetchAllAssociative();
            });
    }

}
