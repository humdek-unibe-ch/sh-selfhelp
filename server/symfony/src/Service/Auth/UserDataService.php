<?php

namespace App\Service\Auth;

use App\Entity\Language;
use App\Entity\User;
use App\Service\Cache\Core\CacheService;
use App\Service\CMS\UserPermissionService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

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
        private readonly TransactionService $transactionService,
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
            ->withEntityScope(CacheService::ENTITY_SCOPE_USER, $user->getId())
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
    public function getUserLanguageInfo(User $user): array
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

    /**
     * Set user language preference
     *
     * @param User $user
     * @param int $languageId
     * @return array Response data containing language information
     * @throws \InvalidArgumentException When language is not found
     */
    public function setUserLanguage(User $user, int $languageId): array
    {
        $this->entityManager->beginTransaction();

        try {
            // Ensure we have a managed entity by fetching fresh from database
            $user = $this->entityManager->find(User::class, $user->getId());
            if (!$user) {
                throw new \InvalidArgumentException('User not found');
            }

            // Validate that the language exists
            $language = $this->entityManager->getRepository(Language::class)->find($languageId);
            if (!$language) {
                throw new \InvalidArgumentException('Invalid language ID');
            }

            // Update user's language
            $user->setLanguage($language);
            $this->entityManager->flush();

            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'user',
                $user->getId(),
                $user,
                'User language updated to: ' . $language->getLanguage() . ' (' . $language->getLocale() . ')'
            );

            $this->entityManager->commit();

            $this->cache                
                ->invalidateEntityScope(CacheService::ENTITY_SCOPE_USER, $user->getId());

            $this->cache
                ->withCategory(CacheService::CATEGORY_USERS)
                ->invalidateAllListsInCategory();

            return [
                'message' => 'User language updated successfully',
                'language_id' => $languageId,
                'language_locale' => $language->getLocale(),
                'language_name' => $language->getLanguage(),
            ];
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

}
