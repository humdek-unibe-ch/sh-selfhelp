<?php

namespace App\Repository;

use App\Entity\StylesField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StylesField>
 */
class StylesFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StylesField::class);
    }

    /**
     * Find default values for all fields of multiple styles in a single query
     * This method eliminates N+1 queries when fetching default values for multiple styles
     *
     * @param array $styleIds Array of style IDs
     * @return array Nested associative array with style ID as first key, field name as second key, and default value as value
     */
    public function findDefaultValuesByStyleIds(array $styleIds): array
    {
        if (empty($styleIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('sf')
            ->select('s.id AS style_id, f.name AS field_name, sf.defaultValue')
            ->leftJoin('sf.field', 'f')
            ->leftJoin('sf.style', 's')
            ->where('sf.style IN (:styleIds)')
            ->setParameter('styleIds', $styleIds);

        $results = $qb->getQuery()->getResult();
        
        // Organize results by style ID and field name
        $defaultValuesByStyle = [];
        foreach ($results as $result) {
            $styleId = $result['style_id'];
            $fieldName = $result['field_name'];
            $defaultValue = $result['defaultValue'];
            
            if (!isset($defaultValuesByStyle[$styleId])) {
                $defaultValuesByStyle[$styleId] = [];
            }
            
            $defaultValuesByStyle[$styleId][$fieldName] = $defaultValue;
        }
        
        return $defaultValuesByStyle;
    }
}
