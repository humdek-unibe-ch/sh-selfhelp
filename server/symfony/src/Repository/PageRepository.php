<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Helper to get lookup ID by code for a given lookup table
     * @param string $table
     * @param string $code
     * @return int|null
     */
    public function getLookupIdByCode(string $table, string $code): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = sprintf('SELECT id FROM %s WHERE code = :code', $table);
        $result = $conn->executeQuery($sql, ['code' => $code])->fetchAssociative();
        return $result ? (int)$result['id'] : null;
    }
}