<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

class UserContextService
{
    public function __construct(private Security $security) {}

    /**
     * Returns the current authenticated User entity or null if not authenticated.
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }
}
