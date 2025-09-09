<?php

namespace App\Repository;

use App\Entity\StylesAllowedRelationship;
use App\Entity\Style;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StylesAllowedRelationship>
 */
class StylesAllowedRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StylesAllowedRelationship::class);
    }

    /**
     * Get all allowed children for a specific parent style
     */
    public function findAllowedChildren(Style $parentStyle): array
    {
        return $this->createQueryBuilder('sar')
            ->select('s.id AS id', 's.name AS name')
            ->join('sar.childStyle', 's')
            ->where('sar.parentStyle = :parentStyle')
            ->setParameter('parentStyle', $parentStyle)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Get all allowed parents for a specific child style
     */
    public function findAllowedParents(Style $childStyle): array
    {
        return $this->createQueryBuilder('sar')
            ->select('s.id AS id', 's.name AS name')
            ->join('sar.parentStyle', 's')
            ->where('sar.childStyle = :childStyle')
            ->setParameter('childStyle', $childStyle)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Check if a specific parent-child relationship is allowed
     */
    public function isRelationshipAllowed(Style $parentStyle, Style $childStyle): bool
    {
        $count = $this->createQueryBuilder('sar')
            ->select('COUNT(sar)')
            ->where('sar.parentStyle = :parentStyle')
            ->andWhere('sar.childStyle = :childStyle')
            ->setParameter('parentStyle', $parentStyle)
            ->setParameter('childStyle', $childStyle)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Get relationship information for multiple styles at once
     */
    public function getRelationshipsForStyles(array $styleIds): array
    {
        $relationships = [
            'allowedChildren' => [],
            'allowedParents' => []
        ];

        if (empty($styleIds)) {
            return $relationships;
        }

        // Get allowed children for all styles
        $childrenQuery = $this->createQueryBuilder('sar')
            ->select('IDENTITY(sar.parentStyle) AS parent_id', 's.id AS child_id', 's.name AS child_name')
            ->join('sar.childStyle', 's')
            ->where('sar.parentStyle IN (:styleIds)')
            ->setParameter('styleIds', $styleIds)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // Get allowed parents for all styles
        $parentsQuery = $this->createQueryBuilder('sar')
            ->select('IDENTITY(sar.childStyle) AS child_id', 's.id AS parent_id', 's.name AS parent_name')
            ->join('sar.parentStyle', 's')
            ->where('sar.childStyle IN (:styleIds)')
            ->setParameter('styleIds', $styleIds)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // Organize by style ID
        foreach ($childrenQuery as $row) {
            $parentId = $row['parent_id'];
            if (!isset($relationships['allowedChildren'][$parentId])) {
                $relationships['allowedChildren'][$parentId] = [];
            }
            $relationships['allowedChildren'][$parentId][] = [
                'id' => $row['child_id'],
                'name' => $row['child_name']
            ];
        }

        foreach ($parentsQuery as $row) {
            $childId = $row['child_id'];
            if (!isset($relationships['allowedParents'][$childId])) {
                $relationships['allowedParents'][$childId] = [];
            }
            $relationships['allowedParents'][$childId][] = [
                'id' => $row['parent_id'],
                'name' => $row['parent_name']
            ];
        }

        return $relationships;
    }
}
