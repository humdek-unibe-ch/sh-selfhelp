<?php

namespace App\Repository;

use App\Entity\ApiRoute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiRoute>
 */
class ApiRouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiRoute::class);
    }

    // In ApiRouteRepository
    public function findAllRoutesByVersion(string $version): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.version = :version')
            ->setParameter('version', $version)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
