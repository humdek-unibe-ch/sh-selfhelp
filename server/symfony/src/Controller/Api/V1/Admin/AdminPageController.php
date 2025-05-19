<?php

namespace App\Controller\Api\V1\Admin;

use App\Exception\ServiceException;
use App\Service\Core\ApiResponseFormatter;
use App\Service\CMS\Admin\AdminPageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 Admin Controller
 * 
 * Handles admin-related endpoints for API v1
 */
class AdminPageController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly AdminPageService $pageService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Get all pages for admin
     * 
     * @route /admin/pages
     * @method GET
     */
    public function getPages(Request $request): JsonResponse
    {
        try {
            // Empty implementation for now
            return $this->responseFormatter->formatSuccess([
                'message' => 'Admin get pages endpoint (placeholder)'
            ]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getUser() !== null
            );
        }
    }

    /**
     * Get page fields
     * 
     * @route /admin/pages/{page_keyword}/fields
     * @method GET
     */
    public function getPageFields(string $page_keyword, Request $request): JsonResponse
    {
        try {
            $fields = $this->pageService->getPageFields($page_keyword);
            return $this->responseFormatter->formatSuccess([
                'page_keyword' => $page_keyword,
                'fields' => $fields
            ]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e, $this->getUser() !== null);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getUser() !== null
            );
        }
    }

    /**
     * Get page sections
     * 
     * @route /admin/pages/{page_keyword}/sections
     * @method GET
     */
    public function getPageSections(string $page_keyword, Request $request): JsonResponse
    {
        try {
            $sections = $this->pageService->getPageSections($page_keyword);
            return $this->responseFormatter->formatSuccess([
                'page_keyword' => $page_keyword,
                'sections' => $sections
            ]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e, $this->getUser() !== null);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getUser() !== null
            );
        }
    }
}
