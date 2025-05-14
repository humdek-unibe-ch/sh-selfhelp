<?php

namespace App\Controller;

use App\Service\DynamicControllerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for handling all dynamic routes
 */
class DynamicController extends AbstractController
{
    public function __construct(
        private DynamicControllerService $dynamicControllerService
    ) {
    }

    /**
     * Generic handler for auth routes
     */
    #[Route('/cms-api/auth/{action}', name: 'dynamic_auth')]
    public function authAction(string $action, Request $request): JsonResponse
    {
        $routeName = 'auth_' . $action;
        return $this->dynamicControllerService->handle($routeName, $request);
    }

    /**
     * Generic handler for content page routes
     */
    #[Route('/cms-api/pages', name: 'dynamic_content_pages', methods: ['GET'])]
    public function pagesAction(Request $request): JsonResponse
    {
        return $this->dynamicControllerService->handle('content_pages', $request);
    }

    /**
     * Generic handler for content page routes
     */
    #[Route('/cms-api/pages/{page_keyword}', name: 'dynamic_content_page', methods: ['GET'])]
    public function pageAction(string $page_keyword, Request $request): JsonResponse
    {
        return $this->dynamicControllerService->handle('content_page', $request, [$page_keyword]);
    }

    /**
     * Generic handler for content page update routes
     */
    #[Route('/cms-api/pages/{page_keyword}', name: 'dynamic_content_update_page', methods: ['PUT'])]
    public function pageUpdateAction(string $page_keyword, Request $request): JsonResponse
    {
        return $this->dynamicControllerService->handle('content_update_page', $request, [$page_keyword]);
    }

    /**
     * Generic handler for admin pages routes
     */
    #[Route('/cms-api/admin/pages', name: 'dynamic_admin_pages', methods: ['GET'])]
    public function adminPagesAction(Request $request): JsonResponse
    {
        return $this->dynamicControllerService->handle('admin_get_pages', $request);
    }

    /**
     * Generic handler for admin page fields routes
     */
    #[Route('/cms-api/admin/pages/{page_keyword}/fields', name: 'dynamic_admin_page_fields', methods: ['GET'])]
    public function adminPageFieldsAction(string $page_keyword, Request $request): JsonResponse
    {
        return $this->dynamicControllerService->handle('admin_page_fields', $request, [$page_keyword]);
    }

    /**
     * Generic handler for admin page sections routes
     */
    #[Route('/cms-api/admin/pages/{page_keyword}/sections', name: 'dynamic_admin_page_sections', methods: ['GET'])]
    public function adminPageSectionsAction(string $page_keyword, Request $request): JsonResponse
    {
        return $this->dynamicControllerService->handle('admin_page_sections', $request, [$page_keyword]);
    }
}
