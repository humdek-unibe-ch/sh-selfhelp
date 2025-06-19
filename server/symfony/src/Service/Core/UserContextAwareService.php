<?php

namespace App\Service\Core;

use App\Entity\User;
use App\Repository\PageRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;

abstract class UserContextAwareService extends BaseService
{
    protected UserContextService $userContextService;
    protected ?ACLService $aclService;
    protected ?PageRepository $pageRepository;

    public function __construct(UserContextService $userContextService, ?ACLService $aclService = null, ?PageRepository $pageRepository = null)
    {
        $this->userContextService = $userContextService;
        $this->aclService = $aclService;
        $this->pageRepository = $pageRepository;
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
     */
    protected function checkAccess(string $page_keyword, string $permission = 'select'): void
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
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
