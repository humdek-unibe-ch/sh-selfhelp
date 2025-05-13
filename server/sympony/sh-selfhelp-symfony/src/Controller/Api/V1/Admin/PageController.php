<?php

namespace App\Controller\Api\V1\Admin\Pages;

use App\Service\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Admin page detail API controller
 */
#[Route('/api/v1/admin/pages', name: 'api_admin_pages_')]
class PageController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly PageService $pageService
    ) {
    }

    /**
     * Get page fields
     * 
     * Retrieves fields for a specific page
     */
    #[Route('/{pageKeyword}/fields', name: 'get_fields', methods: ['GET'])]
    public function getPageFields(string $pageKeyword): JsonResponse
    {
        try {
            $pageFields = $this->pageService->getPageFields($pageKeyword);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'OK',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $pageFields
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get page sections
     * 
     * Retrieves sections for a specific page in a hierarchical structure
     */
    #[Route('/{pageKeyword}/sections', name: 'get_sections', methods: ['GET'])]
    public function getPageSections(string $pageKeyword): JsonResponse
    {
        try {
            $sections = $this->pageService->getPageSections($pageKeyword);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'OK',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $sections
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')