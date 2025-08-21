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
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
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

    /**
     * Delete a single unused section
     * Requires permission: admin.section.delete
     */
    public function deleteUnusedSection(int $section_id): Response
    {
        try {
            $this->adminSectionUtilityService->deleteUnusedSection($section_id);

            return $this->apiResponseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }



    /**
     * Delete all unused sections
     * Requires permission: admin.section.delete
     */
    public function deleteAllUnusedSections(): Response
    {
        try {
            $deletedCount = $this->adminSectionUtilityService->deleteAllUnusedSections();

            return $this->apiResponseFormatter->formatSuccess(
                ['deleted_count' => $deletedCount],
                null,
                Response::HTTP_OK
            );
        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
