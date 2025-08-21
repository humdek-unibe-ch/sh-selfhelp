<?php

namespace App\Repository;

use App\Service\Cache\Core\ReworkedCacheService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Section;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ReworkedCacheService $cache)
    {
        parent::__construct($registry, Section::class);
    }

    /**
     * Fetch hierarchical sections for a page using a stored procedure.
     *
     * @param int $pageId
     * @return array
     */
    public function fetchSectionsHierarchicalByPageId(int $pageId): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId)
            ->getList("page_sections_hierarchical_{$pageId}", function () use ($pageId) {
                $conn = $this->getEntityManager()->getConnection();
                $sql = 'CALL get_page_sections_hierarchical(:page_id)';
                $stmt = $conn->prepare($sql);
                $stmt->bindValue('page_id', $pageId, \PDO::PARAM_INT);
                $result = $stmt->executeQuery(); // Use executeQuery for statements returning results
                return $result->fetchAllAssociative();
            });
    }
}