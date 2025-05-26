<?php

namespace App\Controller\Api\V1\Auth;

use App\Repository\AuthRepository;
use App\Service\Auth\JWTService;
use App\Service\Auth\LoginService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
        private readonly \Doctrine\ORM\EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthRepository $authRepository,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {}

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
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->responseFormatter->formatError(
                    'Invalid JSON payload: ' . json_last_error_msg(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $validationErrors = $this->jsonSchemaValidationService->validate((object)$data, 'requests/auth/login');
            if (!empty($validationErrors)) {
                return $this->responseFormatter->formatError(
                    'Validation failed',
                    Response::HTTP_BAD_REQUEST,
                    ['errors' => $validationErrors]
                );
            }

            $userInput = $data['email'] ?? null; // Schema ensures 'email' exists if valid
            $password = $data['password'] ?? null; // Schema ensures 'password' exists if valid

            $user = $this->loginService->validateUser($userInput, $password);

            if (!$user) {
                return $this->responseFormatter->formatError(
                    'Invalid credentials',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // If the user is found check if 2fa is required
            if ($user->isTwoFactorRequired()) {
                $this->authRepository->generateAndStore2faCode($user->getId()); // Ensure code is generated when 2FA is required
                return $this->responseFormatter->formatSuccess([
                    'requires_2fa' => true,
                    'id_users' => $user->getId()
                ], 'responses/auth/2fa_required', Response::HTTP_OK, true);
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
            ], 'responses/auth/login', Response::HTTP_OK, true);
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
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->responseFormatter->formatError(
                    'Invalid JSON payload: ' . json_last_error_msg(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $validationErrors = $this->jsonSchemaValidationService->validate((object)$data, 'requests/auth/2fa_verify');
            if (!empty($validationErrors)) {
                return $this->responseFormatter->formatError(
                    'Validation failed',
                    Response::HTTP_BAD_REQUEST,
                    ['errors' => $validationErrors]
                );
            }

            $code = $data['code'] ?? null; // Schema ensures 'code' exists
            $userId = $data['id_users'] ?? null; // Schema ensures 'id_users' exists

            $verified = $this->authRepository->verify2faCode($userId, $code);

            if (!$verified) {
                return $this->responseFormatter->formatError(
                    'Invalid or expired verification code',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $user = $this->entityManager->getRepository(\App\Entity\User::class)->find($userId);

            if (!$user) {
                return $this->responseFormatter->formatError(
                    'User not found',
                    Response::HTTP_NOT_FOUND
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
            ], 'responses/auth/2fa_verify', Response::HTTP_OK, true);
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

            $validationErrors = $this->jsonSchemaValidationService->validate((object)$data, 'requests/auth/refresh_token');
            if (!empty($validationErrors)) {
                return $this->responseFormatter->formatError(
                    'Validation failed',
                    Response::HTTP_BAD_REQUEST,
                    ['errors' => $validationErrors]
                );
            }

            $refreshTokenString = $data['refresh_token'] ?? null; // Schema ensures 'refresh_token' exists

            $newTokens = $this->jwtService->processRefreshToken($refreshTokenString);

            return $this->responseFormatter->formatSuccess($newTokens, 'responses/auth/refresh_token', Response::HTTP_OK, true);
        } catch (AuthenticationException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_UNAUTHORIZED
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
            $data = [];
            if ($request->getContent()) {
                $data = $request->toArray(); // Use toArray() for JSON body, handle potential JsonException
                $validationErrors = $this->jsonSchemaValidationService->validate((object)$data, 'requests/auth/logout');
                if (!empty($validationErrors)) {
                    return $this->responseFormatter->formatError(
                        'Validation failed for refresh_token in body',
                        Response::HTTP_BAD_REQUEST,
                        ['errors' => $validationErrors]
                    );
                }
            }
            $refreshTokenString = $data['refresh_token'] ?? null;

            if (!$refreshTokenString) {
                return $this->responseFormatter->formatSuccess([
                    'message' => 'Access token was blacklisted. No refresh token was sent.'
                ], 'responses/auth/logout', Response::HTTP_OK, true); // loggedIn status is now handled by ApiResponseFormatter
            }

            $tokenEntity = $this->entityManager->getRepository(\App\Entity\RefreshToken::class)
                ->findOneBy(['tokenHash' => $refreshTokenString]);

            if ($tokenEntity) {
                $this->entityManager->remove($tokenEntity);
                $this->entityManager->flush();
            }

            $this->tokenStorage->setToken(null); // Clear the security token

            return $this->responseFormatter->formatSuccess([
                'message' => 'Successfully logged out'
            ], 'responses/auth/logout', Response::HTTP_OK, false); // loggedIn status is now handled by ApiResponseFormatter
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
