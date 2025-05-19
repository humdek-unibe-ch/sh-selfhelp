<?php

namespace App\Service\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * JWT service for token management
 */
class JWTService
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly JWTEncoderInterface $jwtEncoder,
        private readonly EntityManagerInterface $entityManager
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
        $refreshToken->setExpiresAt(new \DateTime('+30 days'));
        
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
        
        return $refreshToken;
    }

    /**
     * Validate a JWT token
     */
    public function validateToken(string $token): bool
    {
        try {
            $this->jwtEncoder->decode($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Extract token from request
     */
    public function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return null;
        }
        
        // Bearer token format: "Bearer {token}"
        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return null;
        }
        
        return $parts[1];
    }
}
