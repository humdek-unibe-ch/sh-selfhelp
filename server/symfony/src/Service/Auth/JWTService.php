<?php

namespace App\Service\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * JWT service for token management
 */
class JWTService
{
    private const REFRESH_TOKEN_LIFETIME = '+30 days';
    public const BLACKLIST_PREFIX = 'jwt_blacklist_';

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly JWTEncoderInterface $jwtEncoder,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Create a JWT token for a user
     */
    public function createToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }

    /**
     * Create a refresh token for a user
     */
    public function createRefreshToken(User $user): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setTokenHash(bin2hex(random_bytes(32)));
        $refreshToken->setExpiresAt(new \DateTime(self::REFRESH_TOKEN_LIFETIME));
        
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
        
        return $refreshToken;
    }

    /**
     * Verify and decode a JWT access token, optionally checking against the blacklist.
     *
     * @throws AuthenticationException if the token is invalid, expired, or blacklisted
     */
    public function verifyAndDecodeAccessToken(string $token, bool $checkBlacklist = true): array
    {
        $this->logger->debug('[JWTService] Verifying access token.', ['checkBlacklist' => $checkBlacklist]);
        if ($checkBlacklist) {
            $cacheKey = self::BLACKLIST_PREFIX . md5($token);
            $this->logger->debug('[JWTService] Checking blacklist for cache key.', ['cacheKey' => $cacheKey]);
            
            $cachedValue = $this->cache->get($cacheKey, function(ItemInterface $item) use ($cacheKey) {
                $this->logger->debug('[JWTService] Cache miss for blacklist key.', ['cacheKey' => $cacheKey]);
                return false; // Default value if not found (meaning not blacklisted)
            });

            $this->logger->debug('[JWTService] Value retrieved from blacklist cache.', ['cacheKey' => $cacheKey, 'cachedValue' => $cachedValue, 'type' => gettype($cachedValue)]);

            if ($cachedValue === true) {
                $this->logger->warning('[JWTService] Token is blacklisted.', ['cacheKey' => $cacheKey]);
                throw new AuthenticationException('Token has been blacklisted.');
            }
        }

        try {
            $this->logger->debug('[JWTService] Decoding token with jwtEncoder.');
            $payload = $this->jwtEncoder->decode($token);
            if (!$payload) {
                $this->logger->error('[JWTService] Token decoding returned no payload.');
                throw new AuthenticationException('Invalid token payload.');
            }
            $this->logger->debug('[JWTService] Token decoded successfully.');
            return $payload;
        } catch (\Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException $e) {
            $this->logger->error('[JWTService] JWTDecodeFailureException during token decoding.', ['exception_message' => $e->getReason()]);
            throw new AuthenticationException('Invalid token: ' . $e->getReason(), 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('[JWTService] Exception during token decoding.', ['exception_class' => get_class($e), 'exception_message' => $e->getMessage()]);
            throw new AuthenticationException('Token validation failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Blacklist an access token.
     */
    public function blacklistAccessToken(string $accessToken): void
    {
        $this->logger->debug('[JWTService] Attempting to blacklist access token.');
        try {
            $payload = $this->jwtEncoder->decode($accessToken);
            $this->logger->debug('[JWTService] Token decoded for blacklisting.', ['payload' => $payload]);

            $expiresAt = $payload['exp'] ?? (time() + 3600); // Default to 1 hour if 'exp' is not present
            $remainingLifetime = $expiresAt - time();
            $this->logger->debug('[JWTService] Calculated remaining lifetime for blacklist entry.', ['expiresAt' => $expiresAt, 'remainingLifetime' => $remainingLifetime]);

            if ($remainingLifetime > 0) {
                $cacheKey = self::BLACKLIST_PREFIX . md5($accessToken);
                $this->logger->debug('[JWTService] Preparing to add token to blacklist cache.', ['cacheKey' => $cacheKey, 'lifetime' => $remainingLifetime]);
                
                // Step 1: Delete any existing entry to ensure clean set/overwrite with new TTL
                $this->cache->delete($cacheKey);
                $this->logger->debug('[JWTService] Attempted to delete existing blacklist cache item (if any).', ['cacheKey' => $cacheKey]);

                // Step 2: Use get() with a callback. Since it was deleted, this will be a cache miss,
                // so the callback will execute, setting the value to true and the new expiry.
                $this->cache->get($cacheKey, function (ItemInterface $item) use ($remainingLifetime, $cacheKey) {
                    $item->expiresAfter($remainingLifetime);
                    $this->logger->info('[JWTService] Setting blacklist cache item via get() callback.', ['cacheKey' => $cacheKey, 'expiresAfter' => $remainingLifetime]);
                    return true; // Store true to mark as blacklisted
                });
                $this->logger->debug('[JWTService] Token blacklist cache operation completed using delete then get.', ['cacheKey' => $cacheKey]);
            } else {
                $this->logger->info('[JWTService] Token not added to blacklist because remaining lifetime is not positive.', ['remainingLifetime' => $remainingLifetime]);
            }
        } catch (\Exception $e) {
            $this->logger->error('[JWTService] Error during token blacklisting process.', ['exception_class' => get_class($e), 'exception_message' => $e->getMessage()]);
            // Not adding to blacklist if it's already invalid might be acceptable.
        }
    }

    /**
     * Process a refresh token string: validate it, issue new tokens, and invalidate the old one.
     *
     * @throws AuthenticationException if the refresh token is invalid or processing fails
     */
    public function processRefreshToken(string $refreshTokenString): array
    {
        $tokenEntity = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['tokenHash' => $refreshTokenString]);

        if (!$tokenEntity || $tokenEntity->getExpiresAt() < new \DateTime()) {
            if ($tokenEntity) {
                $this->entityManager->remove($tokenEntity);
                $this->entityManager->flush();
            }
            throw new AuthenticationException('Invalid or expired refresh token.');
        }

        $user = $tokenEntity->getUser();
        if (!$user) {
            $this->entityManager->remove($tokenEntity);
            $this->entityManager->flush();
            throw new AuthenticationException('Refresh token is not associated with a user.');
        }

        $newAccessToken = $this->createToken($user);
        
        $this->entityManager->remove($tokenEntity);
        $newRefreshToken = $this->createRefreshToken($user);

        return [
            'token' => $newAccessToken,
            'refresh_token' => $newRefreshToken->getTokenHash(),
        ];
    }

    /**
     * Extract token from request
     */
    public function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        return null;
    }
}
