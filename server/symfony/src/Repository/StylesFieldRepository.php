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
     * Find default values for all fields of a specific style
     *
     * @param int $styleId The style ID
     * @return array Associative array with field name as key and default value as value
     */
    public function findDefaultValuesByStyleId(int $styleId): array
    {
        $qb = $this->createQueryBuilder('sf')
            ->select('f.name AS field_name, sf.defaultValue')
            ->leftJoin('sf.field', 'f')
            ->where('sf.style = :styleId')
            ->setParameter('styleId', $styleId);

        $results = $qb->getQuery()->getResult();
        
        // Organize results by field name
        $defaultValues = [];
        foreach ($results as $result) {
            $defaultValues[$result['field_name']] = $result['defaultValue'];
        }
        
        return $defaultValues;
    }
}
