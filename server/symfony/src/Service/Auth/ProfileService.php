<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Cache\Core\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service for handling user profile operations
 */
class ProfileService extends BaseService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cache,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Update user name
     *
     * @param User $user The user entity
     * @param string $newName The new name
     * @return User The updated user entity
     */
    public function updateName(User $user, string $newName): User
    {
        return $this->executeInTransaction(function () use ($user, $newName) {
            // Fetch fresh managed entity to ensure proper change tracking
            $managedUser = $this->entityManager->find(User::class, $user->getId());
            if (!$managedUser) {
                throw new \InvalidArgumentException('User not found');
            }

            $oldName = $managedUser->getName();
            $managedUser->setName($newName);
            $this->entityManager->flush();

            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $managedUser->getId(),
                false,
                json_encode([
                    'action' => 'name_changed',
                    'old_name' => $oldName,
                    'new_name' => $newName,
                    'user_email' => $managedUser->getEmail()
                ])
            );

            // Invalidate user caches
            $this->invalidateUserCaches($managedUser->getId());

            return $managedUser;
        });
    }

    /**
     * Update user password
     *
     * @param User $user The user entity
     * @param string $currentPassword The current password for verification
     * @param string $newPassword The new password
     * @throws \InvalidArgumentException If passwords are invalid
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        // Verify current password
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        // $this->validatePasswordStrength($newPassword);

        $this->executeInTransaction(function () use ($user, $newPassword) {
            // Fetch fresh managed entity to ensure proper change tracking
            $managedUser = $this->entityManager->find(User::class, $user->getId());
            if (!$managedUser) {
                throw new \InvalidArgumentException('User not found');
            }

            // Hash and set new password
            $hashedPassword = $this->passwordHasher->hashPassword($managedUser, $newPassword);
            $managedUser->setPassword($hashedPassword);
            $this->entityManager->flush();

            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $managedUser->getId(),
                false,
                json_encode([
                    'action' => 'password_changed',
                    'user_email' => $managedUser->getEmail(),
                    'user_name' => $managedUser->getUserName()
                ])
            );

            // Invalidate user caches
            $this->invalidateUserCaches($managedUser->getId());
        });
    }

    /**
     * Delete user account
     *
     * @param User $user The user entity
     * @param string $emailConfirmation Email confirmation for safety
     * @throws \InvalidArgumentException If email confirmation doesn't match
     */
    public function deleteAccount(User $user, string $emailConfirmation): void
    {
        // Verify email confirmation matches user's email
        if (strtolower(trim($emailConfirmation)) !== strtolower(trim($user->getEmail()))) {
            throw new \InvalidArgumentException('Email confirmation does not match your account email');
        }

        $this->executeInTransaction(function () use ($user, $emailConfirmation) {
            // Fetch fresh managed entity to ensure proper change tracking
            $managedUser = $this->entityManager->find(User::class, $user->getId());
            if (!$managedUser) {
                throw new \InvalidArgumentException('User not found');
            }

            // Prevent deletion of system users
            if (in_array(strtolower($managedUser->getName()), ['admin', 'tpf'])) {
                throw new \InvalidArgumentException('Cannot delete system accounts');
            }

            // Log the transaction before deletion
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'users',
                $managedUser->getId(),
                false,
                json_encode([
                    'action' => 'account_deleted',
                    'user_email' => $managedUser->getEmail(),
                    'user_name' => $managedUser->getUserName(),
                    'deleted_at' => date('Y-m-d H:i:s')
                ])
            );

            // Get user ID before deletion for cache invalidation
            $userId = $managedUser->getId();

            // Delete the user (cascade will handle related entities)
            $this->entityManager->remove($managedUser);
            $this->entityManager->flush();

            // Invalidate user caches
            $this->invalidateUserCaches($userId);
        });
    }


    /**
     * Validate password strength
     *
     * @param string $password The password to validate
     * @throws \InvalidArgumentException If password is too weak
     */
    private function validatePasswordStrength(string $password): void
    {
        // Check minimum length
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }

        // Check maximum length
        if (strlen($password) > 255) {
            throw new \InvalidArgumentException('Password cannot be longer than 255 characters');
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one number');
        }

        // Check for common weak passwords
        $weakPasswords = [
            'password', '12345678', 'qwerty', 'abc123', 'password123',
            'admin', 'letmein', 'welcome', 'monkey', 'dragon'
        ];

        if (in_array(strtolower($password), $weakPasswords)) {
            throw new \InvalidArgumentException('This password is too common. Please choose a stronger password');
        }
    }

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
     * Invalidate user-related caches
     *
     * @param int $userId The user ID
     */
    private function invalidateUserCaches(int $userId): void
    {
        // Invalidate all user lists
        $this->cache
            ->withCategory(CacheService::CATEGORY_USERS)
            ->invalidateAllListsInCategory();

        // Invalidate entity scope (affects all cache depending on this user)
        $this->cache
            ->withCategory(CacheService::CATEGORY_USERS)
            ->withEntityScope(CacheService::ENTITY_SCOPE_USER, $userId)
            ->invalidateEntityScope(CacheService::ENTITY_SCOPE_USER, $userId);
    }
}
