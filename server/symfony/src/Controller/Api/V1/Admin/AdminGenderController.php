<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\CMS\Admin\AdminGenderService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Admin Gender Controller
 * 
 * Handles gender-related operations for admin interface
 */
class AdminGenderController extends AbstractController
{
    public function __construct(
        private readonly AdminGenderService $adminGenderService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Get all genders
     * 
     * @route /admin/genders
     * @method GET
     */
    public function getAllGenders(): JsonResponse
    {
        try {
            $genders = $this->adminGenderService->getAllGenders();
            return $this->responseFormatter->formatSuccess(['genders' => $genders]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }
} 