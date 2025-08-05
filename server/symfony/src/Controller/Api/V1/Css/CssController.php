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
            ['value' => 'container', 'text' => 'Container'],
            ['value' => 'mx-auto', 'text' => 'Margin auto'],
            ['value' => 'flex', 'text' => 'Flex'],
            ['value' => 'grid', 'text' => 'Grid'],
            ['value' => 'block', 'text' => 'Block'],
            ['value' => 'inline-block', 'text' => 'Inline block'],
            ['value' => 'hidden', 'text' => 'Hidden'],
            
            // Spacing
            ['value' => 'p-0', 'text' => 'Padding 0'],
            ['value' => 'p-1', 'text' => 'Padding 1'],
            ['value' => 'p-2', 'text' => 'Padding 2'],
            ['value' => 'p-3', 'text' => 'Padding 3'],
            ['value' => 'p-4', 'text' => 'Padding 4'],
            ['value' => 'p-6', 'text' => 'Padding 6'],
            ['value' => 'p-8', 'text' => 'Padding 8'],
            ['value' => 'px-2', 'text' => 'Padding x 2'],
            ['value' => 'px-4', 'text' => 'Padding x 4'],
            ['value' => 'px-6', 'text' => 'Padding x 6'],
            
            // Typography
            ['value' => 'text-xs', 'text' => 'Text xs'],
            ['value' => 'text-sm', 'text' => 'Text sm'],
            ['value' => 'text-base', 'text' => 'Text base'],
            ['value' => 'text-lg', 'text' => 'Text lg'],
            ['value' => 'text-xl', 'text' => 'Text xl'],
            ['value' => 'text-2xl', 'text' => 'Text 2xl'],
            ['value' => 'font-normal', 'text' => 'Font normal'],
            ['value' => 'font-medium', 'text' => 'Font medium'],
            ['value' => 'font-semibold', 'text' => 'Font semibold'],
            
            // Colors
            ['value' => 'text-white', 'text' => 'Text white'],
            ['value' => 'text-black', 'text' => 'Text black'],
            ['value' => 'text-gray-500', 'text' => 'Text gray 500'],
            ['value' => 'text-gray-700', 'text' => 'Text gray 700'],
            ['value' => 'text-gray-900', 'text' => 'Text gray 900'],
            ['value' => 'bg-white', 'text' => 'Background white'],
            ['value' => 'bg-black', 'text' => 'Background black'],
            ['value' => 'bg-gray-50', 'text' => 'Background gray 50'],
            ['value' => 'bg-gray-100', 'text' => 'Background gray 100'],
            
            // Borders & Radius
            ['value' => 'border', 'text' => 'Border'],
            ['value' => 'border-2', 'text' => 'Border 2'],
            ['value' => 'border-gray-300', 'text' => 'Border gray 300'],
            ['value' => 'rounded', 'text' => 'Rounded'],
            ['value' => 'rounded-lg', 'text' => 'Rounded lg'],
            
            // Sizing
            ['value' => 'w-full', 'text' => 'Width full'],
            ['value' => 'w-1/2', 'text' => 'Width 1/2'],
            ['value' => 'w-1/3', 'text' => 'Width 1/3'],
            ['value' => 'w-2/3', 'text' => 'Width 2/3'],
            ['value' => 'h-auto', 'text' => 'Height auto'],
            ['value' => 'h-full', 'text' => 'Height full'],
            
            // Flexbox
            ['value' => 'justify-center', 'text' => 'Justify center'],
            ['value' => 'justify-between', 'text' => 'Justify between'],
            ['value' => 'items-center', 'text' => 'Items center'],
            ['value' => 'items-start', 'text' => 'Items start'],
            
            // Grid
            ['value' => 'grid-cols-1', 'text' => 'Grid columns 1'],
            ['value' => 'grid-cols-2', 'text' => 'Grid columns 2'],
            ['value' => 'grid-cols-3', 'text' => 'Grid columns 3'],
            ['value' => 'grid-cols-4', 'text' => 'Grid columns 4'],
            ['value' => 'gap-2', 'text' => 'Gap 2'],
            ['value' => 'gap-4', 'text' => 'Gap 4'],
            ['value' => 'gap-6', 'text' => 'Gap 6'],
            
            // Responsive
            ['value' => 'sm:block', 'text' => 'Responsive block'],
            ['value' => 'md:flex', 'text' => 'Responsive flex'],
            ['value' => 'lg:grid-cols-3', 'text' => 'Responsive grid columns 3'],
            ['value' => 'xl:text-xl', 'text' => 'Responsive text xl'],
            
            // States
            ['value' => 'hover:bg-gray-100', 'text' => 'Hover background gray 100'],
            ['value' => 'focus:outline-none', 'text' => 'Focus outline none'],
            ['value' => 'active:bg-gray-200', 'text' => 'Active background gray 200']
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