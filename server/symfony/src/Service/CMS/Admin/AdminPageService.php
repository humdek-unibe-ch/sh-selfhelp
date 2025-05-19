<?php

namespace App\Service\CMS\Admin;

use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Service\UserContextAwareService;
use App\Service\UserContextService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ACLService;

/**
 * Service for handling page-related operations in the admin panel
 */
class AdminPageService extends UserContextAwareService
{
    /************************* START ADMIN PAGES *************************/
    /**
     * CMS select page keyword
     */
    private const CMS_SELECT_PAGE_KEYWORD = 'cmsSelect';

    /************************* END ADMIN PAGES *************************/
    
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        ACLService $aclService,
        UserContextService $userContextService
    ) {
        parent::__construct($userContextService, $aclService);
    }

    /**
     * Get page fields
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page fields
     * @throws ServiceException If page not found or access denied
     */
    public function getPageFields(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Check if user has access to the page
        if (!$this->hasAccess($page->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }                
        
        // Return raw data - no wrapping in API response structure
        return [
            'fields' => [], // Future implementation will populate this
            'page_id' => $page->getId(),
            'page_keyword' => $page->getKeyword()
        ];
    }
    
    /**
     * Get page sections
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page sections in a hierarchical structure
     * @throws AccessDeniedException If user doesn't have access to the page
     * @throws \Exception If page not found
     */
    public function getPageSections(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        if (!$this->hasAccess($page->getId(), 'select') || !$this->hasAccess($this->pageRepository->findOneBy(['keyword' => self::CMS_SELECT_PAGE_KEYWORD])->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

        // Call stored procedure for hierarchical sections
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        return $this->buildNestedSections($flatSections);
    }

    /** Private methods */
    /**
     * @brief Transforms a flat array of sections into a nested hierarchical structure
     * 
     * @param array $sections Flat array of section objects with level and path properties
     * @return array Nested array with children properly nested under their parents
     */
    private function buildNestedSections(array $sections): array
    {
        // Create a map of sections by ID for quick lookup
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
            
            // If it's a root section (level 0), add to root array
            if ($section['level'] === 0) {
                $rootSections[] = &$sectionsById[$id];
            } else {
                // Find parent using the path
                $pathParts = explode(',', $section['path']);
                if (count($pathParts) >= 2) {
                    $parentId = (int)$pathParts[count($pathParts) - 2];
                    
                    // If parent exists, add this as its child
                    if (isset($sectionsById[$parentId])) {
                        $sectionsById[$parentId]['children'][] = &$sectionsById[$id];
                    }
                }
            }
        }
        
        return $rootSections;
    }
}