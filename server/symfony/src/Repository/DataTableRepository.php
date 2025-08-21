<?php

namespace App\Repository;

use App\Service\Cache\Core\CacheService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use App\Entity\DataTable;

class DataTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly CacheService $cache)
    {
        parent::__construct($registry, DataTable::class);
    }

    /**
     * Calls the stored procedure get_dataTable_with_filter and returns the result.
     */
    public function getDataTableWithFilter(int $tableId, int $userId, string $filter, bool $excludeDeleted): array
    {
        return $this->cache
            ->withCategory(CacheService::CATEGORY_DATA_TABLES)
            ->withEntityScope(CacheService::ENTITY_SCOPE_DATA_TABLE, $tableId)
            ->withEntityScope(CacheService::ENTITY_SCOPE_USER, $userId)
            ->getList("data_table_with_filter_{$tableId}_{$userId}_{$filter}_{$excludeDeleted}", function () use ($tableId, $userId, $filter, $excludeDeleted) {
                $conn = $this->getEntityManager()->getConnection();
                $sql = 'CALL get_dataTable_with_filter(:tableId, :userId, :filter, :excludeDeleted)';
                $stmt = $conn->prepare($sql);
                $stmt->bindValue('tableId', $tableId, \PDO::PARAM_INT);
                $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
                $stmt->bindValue('filter', $filter, \PDO::PARAM_STR);
                $stmt->bindValue('excludeDeleted', $excludeDeleted, \PDO::PARAM_BOOL);
                $result = $stmt->executeQuery();

                return $result->fetchAllAssociative();
            });
    }

    /**
     * Get data table id by name
     * 
     * @param string $name Data table name
     * @return int Data table id
     */
    public function getDataTableIdByName(string $name): int
    {
        $dataTable = $this->findOneBy(['name' => $name]);
        return $dataTable->getId();
    }
}
