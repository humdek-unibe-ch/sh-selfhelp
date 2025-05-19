<?php

namespace App\Service;

use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Page service
 * 
 * Handles page-related operations
 */
class PageService extends UserContextAwareService
{
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
        if (!$this->hasAccess($page->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

        // Call stored procedure for hierarchical sections
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        return $this->buildNestedSections($flatSections);
    }

    /**
     * Build a nested section structure from a flat list.
     *
     * @param array $sections
     * @return array
     */
    private function buildNestedSections(array $sections): array
    {
        $tree = [];
        $refs = [];
        foreach ($sections as $section) {
            $section['children'] = [];
            $refs[$section['id']] = $section;
        }
        foreach ($refs as $id => &$section) {
            if (isset($section['parent_id']) && $section['parent_id'] && isset($refs[$section['parent_id']])) {
                $refs[$section['parent_id']]['children'][] = &$section;
            } else {
                $tree[] = &$section;
            }
        }
        unset($section);
        return $tree;
    }
}