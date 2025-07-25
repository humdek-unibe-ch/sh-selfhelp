<?php

namespace App\Repository;

use App\Entity\ScheduledJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<ScheduledJob>
 */
class ScheduledJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledJob::class);
    }

    /**
     * Create query builder for scheduled jobs with joins
     */
    public function createScheduledJobsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('sj')
            ->leftJoin('sj.status', 'status')
            ->leftJoin('sj.jobType', 'jobType')
            ->leftJoin('sj.scheduledJobsUsers', 'sju')
            ->leftJoin('sju.user', 'user')
            ->leftJoin('sj.scheduledJobsTasks', 'sjt')
            ->leftJoin('sjt.task', 'task');
    }

    /**
     * Find scheduled jobs with pagination, search, and filtering
     */
    public function findScheduledJobsWithPagination(
        int $page = 1,
        int $pageSize = 20,
        ?string $search = null,
        ?string $status = null,
        ?string $jobType = null,
        ?\DateTime $dateFrom = null,
        ?\DateTime $dateTo = null,
        ?string $dateType = 'date_to_be_executed',
        ?string $sort = null,
        string $sortDirection = 'asc'
    ): array {
        $qb = $this->createScheduledJobsQueryBuilder();

        // Apply search filter
        if ($search) {
            $qb->andWhere('(sj.description LIKE :search OR sj.id LIKE :search OR user.name LIKE :search OR task.config LIKE :search OR sj.config LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply status filter
        if ($status) {
            $qb->andWhere('status.lookupValue = :status')
                ->setParameter('status', $status);
        }

        // Apply job type filter
        if ($jobType) {
            $qb->andWhere('jobType.lookupValue = :jobType')
                ->setParameter('jobType', $jobType);
        }

        // Apply date range filter
        if ($dateFrom && $dateTo) {
            // Set $dateFrom as start of day, $dateTo as end of day
            $dateFrom->setTime(0, 0, 0);
            $dateTo->setTime(23, 59, 59);

            switch ($dateType) {
                case 'date_create':
                    $qb->andWhere('sj.dateCreate BETWEEN :dateFrom AND :dateTo');
                    break;
                case 'date_to_be_executed':
                    $qb->andWhere('sj.dateToBeExecuted BETWEEN :dateFrom AND :dateTo');
                    break;
                case 'date_executed':
                    $qb->andWhere('sj.dateExecuted BETWEEN :dateFrom AND :dateTo');
                    break;
            }
            $qb->setParameter('dateFrom', $dateFrom->format('Y-m-d H:i:s'))
                ->setParameter('dateTo', $dateTo->format('Y-m-d H:i:s'));


        }

        // Apply sorting
        $validSortFields = ['id', 'date_create', 'date_to_be_executed', 'date_executed', 'description'];
        if ($sort && in_array($sort, $validSortFields)) {
            switch ($sort) {
                case 'date_create':
                    $qb->orderBy('sj.dateCreate', $sortDirection);
                    break;
                case 'date_to_be_executed':
                    $qb->orderBy('sj.dateToBeExecuted', $sortDirection);
                    break;
                case 'date_executed':
                    $qb->orderBy('sj.dateExecuted', $sortDirection);
                    break;
                default:
                    $qb->orderBy('sj.' . $sort, $sortDirection);
                    break;
            }
        } else {
            $qb->orderBy('sj.dateCreate', 'desc');
        }

        // Get total count
        $countQb = clone $qb;
        $totalCount = $countQb->select('COUNT(DISTINCT sj.id)')->getQuery()->getSingleScalarResult();

        // Apply pagination
        $offset = ($page - 1) * $pageSize;
        $qb->setFirstResult($offset)->setMaxResults($pageSize);

        $scheduledJobs = $qb->getQuery()->getResult();

        return [
            'scheduledJobs' => $scheduledJobs,
            'totalCount' => (int) $totalCount,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($totalCount / $pageSize)
        ];
    }

    /**
     * Find scheduled job by ID with all related data
     */
    public function findScheduledJobById(int $id): ?ScheduledJob
    {
        return $this->createScheduledJobsQueryBuilder()
            ->andWhere('sj.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find scheduled jobs by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createScheduledJobsQueryBuilder()
            ->andWhere('status.lookupValue = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find scheduled jobs by job type
     */
    public function findByJobType(string $jobType): array
    {
        return $this->createScheduledJobsQueryBuilder()
            ->andWhere('jobType.lookupValue = :jobType')
            ->setParameter('jobType', $jobType)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find scheduled jobs by user
     */
    public function findByUser(int $userId): array
    {
        return $this->createScheduledJobsQueryBuilder()
            ->andWhere('user.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find scheduled jobs that need to be executed
     */
    public function findJobsToExecute(): array
    {
        return $this->createScheduledJobsQueryBuilder()
            ->andWhere('sj.dateToBeExecuted <= :now')
            ->andWhere('status.lookupValue = :status')
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'Queued')
            ->getQuery()
            ->getResult();
    }
}