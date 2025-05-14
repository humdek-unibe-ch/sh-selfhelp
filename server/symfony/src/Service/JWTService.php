<?php

namespace App\Service;

use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;

/**
 * JWT token service
 * 
 * Handles JWT token operations including validation and refresh token management
 */
class JWTService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        // private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    // /**
    //  * Validate a refresh token
    //  * 
    //  * @param string $token The refresh token to validate
    //  * @return array|null The token payload if valid, null otherwise
    //  */
    // public function validateRefreshToken(string $token): ?array
    // {
    //     $refreshToken = $this->entityManager->getRepository(RefreshToken::class)
    //         ->findOneBy(['token' => $token]);
            
    //     if (!$refreshToken) {
    //         return null;
    //     }
        
    //     // Check if token is expired
    //     if ($refreshToken->getExpiresAt() < new \DateTime()) {
    //         $this->entityManager->remove($refreshToken);
    //         $this->entityManager->flush();
    //         return null;
    //     }
        
    //     // Return token payload
    //     return [
    //         'sub' => $refreshToken->getUser()->getId(),
    //         'type' => 'refresh'
    //     ];
    // }
    
    // /**
    //  * Generate a refresh token
    //  * 
    //  * @param int $userId The user ID
    //  * @return string The generated refresh token
    //  */
    // public function generateRefreshToken(int $userId): string
    // {
    //     $user = $this->entityManager->getReference('App:User', $userId);
        
    //     if (!$user) {
    //         throw new AuthenticationException('User not found');
    //     }
        
    //     // Generate a random token
    //     $tokenString = bin2hex(random_bytes(32));
        
    //     // Create a new refresh token
    //     $refreshToken = new RefreshToken();
    //     $refreshToken->setUser($user);
    //     $refreshToken->setToken($tokenString);
    //     $refreshToken->setExpiresAt(new \DateTime('+30 days'));
        
    //     // Save to database
    //     $this->entityManager->persist($refreshToken);
    //     $this->entityManager->flush();
        
    //     return $tokenString;
    // }
    
    // /**
    //  * Revoke a refresh token
    //  * 
    //  * @param string $token The refresh token to revoke
    //  * @return bool True if token was revoked, false otherwise
    //  */
    // public function revokeRefreshToken(string $token): bool
    // {
    //     $refreshToken = $this->entityManager->getRepository(RefreshToken::class)
    //         ->findOneBy(['token' => $token]);
            
    //     if (!$refreshToken) {
    //         return false;
    //     }
        
    //     $this->entityManager->remove($refreshToken);
    //     $this->entityManager->flush();
        
    //     return true;
    // }

    public function getTokenFromRequest(Request $request): ?string
    {
        // Example: get token from Authorization header
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        // Or get from cookie/query param/etc as needed
        return null;
    }
}