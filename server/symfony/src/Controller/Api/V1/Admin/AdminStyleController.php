<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Repository\StyleRepository;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing styles in the admin API
 */
class AdminStyleController extends AbstractController
{
    use RequestValidatorTrait;
    
    /**
     * @param StyleRepository $styleRepository
     * @param ApiResponseFormatter $apiResponseFormatter
     * @param JsonSchemaValidationService $jsonSchemaValidationService
     */
    public function __construct(
        private readonly StyleRepository $styleRepository,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get all styles grouped by style group with relationship information
     *
     * Returns styles with:
     * - Basic style information (id, name, description, type)
     * - canHaveChildren flag
     * - Relationship constraints (allowedChildren, allowedParents)
     *
     * @Route("/api/v1/admin/styles", name="admin_styles_get", methods={"GET"})
     * @return JsonResponse
     */
    public function getStyles(): JsonResponse
    {
        // Get all styles grouped by style group with relationship information
        $styles = $this->styleRepository->findAllStylesGroupedByGroup();
        
        // Return formatted response with schema validation
        return $this->apiResponseFormatter->formatSuccess(
            $styles,
            'responses/style/styleGroups'
        );
    }
}
