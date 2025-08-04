<?php

namespace App\Controller\Api\V1\Css;

use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 CSS Controller
 * 
 * Handles CSS classes endpoints for API v1 (open access)
 */
class CssController extends AbstractController
{
    public function __construct(
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Get all CSS classes
     * 
     * @route /frontend/css-classes
     * @method GET
     */
    public function getCssClasses(): JsonResponse
    {
        try {
            $cssClasses = $this->loadCssClasses();
            
            return $this->responseFormatter->formatSuccess(
                ['classes' => $cssClasses],
                'responses/frontend/css_classes',
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            $statusCode = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599) 
                ? $e->getCode() 
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $statusCode
            );
        }
    }

    /**
     * Load CSS classes from the JSON file or fallback
     * 
     * @return array
     */
    private function loadCssClasses(): array
    {
        $jsonPath = $this->getProjectDir() . '/public/assets/tailwind-classes.json';
        
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $allClasses = json_decode($jsonContent, true);
            
            if (is_array($allClasses)) {
                return $allClasses;
            }
        }
        
        // Fallback to a curated list of common classes
        return $this->getFallbackCssClasses();
    }

    /**
     * Get fallback CSS classes when JSON file is not available
     * 
     * @return array Common CSS classes
     */
    private function getFallbackCssClasses(): array
    {
        return [
            // Layout
            'container', 'mx-auto', 'flex', 'grid', 'block', 'inline-block', 'hidden',
            
            // Spacing
            'p-0', 'p-1', 'p-2', 'p-3', 'p-4', 'p-6', 'p-8',
            'px-2', 'px-4', 'px-6', 'py-2', 'py-4', 'py-6',
            'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-6', 'm-8',
            'mx-auto', 'mx-2', 'mx-4', 'my-2', 'my-4',
            
            // Typography
            'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl',
            'font-normal', 'font-medium', 'font-semibold', 'font-bold',
            'text-left', 'text-center', 'text-right',
            
            // Colors
            'text-white', 'text-black', 'text-gray-500', 'text-gray-700', 'text-gray-900',
            'bg-white', 'bg-black', 'bg-gray-50', 'bg-gray-100', 'bg-gray-500',
            'bg-blue-500', 'bg-green-500', 'bg-red-500',
            
            // Borders & Radius
            'border', 'border-2', 'border-gray-300', 'rounded', 'rounded-lg',
            
            // Sizing
            'w-full', 'w-1/2', 'w-1/3', 'w-2/3', 'h-auto', 'h-full',
            
            // Flexbox
            'justify-center', 'justify-between', 'items-center', 'items-start',
            
            // Grid
            'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4',
            'gap-2', 'gap-4', 'gap-6',
            
            // Responsive
            'sm:block', 'md:flex', 'lg:grid-cols-3', 'xl:text-xl',
            
            // States
            'hover:bg-gray-100', 'focus:outline-none', 'active:bg-gray-200'
        ];
    }

    /**
     * Get the project directory
     * 
     * @return string
     */
    private function getProjectDir(): string
    {
        return $this->getParameter('kernel.project_dir');
    }
} 