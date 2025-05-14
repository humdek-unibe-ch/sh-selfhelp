<?php

namespace App\Controller;

use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/api/auth")
 */
class AuthController extends AbstractController
{
    private JWTService $jwtService;
    private EntityManagerInterface $entityManager;
    // private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        JWTService $jwtService,
        EntityManagerInterface $entityManager,
        // UserPasswordHasherInterface $passwordHasher
    ) {
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        // $this->passwordHasher = $passwordHasher;
    }

    // /**
    //  * @Route(\"/login\", name=\"auth_login\", methods={\"POST\"})
    //  */
    // public function login(Request $request): JsonResponse
    // {
    //     $data = json_decode($request->getContent(), true);
    //     $username = $data['username'] ?? '';
    //     $password = $data['password'] ?? '';

    //     // TODO: Replace with your actual user entity and repository
    //     // For now, mocking a user lookup and password verification
    //     // In a real app, you would use your User entity and repository
        
    //     // Example:
    //     // $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
    //     // if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
    //     //     return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    //     // }
        
    //     // For demo purposes:
    //     if ($username !== 'admin' || $password !== 'password') {
    //         return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     // Mock user data (replace with actual user data in real implementation)
    //     $userData = [
    //         'id' => 1,
    //         'username' => $username,
    //         'roles' => ['ROLE_USER']
    //     ];

    //     // Create JWT token and refresh token
    //     $token = $this->jwtService->generateRefreshToken($userData['id']);
    //     $refreshToken = $this->jwtService->generateRefreshToken($userData['id']);

    //     // Create response with cookies
    //     $response = new JsonResponse([
    //         'message' => 'Login successful',
    //         'token' => $token
    //     ]);

    //     // Set cookies for both tokens
    //     $response->headers->setCookie(
    //         new Cookie(
    //             'jwt',
    //             $token,
    //             time() + 3600, // 1 hour
    //             '/',
    //             null,
    //             false,
    //             true,
    //             false,
    //             'lax'
    //         )
    //     );

    //     $response->headers->setCookie(
    //         new Cookie(
    //             'refresh_token',
    //             $refreshToken,
    //             time() + 86400 * 30, // 30 days
    //             '/',
    //             null,
    //             false,
    //             true,
    //             false,
    //             'lax'
    //         )
    //     );

    //     return $response;
    // }

    /**
     * @Route(\"/refresh_token\", name=\"auth_refresh_token\", methods={\"POST\"})
     */
    // public function refreshToken(Request $request): JsonResponse
    // {
    //     // Get refresh token from cookie
    //     $refreshToken = $request->cookies->get('refresh_token');
        
    //     if (!$refreshToken) {
    //         return new JsonResponse(['message' => 'Refresh token not found'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     // Validate refresh token
    //     $userData = $this->jwtService->validateRefreshToken($refreshToken);
        
    //     if (!$userData) {
    //         return new JsonResponse(['message' => 'Invalid refresh token'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     // Create new tokens
    //     $token = $this->jwtService->generateRefreshToken($userData['sub']);
    //     $newRefreshToken = $this->jwtService->generateRefreshToken($userData['sub']);

    //     // Create response with cookies
    //     $response = new JsonResponse([
    //         'message' => 'Token refreshed successfully',
    //         'token' => $token
    //     ]);

    //     // Set cookies for both tokens
    //     $response->headers->setCookie(
    //         new Cookie(
    //             'jwt',
    //             $token,
    //             time() + 3600, // 1 hour
    //             '/',
    //             null,
    //             false,
    //             true,
    //             false,
    //             'lax'
    //         )
    //     );

    //     $response->headers->setCookie(
    //         new Cookie(
    //             'refresh_token',
    //             $newRefreshToken,
    //             time() + 86400 * 30, // 30 days
    //             '/',
    //             null,
    //             false,
    //             true,
    //             false,
    //             'lax'
    //         )
    //     );

    //     return $response;
    // }

    /**
     * @Route(\"/logout\", name=\"auth_logout\", methods={\"POST\"})
     */
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['message' => 'Logged out successfully']);
        
        // Clear cookies
        $response->headers->clearCookie('jwt');
        $response->headers->clearCookie('refresh_token');
        
        return $response;
    }
}