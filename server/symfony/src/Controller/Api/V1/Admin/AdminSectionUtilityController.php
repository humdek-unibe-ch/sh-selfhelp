<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\Admin\AdminSectionUtilityService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for section utility operations
 */
class AdminSectionUtilityController extends AbstractController
{
    use RequestValidatorTrait;
    
    public function __construct(
        private readonly AdminSectionUtilityService $adminSectionUtilityService,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {}

    /**
     * Get all unused sections (not in hierarchy and not in pages_sections)
     * Requires permission: admin.page.update
     */
    public function getUnusedSections(): JsonResponse
    {
        $unusedSections = $this->adminSectionUtilityService->getUnusedSections();
        
        return $this->apiResponseFormatter->formatSuccess(
            $unusedSections,
            'responses/admin/sections/unused_sections_envelope'
        );
    }

    /**
     * Get all refContainer sections
     * Requires permission: admin.page.update
     */
    public function getRefContainers(): JsonResponse
    {
        $refContainers = $this->adminSectionUtilityService->getRefContainers();
        
        return $this->apiResponseFormatter->formatSuccess(
            $refContainers,
            'responses/admin/sections/ref_containers_envelope'
        );
    }
}
