<?php

namespace App\Repository;

use App\Entity\Lookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lookup>
 */
class LookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lookup::class);
    }

    /**
     * Find a lookup by type and value
     * 
     * @param string $typeCode The type code to search for
     * @param string $lookupValue The lookup value to search for
     * @return Lookup|null The lookup if found, null otherwise
     */
    public function findByTypeAndValue(string $typeCode, string $lookupValue): ?Lookup
    {
        return $this->createQueryBuilder('l')
            ->where('l.typeCode = :typeCode')
            ->andWhere('l.lookupValue = :lookupValue')
            ->setParameter('typeCode', $typeCode)
            ->setParameter('lookupValue', $lookupValue)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the default user type (used for new users)
     * 
     * @return Lookup|null The default user type lookup
     */
    public function getDefaultUserType(): ?Lookup
    {
        return $this->findByTypeAndValue('userTypes', 'user');
    }
}
