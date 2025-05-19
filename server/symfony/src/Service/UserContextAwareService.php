<?php

namespace App\Service;

use App\Entity\User;

abstract class UserContextAwareService extends BaseService
{
    protected UserContextService $userContextService;
    protected ?ACLService $aclService;

    public function __construct(
        UserContextService $userContextService,
        ?ACLService $aclService = null
    ) {
        $this->userContextService = $userContextService;
        $this->aclService = $aclService;
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
     * Check if the current user has access to a resource
     */
    protected function hasAccess(int $pageId, string $permission = 'select'): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        if ($this->aclService instanceof ACLService) {
            return $this->aclService->hasAccess($user->getId(), $pageId, $permission);
        }
        
        return false;
    }
}
