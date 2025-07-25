<?php

namespace App\Service\CMS\Admin;

use App\Entity\ScheduledJob;
use App\Entity\ScheduledJobsTask;
use App\Entity\ScheduledJobsUser;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Transaction;
use App\Repository\ScheduledJobRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use App\Service\Core\LookupService;
use App\Service\Core\UserContextAwareService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminScheduledJobService extends UserContextAwareService
{
    public function __construct(
        UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ScheduledJobRepository $scheduledJobRepository,
        private readonly TaskRepository $taskRepository,
        private readonly UserRepository $userRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly LookupService $lookupService,
        private readonly TransactionService $transactionService
    ) {
        parent::__construct($userContextService);
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
        if ($page < 1) $page = 1;
        if ($pageSize < 1 || $pageSize > 100) $pageSize = 20;
        if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'asc';

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
            'totalPages' => (int)$result['totalPages']
        ];
    }

    /**
     * Get scheduled job by ID with all related data
     */
    public function getScheduledJobById(int $jobId): array
    {
        $job = $this->scheduledJobRepository->findScheduledJobById($jobId);
        
        if (!$job) {
            throw new ServiceException('Scheduled job not found', Response::HTTP_NOT_FOUND);
        }

        return $this->formatScheduledJobForDetail($job);
    }

    /**
     * Execute a scheduled job
     */
    public function executeScheduledJob(int $jobId): array
    {
        return $this->transactionService->executeInTransaction(function () use ($jobId) {
            $job = $this->scheduledJobRepository->find($jobId);
            
            if (!$job) {
                throw new ServiceException('Scheduled job not found', Response::HTTP_NOT_FOUND);
            }

            if ($job->getStatus()->getLookupValue() !== 'Queued') {
                throw new ServiceException('Job is not in queued status', Response::HTTP_BAD_REQUEST);
            }

            // Update job status to executing
            $executingStatus = $this->lookupService->getLookupByValue('Executing');
            $job->setStatus($executingStatus);
            $job->setDateExecuted(new \DateTime());

            $this->entityManager->persist($job);
            $this->entityManager->flush();

            // Log transaction
            $this->logTransaction($job, 'Execute Job', 'Job executed manually');

            // Here you would implement the actual job execution logic
            // For now, we'll just mark it as done
            $doneStatus = $this->lookupService->getLookupByValue('Done');
            $job->setStatus($doneStatus);

            $this->entityManager->persist($job);
            $this->entityManager->flush();

            return $this->formatScheduledJobForDetail($job);
        }, 'Execute scheduled job: ' . $jobId);
    }

    /**
     * Delete a scheduled job (change status to deleted)
     */
    public function deleteScheduledJob(int $jobId): bool
    {
        return $this->transactionService->executeInTransaction(function () use ($jobId) {
            $job = $this->scheduledJobRepository->find($jobId);
            
            if (!$job) {
                throw new ServiceException('Scheduled job not found', Response::HTTP_NOT_FOUND);
            }

            // Change status to deleted instead of actually deleting
            $deletedStatus = $this->lookupService->getLookupByValue('Deleted');
            $job->setStatus($deletedStatus);

            $this->entityManager->persist($job);
            $this->entityManager->flush();

            // Log transaction
            $this->logTransaction($job, 'Delete Job', 'Job marked as deleted');

            return true;
        }, 'Delete scheduled job: ' . $jobId);
    }

    /**
     * Get transactions related to a scheduled job
     */
    public function getJobTransactions(int $jobId): array
    {
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
    }

    /**
     * Get available job statuses
     */
    public function getJobStatuses(): array
    {
        return $this->lookupService->getLookups(LookupService::SCHEDULED_JOBS_STATUS);
    }

    /**
     * Get available job types
     */
    public function getJobTypes(): array
    {
        return $this->lookupService->getLookups(LookupService::JOB_TYPES);
    }

    /**
     * Get available search date types
     */
    public function getSearchDateTypes(): array
    {
        return $this->lookupService->getLookups(LookupService::SCHEDULED_JOBS_SEARCH_DATE_TYPES);
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

    /**
     * Log transaction for scheduled job operations
     */
    private function logTransaction(ScheduledJob $job, string $action, string $log): void
    {
        $transaction = new Transaction();
        $transaction->setTransactionTime(new \DateTime());
        $transaction->setTableName('scheduledJobs');
        $transaction->setIdTableName($job->getId());
        $transaction->setTransactionLog($log);
        $transaction->setUser($this->getCurrentUser());
        
        $transactionType = $this->lookupService->getLookupByValue($action);
        $transaction->setTransactionType($transactionType);
        
        $transactionBy = $this->lookupService->getLookupByValue('Manual');
        $transaction->setTransactionBy($transactionBy);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }
} 