<?php

namespace App\Repository;

use App\Entity\Style;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Style>
 */
class StyleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Style::class);
    }

    /**
     * Get all styles grouped by their style groups
     *
     * @return array Returns an array of styles grouped by style group
     */
    public function findAllStylesGroupedByGroup(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select(
                's.id AS style_id',
                's.name AS style_name',
                's.description AS style_description',
                's.canHaveChildren AS can_have_children',
                'sg.id AS style_group_id',
                'sg.name AS style_group',
                'sg.description AS style_group_description',
                'sg.position AS style_group_position'
            )
            ->leftJoin('s.group', 'sg')
            ->orderBy('sg.position', 'ASC')
            ->addOrderBy('s.name', 'ASC');

        $styles = $qb->getQuery()->getArrayResult();

        // Get relationship information for all styles
        $relationships = $this->getStylesRelationshipInfo();

        // Group styles by their style group
        $groupedStyles = [];
        foreach ($styles as $style) {
            $groupId = $style['style_group_id'];
            $styleId = $style['style_id'];

            if (!isset($groupedStyles[$groupId])) {
                $groupedStyles[$groupId] = [
                    'id' => $style['style_group_id'],
                    'name' => $style['style_group'],
                    'description' => $style['style_group_description'],
                    'position' => $style['style_group_position'],
                    'styles' => []
                ];
            }

            $groupedStyles[$groupId]['styles'][] = [
                'id' => $style['style_id'],
                'name' => $style['style_name'],
                'description' => $style['style_description'],
                'relationships' => [
                    // If can_have_children is 1 (true), return empty array (can have all children)
                    // If can_have_children is 0 (false), return custom allowed children from relationships
                    'allowedChildren' => $style['can_have_children'] ? [] : ($relationships['allowedChildren'][$styleId] ?? []),
                    'allowedParents' => $relationships['allowedParents'][$styleId] ?? []
                ]
            ];
        }

        // Convert to indexed array and preserve order
        return array_values($groupedStyles);
    }

    /**
     * Get relationship information for all styles
     *
     * @return array Returns array with allowedChildren and allowedParents for each style
     */
    private function getStylesRelationshipInfo(): array
    {
        $entityManager = $this->getEntityManager();
        $stylesAllowedRelationshipRepository = $entityManager->getRepository(\App\Entity\StylesAllowedRelationship::class);

        // Get all style IDs to query relationships for
        $styleIds = $this->createQueryBuilder('s')
            ->select('s.id')
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($styleIds)) {
            return [
                'allowedChildren' => [],
                'allowedParents' => []
            ];
        }

        // Use the StylesAllowedRelationshipRepository to get relationships
        return $stylesAllowedRelationshipRepository->getRelationshipsForStyles($styleIds);
    }

    /**
     * Check if a parent-child relationship is allowed between two styles
     */
    public function isStyleRelationshipAllowed(Style $parentStyle, Style $childStyle): bool
    {
        $stylesAllowedRelationshipRepository = $this->getEntityManager()
            ->getRepository(\App\Entity\StylesAllowedRelationship::class);

        return $stylesAllowedRelationshipRepository->isRelationshipAllowed($parentStyle, $childStyle);
    }

    /**
     * Get all allowed children for a specific style
     */
    public function getAllowedChildrenForStyle(Style $parentStyle): array
    {
        $stylesAllowedRelationshipRepository = $this->getEntityManager()
            ->getRepository(\App\Entity\StylesAllowedRelationship::class);

        return $stylesAllowedRelationshipRepository->findAllowedChildren($parentStyle);
    }

    /**
     * Get all allowed parents for a specific style
     */
    public function getAllowedParentsForStyle(Style $childStyle): array
    {
        $stylesAllowedRelationshipRepository = $this->getEntityManager()
            ->getRepository(\App\Entity\StylesAllowedRelationship::class);

        return $stylesAllowedRelationshipRepository->findAllowedParents($childStyle);
    }

}
