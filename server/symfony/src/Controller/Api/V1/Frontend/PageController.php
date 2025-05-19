<?php

namespace App\Controller\Api\V1\Frontend;

use App\Service\Core\ApiResponseFormatter;
use App\Service\CMS\Frontend\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
