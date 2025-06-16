<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Repository\LanguageRepository;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing languages in the admin API
 */
class AdminLanguageController extends AbstractController
{
    use RequestValidatorTrait;
    
    /**
     * @param LanguageRepository $languageRepository
     * @param ApiResponseFormatter $apiResponseFormatter
     * @param JsonSchemaValidationService $jsonSchemaValidationService
     */
    public function __construct(
        private readonly LanguageRepository $languageRepository,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }
    
    /**
     * Get all languages
     *
     * @Route("/api/v1/admin/languages", name="admin_languages_get", methods={"GET"})
     * @return JsonResponse
     */
    public function getLanguages(): JsonResponse
    {
        // Get all languages
        $languages = $this->languageRepository->findAll();
        
        // Return formatted response with schema validation
        return $this->apiResponseFormatter->formatSuccess(
            $languages,
            'responses/languages/get_languages'
        );
    }


}
