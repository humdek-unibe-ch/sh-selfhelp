<?php

namespace App\Service\Core;

use App\Entity\Lookup;
use App\Entity\ScheduledJob;
use App\Entity\ScheduledJobsUser;
use App\Entity\ScheduledJobsMailQueue;
use App\Entity\ScheduledJobsNotification;
use App\Entity\ScheduledJobsTask;
use App\Entity\MailQueue;
use App\Entity\Notification;
use App\Entity\Task;
use App\Entity\User;
use App\Service\Core\TransactionService;
use App\Service\Core\LookupService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for scheduling and executing jobs (emails, notifications, tasks)
 * 
 * This service replaces the legacy JobScheduler functionality and provides:
 * - Job scheduling for emails, push notifications, and tasks
 * - Job execution with proper transaction handling
 * - Support for conditions and user targeting
 * - Integration with the transaction logging system
 */
class JobSchedulerService extends BaseService
{
    private TransactionService $transactionService;
    private LookupService $lookupService;
    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $em,
        TransactionService $transactionService,
        LookupService $lookupService,
        LoggerInterface $logger
    ) {
        $this->transactionService = $transactionService;
        $this->lookupService = $lookupService;
        $this->logger = $logger;
    }

    /**
     * Schedule a job for execution
     * 
     * @param array $jobData Job configuration data
     * @param string $transactionBy Who initiated the transaction
     * @return int|false Job ID if successful, false on failure
     */
    public function scheduleJob(array $jobData, string $transactionBy): ScheduledJob|false
    {
        try {
            
            $job = $this->createScheduledJob($jobData);
            if (!$job) {
                throw new \Exception('Failed to create scheduled job');
            }

            // Schedule specific job type
            $success = match ($jobData['type']) {
                $this->lookupService::JOB_TYPES_EMAIL => $this->scheduleEmailJob($job, $jobData),
                $this->lookupService::JOB_TYPES_NOTIFICATION => $this->scheduleNotificationJob($job, $jobData),
                $this->lookupService::JOB_TYPES_TASK => $this->scheduleTaskJob($job, $jobData),
                default => throw new \Exception('Unknown job type: ' . $jobData['type'])
            };

            if (!$success) {
                throw new \Exception('Failed to schedule job of type: ' . $jobData['type']);
            }

            // Add users to the job
            if (isset($jobData['users']) && is_array($jobData['users'])) {
                $this->addUsersToJob($job, $jobData['users']);
            }

            // Log the transaction
            $this->transactionService->logTransaction(
                'insert',
                $transactionBy,
                'scheduledJobs',
                $job->getId(),
                false,
                'Job scheduled: ' . ($jobData['description'] ?? $jobData['type'])
            );

            return $job;

        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule job', [
                'error' => $e->getMessage(),
                'jobData' => $jobData
            ]);
            return false;
        }
    }

    /**
     * Schedule an email validation job for a user
     * 
     * @param int $userId User ID
     * @param string $token Validation token
     * @param array $emailConfig Email configuration
     * @return int|false Job ID if successful, false on failure
     */
    public function scheduleUserValidationEmail(int $userId, string $token, array $emailConfig = []): ScheduledJob|false
    {
        $defaultConfig = [
            'from_email' => $emailConfig['from_email'] ?? 'noreply@example.com',
            'from_name' => $emailConfig['from_name'] ?? 'System',
            'reply_to' => $emailConfig['reply_to'] ?? 'noreply@example.com',
            'subject' => $emailConfig['subject'] ?? 'Account Validation Required',
            'body' => $emailConfig['body'] ?? $this->getDefaultValidationEmailBody($userId, $token),
            'is_html' => $emailConfig['is_html'] ?? true
        ];

        $jobData = [
            'type' => $this->lookupService::JOB_TYPES_EMAIL,
            'description' => 'User account validation email',
            'date_to_be_executed' => new \DateTime(), // Send immediately
            'users' => [$userId],
            'email_config' => $defaultConfig
        ];

        return $this->scheduleJob($jobData, $this->lookupService::TRANSACTION_BY_BY_SYSTEM);
    }

    /**
     * Execute a scheduled job
     * 
     * @param int $jobId Job ID to execute
     * @param string $transactionBy Who initiated the execution
     * @return bool True if successful, false on failure
     */
    public function executeJob(int $jobId, string $transactionBy): ScheduledJob|false
    {
        try {
            $this->em->beginTransaction();

            $job = $this->em->getRepository(ScheduledJob::class)->find($jobId);
            if (!$job) {
                throw new \Exception('Job not found: ' . $jobId);
            }

            // Determine job type and execute accordingly
            $jobTypeId = $job->getJobType()->getId();
            $jobTypeName = $this->lookupService->getLookupCodeById($jobTypeId);

            $success = match ($jobTypeName) {
                $this->lookupService::JOB_TYPES_EMAIL => $this->executeEmailJob($job, $transactionBy),
                $this->lookupService::JOB_TYPES_NOTIFICATION => $this->executeNotificationJob($job, $transactionBy),
                $this->lookupService::JOB_TYPES_TASK => $this->executeTaskJob($job, $transactionBy),
                default => throw new \Exception('Unknown job type: ' . $jobTypeName)
            };

            // Update job status
            $status = $this->lookupService->findByTypeAndCode($this->lookupService::SCHEDULED_JOBS_STATUS, $success ? $this->lookupService::SCHEDULED_JOBS_STATUS_DONE : $this->lookupService::SCHEDULED_JOBS_STATUS_FAILED);
            
            $job->setStatus($status);
            $job->setDateExecuted(new \DateTime());
            $this->em->flush();

            // Log the execution
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                $transactionBy,
                'scheduledJobs',
                $jobId,
                false,
                'Job executed: ' . ($success ? 'executed' : 'failed')
            );

            $this->em->commit();
            return $job;

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Failed to execute job', [
                'jobId' => $jobId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete a scheduled job (mark as deleted)
     * 
     * @param int $jobId Job ID to delete
     * @param string $transactionBy Who initiated the deletion
     * @return bool True if successful, false on failure
     */
    public function deleteJob(int $jobId, string $transactionBy): bool
    {
        try {
            $this->em->beginTransaction();

            $job = $this->em->getRepository(ScheduledJob::class)->find($jobId);
            if (!$job) {
                throw new \Exception('Job not found: ' . $jobId);
            }

            $deletedStatus = $this->lookupService->findByTypeAndCode($this->lookupService::SCHEDULED_JOBS_STATUS, $this->lookupService::SCHEDULED_JOBS_STATUS_DELETED);
            $job->setStatus($deletedStatus);
            $this->em->flush();

            $this->transactionService->logTransaction(
                'delete',
                $transactionBy,
                'scheduledJobs',
                $jobId,
                false,
                'Job marked as deleted'
            );

            $this->em->commit();
            return true;

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Failed to delete job', [
                'jobId' => $jobId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Schedule an email job directly (public method)
     * 
     * @param array $emailConfig Email configuration
     * @param \DateTime|null $dateToExecute When to execute the job (default: now)
     * @param int|null $userId Optional user ID to associate with the job
     * @return int|false Job ID if successful, false on failure
     */
    public function scheduleDirectEmailJob(
        array $emailConfig, 
        ?\DateTime $dateToExecute = null, 
        ?int $userId = null
    ): int|false {
        $jobData = [
            'type' => $this->lookupService::JOB_TYPES_EMAIL,
            'description' => $emailConfig['subject'] ?? 'Email job',
            'date_to_be_executed' => $dateToExecute ?? new \DateTime(),
            'email_config' => $emailConfig
        ];

        if ($userId) {
            $jobData['users'] = [$userId];
        }

        return $this->scheduleJob($jobData, $this->lookupService::TRANSACTION_BY_BY_SYSTEM);
    }

    /**
     * Create the base scheduled job entry
     */
    private function createScheduledJob(array $jobData): ScheduledJob|false
    {
        try {
            $jobType = $this->em->getRepository(Lookup::class)->findOneBy(['typeCode' => $this->lookupService::JOB_TYPES, 'lookupValue' => $jobData['type']]);
            $status = $this->em->getRepository(Lookup::class)->findOneBy(['typeCode' => $this->lookupService::SCHEDULED_JOBS_STATUS, 'lookupValue' => $this->lookupService::SCHEDULED_JOBS_STATUS_QUEUED]);

            $scheduledJob = new ScheduledJob();
            $scheduledJob->setJobType($jobType);
            $scheduledJob->setStatus($status);
            $scheduledJob->setDescription($jobData['description'] ?? '');
            $scheduledJob->setDateCreate(new \DateTime());
            $scheduledJob->setDateToBeExecuted($jobData['date_to_be_executed'] ?? new \DateTime());
            
            if (isset($jobData['condition'])) {
                $scheduledJob->setConfig(json_encode($jobData['condition']));
            }

            $this->em->persist($scheduledJob);
            
            $this->em->flush();

            return $scheduledJob;

        } catch (\Exception $e) {
            $this->logger->error('Failed to create scheduled job', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Schedule an email job
     */
    private function scheduleEmailJob(ScheduledJob $job, array $jobData): bool
    {
        try {
            $emailConfig = $jobData['email_config'];
            
            $mailQueue = new MailQueue();
            $mailQueue->setFromEmail($emailConfig['from_email']);
            $mailQueue->setFromName($emailConfig['from_name']);
            $mailQueue->setReplyTo($emailConfig['reply_to']);
            $mailQueue->setRecipientEmails($emailConfig['recipient_emails'] ?? '');
            $mailQueue->setSubject($emailConfig['subject']);
            $mailQueue->setBody($emailConfig['body']);
            $mailQueue->setIsHtml($emailConfig['is_html'] ?? true);

            if (isset($emailConfig['cc_emails'])) {
                $mailQueue->setCcEmails($emailConfig['cc_emails']);
            }
            if (isset($emailConfig['bcc_emails'])) {
                $mailQueue->setBccEmails($emailConfig['bcc_emails']);
            }

            $this->em->persist($mailQueue);

            $this->em->flush();

            // Link scheduled job to mail queue
            $scheduledJobMailQueue = new ScheduledJobsMailQueue();
            $scheduledJobMailQueue->setScheduledJob($job);
            $scheduledJobMailQueue->setMailQueue($mailQueue);

            $this->em->persist($scheduledJobMailQueue);

            $this->em->flush();

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule email job', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Schedule a notification job
     */
    private function scheduleNotificationJob(int $jobId, array $jobData): bool
    {
        try {
            $notificationConfig = $jobData['notification_config'];
            
            $notification = new Notification();
            $notification->setSubject($notificationConfig['subject']);
            $notification->setBody($notificationConfig['body']);
            
            if (isset($notificationConfig['url'])) {
                $notification->setUrl($notificationConfig['url']);
            }

            $this->em->persist($notification);
            
            $this->em->flush();

            // Link scheduled job to notification
            $scheduledJobNotification = new ScheduledJobsNotification();
            $job = $this->em->getRepository(ScheduledJob::class)->find($jobId);
            $scheduledJobNotification->setScheduledJob($job);
            $scheduledJobNotification->setNotification($notification);

            $this->em->persist($scheduledJobNotification);
            
            $this->em->flush();

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule notification job', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Schedule a task job
     */
    private function scheduleTaskJob(int $jobId, array $jobData): bool
    {
        try {
            $taskConfig = $jobData['task_config'];
            
            $task = new Task();
            $task->setConfig(json_encode($taskConfig));

            $this->em->persist($task);
            
            $this->em->flush();

            // Link scheduled job to task
            $scheduledJobTask = new ScheduledJobsTask();
            $job = $this->em->getRepository(ScheduledJob::class)->find($jobId);
            $scheduledJobTask->setScheduledJob($job);
            $scheduledJobTask->setTask($task);

            $this->em->persist($scheduledJobTask);
            
            $this->em->flush();

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule task job', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Add users to a scheduled job
     */
    private function addUsersToJob(ScheduledJob $job, array $userIds): void
    {
        foreach ($userIds as $userId) {
            $scheduledJobUser = new ScheduledJobsUser();
            $scheduledJobUser->setScheduledJob($job);
            $user = $this->em->getRepository(User::class)->findOneBy(['id' => $userId]);
            $scheduledJobUser->setUser($user);

            $this->em->persist($scheduledJobUser);
        }
        
        $this->em->flush();
    }

    /**
     * Execute an email job
     */
    private function executeEmailJob(ScheduledJob $job, string $transactionBy): bool
    {
        // TODO: Implement email sending logic
        // This will be implemented in a separate EmailService
        $this->logger->info('Email job execution not yet implemented', ['jobId' => $job->getId()]);
        return true;
    }

    /**
     * Execute a notification job
     */
    private function executeNotificationJob(ScheduledJob $job, string $transactionBy): bool
    {
        // TODO: Implement push notification sending logic
        // This will be implemented in a separate NotificationService
        $this->logger->info('Notification job execution not yet implemented', ['jobId' => $job->getId()]);
        return true;
    }

    /**
     * Execute a task job
     */
    private function executeTaskJob(ScheduledJob $job, string $transactionBy): bool
    {
        // TODO: Implement task execution logic (add/remove groups)
        // This will be implemented in a separate TaskService
        $this->logger->info('Task job execution not yet implemented', ['jobId' => $job->getId()]);
        return true;
    }

    /**
     * Get default validation email body
     */
    private function getDefaultValidationEmailBody(int $userId, string $token): string
    {
        $validationUrl = "validate/{$userId}/{$token}";
        
        return "
        <h2>Account Validation Required</h2>
        <p>Thank you for registering! Please click the link below to validate your account:</p>
        <p><a href=\"{$validationUrl}\">{$validationUrl}</a></p>
        <p>If you did not create this account, please ignore this email.</p>
        ";
    }
} 