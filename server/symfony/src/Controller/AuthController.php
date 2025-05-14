<?php

namespace App\Controller;

use App\Service\JWTService;
use App\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    /**
     * Login endpoint
     */
    public function __construct(
        private readonly LoginService $loginService,
        private readonly JWTService $jwtService,
        private readonly \Doctrine\ORM\EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Login endpoint
     */
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userInput = $data['user'] ?? null;
        $password = $data['password'] ?? null;

        if (!$userInput || !$password) {
            return $this->createApiResponse(null, Response::HTTP_BAD_REQUEST, 'Missing credentials');
        }

        $user = $this->loginService->validateUser($userInput, $password);

        if (!$user) {
            return $this->createApiResponse(null, Response::HTTP_UNAUTHORIZED, 'Invalid credentials');
        } elseif ($user->isTwoFactorRequired()) {
            // 2FA required
            return $this->createApiResponse([
                'two_factor' => [
                    'required' => true,
                    'id_users' => $user->getId(),
                ]
            ]);
        }

        // Generate access and refresh tokens
        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user);

        // Optionally set session or logged_in state here if needed

        return $this->createApiResponse([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => getenv('ACCESS_TOKEN_EXPIRATION') ?: 3600,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Refresh token endpoint
     */
    public function refreshToken(Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Refresh token endpoint (placeholder)'
        ]);
    }

    /**
     * Logout endpoint
     */
    public function logout(Request $request): JsonResponse
    {
        // Empty implementation for now
        return $this->createApiResponse([
            'message' => 'Logout endpoint (placeholder)'
        ]);
    }

    /**
     * Create standardized API response
     */
    private function createApiResponse(
        $data = null,
        int $status = Response::HTTP_OK,
        ?string $error = null
    ): JsonResponse {
        $response = [
            'status' => $status,
            'message' => $status === 200 ? 'OK' : Response::$statusTexts[$status] ?? 'Unknown status',
            'error' => $error,
            'logged_in' => $this->getUser() !== null,
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTime())->format('c')
            ],
            'data' => $data
        ];

        return new JsonResponse($response, $status);
    }
}
