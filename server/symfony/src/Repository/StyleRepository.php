<?php

namespace App\Repository;

use App\Entity\Style;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Style>
 */
class StyleRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Style::class);
        $this->connection = $connection;
    }

    /**
     * Get all styles grouped by their style groups
     * 
     * @return array Returns an array of styles grouped by style group
     */
    public function findAllStylesGroupedByGroup(): array
    {
        $sql = 'SELECT * FROM view_styles ORDER BY style_group_position ASC, style_name ASC';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $styles = $result->fetchAllAssociative();

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

    //    /**
    //     * @return Style[] Returns an array of Style objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Style
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
