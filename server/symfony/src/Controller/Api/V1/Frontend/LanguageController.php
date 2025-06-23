<?php

namespace App\Controller\Api\V1\Frontend;

use App\Service\CMS\LanguageService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class LanguageController extends AbstractController
{
    private LanguageService $languageService;
    private ApiResponseFormatter $apiResponseFormatter;

    public function __construct(LanguageService $languageService, ApiResponseFormatter $apiResponseFormatter) {
        $this->languageService = $languageService;
        $this->apiResponseFormatter = $apiResponseFormatter;
    }

    /**
     * Get all languages except 1 (ID = 1)
     * Always returns the default language first, followed by other languages
     * 
     * @return JsonResponse
     */
    public function getAllLanguages(): JsonResponse
    {
        $languages = $this->languageService->getAllNonInternalLanguages();
        return $this->apiResponseFormatter->formatSuccess($languages, 'responses/languages/get_languages');
    }
}
