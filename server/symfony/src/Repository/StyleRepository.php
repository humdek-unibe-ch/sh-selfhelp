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
                'lst.id AS style_type_id',
                'lst.lookupValue AS style_type',
                'sg.id AS style_group_id',
                'sg.name AS style_group',
                'sg.description AS style_group_description',
                'sg.position AS style_group_position'
            )
            ->leftJoin('s.type', 'lst', 'WITH', 'lst.typeCode = :typeCode')
            ->leftJoin('s.group', 'sg')
            ->setParameter('typeCode', 'styleType')
            ->orderBy('sg.position', 'ASC')
            ->addOrderBy('s.name', 'ASC');

        $styles = $qb->getQuery()->getArrayResult();

        // Group styles by their style group
        $groupedStyles = [];
        foreach ($styles as $style) {
            $groupId = $style['style_group_id'];
            
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
                'typeId' => $style['style_type_id'],
                'type' => $style['style_type']
            ];
        }
        
        // Convert to indexed array and preserve order
        return array_values($groupedStyles);
    }

}
