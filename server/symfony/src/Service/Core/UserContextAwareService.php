<?php

namespace App\Service\Core;

use App\Entity\User;
use App\Repository\PageRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\ReworkedCacheService;

class UserContextAwareService extends BaseService
{
    public function __construct(
        private readonly UserContextService $userContextService,
        private readonly ACLService $aclService,
        private readonly PageRepository $pageRepository,
    ) {}

    /**
     * Get the current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        return $this->userContextService->getCurrentUser();
    }

    /**
     * Check if a user is logged in
     */
    public function isUserLoggedIn(): bool
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
    public function checkAccess(string $page_keyword, string $permission = 'select'): void
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

}
