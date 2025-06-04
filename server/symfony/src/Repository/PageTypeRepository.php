<?php

namespace App\Repository;

use App\Entity\PageType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageType>
 *
 * @method PageType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageType[]    findAll()
 * @method PageType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageType::class);
    }
}
