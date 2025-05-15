<?php

namespace App\Controller;

use App\Service\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    private PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }
    /**
     * Get all pages for admin
     */
    public function getPages(Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Admin get pages endpoint (placeholder)'
        ]);
    }

    /**
     * Get page fields
     */
    public function getPageFields(string $page_keyword, Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Admin get page fields endpoint (placeholder)',
            'page_keyword' => $page_keyword
        ]);
    }

    /**
     * Get page sections
     */
    public function getPageSections(string $page_keyword, Request $request): JsonResponse
    {
        $sections = $this->pageService->getPageSections($page_keyword);
        return $this->createApiResponse([
            'page_keyword' => $page_keyword,
            'sections' => $sections
        ]);
    }

    /**
     * Create standardized API response
     */
    private function createApiResponse(
        $data = null,
        int $status = Response::HTTP_OK,
        ?string $error = null
    ): JsonResponse {
        $response = [
            'status' => $status,
            'message' => $status === 200 ? 'OK' : Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => $error,
            'logged_in' => $this->getUser() !== null,
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTime())->format('c')
            ],
            'data' => $data
        ];

        return new JsonResponse($response, $status);
    }
}
