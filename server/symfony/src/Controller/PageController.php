<?php

namespace App\Controller;

use App\Service\ApiResponseFormatter;
use App\Service\CMS\Frontend\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
{
    public function __construct(
        private PageService $pageService,
        private ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Render a public page
     */
    public function renderPage(string $pageKeyword, Request $request): JsonResponse
    {
        try {
            $page = $this->pageService->renderPage($pageKeyword);
            return $this->responseFormatter->formatSuccess($page);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() >= 400 ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getUser() !== null
            );
        }
    }
}
