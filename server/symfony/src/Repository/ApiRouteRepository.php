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

    /**
     * Find all active API routes
     * 
     * @return ApiRoute[]
     */
    public function findAllRoutes(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
