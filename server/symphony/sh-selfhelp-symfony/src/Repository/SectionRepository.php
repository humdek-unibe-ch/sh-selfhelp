<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Section repository
 * 
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     */
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection
    ) {
        parent::__construct($registry, Section::class);
    }

    /**
     * Find hierarchical sections for a page
     * 
     * @param int $pageId The page ID
     * @return array The sections in a hierarchical structure
     */
    public function findHierarchicalSections(int $pageId): array
    {
        // Use the stored procedure to get hierarchical sections
        $sql = "CALL get_page_sections_hierarchical(:page_id)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('page_id', $pageId);
        $result = $stmt->executeQuery();
        
        // Get the JSON result
        $row = $result->fetchAssociative();
        
        // Return the sections JSON or empty array if no sections found
        return $row ? json_decode($row['sections_json'], true) : [];
    }

    /**
     * Find unassigned sections
     * 
     * @param int $styleId The style ID to filter by
     * @param bool $includeStyle Whether to include or exclude the style ID
     * @return array The unassigned sections
     */
    public function findUnassignedSections(int $styleId, bool $includeStyle = false): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id', 's.name', 'st.id as id_styles')
            ->leftJoin('s.style', 'st')
            ->leftJoin('s.parents', 'sh')
            ->leftJoin('s.pageSections', 'ps')
            ->leftJoin('App\Entity\SectionNavigation', 'sn', 'WITH', 's.id = sn.child')
            ->where('sh.id IS NULL')
            ->andWhere('ps.id IS NULL')
            ->andWhere('sn.id IS NULL')
            ->andWhere('st.idType != 3');
            
        if ($includeStyle) {
            $qb->andWhere('st.id = :styleId');
        } else {
            $qb->andWhere('st.id != :styleId');
        }
        
        $qb->setParameter('styleId', $styleId)
            ->orderBy('s.name', 'ASC');
            
        return $qb->getQuery()->getArrayResult();
    }
}