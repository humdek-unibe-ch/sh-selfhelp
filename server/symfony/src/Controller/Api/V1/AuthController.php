<?php

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Authentication API endpoints
 */
#[Route('/api/v1/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        // private readonly JWTTokenManagerInterface $jwtManager,
        // private readonly TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTService $jwtService
    ) {
    }

    /**
     * Login endpoint
     * 
     * This endpoint is handled by the security system
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This will not be executed, as the request is handled by the security system
        throw new \RuntimeException('You must configure the check_path to be handled by the firewall');
    }

    /**
     * Refresh token endpoint
     * 
     * Validates a refresh token and generates a new access token
     */
    // #[Route('/refresh', name: 'refresh_token', methods: ['POST'])]
    // public function refreshToken(Request $request): JsonResponse
    // {
    //     try {
    //         $refreshToken = $request->request->get('refresh_token');
            
    //         if (!$refreshToken) {
    //             throw new AuthenticationException('Refresh token is required');
    //         }
            
    //         $tokenData = $this->jwtService->validateRefreshToken($refreshToken);
            
    //         if (!$tokenData) {
    //             throw new AuthenticationException('Invalid refresh token');
    //         }
            
    //         $user = $this->entityManager->getRepository(User::class)->find($tokenData['sub']);
            
    //         if (!$user) {
    //             throw new AuthenticationException('User not found');
    //         }
            
    //         $accessToken = $this->jwtManager->create($user);
            
    //         return $this->json([
    //             'status' => Response::HTTP_OK,
    //             'message' => 'OK',
    //             'error' => null,
    //             'logged_in' => true,
    //             'meta' => [
    //                 'version' => 'v1',
    //                 'timestamp' => (new \DateTime())->format('c')
    //             ],
    //             'data' => [
    //                 'access_token' => $accessToken,
    //                 'expires_in' => $_ENV['JWT_TOKEN_TTL'],
    //                 'token_type' => 'Bearer',
    //             ]
    //         ]);
    //     } catch (AuthenticationException $e) {
    //         return $this->json([
    //             'status' => Response::HTTP_UNAUTHORIZED,
    //             'message' => 'Unauthorized',
    //             'error' => $e->getMessage(),
    //             'logged_in' => false,
    //             'meta' => [
    //                 'version' => 'v1',
    //                 'timestamp' => (new \DateTime())->format('c')
    //             ],
    //             'data' => null
    //         ], Response::HTTP_UNAUTHORIZED);
    //     }
    // }

    /**
     * Logout endpoint
     * 
     * Invalidates the current token
     */
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        try {
            $accessToken = $request->request->get('access_token');
            $refreshToken = $request->request->get('refresh_token');
            
            // Revoke refresh token if provided
            if ($refreshToken) {
                // $this->jwtService->revokeRefreshToken($refreshToken);
            }
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'Successfully logged out',
                'error' => null,
                'logged_in' => false,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => false,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}