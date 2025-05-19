<?php

namespace App\Controller\Api\V1\Content;

use App\Exception\ServiceException;
use App\Service\Core\ApiResponseFormatter;
use App\Service\CMS\Frontend\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 Content Controller
 * 
 * Handles content-related endpoints for API v1
 */
class ContentController extends AbstractController
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
     * Get all pages
     * 
     * @route /pages
     * @method GET
     */
    public function getAllPages(Request $request): JsonResponse
    {
        try {
            // This would be implemented to return all published pages
            return $this->responseFormatter->formatSuccess([
                'message' => 'Get all pages endpoint (placeholder)',
                'pages' => []
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
     * Get page by keyword
     * 
     * @route /pages/{page_keyword}
     * @method GET
     */
    public function getPage(string $page_keyword, Request $request): JsonResponse
    {
        try {
            $page = $this->pageService->renderPage($page_keyword);
            return $this->responseFormatter->formatSuccess($page);
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
     * Update page
     * 
     * @route /pages/{page_keyword}
     * @method PUT
     */
    public function updatePage(string $page_keyword, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->responseFormatter->formatError(
                    'Invalid request body',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            // This would be implemented to update a page
            return $this->responseFormatter->formatSuccess([
                'message' => 'Update page endpoint (placeholder)',
                'page_keyword' => $page_keyword
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
