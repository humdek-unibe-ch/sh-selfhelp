<?php

namespace App\Service\CMS\Admin;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Role;
use App\Entity\ValidationCode;
use App\Entity\UsersGroup;
use App\Entity\Gender;
use App\Entity\Language;
use App\Repository\UserRepository;
use App\Service\Core\LookupService;
use App\Service\Core\BaseService;
use App\Service\Core\TransactionService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Service\Auth\UserContextService;
use App\Service\Auth\UserValidationService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling user-related operations in the admin panel
 * ENTITY RULE
 */
class AdminUserService extends BaseService
{
    private const SYSTEM_USERS = ['admin', 'tpf'];
    private const VALID_SORT_FIELDS = ['email', 'name', 'last_login', 'blocked', 'user_type', 'code', 'id'];
    private const MAX_PAGE_SIZE = 100;
    private const DEFAULT_PAGE_SIZE = 20;

    public function __construct(
        private readonly UserContextService $userContextService,
        private readonly UserRepository $userRepository,
        private readonly LookupService $lookupService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TransactionService $transactionService,
        private readonly UserValidationService $userValidationService,
        private readonly ReworkedCacheService $cache,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get users with pagination, search, and sorting
     */
    public function getUsers(
        int $page = 1,
        int $pageSize = 20,
        ?string $search = null,
        ?string $sort = null,
        ?string $sortDirection = 'asc'
    ): array {
        [$page, $pageSize, $sortDirection] = $this->validatePaginationParams($page, $pageSize, $sortDirection);

        $cacheKey = $this->buildCacheKey('users_list', $page, $pageSize, $search, $sort, $sortDirection);

        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->getList(
                $cacheKey,
                fn() => $this->fetchUsersFromDatabase($page, $pageSize, $search, $sort, $sortDirection)
            );
    }

    /**
     * Get single user by ID with full details
     */
    public function getUserById(int $userId): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
            ->getItem(
                "user_{$userId}",
                fn() => $this->formatUserForDetail($this->findUserOrThrow($userId))
            );
    }

    /**
     * Create new user
     * Every user created will automatically require validation unless specified otherwise
     */
    public function createUser(array $userData): array
    {
        return $this->executeInTransaction(function () use ($userData) {
            $this->validateUserData($userData, true);

            $user = $this->buildUserFromData(new User(), $userData);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Handle validation code (legacy)
            if (isset($userData['validation_code'])) {
                $this->handleValidationCode($user, $userData['validation_code']);
            }

            // Handle relationships
            $this->handleUserRelationships($user, $userData);
            $this->entityManager->flush();

            // Setup validation
            $validationResult = $this->setupUserValidation($user, $userData);

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                $user,
                $this->buildCreateLogMessage($user, $validationResult)
            );

            // Get fresh data before invalidating caches
            $result = $this->formatUserForDetail($user, true);

            // Add validation info to response
            if ($validationResult) {
                $result['validation'] = $this->formatValidationResult($validationResult);
            }

            // Invalidate caches
            $this->invalidateUserCaches($user->getId());

            return $result;
        });
    }

    /**
     * Update existing user
     */
    public function updateUser(int $userId, array $userData): array
    {
        return $this->executeInTransaction(function () use ($userId, $userData) {
            $user = $this->findUserOrThrow($userId);
            $this->validateUserData($userData, false, $user);

            $this->updateUserFromData($user, $userData);
            $this->handleUserRelationships($user, $userData);
            $this->entityManager->flush();

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                $user,
                'User updated: ' . $user->getEmail()
            );

            // Get fresh data before invalidating caches
            $result = $this->formatUserForDetail($user, true);

            // Invalidate caches
            $this->invalidateUserCaches($userId);
            
            // Only invalidate related caches if the relationships were actually updated
            if (isset($userData['group_ids']) && is_array($userData['group_ids']) && !empty($userData['group_ids'])) {
                $groupCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS);
                $groupCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $userData['group_ids']);
                $groupCache->invalidateAllListsInCategory();
            }

            if (isset($userData['role_ids']) && is_array($userData['role_ids']) && !empty($userData['role_ids'])) {
                $roleCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_ROLES);
                $roleCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_ROLE, $userData['role_ids']);
                $roleCache->invalidateAllListsInCategory();
            }

            return $result;
        });
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): bool
    {
        return $this->executeInTransaction(function () use ($userId) {
            $user = $this->findUserOrThrow($userId);

            // Prevent deletion of system users
            if (in_array($user->getName(), self::SYSTEM_USERS)) {
                throw new ServiceException('Cannot delete system users', Response::HTTP_FORBIDDEN);
            }

            // Log transaction before deletion
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                $user,
                'User deleted: ' . $user->getEmail()
            );

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            // Invalidate caches
            $this->invalidateUserCaches($userId);

            return true;
        });
    }

    /**
     * Block/Unblock user
     */
    public function toggleUserBlock(int $userId, bool $blocked): array
    {
        return $this->executeInTransaction(function () use ($userId, $blocked) {
            $user = $this->findUserOrThrow($userId);

            $user->setBlocked($blocked);
            $this->entityManager->flush();

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                $user,
                'User ' . ($blocked ? 'blocked' : 'unblocked') . ': ' . $user->getEmail()
            );

            // Get fresh data before invalidating caches
            $result = $this->formatUserForDetail($user, true);

            // Invalidate caches
            $this->invalidateUserCaches($userId);

            return $result;
        });
    }

    /**
     * Get user groups
     */
    public function getUserGroups(int $userId): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
            ->getItem(
                "user_groups_{$userId}",
                fn() => $this->fetchUserGroups($userId)
            );
    }

    /**
     * Get user roles
     */
    public function getUserRoles(int $userId): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
            ->getItem(
                "user_roles_{$userId}",
                fn() => $this->fetchUserRoles($userId)
            );
    }

    /**
     * Add groups to user
     */
    public function addGroupsToUser(int $userId, array $groupIds): array
    {
        return $this->executeInTransaction(function () use ($userId, $groupIds) {
            $user = $this->findUserOrThrow($userId);

            $this->assignGroupsToUser($user, $groupIds, false);
            $this->entityManager->flush();

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                $user,
                'Groups added to user: ' . $user->getEmail() . ' (Group IDs: ' . implode(', ', $groupIds) . ')',
                'users_groups'
            );

            // Get fresh data before invalidating caches
            $result = $this->fetchUserGroupsFromEntity($user);

            // Invalidate caches
            $this->invalidateUserGroupCaches($userId, $groupIds);

            return $result;
        });
    }

    /**
     * Remove groups from user
     */
    public function removeGroupsFromUser(int $userId, array $groupIds): array
    {
        return $this->executeInTransaction(function () use ($userId, $groupIds) {
            $user = $this->findUserOrThrow($userId);

            if (!empty($groupIds)) {
                $this->removeUserGroupRelationships($user, $groupIds);
            }

            $this->entityManager->flush();

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                $user,
                'Groups removed from user: ' . $user->getEmail() . ' (Group IDs: ' . implode(', ', $groupIds) . ')',
                'users_groups'
            );

            // Get fresh data before invalidating caches
            $result = $this->fetchUserGroupsFromEntity($user);

            // Invalidate caches
            $this->invalidateUserGroupCaches($userId, $groupIds);

            return $result;
        });
    }

    /**
     * Add roles to user
     */
    public function addRolesToUser(int $userId, array $roleIds): array
    {
        return $this->executeInTransaction(function () use ($userId, $roleIds) {
            $user = $this->findUserOrThrow($userId);

            $this->assignRolesToUser($user, $roleIds, false);
            $this->entityManager->flush();

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                $user,
                'Roles added to user: ' . $user->getEmail() . ' (Role IDs: ' . implode(', ', $roleIds) . ')'
            );

            // Get fresh data before invalidating caches
            $result = $this->fetchUserRolesFromEntity($user);

            // Invalidate caches
            $this->invalidateUserRoleCaches($userId, $roleIds);

            return $result;
        });
    }

    /**
     * Remove roles from user
     */
    public function removeRolesFromUser(int $userId, array $roleIds): array
    {
        return $this->executeInTransaction(function () use ($userId, $roleIds) {
            $user = $this->findUserOrThrow($userId);

            if (!empty($roleIds)) {
                $this->removeUserRoleRelationships($user, $roleIds);
            }

            $this->entityManager->flush();

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                $user,
                'Roles removed from user: ' . $user->getEmail() . ' (Role IDs: ' . implode(', ', $roleIds) . ')'
            );

            // Get fresh data before invalidating caches
            $result = $this->fetchUserRolesFromEntity($user);

            // Invalidate caches
            $this->invalidateUserRoleCaches($userId, $roleIds);

            return $result;
        });
    }

    /**
     * Send activation mail with new validation URL
     */
    public function sendActivationMail(int $userId): array
    {
        return $this->executeInTransaction(function () use ($userId) {
            $user = $this->findUserOrThrow($userId);

            // Use UserValidationService to resend validation email
            $validationResult = $this->userValidationService->resendValidationEmail($userId);

            if (!$validationResult['success']) {
                throw new ServiceException('Failed to send activation email: ' . $validationResult['error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Log transaction
            $this->logUserTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                $user,
                'Activation email sent: ' . $user->getEmail() . ' (token: ' . $validationResult['token'] . ', job_id: ' . $validationResult['job_id'] . ')'
            );

            // Invalidate user list caches
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_USERS)
                ->invalidateAllListsInCategory();

            return [
                'success' => true,
                'message' => 'Activation email sent successfully',
                'user_id' => $userId,
                'email' => $user->getEmail(),
                'token' => $validationResult['token'],
                'job_id' => $validationResult['job_id'],
                'validation_url' => $validationResult['validation_url']
            ];
        });
    }

    /**
     * Clean user data (placeholder)
     */
    public function cleanUserData(int $userId): bool
    {
        $user = $this->findUserOrThrow($userId);
        // TODO: Implement data cleaning logic
        return true;
    }

    /**
     * Impersonate user (placeholder)
     */
    public function impersonateUser(int $userId): array
    {
        $user = $this->findUserOrThrow($userId);
        // TODO: Implement impersonation logic
        return ['impersonation_token' => 'placeholder_token'];
    }

    // Private helper methods

    /**
     * Execute operation within a database transaction
     */
    private function executeInTransaction(callable $operation): mixed
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $operation();
            $this->entityManager->commit();
            return $result;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Validate and normalize pagination parameters
     */
    private function validatePaginationParams(int $page, int $pageSize, string $sortDirection): array
    {
        $page = max(1, $page);
        $pageSize = max(1, min(self::MAX_PAGE_SIZE, $pageSize)) ?: self::DEFAULT_PAGE_SIZE;
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        return [$page, $pageSize, $sortDirection];
    }

    /**
     * Build cache key from parameters
     */
    private function buildCacheKey(string $prefix, ...$params): string
    {
        $hashableParams = array_slice($params, 2); // Skip page and pageSize for hash
        return $prefix . '_' . $params[0] . '_' . $params[1] . '_' . md5(implode('_', $hashableParams));
    }

    /**
     * Find user by ID or throw exception
     */
    private function findUserOrThrow(int $userId): User
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }
        return $user;
    }

    /**
     * Fetch users from database with pagination and filtering
     */
    private function fetchUsersFromDatabase(int $page, int $pageSize, ?string $search, ?string $sort, string $sortDirection): array
    {
        $qb = $this->createUserQueryBuilder();

        // Apply search filter
        if ($search) {
            $qb->andWhere('(u.email LIKE :search OR u.name LIKE :search OR u.user_name LIKE :search OR u.id LIKE :search OR vc.code LIKE :search OR ur.name LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply sorting
        $this->applySorting($qb, $sort, $sortDirection);

        // Get total count
        $totalCount = $this->getTotalUserCount($search);

        // Apply pagination
        $offset = ($page - 1) * $pageSize;
        $qb->setFirstResult($offset)->setMaxResults($pageSize);

        $users = $qb->getQuery()->getResult();

        return [
            'users' => array_map([$this, 'formatUserForList'], $users),
            'pagination' => $this->buildPaginationInfo($page, $pageSize, $totalCount)
        ];
    }

    /**
     * Create optimized query builder for users
     */
    private function createUserQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->leftJoin('u.usersGroups', 'ug')
            ->leftJoin('u.roles', 'ur')
            ->leftJoin('u.userType', 'ut')
            ->leftJoin('ug.group', 'g')
            ->leftJoin('u.userActivities', 'ua')
            ->leftJoin('u.validationCodes', 'vc')
            ->leftJoin('u.status', 'us')
            ->where('u.intern = :intern AND u.id_status > 0')
            ->setParameter('intern', false)
            ->addSelect('ut', 'ug', 'g', 'ua', 'vc', 'ur', 'us');
    }

    /**
     * Apply sorting to query builder
     */
    private function applySorting(QueryBuilder $qb, ?string $sort, string $sortDirection): void
    {
        if ($sort && in_array($sort, self::VALID_SORT_FIELDS)) {
            switch ($sort) {
                case 'user_type':
                    $qb->orderBy('ut.lookupValue', $sortDirection);
                    break;
                case 'last_login':
                    $qb->orderBy('u.last_login', $sortDirection);
                    break;
                default:
                    $qb->orderBy('u.' . $sort, $sortDirection);
                    break;
            }
        } else {
            $qb->orderBy('u.email', 'asc');
        }
    }

    /**
     * Get total user count for pagination
     */
    private function getTotalUserCount(?string $search): int
    {
        $countQb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(DISTINCT u.id)')
            ->from(User::class, 'u')
            ->leftJoin('u.validationCodes', 'vc')
            ->where('u.intern = :intern AND u.id_status > 0')
            ->setParameter('intern', false);

        if ($search) {
            $countQb->andWhere('(u.email LIKE :search OR u.name LIKE :search OR u.user_name LIKE :search OR u.id LIKE :search OR vc.code LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $countQb->getQuery()->getSingleScalarResult();
    }

    /**
     * Build pagination information
     */
    private function buildPaginationInfo(int $page, int $pageSize, int $totalCount): array
    {
        $totalPages = (int) ceil($totalCount / $pageSize);

        return [
            'page' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrevious' => $page > 1
        ];
    }

    /**
     * Build user entity from data
     */
    private function buildUserFromData(User $user, array $userData): User
    {
        $user->setEmail($userData['email']);
        $user->setName($userData['name'] ?? null);
        $user->setUserName($userData['user_name'] ?? null);

        if (isset($userData['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
        }

        if (isset($userData['blocked'])) {
            $user->setBlocked($userData['blocked']);
        }

        $this->setUserRelatedEntities($user, $userData);

        return $user;
    }

    /**
     * Update user entity from data
     */
    private function updateUserFromData(User $user, array $userData): void
    {
        if (isset($userData['email'])) {
            $user->setEmail($userData['email']);
        }
        if (isset($userData['name'])) {
            $user->setName($userData['name']);
        }
        if (isset($userData['user_name'])) {
            $user->setUserName($userData['user_name']);
        }
        if (isset($userData['password']) && !empty($userData['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
        }
        if (isset($userData['blocked'])) {
            $user->setBlocked($userData['blocked']);
        }

        $this->setUserRelatedEntities($user, $userData);
    }

    /**
     * Set user related entities (gender, language, user type)
     */
    private function setUserRelatedEntities(User $user, array $userData): void
    {
        if (isset($userData['id_genders'])) {
            $gender = $this->entityManager->getRepository(Gender::class)->find($userData['id_genders']);
            $user->setGender($gender);
        }

        if (isset($userData['id_languages'])) {
            $language = $this->entityManager->getRepository(Language::class)->find($userData['id_languages']);
            $user->setLanguage($language);
        }

        if (isset($userData['user_type_id'])) {
            $userType = $this->lookupService->findById($userData['user_type_id']);
            if (!$userType || $userType->getTypeCode() !== LookupService::USER_TYPES) {
                throw new ServiceException('Invalid user type', Response::HTTP_BAD_REQUEST);
            }
            $user->setUserType($userType);
        } elseif (!$user->getUserType()) {
            // Set default user type for new users
            $defaultUserType = $this->lookupService->getDefaultUserType();
            if ($defaultUserType) {
                $user->setUserType($defaultUserType);
            }
        }
    }

    /**
     * Handle user relationships (groups and roles)
     */
    private function handleUserRelationships(User $user, array $userData): void
    {
        if (isset($userData['group_ids']) && is_array($userData['group_ids'])) {
            $this->syncUserGroups($user, $userData['group_ids']);
        }

        if (isset($userData['role_ids']) && is_array($userData['role_ids'])) {
            $this->syncUserRoles($user, $userData['role_ids']);
        }
    }

    /**
     * Setup user validation if enabled
     */
    private function setupUserValidation(User $user, array $userData): ?array
    {
        $enableValidation = $userData['enable_validation'] ?? true;

        if (!$enableValidation) {
            return null;
        }

        $validationResult = $this->userValidationService->setupUserValidation(
            $user,
            $userData['email_config'] ?? []
        );

        if (!$validationResult['success']) {
            throw new ServiceException('Failed to setup user validation: ' . $validationResult['error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $validationResult;
    }

    /**
     * Build log message for user creation
     */
    private function buildCreateLogMessage(User $user, ?array $validationResult): string
    {
        $logMessage = 'User created: ' . $user->getEmail();

        if ($validationResult && $validationResult['success']) {
            $logMessage .= ' (with validation - token: ' . $validationResult['token'] . ', job_id: ' . $validationResult['job_id'] . ')';
        } elseif ($validationResult) {
            $logMessage .= ' (validation setup failed)';
        }

        return $logMessage;
    }

    /**
     * Format validation result for response
     */
    private function formatValidationResult(array $validationResult): array
    {
        return [
            'token' => $validationResult['token'],
            'job_id' => $validationResult['job_id'],
            'validation_url' => $validationResult['validation_url'],
            'message' => $validationResult['message']
        ];
    }

    /**
     * Fetch user groups
     */
    private function fetchUserGroups(int $userId): array
    {
        $user = $this->findUserOrThrow($userId);

        return array_map(function (Group $group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'description' => $group->getDescription()
            ];
        }, $user->getGroups()->toArray());
    }

    /**
     * Fetch user roles
     */
    private function fetchUserRoles(int $userId): array
    {
        $user = $this->findUserOrThrow($userId);

        return array_map(function (Role $role) {
            return [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription()
            ];
        }, $user->getUserRoles()->toArray());
    }

    /**
     * Fetch user groups directly from entity (bypasses cache)
     */
    private function fetchUserGroupsFromEntity(User $user): array
    {
        return array_map(function (Group $group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'description' => $group->getDescription()
            ];
        }, $user->getGroups()->toArray());
    }

    /**
     * Fetch user roles directly from entity (bypasses cache)
     */
    private function fetchUserRolesFromEntity(User $user): array
    {
        return array_map(function (Role $role) {
            return [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription()
            ];
        }, $user->getUserRoles()->toArray());
    }

    /**
     * Format user for list view
     */
    private function formatUserForList(User $user): array
    {
        $lastLogin = $user->getLastLogin();
        $lastLoginFormatted = 'never';
        if ($lastLogin) {
            $daysDiff = (new \DateTime())->diff($lastLogin)->days;
            $lastLoginFormatted = $lastLogin->format('Y-m-d') . ' (' . $daysDiff . ' days ago)';
        }

        $groups = array_map(fn(Group $g) => $g->getName(), $user->getGroups()->toArray());
        $roles = array_map(fn(Role $r) => $r->getName(), $user->getUserRoles()->toArray());

        // Get validation code
        $validationCode = $this->getValidationCode($user);

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'last_login' => $lastLoginFormatted,
            'status' => $user->getStatus()?->getLookupValue(),
            'blocked' => $user->isBlocked(),
            'code' => $validationCode,
            'groups' => implode('; ', $groups),
            'user_activity' => $user->getUserActivities()->count(),
            'user_type_code' => $user->getUserType()?->getLookupCode(),
            'user_type' => $user->getUserType()?->getLookupValue(),
            'roles' => implode('; ', $roles)
        ];
    }

    /**
     * Format user for detail view
     */
    private function formatUserForDetail(User $user, bool $fresh = false): array
    {
        $basic = $this->formatUserForList($user);

        return array_merge($basic, [
            'user_name' => $user->getUserName(),
            'id_genders' => $user->getGender()?->getId(),
            'id_languages' => $user->getLanguage()?->getId(),
            'id_userTypes' => $user->getUserType()?->getId(),
            'groups' => $fresh ? $this->fetchUserGroupsFromEntity($user) : $this->getUserGroups($user->getId()),
            'roles' => $fresh ? $this->fetchUserRolesFromEntity($user) : $this->getUserRoles($user->getId())
        ]);
    }

    /**
     * Get validation code for user
     */
    private function getValidationCode(User $user): string
    {
        if (in_array($user->getName(), self::SYSTEM_USERS)) {
            return $user->getName();
        }

        $activeCode = $user->getValidationCodes()->filter(fn($vc) => $vc->getConsumed() === null)->first();
        return $activeCode ? $activeCode->getCode() : '-';
    }

    /**
     * Validate user data for create/update operations
     */
    private function validateUserData(array $data, bool $isCreate, ?User $existingUser = null): void
    {
        if ($isCreate && empty($data['email'])) {
            throw new ServiceException('Email is required', Response::HTTP_BAD_REQUEST);
        }

        $this->validateUniqueEmail($data, $existingUser);
        $this->validateUniqueUserName($data, $existingUser);
    }

    /**
     * Validate email uniqueness
     */
    private function validateUniqueEmail(array $data, ?User $existingUser): void
    {
        if (!isset($data['email'])) {
            return;
        }

        $existingUserWithEmail = $this->userRepository->findOneByEmail($data['email']);
        if ($existingUserWithEmail && (!$existingUser || $existingUserWithEmail->getId() !== $existingUser->getId())) {
            throw new ServiceException('Email already exists', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate username uniqueness
     */
    private function validateUniqueUserName(array $data, ?User $existingUser): void
    {
        if (!isset($data['user_name']) || empty($data['user_name'])) {
            return;
        }

        $existingUserWithUserName = $this->userRepository->findOneBy(['user_name' => $data['user_name']]);
        if ($existingUserWithUserName && (!$existingUser || $existingUserWithUserName->getId() !== $existingUser->getId())) {
            throw new ServiceException('Username already exists', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Handle validation code (legacy)
     */
    private function handleValidationCode(User $user, string $code): void
    {
        $existingCode = $this->entityManager->getRepository(ValidationCode::class)->find($code);

        if ($existingCode) {
            if ($existingCode->getConsumed()) {
                throw new ServiceException('Validation code already used', Response::HTTP_BAD_REQUEST);
            }
            $existingCode->setUser($user);
            $existingCode->setConsumed(new \DateTime());
        } else {
            $validationCode = new ValidationCode();
            $validationCode->setCode($code);
            $validationCode->setUser($user);
            $validationCode->setCreated(new \DateTime());
            $validationCode->setConsumed(new \DateTime());
            $this->entityManager->persist($validationCode);
        }
    }

    /**
     * Synchronize user groups - handles both creation and updates intelligently
     */
    private function syncUserGroups(User $user, array $groupIds): void
    {
        if (empty($groupIds)) {
            // If no groups provided, remove all existing groups
            $this->removeAllUserGroups($user);
            return;
        }

        // Get current group IDs
        $currentGroupIds = array_map(fn($ug) => $ug->getGroup()->getId(), $user->getUsersGroups()->toArray());
        
        // Determine what needs to be added and removed
        $groupIdsToAdd = array_diff($groupIds, $currentGroupIds);
        $groupIdsToRemove = array_diff($currentGroupIds, $groupIds);

        // Remove groups that are no longer needed
        if (!empty($groupIdsToRemove)) {
            $this->removeUserGroupsByIds($user, $groupIdsToRemove);
        }

        // Add new groups
        if (!empty($groupIdsToAdd)) {
            $this->addUserGroupsByIds($user, $groupIdsToAdd);
        }
    }

    /**
     * Assign groups to user with optimized batch operations
     */
    private function assignGroupsToUser(User $user, array $groupIds, bool $replace = true): void
    {
        if ($replace) {
            $this->removeAllUserGroups($user);
        }

        if (empty($groupIds)) {
            return;
        }

        $groups = $this->batchLoadGroups($groupIds);
        $existingUserGroups = $replace ? [] : $this->getExistingUserGroups($user, $groupIds);

        foreach ($groupIds as $groupId) {
            if (isset($groups[$groupId]) && !isset($existingUserGroups[$groupId])) {
                $userGroup = new UsersGroup();
                $userGroup->setUser($user);
                $userGroup->setGroup($groups[$groupId]);
                $this->entityManager->persist($userGroup);
            }
        }
    }

    /**
     * Remove all user groups
     */
    private function removeAllUserGroups(User $user): void
    {
        foreach ($user->getUsersGroups() as $userGroup) {
            $this->entityManager->remove($userGroup);
        }
        $user->getUsersGroups()->clear();
    }

    /**
     * Batch load groups to avoid N+1 queries
     */
    private function batchLoadGroups(array $groupIds): array
    {
        $groups = $this->entityManager->getRepository(Group::class)
            ->createQueryBuilder('g')
            ->where('g.id IN (:groupIds)')
            ->setParameter('groupIds', $groupIds)
            ->getQuery()
            ->getResult();

        $groupMap = [];
        foreach ($groups as $group) {
            $groupMap[$group->getId()] = $group;
        }

        return $groupMap;
    }

    /**
     * Get existing user groups to avoid duplicates
     */
    private function getExistingUserGroups(User $user, array $groupIds): array
    {
        $existingUserGroupEntities = $this->entityManager->getRepository(UsersGroup::class)
            ->createQueryBuilder('ug')
            ->where('ug.user = :user')
            ->andWhere('ug.group IN (:groupIds)')
            ->setParameter('user', $user)
            ->setParameter('groupIds', $groupIds)
            ->getQuery()
            ->getResult();

        $existingUserGroups = [];
        foreach ($existingUserGroupEntities as $existingUg) {
            $existingUserGroups[$existingUg->getGroup()->getId()] = true;
        }

        return $existingUserGroups;
    }

    /**
     * Add user groups by IDs
     */
    private function addUserGroupsByIds(User $user, array $groupIds): void
    {
        $groups = $this->batchLoadGroups($groupIds);

        foreach ($groupIds as $groupId) {
            if (isset($groups[$groupId])) {
                $userGroup = new UsersGroup();
                $userGroup->setUser($user);
                $userGroup->setGroup($groups[$groupId]);
                $this->entityManager->persist($userGroup);
            }
        }
    }

    /**
     * Remove user groups by IDs
     */
    private function removeUserGroupsByIds(User $user, array $groupIds): void
    {
        $userGroups = $this->entityManager->getRepository(UsersGroup::class)
            ->createQueryBuilder('ug')
            ->where('ug.user = :user')
            ->andWhere('ug.group IN (:groupIds)')
            ->setParameter('user', $user)
            ->setParameter('groupIds', $groupIds)
            ->getQuery()
            ->getResult();

        foreach ($userGroups as $userGroup) {
            $this->entityManager->remove($userGroup);
        }
    }

    /**
     * Remove user group relationships
     */
    private function removeUserGroupRelationships(User $user, array $groupIds): void
    {
        $this->removeUserGroupsByIds($user, $groupIds);
    }

    /**
     * Synchronize user roles - handles both creation and updates intelligently
     */
    private function syncUserRoles(User $user, array $roleIds): void
    {
        if (empty($roleIds)) {
            // If no roles provided, remove all existing roles
            foreach ($user->getUserRoles() as $role) {
                $user->removeRole($role);
            }
            return;
        }

        // Get current role IDs
        $currentRoleIds = array_map(fn($role) => $role->getId(), $user->getUserRoles()->toArray());
        
        // Determine what needs to be added and removed
        $roleIdsToAdd = array_diff($roleIds, $currentRoleIds);
        $roleIdsToRemove = array_diff($currentRoleIds, $roleIds);

        // Remove roles that are no longer needed
        if (!empty($roleIdsToRemove)) {
            $rolesToRemove = $this->batchLoadRoles($roleIdsToRemove);
            foreach ($rolesToRemove as $role) {
                $user->removeRole($role);
            }
        }

        // Add new roles
        if (!empty($roleIdsToAdd)) {
            $rolesToAdd = $this->batchLoadRoles($roleIdsToAdd);
            foreach ($rolesToAdd as $role) {
                $user->addRole($role);
            }
        }
    }

    /**
     * Assign roles to user with optimized batch operations
     */
    private function assignRolesToUser(User $user, array $roleIds, bool $replace = true): void
    {
        if ($replace) {
            foreach ($user->getUserRoles() as $role) {
                $user->removeRole($role);
            }
        }

        if (empty($roleIds)) {
            return;
        }

        $roles = $this->batchLoadRoles($roleIds);
        $existingRoleIds = $replace ? [] : $this->getExistingRoleIds($user);

        foreach ($roles as $role) {
            if (!isset($existingRoleIds[$role->getId()])) {
                $user->addRole($role);
            }
        }
    }

    /**
     * Batch load roles to avoid N+1 queries
     */
    private function batchLoadRoles(array $roleIds): array
    {
        return $this->entityManager->getRepository(Role::class)
            ->createQueryBuilder('r')
            ->where('r.id IN (:roleIds)')
            ->setParameter('roleIds', $roleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get existing role IDs for user
     */
    private function getExistingRoleIds(User $user): array
    {
        $existingRoleIds = [];
        foreach ($user->getUserRoles() as $role) {
            $existingRoleIds[$role->getId()] = true;
        }
        return $existingRoleIds;
    }

    /**
     * Remove user role relationships
     */
    private function removeUserRoleRelationships(User $user, array $roleIds): void
    {
        $roles = $this->batchLoadRoles($roleIds);

        foreach ($roles as $role) {
            $user->removeRole($role);
        }
    }

    /**
     * Log user transaction
     */
    private function logUserTransaction(string $transactionType, User $user, string $message, string $table = 'users'): void
    {
        $this->transactionService->logTransaction(
            $transactionType,
            LookupService::TRANSACTION_BY_BY_USER,
            $table,
            $user->getId(),
            $table === 'users' ? $user : false,
            $message
        );
    }

    /**
     * Invalidate user-related caches
     */
    private function invalidateUserCaches(int $userId): void
    {
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->invalidateAllListsInCategory();

        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
            ->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
    }

    /**
     * Invalidate user and group caches
     */
    private function invalidateUserGroupCaches(int $userId, array $groupIds): void
    {
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
            ->invalidateItemAndLists("user_groups_{$userId}");

        if (!empty($groupIds)) {
            $groupCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS);
            $groupCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $groupIds);
            $groupCache->invalidateAllListsInCategory();
        }
    }

    /**
     * Invalidate user and role caches
     */
    private function invalidateUserRoleCaches(int $userId, array $roleIds): void
    {
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_USERS)
            ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
            ->invalidateItemAndLists("user_roles_{$userId}");

        if (!empty($roleIds)) {
            $roleCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_ROLES);
            $roleCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_ROLE, $roleIds);
            $roleCache->invalidateAllListsInCategory();
        }
    }


}