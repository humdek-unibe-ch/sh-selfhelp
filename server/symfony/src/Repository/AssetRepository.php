<?php

namespace App\Repository;

use App\Entity\Asset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asset>
 */
class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    /**
     * Find all assets
     * 
     * @return Asset[]
     */
    public function findAllAssets(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find asset by filename
     * 
     * @param string $fileName
     * @return Asset|null
     */
    public function findByFileName(string $fileName): ?Asset
    {
        return $this->createQueryBuilder('a')
            ->where('a.fileName = :fileName')
            ->setParameter('fileName', $fileName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find assets by folder
     * 
     * @param string $folder
     * @return Asset[]
     */
    public function findByFolder(string $folder): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.folder = :folder')
            ->setParameter('folder', $folder)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 