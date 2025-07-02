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
use App\Service\Core\UserContextAwareService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use App\Service\Auth\UserValidationService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminUserService extends UserContextAwareService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManagerInterface,
        private readonly UserRepository $userRepository,
        private readonly LookupService $lookupService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TransactionService $transactionService,
        private readonly UserValidationService $userValidationService
    ) {
        parent::__construct($userContextService);
        $this->entityManager = $entityManagerInterface;
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
        if ($page < 1) $page = 1;
        if ($pageSize < 1 || $pageSize > 100) $pageSize = 20;
        if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'asc';

        $qb = $this->createUserQueryBuilder();
        
        // Apply search filter
        if ($search) {
            $qb->andWhere('(u.email LIKE :search OR u.name LIKE :search OR u.user_name LIKE :search OR u.id LIKE :search OR vc.code LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply sorting
        $validSortFields = ['email', 'name', 'last_login', 'blocked', 'user_type', 'code','id'];
        if ($sort && in_array($sort, $validSortFields)) {
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

        // Get total count - create a separate simpler query for counting
        $countQb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(DISTINCT u.id)')
            ->from(User::class, 'u')
            ->leftJoin('u.validationCodes', 'vc')
            ->where('u.intern = :intern AND u.id_status > 0')
            ->setParameter('intern', false);
        
        // Apply the same search filter to count query
        if ($search) {
            $countQb->andWhere('(u.email LIKE :search OR u.name LIKE :search OR u.user_name LIKE :search OR u.id LIKE :search OR vc.code LIKE :search)')
                   ->setParameter('search', '%' . $search . '%');
        }
        
        $totalCount = $countQb->getQuery()->getSingleScalarResult();

        // Apply pagination
        $offset = ($page - 1) * $pageSize;
        $qb->setFirstResult($offset)->setMaxResults($pageSize);

        $users = $qb->getQuery()->getResult();

        return [
            'users' => array_map([$this, 'formatUserForList'], $users),
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'totalCount' => (int)$totalCount,
                'totalPages' => (int)ceil($totalCount / $pageSize),
                'hasNext' => $page < ceil($totalCount / $pageSize),
                'hasPrevious' => $page > 1
            ]
        ];
    }

    /**
     * Get single user by ID with full details
     */
    public function getUserById(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }

        return $this->formatUserForDetail($user);
    }

    /**
     * Create new user
     * Every user created will automatically require validation unless specified otherwise
     */
    public function createUser(array $userData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $this->validateUserData($userData, true);

            $user = new User();
            $user->setEmail($userData['email']);
            $user->setName($userData['name'] ?? null);
            $user->setUserName($userData['user_name'] ?? null);
            
            if (isset($userData['password'])) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
                $user->setPassword($hashedPassword);
            }

            // Set user type
            if (isset($userData['user_type_id'])) {
                $userType = $this->lookupService->findById($userData['user_type_id']);
                if (!$userType || $userType->getTypeCode() !== LookupService::USER_TYPES) {
                    throw new ServiceException('Invalid user type', Response::HTTP_BAD_REQUEST);
                }
                $user->setUserType($userType);
            } else {
                // Set default user type
                $defaultUserType = $this->lookupService->getDefaultUserType();
                if ($defaultUserType) {
                    $user->setUserType($defaultUserType);
                }
            }

            // Set other properties
            $user->setBlocked($userData['blocked'] ?? false);
            if (isset($userData['id_genders'])) {
                $gender = $this->entityManager->getRepository(Gender::class)->findOneBy(['id' => $userData['id_genders']]);
                if ($gender) {
                    $user->setGender($gender);
                }
            }
            if (isset($userData['id_languages'])) {
                $language = $this->entityManager->getRepository(Language::class)->findOneBy(['id' => $userData['id_languages']]);
                if ($language) {
                    $user->setLanguage($language);
                }
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Handle validation code (legacy)
            if (isset($userData['validation_code'])) {
                $this->handleValidationCode($user, $userData['validation_code']);
            }

            // Handle groups
            if (isset($userData['group_ids']) && is_array($userData['group_ids'])) {
                $this->assignGroupsToUser($user, $userData['group_ids']);
            }

            // Handle roles
            if (isset($userData['role_ids']) && is_array($userData['role_ids'])) {
                $this->assignRolesToUser($user, $userData['role_ids']);
            }

            $this->entityManager->flush();

            // Setup validation for every user (unless explicitly disabled)
            $enableValidation = $userData['enable_validation'] ?? true;
            $validationResult = null;
            
            if ($enableValidation) {
                $validationResult = $this->userValidationService->setupUserValidation(
                    $user, 
                    $userData['email_config'] ?? []
                );
                
                if (!$validationResult['success']) {
                    throw new ServiceException('Failed to setup user validation: ' . $validationResult['error'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Log transaction
            $logMessage = 'User created: ' . $user->getEmail();
            if ($enableValidation && $validationResult && $validationResult['success']) {
                $logMessage .= ' (with validation - token: ' . $validationResult['token'] . ', job_id: ' . $validationResult['job_id'] . ')';
            } elseif ($enableValidation) {
                $logMessage .= ' (validation setup failed)';
            }
            
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $user->getId(),
                $user,
                $logMessage
            );

            $this->entityManager->commit();

            $result = $this->formatUserForDetail($user);
            
            // Add validation info to response
            if ($enableValidation && $validationResult) {
                $result['validation'] = [
                    'token' => $validationResult['token'],
                    'job_id' => $validationResult['job_id'],
                    'validation_url' => $validationResult['validation_url'],
                    'message' => $validationResult['message']
                ];
            }

            return $result;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Update existing user
     */
    public function updateUser(int $userId, array $userData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            $this->validateUserData($userData, false, $user);

            // Update basic fields
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
            if (isset($userData['id_genders'])) {
                $genders = $this->entityManager->getRepository(Gender::class)->findBy(['id' => $userData['id_genders']]);
                $user->setGenders($genders);
            }


            // Update user type
            if (isset($userData['user_type_id'])) {
                $userType = $this->lookupService->findById($userData['user_type_id']);
                if (!$userType || $userType->getTypeCode() !== LookupService::USER_TYPES) {
                    throw new ServiceException('Invalid user type', Response::HTTP_BAD_REQUEST);
                }
                $user->setUserType($userType);
            }

            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $user->getId(),
                $user,
                'User updated: ' . $user->getEmail()
            );

            $this->entityManager->commit();

            return $this->formatUserForDetail($user);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): bool
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            // Prevent deletion of admin users
            if (in_array($user->getName(), ['admin', 'tpf'])) {
                throw new ServiceException('Cannot delete system users', Response::HTTP_FORBIDDEN);
            }

            // Log transaction before deletion
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $user->getId(),
                $user,
                'User deleted: ' . $user->getEmail()
            );

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return true;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Block/Unblock user
     */
    public function toggleUserBlock(int $userId, bool $blocked): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            $user->setBlocked($blocked);
            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $user->getId(),
                $user,
                'User ' . ($blocked ? 'blocked' : 'unblocked') . ': ' . $user->getEmail()
            );

            $this->entityManager->commit();

            return $this->formatUserForDetail($user);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Get user groups
     */
    public function getUserGroups(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }

        return array_map(function(Group $group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'description' => $group->getDescription()
            ];
        }, $user->getGroups()->toArray());
    }

    /**
     * Get user roles
     */
    public function getUserRoles(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }

        return array_map(function(Role $role) {
            return [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription()
            ];
        }, $user->getUserRoles()->toArray());
    }

    /**
     * Add groups to user
     */
    public function addGroupsToUser(int $userId, array $groupIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            $this->assignGroupsToUser($user, $groupIds, false);
            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users_groups',
                $user->getId(),
                false,
                'Groups added to user: ' . $user->getEmail() . ' (Group IDs: ' . implode(', ', $groupIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getUserGroups($userId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Remove groups from user
     */
    public function removeGroupsFromUser(int $userId, array $groupIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            foreach ($groupIds as $groupId) {
                $group = $this->entityManager->getRepository(Group::class)->find($groupId);
                if ($group) {
                    $userGroup = $this->entityManager->getRepository(UsersGroup::class)
                        ->findOneBy(['user' => $user, 'group' => $group]);
                    if ($userGroup) {
                        $this->entityManager->remove($userGroup);
                    }
                }
            }

            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users_groups',
                $user->getId(),
                false,
                'Groups removed from user: ' . $user->getEmail() . ' (Group IDs: ' . implode(', ', $groupIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getUserGroups($userId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Add roles to user
     */
    public function addRolesToUser(int $userId, array $roleIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            $this->assignRolesToUser($user, $roleIds, false);
            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $user->getId(),
                false,
                'Roles added to user: ' . $user->getEmail() . ' (Role IDs: ' . implode(', ', $roleIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getUserRoles($userId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Remove roles from user
     */
    public function removeRolesFromUser(int $userId, array $roleIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
            }

            foreach ($roleIds as $roleId) {
                $role = $this->entityManager->getRepository(Role::class)->find($roleId);
                if ($role) {
                    $user->removeRole($role);
                }
            }

            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $user->getId(),
                false,
                'Roles removed from user: ' . $user->getEmail() . ' (Role IDs: ' . implode(', ', $roleIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getUserRoles($userId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Send activation mail (placeholder)
     */
    public function sendActivationMail(int $userId): bool
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }

        // TODO: Implement mail sending logic
        return true;
    }

    /**
     * Clean user data (placeholder)
     */
    public function cleanUserData(int $userId): bool
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }

        // TODO: Implement data cleaning logic
        return true;
    }

    /**
     * Impersonate user (placeholder)
     */
    public function impersonateUser(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
        }

        // TODO: Implement impersonation logic
        return ['impersonation_token' => 'placeholder_token'];
    }

    // Private helper methods

    private function createUserQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->leftJoin('u.usersGroups', 'ug')
            ->leftJoin('u.userType', 'ut')
            ->leftJoin('ug.group', 'g')
            ->leftJoin('u.userActivities', 'ua')
            ->leftJoin('u.validationCodes', 'vc')
            ->where('u.intern = :intern AND u.id_status > 0')
            ->setParameter('intern', false)
            ->addSelect('ut', 'ug', 'g', 'ua', 'vc');
    }

    private function formatUserForList(User $user): array
    {
        $lastLogin = $user->getLastLogin();
        $lastLoginFormatted = 'never';
        if ($lastLogin) {
            $daysDiff = (new \DateTime())->diff($lastLogin)->days;
            $lastLoginFormatted = $lastLogin->format('Y-m-d') . ' (' . $daysDiff . ' days ago)';
        }

        $groups = array_map(fn(Group $g) => $g->getName(), $user->getGroups()->toArray());
        
        // Get validation code
        $validationCode = '-';
        if (in_array($user->getName(), ['admin', 'tpf'])) {
            $validationCode = $user->getName();
        } else {
            $activeCode = $user->getValidationCodes()->filter(fn($vc) => $vc->getConsumed() === null)->first();
            if ($activeCode) {
                $validationCode = $activeCode->getCode();
            }
        }

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'last_login' => $lastLoginFormatted,
            'status' => $user->getIdStatus() ? 'Active' : 'Inactive', // TODO: Get from lookup
            'blocked' => $user->isBlocked(),
            'code' => $validationCode,
            'groups' => implode('; ', $groups),
            'user_activity' => $user->getUserActivities()->count(),
            'user_type_code' => $user->getUserType()?->getLookupCode(),
            'user_type' => $user->getUserType()?->getLookupValue()
        ];
    }

    private function formatUserForDetail(User $user): array
    {
        $basic = $this->formatUserForList($user);
        
        return array_merge($basic, [
            'user_name' => $user->getUserName(),
            'id_genders' => $user->getGender()?->getId(),
            'id_languages' => $user->getLanguage()?->getId(),
            'id_userTypes' => $user->getUserType()?->getId(),
            'groups' => $this->getUserGroups($user->getId()),
            'roles' => $this->getUserRoles($user->getId())
        ]);
    }

    private function validateUserData(array $data, bool $isCreate, ?User $existingUser = null): void
    {
        if ($isCreate && empty($data['email'])) {
            throw new ServiceException('Email is required', Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['email'])) {
            $existingUserWithEmail = $this->userRepository->findOneByEmail($data['email']);
            if ($existingUserWithEmail && (!$existingUser || $existingUserWithEmail->getId() !== $existingUser->getId())) {
                throw new ServiceException('Email already exists', Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['user_name']) && !empty($data['user_name'])) {
            $existingUserWithUserName = $this->userRepository->findOneBy(['user_name' => $data['user_name']]);
            if ($existingUserWithUserName && (!$existingUser || $existingUserWithUserName->getId() !== $existingUser->getId())) {
                throw new ServiceException('Username already exists', Response::HTTP_BAD_REQUEST);
            }
        }
    }

    private function handleValidationCode(User $user, string $code): void
    {
        // Check if code exists
        $existingCode = $this->entityManager->getRepository(ValidationCode::class)->find($code);
        
        if ($existingCode) {
            if ($existingCode->getConsumed()) {
                throw new ServiceException('Validation code already used', Response::HTTP_BAD_REQUEST);
            }
            // Use existing code
            $existingCode->setUser($user);
            $existingCode->setConsumed(new \DateTime());
        } else {
            // Create new code
            $validationCode = new ValidationCode();
            $validationCode->setCode($code);
            $validationCode->setUser($user);
            $validationCode->setCreated(new \DateTime());
            $validationCode->setConsumed(new \DateTime());
            $this->entityManager->persist($validationCode);
        }
    }

    private function assignGroupsToUser(User $user, array $groupIds, bool $replace = true): void
    {
        if ($replace) {
            // Remove existing groups
            foreach ($user->getUsersGroups() as $userGroup) {
                $this->entityManager->remove($userGroup);
            }
            $user->getUsersGroups()->clear();
        }

        foreach ($groupIds as $groupId) {
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if ($group) {
                // Check if already assigned
                $existingUserGroup = $this->entityManager->getRepository(UsersGroup::class)
                    ->findOneBy(['user' => $user, 'group' => $group]);
                
                if (!$existingUserGroup) {
                    $userGroup = new UsersGroup();
                    $userGroup->setUser($user);
                    $userGroup->setGroup($group);
                    $this->entityManager->persist($userGroup);
                }
            }
        }
    }

    private function assignRolesToUser(User $user, array $roleIds, bool $replace = true): void
    {
        if ($replace) {
            // Remove existing roles
            foreach ($user->getUserRoles() as $role) {
                $user->removeRole($role);
            }
        }

        foreach ($roleIds as $roleId) {
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);
            if ($role) {
                $user->addRole($role);
            }
        }
    }
} 