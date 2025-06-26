<?php

namespace App\Controller\Api\V1\Auth;

use App\Controller\Trait\RequestValidatorTrait;
use App\Exception\RequestValidationException;
use App\Repository\AuthRepository;
use App\Service\Auth\JWTService;
use App\Service\Auth\LoginService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Psr\Log\LoggerInterface;
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
    use RequestValidatorTrait;
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
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly \Psr\Log\LoggerInterface $logger
    ) {}

    /**
     * Get user language information with fallback to CMS preferences
     * 
     * @param User $user
     * @return array ['language_id' => int, 'language_locale' => string|null]
     */
    private function getUserLanguageInfo(\App\Entity\User $user): array
    {
        $userLanguageId = $user->getIdLanguages();
        $userLanguageLocale = null;
        
        if ($userLanguageId) {
            $userLanguage = $this->entityManager->getRepository('App\Entity\Language')->find($userLanguageId);
            if ($userLanguage) {
                $userLanguageLocale = $userLanguage->getLocale();
            }
        } else {
            // User doesn't have language set, use CMS default
            try {
                $cmsPreference = $this->entityManager->getRepository('App\Entity\CmsPreference')->findOneBy([]);
                if ($cmsPreference && $cmsPreference->getDefaultLanguage()) {
                    $userLanguageId = $cmsPreference->getDefaultLanguage()->getId();
                    $userLanguageLocale = $cmsPreference->getDefaultLanguage()->getLocale();
                } else {
                    // No CMS default language set, use fallback
                    $userLanguageId = 2;
                    $fallbackLanguage = $this->entityManager->getRepository('App\Entity\Language')->find(2);
                    if ($fallbackLanguage) {
                        $userLanguageLocale = $fallbackLanguage->getLocale();
                    }
                }
            } catch (\Exception $e) {
                // If there's an error getting the default language, use fallback
                $userLanguageId = 2;
                $fallbackLanguage = $this->entityManager->getRepository('App\Entity\Language')->find(2);
                if ($fallbackLanguage) {
                    $userLanguageLocale = $fallbackLanguage->getLocale();
                }
            }
        }
        
        return [
            'language_id' => $userLanguageId,
            'language_locale' => $userLanguageLocale
        ];
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
            // Validate request against JSON schema
            // Note: We don't catch RequestValidationException here, we let it bubble up to the ApiExceptionListener
            $data = $this->validateRequest($request, 'requests/auth/login', $this->jsonSchemaValidationService);

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

            // Get user language with fallback to CMS preferences
            $userLanguageInfo = $this->getUserLanguageInfo($user);

            return $this->responseFormatter->formatSuccess([
                'access_token' => $token,
                'refresh_token' => $refreshToken->getTokenHash(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'language_id' => $userLanguageInfo['language_id'],
                    'language_locale' => $userLanguageInfo['language_locale']
                ]
            ], 'responses/auth/login', Response::HTTP_OK, true);
        } catch (\App\Exception\RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            // Let the ApiExceptionListener handle this too
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Login error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An error occurred during login.',
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
            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/2fa_verify', $this->jsonSchemaValidationService);

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

            // Get user language with fallback to CMS preferences
            $userLanguageInfo = $this->getUserLanguageInfo($user);

            return $this->responseFormatter->formatSuccess([
                'access_token' => $token,
                'refresh_token' => $refreshToken->getTokenHash(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'language_id' => $userLanguageInfo['language_id'],
                    'language_locale' => $userLanguageInfo['language_locale']
                ]
            ], 'responses/auth/2fa_verify', Response::HTTP_OK, true);
        } catch (\App\Exception\RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            // Let the ApiExceptionListener handle this too
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Two-factor verification error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An error occurred during two-factor verification.',
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
            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/refresh_token', $this->jsonSchemaValidationService);

            $refreshTokenString = $data['refresh_token'] ?? null; // Schema ensures 'refresh_token' exists

            $newTokens = $this->jwtService->processRefreshToken($refreshTokenString);

            return $this->responseFormatter->formatSuccess($newTokens, 'responses/auth/refresh_token', Response::HTTP_OK, true);
        } catch (\App\Exception\RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            // Let the ApiExceptionListener handle this too
            throw $e;
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
            $this->logger->error('Token refresh error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
                try {
                    // Validate request against JSON schema
                    $data = $this->validateRequest($request, 'requests/auth/logout', $this->jsonSchemaValidationService);
                } catch (\App\Exception\RequestValidationException $e) {
                    // For logout, we can continue even if validation fails
                    // Just log the error and proceed with empty data
                    $this->logger->warning('Logout request validation failed', [
                        'errors' => $e->getValidationErrors()
                    ]);
                } catch (\InvalidArgumentException $e) {
                    // Invalid JSON, just log and continue with empty data
                    $this->logger->warning('Invalid JSON in logout request', [
                        'message' => $e->getMessage()
                    ]);
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
            $this->logger->error('Logout error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An unexpected error occurred during logout.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Set user language endpoint
     * 
     * @route /auth/set-language
     * @method POST
     */
    public function setUserLanguage(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = $this->getUser();
            if (!$user) {
                return $this->responseFormatter->formatError(
                    'User not authenticated',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/set_language', $this->jsonSchemaValidationService);

            $languageId = (int) $data['language_id'];

            // Validate that the language exists
            $language = $this->entityManager->getRepository('App\Entity\Language')->find($languageId);
            if (!$language) {
                return $this->responseFormatter->formatError(
                    'Invalid language ID',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Update user's language
            $user->setIdLanguages($languageId);
            $this->entityManager->flush();

            return $this->responseFormatter->formatSuccess([
                'message' => 'User language updated successfully',
                'language_id' => $languageId,
                'language_locale' => $language->getLocale(),
                'language_name' => $language->getLanguage()
            ], null, Response::HTTP_OK, true);

        } catch (\App\Exception\RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            // Let the ApiExceptionListener handle this too
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Set user language error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An error occurred while setting user language.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
