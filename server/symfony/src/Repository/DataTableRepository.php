<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use App\Entity\DataTable;

class DataTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataTable::class);
    }

    /**
     * Calls the stored procedure get_dataTable_with_filter and returns the result.
     */
    public function getDataTableWithFilter(int $tableId, int $userId, string $filter, bool $excludeDeleted): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'CALL get_dataTable_with_filter(:tableId, :userId, :filter, :excludeDeleted)';
        $result = $conn->executeQuery($sql, [
            'tableId' => $tableId,
            'userId' => $userId,
            'filter' => $filter,
            'excludeDeleted' => $excludeDeleted,
        ]);

        return $result->fetchAllAssociative();
    }
}
