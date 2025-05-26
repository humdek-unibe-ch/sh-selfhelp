<?php

namespace App\Controller\Api\V1\Auth;

use App\Service\Auth\JWTService;
use App\Service\Auth\LoginService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * API V1 Auth Controller
 * 
 * Handles authentication-related endpoints for API v1
 */
class AuthController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly LoginService $loginService,
        private readonly JWTService $jwtService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly \Doctrine\ORM\EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Login endpoint
     * 
     * @route /auth/login
     * @method POST
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $userInput = $data['user'] ?? null;
            $password = $data['password'] ?? null;
            
            if (!$userInput || !$password) {
                return $this->responseFormatter->formatError(
                    'Missing required parameters: user and password',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $user = $this->loginService->validateUser($userInput, $password);
            
            if (!$user) {
                return $this->responseFormatter->formatError(
                    'Invalid credentials',
                    Response::HTTP_UNAUTHORIZED,
                    false
                );
            }
            
            if ($user->isTwoFactorRequired()) {
                return $this->responseFormatter->formatSuccess([
                    'requires_2fa' => true,
                    'user_id' => $user->getId()
                ]);
            }
            
            $token = $this->jwtService->createToken($user);
            $refreshToken = $this->jwtService->createRefreshToken($user);
            
            return $this->responseFormatter->formatSuccess([
                'token' => $token,
                'refresh_token' => $refreshToken->getTokenHash(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Two-factor verification endpoint
     * 
     * @route /auth/two-factor-verify
     * @method POST
     */
    public function twoFactorVerify(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $code = $data['code'] ?? null;
            $userId = $data['id_users'] ?? null;
            
            if (!$code || !$userId) {
                return $this->responseFormatter->formatError(
                    'Missing required parameters: code and id_users',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $verified = $this->loginService->verify2faCode($userId, $code);
            
            if (!$verified) {
                return $this->responseFormatter->formatError(
                    'Invalid or expired verification code',
                    Response::HTTP_UNAUTHORIZED,
                    false
                );
            }
            
            $user = $this->entityManager->getRepository(\App\Entity\User::class)->find($userId);
            
            if (!$user) {
                return $this->responseFormatter->formatError(
                    'User not found',
                    Response::HTTP_NOT_FOUND,
                    false
                );
            }
            
            $token = $this->jwtService->createToken($user);
            $refreshToken = $this->jwtService->createRefreshToken($user);
            
            return $this->responseFormatter->formatSuccess([
                'token' => $token,
                'refresh_token' => $refreshToken->getTokenHash(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Refresh token endpoint
     * 
     * @route /auth/refresh-token
     * @method POST
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $data = $request->toArray(); // Use toArray() for JSON body
            $refreshTokenString = $data['refresh_token'] ?? null;
            
            if (!$refreshTokenString) {
                return $this->responseFormatter->formatError(
                    'Missing required parameter: refresh_token',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $newTokens = $this->jwtService->processRefreshToken($refreshTokenString);
            
            return $this->responseFormatter->formatSuccess($newTokens);

        } catch (AuthenticationException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_UNAUTHORIZED,
                false // Do not expose previous exception details for auth errors
            );
        } catch (\JsonException $e) {
            return $this->responseFormatter->formatError(
                'Invalid JSON payload: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            // Log the exception $e->getMessage() and $e->getTraceAsString()
            return $this->responseFormatter->formatError(
                'An unexpected error occurred while refreshing the token.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Logout endpoint
     * 
     * @route /auth/logout
     * @method POST
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // 1. Blacklist the current access token
            $accessToken = $this->jwtService->getTokenFromRequest($request);
            if (!$accessToken) {
                return $this->responseFormatter->formatError(
                    'No access token provided. Logout cannot be completed.',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            try {
                $this->jwtService->blacklistAccessToken($accessToken);
            } catch (AuthenticationException $e) {
                // Token might be already invalid/expired, which is fine for logout.
                // Log this if necessary, but don't fail the logout.
            }

            // 2. Invalidate the refresh token if provided in the body
            $data = $request->toArray(); // Use toArray() for JSON body, handle potential JsonException
            $refreshTokenString = $data['refresh_token'] ?? null;
            
            if (!$refreshTokenString) {
                return $this->responseFormatter->formatSuccess([
                    'message' => 'Access token was blacklisted. No refresh token was sent.'
                ]);
            }

            $tokenEntity = $this->entityManager->getRepository(\App\Entity\RefreshToken::class)
                ->findOneBy(['tokenHash' => $refreshTokenString]);
            
            if ($tokenEntity) {
                $this->entityManager->remove($tokenEntity);
                $this->entityManager->flush();
            }
            
            return $this->responseFormatter->formatSuccess([
                'message' => 'Successfully logged out'
            ]);
        } catch (\JsonException $e) {
            return $this->responseFormatter->formatError(
                'Invalid JSON payload for refresh token: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            // Log the exception $e->getMessage() and $e->getTraceAsString()
            return $this->responseFormatter->formatError(
                'An unexpected error occurred during logout.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
