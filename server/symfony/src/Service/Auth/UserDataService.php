<?php

namespace App\Service\Auth;

use App\Entity\Language;
use App\Entity\User;
use App\Service\Cache\Core\CacheService;
use App\Service\CMS\UserPermissionService;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\CmsPreference;
use Psr\Log\LoggerInterface;

/**
 * Service for retrieving comprehensive user data including roles, permissions, and language
 * This replaces the data that was previously embedded in JWT tokens
 */
class UserDataService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPermissionService $userPermissionService,
        private readonly CacheService $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Get comprehensive user data including roles, permissions, and language
     */
    public function getUserData(User $user): array
    {
        $cacheKey = 'user_data_' . $user->getId();

        return $this->cache
            ->withCategory(CacheService::CATEGORY_USERS)
            ->withUser($user->getId())
            ->getItem($cacheKey, function () use ($user) {
                $user = $this->entityManager->getRepository(User::class)->find($user->getId());
                return [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'user_name' => $user->getUserName(),
                    'blocked' => $user->isBlocked(),
                    'language' => $this->getUserLanguageInfo($user),
                    'roles' => $this->getUserRoles($user),
                    'permissions' => $this->getUserPermissions($user),
                    'groups' => $this->getUserGroups($user)
                ];
            });
    }

    /**
     * Get user language information with fallback to CMS preferences
     */
    private function getUserLanguageInfo(User $user): array
    {
        $userLanguageId = null;
        $userLanguageLocale = null;
        $userLanguageName = null;

        if ($user->getLanguage()) {
            // User has a language set
            $userLanguageId = $user->getLanguage()->getId();
            $userLanguageLocale = $user->getLanguage()->getLocale();
            $userLanguageName = $user->getLanguage()->getLanguage();
        } else {
            // User doesn't have language set, use CMS default
            try {
                $cmsPreference = $this->entityManager->getRepository(CmsPreference::class)->findOneBy([]);
                if ($cmsPreference && $cmsPreference->getDefaultLanguage()) {
                    $userLanguageId = $cmsPreference->getDefaultLanguage()->getId();
                    $userLanguageLocale = $cmsPreference->getDefaultLanguage()->getLocale();
                    $userLanguageName = $cmsPreference->getDefaultLanguage()->getLanguage();
                } else {
                    // No CMS default language set, use fallback
                    $userLanguageId = 2;
                    $fallbackLanguage = $this->entityManager->getRepository(Language::class)->find(2);
                    if ($fallbackLanguage) {
                        $userLanguageLocale = $fallbackLanguage->getLocale();
                        $userLanguageName = $fallbackLanguage->getLanguage();
                    }
                }
            } catch (\Exception $e) {
                // If there's an error getting the default language, use fallback
                $userLanguageId = 2;
                $fallbackLanguage = $this->entityManager->getRepository(Language::class)->find(2);
                if ($fallbackLanguage) {
                    $userLanguageLocale = $fallbackLanguage->getLocale();
                    $userLanguageName = $fallbackLanguage->getLanguage();
                }
            }
        }

        return [
            'id' => $userLanguageId,
            'locale' => $userLanguageLocale,
            'name' => $userLanguageName
        ];
    }

    /**
     * Get user roles
     */
    private function getUserRoles(User $user): array
    {
        return array_map(function ($role) {
            return [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription()
            ];
        }, $user->getUserRoles()->toArray());
    }

    /**
     * Get user permissions via the specialized cache service
     */
    private function getUserPermissions(User $user): array
    {
        return $this->userPermissionService->getUserPermissions($user);
    }

    /**
     * Get user groups
     */
    private function getUserGroups(User $user): array
    {
        return array_map(function ($group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'description' => $group->getDescription()
            ];
        }, $user->getGroups()->toArray());
    }
}
