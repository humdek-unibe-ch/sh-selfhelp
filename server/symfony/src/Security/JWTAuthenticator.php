<?php

namespace App\Security;

use App\Service\JWTService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JWTAuthenticator extends AbstractAuthenticator
{
    private JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Check if this authenticator supports the given request.
     *
     * @param Request $request The request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        // Skip authentication for login, register, and token refresh routes
        $excludedRoutes = [
            '/api/auth/login',
            '/api/auth/register',
            '/api/auth/refresh_token'
        ];

        $path = $request->getPathInfo();
        
        if (in_array($path, $excludedRoutes)) {
            return false;
        }

        return $this->jwtService->getTokenFromRequest($request) !== null;
    }

    /**
     * Authenticate the request.
     *
     * @param Request $request The request
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $token = $this->jwtService->getTokenFromRequest($request);
        if (null === $token) {
            throw new CustomUserMessageAuthenticationException('No JWT token found');
        }

        $userData = $this->jwtService->validateRefreshToken($token);
        if (false === $userData) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT token');
        }

        // Using a UserBadge with a custom user loader
        return new SelfValidatingPassport(
            new UserBadge($userData['username'], function ($userIdentifier) use ($userData) {
                // TODO: Get the user from the database or create a User object
                // For now, we'll use a simple array as the user
                return $userData;
            })
        );
    }

    /**
     * Handle authentication success.
     *
     * @param Request $request The request
     * @param TokenInterface $token The token
     * @param string $firewallName The firewall name
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On success, let the request continue
        return null;
    }

    /**
     * Handle authentication failure.
     *
     * @param Request $request The request
     * @param AuthenticationException $exception The exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => $exception->getMessage()
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}