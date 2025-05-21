<?php

namespace App\Controller\Api\V1\Frontend;

use App\Service\Core\ApiResponseFormatter;
use App\Service\CMS\Frontend\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 Content Controller
 * 
 * Handles content-related endpoints for API v1
 */
class PageController extends AbstractController
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
     * @Route("/cms-api/v1/pages", name="pages", methods={"GET"})
     */
    public function getPages(): JsonResponse
    {
        try {
            // Mode detection logic: default to 'web', could be extended to accept a query param
            $mode = 'web';
            $pages = $this->pageService->getAllAccessiblePagesForUser($mode);
            return $this->responseFormatter->formatSuccess([
                'pages' => $pages
            ]);
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode(),
                $this->getUser() !== null
            );
        }
    }
}
