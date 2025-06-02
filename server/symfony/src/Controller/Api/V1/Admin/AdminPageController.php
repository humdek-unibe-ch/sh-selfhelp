<?php

namespace App\Controller\Api\V1\Admin;

use App\Exception\ServiceException;
use App\Service\Core\ApiResponseFormatter;
use App\Service\CMS\Admin\AdminPageService;
use App\Service\CMS\Frontend\PageService;
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
        private readonly AdminPageService $adminPageService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly PageService $pageService
    ) {
    }

    /**
     * Get all pages for admin
     * 
     * @route /admin/pages
     * @method GET
     */
    public function getPages(): JsonResponse
    {
        try {
            // Mode detection logic: default to 'web', could be extended to accept a query param
            $mode = 'web';
            $pages = $this->pageService->getAllAccessiblePagesForUser($mode);            
            return $this->responseFormatter->formatSuccess(
                ['pages' => $pages],
                'responses/admin/pages',
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
     * Get page fields
     * 
     * @route /admin/pages/{page_keyword}/fields
     * @method GET
     */
    public function getPageFields(string $page_keyword): JsonResponse
    {
        try {
            $fields = $this->adminPageService->getPageFields($page_keyword);
            return $this->responseFormatter->formatSuccess([
                'page_keyword' => $page_keyword,
                'fields' => $fields
            ]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get page sections
     * 
     * @route /admin/pages/{page_keyword}/sections
     * @method GET
     */
    public function getPageSections(string $page_keyword): JsonResponse
    {
        try {
            $sections = $this->adminPageService->getPageSections($page_keyword);
            return $this->responseFormatter->formatSuccess([
                'page_keyword' => $page_keyword,
                'sections' => $sections
            ], 'responses/admin/page_sections');
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
