<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\Admin\AdminCmsPreferenceService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin CMS Preference Controller
 * 
 * Handles CMS preferences management for admin interface
 */
class AdminCmsPreferenceController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminCmsPreferenceService $adminCmsPreferenceService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get CMS preferences
     * 
     * @route /admin/cms-preferences
     * @method GET
     */
    public function getCmsPreferences(): JsonResponse
    {
        try {
            $preferences = $this->adminCmsPreferenceService->getCmsPreferences();
            return $this->responseFormatter->formatSuccess($preferences);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update CMS preferences
     * 
     * @route /admin/cms-preferences
     * @method PUT
     */
    public function updateCmsPreferences(Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/update_cms_preferences', $this->jsonSchemaValidationService);
            
            $preferences = $this->adminCmsPreferenceService->updateCmsPreferences($data);
            
            return $this->responseFormatter->formatSuccess($preferences);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 