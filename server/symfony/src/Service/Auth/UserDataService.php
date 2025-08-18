<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Service\Cache\Core\CacheService;
use App\Service\Cache\Specialized\UserPermissionCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for retrieving comprehensive user data including roles, permissions, and language
 * This replaces the data that was previously embedded in JWT tokens
 */
class UserDataService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPermissionCacheService $userPermissionCacheService,
        private readonly CacheService $cacheService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Get comprehensive user data including roles, permissions, and language
     */
    public function getUserData(User $user): array
    {
        $cacheKey = 'user_data_' . $user->getId();
        
        // Try to get from cache first
        $cachedData = $this->cacheService->get(CacheService::CATEGORY_USERS, $cacheKey, $user->getId());
        
        if ($cachedData !== null) {
            $this->logger->debug('User data cache hit for user {userId}', [
                'userId' => $user->getId()
            ]);
            return $cachedData;
        }

        $this->logger->debug('User data cache miss for user {userId}', [
            'userId' => $user->getId()
        ]);

        // Build comprehensive user data
        $userData = [
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

        // Cache the result
        $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_USERS);
        $this->cacheService->set(CacheService::CATEGORY_USERS, $cacheKey, $userData, $ttl, $user->getId());

        return $userData;
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
                $cmsPreference = $this->entityManager->getRepository('App\Entity\CmsPreference')->findOneBy([]);
                if ($cmsPreference && $cmsPreference->getDefaultLanguage()) {
                    $userLanguageId = $cmsPreference->getDefaultLanguage()->getId();
                    $userLanguageLocale = $cmsPreference->getDefaultLanguage()->getLocale();
                    $userLanguageName = $cmsPreference->getDefaultLanguage()->getLanguage();
                } else {
                    // No CMS default language set, use fallback
                    $userLanguageId = 2;
                    $fallbackLanguage = $this->entityManager->getRepository('App\Entity\Language')->find(2);
                    if ($fallbackLanguage) {
                        $userLanguageLocale = $fallbackLanguage->getLocale();
                        $userLanguageName = $fallbackLanguage->getLanguage();
                    }
                }
            } catch (\Exception $e) {
                // If there's an error getting the default language, use fallback
                $userLanguageId = 2;
                $fallbackLanguage = $this->entityManager->getRepository('App\Entity\Language')->find(2);
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
        return array_map(function($role) {
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
        return $this->userPermissionCacheService->getUserPermissions($user);
    }

    /**
     * Get user groups
     */
    private function getUserGroups(User $user): array
    {
        return array_map(function($group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'description' => $group->getDescription()
            ];
        }, $user->getGroups()->toArray());
    }

    /**
     * Clear user data cache
     */
    public function clearUserDataCache(User $user): void
    {
        $cacheKey = 'user_data_' . $user->getId();
        $this->cacheService->delete(CacheService::CATEGORY_USERS, $cacheKey);
        
        $this->logger->debug('Cleared user data cache for user {userId}', [
            'userId' => $user->getId()
        ]);
    }
}
