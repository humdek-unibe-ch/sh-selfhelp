<?php

namespace App\Controller\Api\V1\Admin\Common;

use App\Service\Core\ApiResponseFormatter;
use App\Service\Core\LookupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LookupController extends AbstractController
{
    public function __construct(
        private readonly LookupService $lookupService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Get all lookups
     * 
     * @route /admin/lookups
     * @method GET
     */
    public function getAllLookups(): JsonResponse
    {
        try {
            $all_lookups = $this->lookupService->getAllLookups(); 
            return $this->responseFormatter->formatSuccess(
                $all_lookups,
                null,
                Response::HTTP_OK // Explicitly pass the status code
            );
        } catch (\Throwable $e) {
            // Attempt to get a valid HTTP status code from the exception, default to 500
            $statusCode = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $statusCode
            );
        }
    }
}