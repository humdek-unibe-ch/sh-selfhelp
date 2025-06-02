<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Section;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'CALL get_page_sections_hierarchical(:page_id)';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('page_id', $pageId, \PDO::PARAM_INT);
        $result = $stmt->executeQuery(); // Use executeQuery for statements returning results
        return $result->fetchAllAssociative();
    }
}