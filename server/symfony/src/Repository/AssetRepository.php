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
     * Find assets with pagination and search
     * 
     * @param int $page
     * @param int $pageSize
     * @param string|null $search
     * @param string|null $folder
     * @return array ['assets' => Asset[], 'total' => int]
     */
    public function findAssetsWithPagination(int $page, int $pageSize, ?string $search = null, ?string $folder = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.assetType', 'at');

        // Add search conditions
        if ($search) {
            $qb->andWhere('a.fileName LIKE :search OR a.folder LIKE :search OR at.lookupValue LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Add folder filter
        if ($folder) {
            $qb->andWhere('a.folder = :folder')
               ->setParameter('folder', $folder);
        }

        // Get total count
        $totalQb = clone $qb;
        $total = $totalQb->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $assets = $qb->orderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();

        return [
            'assets' => $assets,
            'total' => (int) $total
        ];
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

    /**
     * Check if multiple filenames exist
     * 
     * @param array $fileNames
     * @return Asset[]
     */
    public function findByFileNames(array $fileNames): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.fileName IN (:fileNames)')
            ->setParameter('fileNames', $fileNames)
            ->getQuery()
            ->getResult();
    }
} 