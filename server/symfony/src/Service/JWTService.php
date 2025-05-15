<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * JWT token service
 * 
 * Handles JWT token operations including validation and refresh token management
 */
class JWTService
{
    /**
     * Validate an access token
     *
     * @param string $token The JWT access token to validate
     * @return array|false The token payload if valid, false otherwise
     */
    public function validateAccessToken(string $token): array|false
    {
        try {
            $payload = $this->jwtManager->decode($token);
            if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
                return false;
            }
            return $payload;
        } catch (\Exception $e) {
            return false;
        }
    }

    private const ACCESS_TOKEN_EXPIRATION = 3600; // 1 hour
    private const REFRESH_TOKEN_EXPIRATION = 2592000; // 30 days
    
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * Validate a refresh token
     * 
     * @param string $token The refresh token to validate
     * @return array|null The token payload if valid, null otherwise
     */
    public function validateRefreshToken(string $token): ?array
    {
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $token]);
            
        if (!$refreshToken) {
            return null;
        }
        
        // Check if token is expired
        if ($refreshToken->isExpired()) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
            return null;
        }
        
        // Return token payload
        return [
            'sub' => $refreshToken->getUser()->getId(),
            'type' => 'refresh'
        ];
    }
    
    /**
     * Generate a refresh token
     * 
     * @param User $user The user entity
     * @return string The generated refresh token
     */
    public function generateRefreshToken(User $user): string
    {
        // Generate a random token
        $tokenString = bin2hex(random_bytes(32));
        
        // Create a new refresh token
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setTokenHash($tokenString);
        $refreshToken->setExpiresAt(new \DateTime('+30 days'));
        
        // Save to database
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
        
        return $tokenString;
    }
    
    /**
     * Generate an access token for a user
     *
     * @param User $user
     * @return string
     */
    public function generateAccessToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }
    
    /**
     * Revoke a refresh token
     * 
     * @param string $token The refresh token to revoke
     * @return bool True if token was revoked, false otherwise
     */
    public function revokeRefreshToken(string $token): bool
    {
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $token]);
            
        if (!$refreshToken) {
            return false;
        }
        
        $this->entityManager->remove($refreshToken);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Extract token from request
     * 
     * @param Request $request
     * @return string|null
     */
    public function getTokenFromRequest(Request $request): ?string
    {
        // First try Authorization header
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        
        // Then try request parameters
        $token = $request->request->get('access_token');
        if ($token) {
            return $token;
        }
        
        // Finally try query parameters
        return $request->query->get('access_token');
    }
    
    /**
     * Get token expiration in seconds
     * 
     * @return int
     */
    public function getAccessTokenExpiration(): int
    {
        return self::ACCESS_TOKEN_EXPIRATION;
    }
    
    /**
     * Get refresh token expiration in seconds
     * 
     * @return int
     */
    public function getRefreshTokenExpiration(): int
    {
        return self::REFRESH_TOKEN_EXPIRATION;
    }
}