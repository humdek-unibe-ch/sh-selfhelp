<?php

namespace App\Controller;

use App\Exception\ServiceException;
use App\Service\ApiResponseFormatter;
use App\Service\CMS\Admin\AdminPageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    private AdminPageService $pageService;
    private ApiResponseFormatter $responseFormatter;

    public function __construct(AdminPageService $pageService, ApiResponseFormatter $responseFormatter)
    {
        $this->pageService = $pageService;
        $this->responseFormatter = $responseFormatter;
    }
    /**
     * Get all pages for admin
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
     */
    public function getPageFields(string $page_keyword, Request $request): JsonResponse
    {
        try {
            // Empty implementation for now
            return $this->responseFormatter->formatSuccess([
                'message' => 'Admin get page fields endpoint (placeholder)',
                'page_keyword' => $page_keyword
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
     * Get page sections
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

    /**
     * Get all methods should follow this pattern for exception handling
     */
    private function executeServiceMethod(callable $serviceMethod, array $additionalData = []): JsonResponse
    {
        try {
            $result = $serviceMethod();
            $data = $additionalData;
            if ($result !== null) {
                $data = array_merge($data, ['result' => $result]);
            }
            return $this->responseFormatter->formatSuccess($data);
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
