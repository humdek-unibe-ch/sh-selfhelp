<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    public function __construct(
        private readonly ApiResponseFormatter $responseFormatter
    ) {}

    /**
     * Get all pages for admin
     * 
     * @route /admin/access
     * @method GET
     */
    public function getAccess(): JsonResponse
    {
        try {
            
            return $this->responseFormatter->formatSuccess([
                'message' => 'Admin get pages endpoint (placeholder)'
            ]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
