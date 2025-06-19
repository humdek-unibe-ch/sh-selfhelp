<?php

namespace App\Service\CMS\Frontend;

use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\LookupRepository;
use App\Repository\AclRepository;
use App\Service\Auth\UserContextService;
use App\Service\ACL\ACLService;
use App\Service\Core\LookupService;
use App\Service\Core\UserContextAwareService;
use Symfony\Component\HttpFoundation\Response;

class PageService extends UserContextAwareService
{
    public function __construct(
        private readonly SectionRepository $sectionRepository,
        private readonly LookupRepository $lookupRepository,
        UserContextService $userContextService,
        ?ACLService $aclService = null
    ) {
        parent::__construct($userContextService, $aclService);
    }

    /**
     * Recursively sorts pages by nav_position
     * Pages with null nav_position will be placed at the end and sorted alphabetically by keyword
     */
    private function sortPagesRecursively(array &$pages): void
    {
        usort($pages, function ($a, $b) {
            // If both positions are null, sort alphabetically by keyword
            if ($a['nav_position'] === null && $b['nav_position'] === null) {
                return strcasecmp($a['keyword'] ?? '', $b['keyword'] ?? '');
            }
            
            // If only a's position is null, it should go after b
            if ($a['nav_position'] === null) {
                return 1;
            }
            
            // If only b's position is null, it should go after a
            if ($b['nav_position'] === null) {
                return -1;
            }
            
            // If both have positions, compare them normally
            return $a['nav_position'] <=> $b['nav_position'];
        });

        foreach ($pages as &$page) {
            if (!empty($page['children'])) {
                $this->sortPagesRecursively($page['children']);
            }
        }
    }

    /**
     * Get all published pages for the current user, filtered by mode and ACL
     *
     * @param string $mode Either 'web' or 'mobile'
     * @return array
     */
    public function getAllAccessiblePagesForUser(string $mode): array
    {
        $user = $this->getCurrentUser();
        $userId = 1; // guest user
        if ($user) {
            $userId = $user->getId();
        }

        // Get all pages with ACL for the user using the ACLService (cached)
        if (!$this->aclService) {
            throw new ServiceException('ACLService not available', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $allPages = $this->aclService->getAllUserAcls($userId);

        // Determine which type to remove based on mode
        $removeType = $mode === LookupService::PAGE_ACCESS_TYPES_MOBILE ? LookupService::PAGE_ACCESS_TYPES_WEB : LookupService::PAGE_ACCESS_TYPES_MOBILE;
        $removeTypeId = $this->lookupRepository->getLookupIdByCode(LookupService::PAGE_ACCESS_TYPES, $removeType);
        $sectionsTypeId = $this->lookupRepository->getLookupIdByCode(LookupService::PAGE_ACTIONS, LookupService::PAGE_ACTIONS_SECTIONS);

        // First, filter the pages as you were doing
        $filteredPages = array_values(array_filter($allPages, function ($item) use ($removeTypeId, $sectionsTypeId) {
            return $item['id_pageAccessTypes'] != $removeTypeId &&
                $item['acl_select'] == 1 &&
                $item['id_actions'] == $sectionsTypeId &&
                in_array($item['id_type'], ['2', '3', '4']) &&
                $item['url'] != '';
        }));

        // Create a map of pages by their ID for quick lookup
        $pagesMap = [];
        foreach ($filteredPages as &$page) {
            // Set default protocol if missing
            if (!isset($page['protocol']) || $page['protocol'] === null) {
                // Extract protocol from URL if possible, otherwise default to https
                if (!empty($page['url']) && strpos($page['url'], '://') !== false) {
                    $parts = parse_url($page['url']);
                    $page['protocol'] = $parts['scheme'] ?? 'https';
                } else {
                    $page['protocol'] = 'https';
                }
            }
            
            $page['children'] = []; // Initialize children array
            $pagesMap[$page['id_pages']] = &$page;
        }
        unset($page); // Break the reference

        // Build the hierarchy
        $nestedPages = [];
        foreach ($pagesMap as $id => &$page) {
            if (isset($page['parent']) && $page['parent'] !== null && isset($pagesMap[$page['parent']])) {
                // This is a child page, add it to its parent's children array
                $pagesMap[$page['parent']]['children'][] = &$page;
            } else {
                // This is a root level page
                $nestedPages[] = &$page;
            }
        }
        unset($page); // Break the reference

        // Optional: Sort children by nav_position if needed
        $this->sortPagesRecursively($nestedPages);

        return $nestedPages;
    }

    /**
     * Get page by keyword
     * TODO: Adjust this method
     * 
     * @param string $page_keyword The page keyword
     * @return array The page object
     * @throws ServiceException If page not found or access denied
     */
    public function getPage(string $page_keyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        $this->checkAccess($page_keyword, 'select');

        return [
            'id' => $page->getId(),
            'keyword' => $page->getKeyword(),
            'url' => $page->getUrl(),
            'parent_page_id' => $page->getParentPage()?->getId(),
            'is_headless' => $page->isHeadless(),
            'nav_position' => $page->getNavPosition(),
            'footer_position' => $page->getFooterPosition(),
            'sections' => $this->getPageSections($page->getId())
        ];
    }

    /**
     * Get page sections
     * TODO: Adjust this method
     * 
     * @param int $page_id The page id
     * @return array The page sections in a hierarchical structure
     * @throws ServiceException If page not found or access denied
     */
    public function getPageSections(int $page_id): array
    {
        return $this->sectionRepository->fetchSectionsHierarchicalByPageId($page_id);
    }
}
