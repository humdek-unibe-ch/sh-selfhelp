<?php

namespace App\Service\CMS\Admin;

use App\Entity\ScheduledJob;

use App\Repository\ScheduledJobRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Service\Core\LookupService;
use App\Service\Core\BaseService;
use App\Service\Core\TransactionService;

use App\Service\Auth\UserContextService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Core\JobSchedulerService;

class AdminScheduledJobService extends BaseService
{

    public function __construct(
        private readonly UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ScheduledJobRepository $scheduledJobRepository,
        private readonly TaskRepository $taskRepository,
        private readonly UserRepository $userRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly LookupService $lookupService,
        private readonly TransactionService $transactionService,
        private readonly JobSchedulerService $jobSchedulerService,
        private readonly ReworkedCacheService $cache
    ) {
    }

    /**
     * Get scheduled jobs with pagination, search, and filtering
     */
    public function getScheduledJobs(
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
        if ($page < 1)
            $page = 1;
        if ($pageSize < 1 || $pageSize > 100)
            $pageSize = 20;
        if (!in_array($sortDirection, ['asc', 'desc']))
            $sortDirection = 'asc';
        $cacheKey = "scheduled_jobs_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($status ?? '') . ($jobType ?? '') . ($dateFrom ?? '') . ($dateTo ?? '') . ($dateType ?? '') . ($sort ?? '') . $sortDirection);
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_SCHEDULED_JOBS)
            ->getList(
                $cacheKey,
                function () use ($page, $pageSize, $search, $status, $jobType, $dateFrom, $dateTo, $dateType, $sort, $sortDirection) {
                    $result = $this->scheduledJobRepository->findScheduledJobsWithPagination(
                        $page,
                        $pageSize,
                        $search,
                        $status,
                        $jobType,
                        $dateFrom,
                        $dateTo,
                        $dateType,
                        $sort,
                        $sortDirection
                    );

                    $formattedJobs = [];
                    foreach ($result['scheduledJobs'] as $job) {
                        $formattedJob = $this->formatScheduledJobForList($job);
                        // Add transactions for each job
                        $formattedJob['transactions'] = $this->getJobTransactions($job->getId());
                        $formattedJobs[] = $formattedJob;
                    }

                    return [
                        'scheduledJobs' => $formattedJobs,
                        'totalCount' => $result['totalCount'],
                        'page' => $result['page'],
                        'pageSize' => $result['pageSize'],
                        'totalPages' => (int) $result['totalPages']
                    ];
                }
            );
    }

    /**
     * Get scheduled job by ID with all related data
     */
    public function getScheduledJobById(int $jobId): array
    {
        $cacheKey = "scheduled_job_{$jobId}";
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_SCHEDULED_JOBS)
            ->getItem($cacheKey, function () use ($jobId) {
                $job = $this->scheduledJobRepository->findScheduledJobById($jobId);

                if (!$job) {
                    throw new ServiceException('Scheduled job not found', Response::HTTP_NOT_FOUND);
                }

                return $this->formatScheduledJobForDetail($job);
            });
    }

    /**
     * Execute a scheduled job
     */
    public function executeScheduledJob(int $jobId): array|false
    {
        $job = $this->jobSchedulerService->executeJob($jobId, LookupService::TRANSACTION_BY_BY_USER);
        return $job ? $this->formatScheduledJobForDetail($job) : false;
    }

    /**
     * Delete a scheduled job (change status to deleted)
     */
    public function deleteScheduledJob(int $jobId): bool
    {
        return $this->jobSchedulerService->deleteJob($jobId, LookupService::TRANSACTION_BY_BY_USER);
    }

    /**
     * Get transactions related to a scheduled job
     */
    public function getJobTransactions(int $jobId): array
    {
        $cacheKey = "scheduled_job_transactions_{$jobId}";
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_SCHEDULED_JOBS)
            ->getItem($cacheKey, function () use ($jobId) {
                $job = $this->scheduledJobRepository->find($jobId);

                if (!$job) {
                    throw new ServiceException('Scheduled job not found', Response::HTTP_NOT_FOUND);
                }

                $transactions = $this->transactionRepository->createQueryBuilder('t')
                    ->where('t.tableName = :tableName')
                    ->andWhere('t.idTableName = :idTableName')
                    ->setParameter('tableName', 'scheduledJobs')
                    ->setParameter('idTableName', $jobId)
                    ->orderBy('t.transactionTime', 'desc')
                    ->getQuery()
                    ->getResult();

                $formattedTransactions = [];
                foreach ($transactions as $transaction) {
                    $formattedTransactions[] = [
                        'transaction_id' => $transaction->getId(),
                        'transaction_time' => $transaction->getTransactionTime()->format('Y-m-d H:i:s'),
                        'transaction_type' => $transaction->getTransactionType()?->getLookupValue(),
                        'transaction_verbal_log' => $transaction->getTransactionLog(),
                        'user' => $transaction->getUser()?->getName()
                    ];
                }

                return $formattedTransactions;
            });
    }

    /**
     * Format scheduled job for list view
     */
    private function formatScheduledJobForList(ScheduledJob $job): array
    {
        $users = [];
        foreach ($job->getScheduledJobsUsers() as $sju) {
            $users[] = $sju->getUser()->getName();
        }

        $tasks = [];
        foreach ($job->getScheduledJobsTasks() as $sjt) {
            $tasks[] = $sjt->getTask()->getName();
        }

        return [
            'id' => $job->getId(),
            'status' => $job->getStatus()?->getLookupValue(),
            'type' => $job->getJobType()?->getLookupValue(),
            'entry_date' => $job->getDateCreate()->format('Y-m-d H:i:s'),
            'date_to_be_executed' => $job->getDateToBeExecuted()?->format('Y-m-d H:i:s'),
            'execution_date' => $job->getDateExecuted()?->format('Y-m-d H:i:s'),
            'description' => $job->getDescription(),
            'recipient' => implode(', ', $users),
            'title' => implode(', ', $tasks),
            'message' => $job->getConfig()
        ];
    }

    /**
     * Format scheduled job for detail view
     */
    private function formatScheduledJobForDetail(ScheduledJob $job): array
    {
        $users = [];
        foreach ($job->getScheduledJobsUsers() as $sju) {
            $users[] = [
                'id' => $sju->getUser()->getId(),
                'name' => $sju->getUser()->getName(),
                'email' => $sju->getUser()->getEmail()
            ];
        }

        $tasks = [];
        foreach ($job->getScheduledJobsTasks() as $sjt) {
            $tasks[] = [
                'id' => $sjt->getTask()->getId(),
                'name' => $sjt->getTask()->getName(),
                'description' => $sjt->getTask()->getDescription()
            ];
        }

        return [
            'id' => $job->getId(),
            'status' => [
                'id' => $job->getStatus()?->getId(),
                'value' => $job->getStatus()?->getLookupValue()
            ],
            'job_type' => [
                'id' => $job->getJobType()?->getId(),
                'value' => $job->getJobType()?->getLookupValue()
            ],
            'description' => $job->getDescription(),
            'date_create' => $job->getDateCreate()->format('Y-m-d H:i:s'),
            'date_to_be_executed' => $job->getDateToBeExecuted()?->format('Y-m-d H:i:s'),
            'date_executed' => $job->getDateExecuted()?->format('Y-m-d H:i:s'),
            'config' => $job->getConfig(),
            'users' => $users,
            'tasks' => $tasks
        ];
    }

}