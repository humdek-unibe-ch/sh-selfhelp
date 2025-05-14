<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Admin page sections API controller
 */
#[Route('/page_sections', name: 'api_admin_page_sections_')]
class PageSectionController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly PageService $pageService
    ) {
    }

    /**
     * Get page sections
     * 
     * Retrieves sections for a specific page in a hierarchical structure
     */
    #[Route('/{pageKeyword}', name: 'get_sections', methods: ['GET'])]
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
}
