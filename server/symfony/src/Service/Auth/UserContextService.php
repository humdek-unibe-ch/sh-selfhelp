<?php

namespace App\Service\Auth;

use App\Service\Cache\Core\CacheService;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

class UserContextService
{
    private ?User $cachedUser = null;
    private bool $userResolved = false;

    public function __construct(private Security $security, private CacheService $cache) {}

    /**
     * Returns the current authenticated User entity or null if not authenticated.
     * Uses request-scoped caching to avoid multiple security context lookups.
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        // Use request-scoped cache to avoid multiple security context lookups
        if (!$this->userResolved) {
            $user = $this->security->getUser();
            $this->cachedUser = $user instanceof User ? $user : null;
            $this->userResolved = true;
        }

        return $this->cachedUser;
    }

    public function getCache(): CacheService
    {
        return $this->cache;
    }
}
