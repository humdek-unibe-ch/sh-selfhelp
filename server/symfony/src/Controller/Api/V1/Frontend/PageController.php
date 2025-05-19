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

}
