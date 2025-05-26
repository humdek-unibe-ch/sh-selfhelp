<?php

namespace App\Security;

use App\Entity\User;
use App\Service\Auth\JWTService;
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

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);

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
        // Use ApiResponseFormatter to ensure consistent error structure
        $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
        if (str_contains($errorMessage, 'Token has been blacklisted')) {
            $errorDetail = 'Token has been blacklisted.';
        } elseif (str_contains($errorMessage, 'Invalid API Token')) {
            // Extract the specific reason if available, otherwise use a generic message
            $parts = explode('Invalid API Token: ', $errorMessage, 2);
            $errorDetail = $parts[1] ?? 'Invalid API token.';
        } else {
            $errorDetail = 'Authentication failed.'; // Fallback generic error
        }

        // The first argument to formatError is the string for the 'error' field in the JSON.
        // The 'message' field in the JSON will be automatically set based on the HTTP status code (e.g., "Forbidden" for 403).
        return $this->responseFormatter->formatError(
            $errorDetail, // This will be the value of the 'error' field in the response
            Response::HTTP_FORBIDDEN, // Sets HTTP status and the 'message' field (e.g., "Forbidden")
            false, // logged_in status
            null // additionalData, can be null if not needed
        );
    }
}
