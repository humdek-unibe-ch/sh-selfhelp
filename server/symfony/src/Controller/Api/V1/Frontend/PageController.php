<?php

namespace App\Controller\Api\V1\Frontend;

use App\Service\Core\ApiResponseFormatter;
use App\Service\CMS\Frontend\PageService;
use App\Service\Core\LookupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 Content Controller
 * 
 * Handles content-related endpoints for API v1
 */
class PageController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly PageService $pageService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * @Route("/cms-api/v1/pages", name="pages", methods={"GET"})
     * @Route("/cms-api/v1/pages/{language_id}", name="pages_with_language", methods={"GET"})
     */
    public function getPages(Request $request, ?int $language_id = null): JsonResponse
    {
        try {
            // Mode detection logic: default to 'web', could be extended to accept a query param
            $pages = $this->pageService->getAllAccessiblePagesForUser(LookupService::PAGE_ACCESS_TYPES_WEB, false, $language_id );            
            return $this->responseFormatter->formatSuccess(
                $pages,
                'responses/common/_acl_page_definition',
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

    /**
     * @Route("/cms-api/v1/pages/{page_id}", name="get_page", methods={"GET"})
     */
    public function getPage(Request $request, int $page_id): JsonResponse
    {
        try {
            // Get language_id from query parameter
            $language_id = $request->query->get('language_id') ? (int) $request->query->get('language_id') : null;
            
            $page = $this->pageService->getPage($page_id, $language_id);
            return $this->responseFormatter->formatSuccess(
                $page,
                'responses/frontend/get_page',
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            $statusCode = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $statusCode
            );
        }
    }
}
