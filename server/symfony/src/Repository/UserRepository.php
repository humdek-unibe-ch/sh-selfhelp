<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find one user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find one user by username
     *
     * @param string $username
     * @return User|null
     */
    public function findOneByUsername(string $username): ?User
    {
        return $this->findOneBy(['user_name' => $username]);
    }

    /**
     * Create query builder for active users (non-intern, active status)
     *
     * @return QueryBuilder
     */
    public function createActiveUsersQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.intern = :intern')
            ->andWhere('u.id_status > 0')
            ->setParameter('intern', false);
    }

    /**
     * Find users with pagination and search
     *
     * @param int $page
     * @param int $pageSize
     * @param string|null $search
     * @param string|null $sort
     * @param string $sortDirection
     * @return array
     */
    public function findUsersWithPagination(
        int $page = 1,
        int $pageSize = 20,
        ?string $search = null,
        ?string $sort = null,
        string $sortDirection = 'asc'
    ): array {
        $qb = $this->createActiveUsersQueryBuilder();

        // Apply search filter
        if ($search) {
            $qb->andWhere('(u.email LIKE :search OR u.name LIKE :search OR u.user_name LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply sorting
        $validSortFields = ['email', 'name', 'last_login', 'blocked'];
        if ($sort && in_array($sort, $validSortFields)) {
            $qb->orderBy('u.' . $sort, $sortDirection);
        } else {
            $qb->orderBy('u.email', 'asc');
        }

        // Get total count
        $countQb = clone $qb;
        $totalCount = $countQb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        // Apply pagination
        $offset = ($page - 1) * $pageSize;
        $qb->setFirstResult($offset)->setMaxResults($pageSize);

        $users = $qb->getQuery()->getResult();

        return [
            'users' => $users,
            'totalCount' => (int)$totalCount,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => (int)ceil($totalCount / $pageSize)
        ];
    }

    /**
     * Find users by group
     *
     * @param int $groupId
     * @return User[]
     */
    public function findByGroup(int $groupId): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.usersGroups', 'ug')
            ->innerJoin('ug.group', 'g')
            ->where('g.id = :groupId')
            ->setParameter('groupId', $groupId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users by role
     *
     * @param int $roleId
     * @return User[]
     */
    public function findByRole(int $roleId): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.roles', 'r')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $roleId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count active users
     *
     * @return int
     */
    public function countActiveUsers(): int
    {
        return $this->createActiveUsersQueryBuilder()
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find users with validation codes
     *
     * @param bool $consumed
     * @return User[]
     */
    public function findUsersWithValidationCodes(bool $consumed = false): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.validationCodes', 'vc');

        if ($consumed) {
            $qb->where('vc.consumed IS NOT NULL');
        } else {
            $qb->where('vc.consumed IS NULL');
        }

        return $qb->getQuery()->getResult();
    }
}
