<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\LanguageService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing languages in the admin API
 */
class AdminLanguageController extends AbstractController
{
    use RequestValidatorTrait;
    
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly ApiResponseFormatter $apiResponseFormatter
    ) {
    }
    
     /**Add commentMore actions
     * Get all languages
     * 
     * @return JsonResponse
     */
    public function getAllLanguages(): JsonResponse
    {
        $languages = $this->languageService->getAllNonInternalLanguages();
        return $this->apiResponseFormatter->formatSuccess($languages, 'responses/languages/get_languages');
    }

    /**
     * Get a language by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getLanguage(int $id): JsonResponse
    {
        $language = $this->languageService->getLanguageById($id);
        return $this->apiResponseFormatter->formatSuccess($language, 'responses/languages/language');
    }

    /**
     * Create a new language
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function createLanguage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $language = $this->languageService->createLanguage($data);
        return $this->apiResponseFormatter->formatSuccess($language);
    }

    /**
     * Update an existing language
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLanguage(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $language = $this->languageService->updateLanguage($id, $data);
        return $this->apiResponseFormatter->formatSuccess($language, 'responses/languages/language');
    }

    /**
     * Delete a language
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function deleteLanguage(int $id): JsonResponse
    {
        $language = $this->languageService->deleteLanguage($id);
        return $this->apiResponseFormatter->formatSuccess($language, 'responses/languages/language');
    }


}
