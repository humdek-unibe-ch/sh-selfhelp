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
     * @param page: which page of results (default: 1)
     * @param pageSize: how many jobs per page (default: 20, max: 100)
     * @param search: search term for description, id, user name, or task name
     * @param status: filter by job status
     * @param jobType: filter by job type
     * @param dateFrom: start date for date range filter
     * @param dateTo: end date for date range filter
     * @param dateType: type of date to filter (date_create, date_to_be_executed, date_executed)
     * @param sort: sort field (id, date_create, date_to_be_executed, date_executed, description)
     * @param sortDirection: asc or desc (default: asc)
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

            return $this->responseFormatter->formatSuccess($result);
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
            return $this->responseFormatter->formatSuccess($job);
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
            return $this->responseFormatter->formatSuccess($result, 'Job executed successfully');
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
            $this->adminScheduledJobService->deleteScheduledJob($jobId);
            return $this->responseFormatter->formatSuccess(null, 'Job deleted successfully');
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
            return $this->responseFormatter->formatSuccess($transactions);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get available job statuses
     * 
     * @route /admin/scheduled-jobs/statuses
     * @method GET
     */
    public function getJobStatuses(): JsonResponse
    {
        try {
            $statuses = $this->adminScheduledJobService->getJobStatuses();
            return $this->responseFormatter->formatSuccess($statuses);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get available job types
     * 
     * @route /admin/scheduled-jobs/types
     * @method GET
     */
    public function getJobTypes(): JsonResponse
    {
        try {
            $types = $this->adminScheduledJobService->getJobTypes();
            return $this->responseFormatter->formatSuccess($types);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 