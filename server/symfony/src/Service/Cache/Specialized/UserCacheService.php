<?php

namespace App\Service\Cache\Specialized;

use App\Entity\User;
use App\Service\Cache\Core\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for caching user entities during request lifecycle
 * This prevents multiple database queries for the same user within a single request
 * 
 * Uses unified CacheService with user category for consistent caching
 */
class UserCacheService
{
    private ?CacheService $cacheService = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Set the cache service (injected via services.yaml)
     */
    public function setCacheService(?CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get user by email with caching
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$this->cacheService) {
            return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        }

        $cacheKey = 'user_email_' . md5($email);
        $cachedUser = $this->cacheService->get(CacheService::CATEGORY_USERS, $cacheKey);

        if ($cachedUser !== null) {
            if ($this->logger) {
                $this->logger->debug('User cache hit for email {email}', [
                    'email' => $email
                ]);
            }
            return $cachedUser;
        }

        if ($this->logger) {
            $this->logger->debug('User cache miss for email {email}', [
                'email' => $email
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Cache the result (even if null)
        if ($user !== null) {
            $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_USERS);
            $this->cacheService->set(CacheService::CATEGORY_USERS, $cacheKey, $user, $ttl);
        }

        return $user;
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(int $id): ?User
    {
        if (!$this->cacheService) {
            return $this->entityManager->getRepository(User::class)->find($id);
        }

        $cacheKey = 'user_id_' . $id;
        $cachedUser = $this->cacheService->get(CacheService::CATEGORY_USERS, $cacheKey);

        if ($cachedUser !== null) {
            if ($this->logger) {
                $this->logger->debug('User cache hit for ID {userId}', [
                    'userId' => $id
                ]);
            }
            return $cachedUser;
        }

        if ($this->logger) {
            $this->logger->debug('User cache miss for ID {userId}', [
                'userId' => $id
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        // Cache the result (even if null)
        if ($user !== null) {
            $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_USERS);
            $this->cacheService->set(CacheService::CATEGORY_USERS, $cacheKey, $user, $ttl);
        }

        return $user;
    }

    /**
     * Store user in cache (useful when we already have the user object)
     */
    public function cacheUser(User $user): void
    {
        if (!$this->cacheService) {
            return;
        }

        // Cache by both email and ID
        $emailCacheKey = 'user_email_' . md5($user->getEmail());
        $idCacheKey = 'user_id_' . $user->getId();
        
        $ttl = $this->cacheService->getCacheTTL(CacheService::CATEGORY_USERS);
        
        $this->cacheService->set(CacheService::CATEGORY_USERS, $emailCacheKey, $user, $ttl);
        $this->cacheService->set(CacheService::CATEGORY_USERS, $idCacheKey, $user, $ttl);
    }

    /**
     * Clear cache for a specific user
     */
    public function clearUserCache(User $user): void
    {
        if (!$this->cacheService) {
            return;
        }

        $emailCacheKey = 'user_email_' . md5($user->getEmail());
        $idCacheKey = 'user_id_' . $user->getId();

        $this->cacheService->delete(CacheService::CATEGORY_USERS, $emailCacheKey);
        $this->cacheService->delete(CacheService::CATEGORY_USERS, $idCacheKey);
    }

    /**
     * Clear all cached users
     */
    public function clearAll(): void
    {
        if (!$this->cacheService) {
            return;
        }

        $this->cacheService->invalidateCategory(CacheService::CATEGORY_USERS);
    }
}
