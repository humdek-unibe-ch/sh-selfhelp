<?php

namespace App\Service\Core;

use App\Entity\User;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\ReworkedCacheService;

abstract class UserContextAwareService extends BaseService
{
    protected UserContextService $userContextService;
    protected ?ACLService $aclService;
    protected ?PageRepository $pageRepository;
    protected ?SectionRepository $sectionRepository;

    public function __construct(        
        UserContextService $userContextService,
        ?ACLService $aclService = null,
        ?PageRepository $pageRepository = null,
        ?SectionRepository $sectionRepository = null        
    ) {
        $this->userContextService = $userContextService;
        $this->aclService = $aclService;
        $this->pageRepository = $pageRepository;
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * Get the current authenticated user
     */
    protected function getCurrentUser(): ?User
    {
        return $this->userContextService->getCurrentUser();
    }

    /**
     * Check if a user is logged in
     */
    protected function isUserLoggedIn(): bool
    {
        return $this->userContextService->getCurrentUser() !== null;
    }

    /**
     * Check if the current user has access to page
     * 
     * @param string $page_keyword The page keyword
     * @param string $permission The permission to check
     * @throws ServiceException If the page is not found or access denied
     */
    protected function checkAccess(string $page_keyword, string $permission = 'select'): void
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        $page = $this->userContextService->getCache()
            ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
            ->getItem("page_{$page_keyword}", function () use ($page_keyword) {
                $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
                if (!$page) {
                    $this->throwNotFound('Page not found');
                }

                return $page;
            });
        $user = $this->getCurrentUser();
        $userId = 1; // guest user
        if ($user) {
            $userId = $user->getId();
        }

        if ($this->aclService instanceof ACLService) {
            if (!$this->aclService->hasAccess($userId, $page->getId(), $permission)) {
                $this->throwForbidden('Access denied');
            }
        }
    }

    /**
     * Check if the section is in the page
     * 
     * IMportant check for api calls in order to manipulate sections. 
     * 
     * @param string $page_keyword The page keyword
     * @param string $section_id The section ID
     * @throws ServiceException If the section is not found or access denied
     */
    protected function checkSectionInPage(string $page_keyword, string $section_id): void
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        // Fetch all sections (flat) for this page
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        // Extract all section IDs
        $sectionIds = array_map(function ($section) {
            return is_array($section) && isset($section['id']) ? (string) $section['id'] : null;
        }, $flatSections);
        if (!in_array((string) $section_id, $sectionIds, true)) {
            $this->throwForbidden('Access denied: Section does not belong to page');
        }
    }

}
