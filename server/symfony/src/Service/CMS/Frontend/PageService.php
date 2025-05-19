<?php

namespace App\Service\CMS\Frontend;

use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Service\Core\BaseService;

/**
 * Service for handling page rendering and content delivery
 */
class PageService extends BaseService
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository
    ) {
    }

    /**
     * Render a page for public view
     * 
     * @throws ServiceException If page not found or access denied
     */
    public function renderPage(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy([
            'keyword' => $pageKeyword,
            'isPublished' => true
        ]);

        if (!$page) {
            $this->throwNotFound('Page not found or not published');
        }

        // Get only published sections
        $sections = $this->sectionRepository->findPublishedSectionsByPage($page->getId());
        
        return [
            'page' => [
                'id' => $page->getId(),
                'title' => $page->getTitle(),
                'keyword' => $page->getKeyword(),
                'sections' => $this->buildNestedSections($sections)
            ]
        ];
    }

    /**
     * Build a nested section structure for frontend display
     */
    private function buildNestedSections(array $sections): array
    {
        // Similar to AdminPageService but with frontend-specific transformations
        $sectionsById = [];
        $rootSections = [];
        
        // First pass: index all sections by ID
        foreach ($sections as $section) {
            $section['children'] = [];
            $sectionsById[$section['id']] = $section;
        }
        
        // Second pass: build the hierarchy
        foreach ($sections as $section) {
            $id = $section['id'];
            
            if ($section['level'] === 0) {
                $rootSections[] = &$sectionsById[$id];
            } else {
                $pathParts = explode(',', $section['path']);
                if (count($pathParts) >= 2) {
                    $parentId = (int)$pathParts[count($pathParts) - 2];
                    
                    if (isset($sectionsById[$parentId])) {
                        $sectionsById[$parentId]['children'][] = &$sectionsById[$id];
                    }
                }
            }
        }
        
        return $rootSections;
    }
}
