<?php

namespace App\Service\Cache\Specialized;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for caching user entities during request lifecycle
 * This prevents multiple database queries for the same user within a single request
 * 
 * Uses ArrayAdapter for fast in-memory caching during request
 */
class UserCacheService
{
    private CacheItemPoolInterface $cache;
    private const CACHE_PREFIX = 'user_';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null
    ) {
        // Use ArrayAdapter for fast in-memory caching during request
        $this->cache = new ArrayAdapter(0, false);
    }

    /**
     * Get user by email with caching
     */
    public function getUserByEmail(string $email): ?User
    {
        $cacheKey = self::CACHE_PREFIX . 'email_' . md5($email);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            if ($this->logger) {
                $this->logger->debug('User cache hit for email {email}', [
                    'email' => $email
                ]);
            }
            return $cacheItem->get();
        }

        if ($this->logger) {
            $this->logger->debug('User cache miss for email {email}', [
                'email' => $email
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Cache the result (even if null)
        $cacheItem->set($user);
        $this->cache->save($cacheItem);

        return $user;
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(int $id): ?User
    {
        $cacheKey = self::CACHE_PREFIX . 'id_' . $id;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            if ($this->logger) {
                $this->logger->debug('User cache hit for ID {userId}', [
                    'userId' => $id
                ]);
            }
            return $cacheItem->get();
        }

        if ($this->logger) {
            $this->logger->debug('User cache miss for ID {userId}', [
                'userId' => $id
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        // Cache the result (even if null)
        $cacheItem->set($user);
        $this->cache->save($cacheItem);

        return $user;
    }

    /**
     * Store user in cache (useful when we already have the user object)
     */
    public function cacheUser(User $user): void
    {
        // Cache by both email and ID
        $emailCacheKey = self::CACHE_PREFIX . 'email_' . md5($user->getEmail());
        $idCacheKey = self::CACHE_PREFIX . 'id_' . $user->getId();

        $emailCacheItem = $this->cache->getItem($emailCacheKey);
        $emailCacheItem->set($user);
        $this->cache->save($emailCacheItem);

        $idCacheItem = $this->cache->getItem($idCacheKey);
        $idCacheItem->set($user);
        $this->cache->save($idCacheItem);
    }

    /**
     * Clear cache for a specific user
     */
    public function clearUserCache(User $user): void
    {
        $emailCacheKey = self::CACHE_PREFIX . 'email_' . md5($user->getEmail());
        $idCacheKey = self::CACHE_PREFIX . 'id_' . $user->getId();

        $this->cache->deleteItems([$emailCacheKey, $idCacheKey]);
    }

    /**
     * Clear all cached users
     */
    public function clearAll(): void
    {
        $this->cache->clear();
    }
}
