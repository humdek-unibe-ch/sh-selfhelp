<?php

namespace App\Security;

use App\Entity\User;
use App\Service\Auth\JWTService;
use App\Service\Auth\UserCacheService;
use App\Service\Core\ApiResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
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

class JWTTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly JWTService $jwtService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserCacheService $userCacheService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Only try to authenticate on API routes and if an Authorization header with Bearer token exists
        return str_starts_with($request->getPathInfo(), '/cms-api/v1/') && $request->headers->has('Authorization') && str_starts_with($request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->jwtService->getTokenFromRequest($request);

        if (null === $token) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        try {
            $payload = $this->jwtService->verifyAndDecodeAccessToken($token, true); // Ensure blacklist is checked
        } catch (AuthenticationException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid API Token: ' . $e->getMessage());
        }

        // Assuming your JWT payload contains 'username' or 'email' as the user identifier
        // Adjust 'email' to whatever claim you use (e.g., 'sub', 'id', 'user_identifier')
        $userIdentifier = $payload['email'] ?? ($payload['username'] ?? ($payload['sub'] ?? null));

        if (null === $userIdentifier) {
            throw new CustomUserMessageAuthenticationException('User identifier not found in token payload.');
        }

        $user = $this->userCacheService->getUserByEmail($userIdentifier);

        if (null === $user) {
            throw new CustomUserMessageAuthenticationException(sprintf('User "%s" not found.', $userIdentifier));
        }

        return new SelfValidatingPassport(new UserBadge($userIdentifier, function ($userIdentifier) use ($user) {
            // This callable is used to load the user from the UserBadge's identifier.
            // Since we already loaded the user, we can just return it.
            // Or, ensure UserBadge is constructed with the User object itself if your UserProvider supports it.
            // For simplicity with SelfValidatingPassport, providing the user directly to UserBadge is often done
            // or by ensuring the UserProvider can load by the identifier in UserBadge.
            // Let's assume UserBadge with identifier is sufficient for UserProvider configured in security.yaml
            return $user; 
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // If an AuthenticationException occurs, it means the token provided was problematic
        // (e.g., invalid, expired, blacklisted). In this case, we return null.
        // This allows Symfony's security system to proceed as if the user is anonymous.
        // The access_control rules in security.yaml will then determine if anonymous
        // access is permitted for the requested path.
        //
        // This simplifies the authenticator by removing path-specific logic.
        // For PUBLIC_ACCESS routes, the user will get anonymous access.
        // For protected routes, Symfony will deny access if the (now anonymous) user lacks roles.
        return null;
    }
}
