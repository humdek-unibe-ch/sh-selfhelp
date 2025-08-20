<?php

namespace App\Repository;

use App\Service\Cache\Core\ReworkedCacheService;
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
    private ?LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $registry,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($registry, Page::class);
        $this->logger = $logger;
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
        $conn = $this->getEntityManager()->getConnection();

        // Call the stored procedure directly
        $sql = 'CALL get_user_acl(:userId, :pageId)';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('pageId', $pageId, \PDO::PARAM_INT);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

}
