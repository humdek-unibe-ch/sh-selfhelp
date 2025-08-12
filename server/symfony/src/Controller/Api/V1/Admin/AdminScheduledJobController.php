<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\Admin\AdminScheduledJobService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin Scheduled Job Controller
 * 
 * Handles all scheduled job management operations for admin interface
 */
class AdminScheduledJobController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminScheduledJobService $adminScheduledJobService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get scheduled jobs with pagination, search, and filtering
     * 
     * @route /admin/scheduled-jobs
     * @method GET
     * Query params: page, pageSize, search, status, jobType, dateFrom, dateTo, dateType, sort, sortDirection
     */
    public function getScheduledJobs(Request $request): JsonResponse
    {
        try {
            $page = (int)$request->query->get('page', 1);
            $pageSize = (int)$request->query->get('pageSize', 20);
            $search = $request->query->get('search');
            $status = $request->query->get('status');
            $jobType = $request->query->get('jobType');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $dateType = $request->query->get('dateType', 'date_to_be_executed');
            $sort = $request->query->get('sort');
            $sortDirection = $request->query->get('sortDirection', 'asc');

            // Parse dates if provided
            $dateFromObj = $dateFrom ? new \DateTime($dateFrom) : null;
            $dateToObj = $dateTo ? new \DateTime($dateTo) : null;

            $result = $this->adminScheduledJobService->getScheduledJobs(
                $page,
                $pageSize,
                $search,
                $status,
                $jobType,
                $dateFromObj,
                $dateToObj,
                $dateType,
                $sort,
                $sortDirection
            );

            return $this->responseFormatter->formatSuccess($result, 'responses/admin/scheduled_jobs/scheduled_jobs');
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get single scheduled job by ID
     * 
     * @route /admin/scheduled-jobs/{jobId}
     * @method GET
     */
    public function getScheduledJobById(int $jobId): JsonResponse
    {
        try {
            $job = $this->adminScheduledJobService->getScheduledJobById($jobId);
            
            // Add transactions to the job detail
            $job['transactions'] = $this->adminScheduledJobService->getJobTransactions($jobId);
            
            return $this->responseFormatter->formatSuccess($job, 'responses/admin/scheduled_jobs/scheduled_job');
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Execute a scheduled job
     * 
     * @route /admin/scheduled-jobs/{jobId}/execute
     * @method POST
     */
    public function executeScheduledJob(int $jobId): JsonResponse
    {
        try {
            $result = $this->adminScheduledJobService->executeScheduledJob($jobId);

            if ($result) {
                $result['transactions'] = $this->adminScheduledJobService->getJobTransactions($jobId);
            }

            return $this->responseFormatter->formatSuccess($result, 'responses/admin/scheduled_jobs/scheduled_job');
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete a scheduled job (change status to deleted)
     * 
     * @route /admin/scheduled-jobs/{jobId}
     * @method DELETE
     */
    public function deleteScheduledJob(int $jobId): JsonResponse
    {
        try {
            $res = $this->adminScheduledJobService->deleteScheduledJob($jobId);
            return $this->responseFormatter->formatSuccess($res, null, $res ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get transactions related to a scheduled job
     * 
     * @route /admin/scheduled-jobs/{jobId}/transactions
     * @method GET
     */
    public function getJobTransactions(int $jobId): JsonResponse
    {
        try {
            $transactions = $this->adminScheduledJobService->getJobTransactions($jobId);
            
            return $this->responseFormatter->formatSuccess($transactions,  'responses/admin/scheduled_jobs/job_transactions');
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 