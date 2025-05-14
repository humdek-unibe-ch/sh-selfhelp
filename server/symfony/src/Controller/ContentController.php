<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    /**
     * Get all pages
     */
    public function getAllPages(Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Get all pages endpoint (placeholder)'
        ]);
    }

    /**
     * Get page by keyword
     */
    public function getPage(string $page_keyword, Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Get page endpoint (placeholder)',
            'page_keyword' => $page_keyword
        ]);
    }

    /**
     * Update page
     */
    public function updatePage(string $page_keyword, Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Update page endpoint (placeholder)',
            'page_keyword' => $page_keyword
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
