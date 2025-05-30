<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerInterface;

/**
 * Voter to check if a user has the required permissions for an API route
 */
class ApiRouteVoter extends Voter
{
    public const API_ROUTE_ACCESS = 'api_route_access';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Only support our custom attribute
        return $attribute === self::API_ROUTE_ACCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // If the user is anonymous, deny access
        if (!$user instanceof User) {
            return false;
        }

        // Get the current request
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        // Get the matched route
        $routeName = $request->attributes->get('_route');
        if (!$routeName) {
            // No route matched, allow access by default (other security measures will apply)
            return true;
        }

        try {
            // Get the route from the router
            $route = $this->router->getRouteCollection()->get($routeName);
            if (!$route) {
                // Route not found, allow access by default (other security measures will apply)
                return true;
            }

            // Get the required permissions from the route options
            $requiredPermissions = $route->getOption('permissions') ?? [];
            
            // If no permissions are required, allow access
            if (empty($requiredPermissions)) {
                return true;
            }

            // Get the user's permissions
            $userPermissions = $user->getPermissionNames();
            
            // Log permission check for debugging
            $this->logger->debug('Checking permissions for route', [
                'route' => $routeName,
                'requiredPermissions' => $requiredPermissions,
                'userPermissions' => $userPermissions
            ]);

            // Check if the user has at least one of the required permissions
            foreach ($requiredPermissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    return true;
                }
            }

            // User doesn't have any of the required permissions
            $this->logger->info('Access denied to route due to missing permissions', [
                'route' => $routeName,
                'requiredPermissions' => $requiredPermissions,
                'userId' => $user->getId()
            ]);
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error checking route permissions', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In case of error, deny access by default
            return false;
        }
    }
}
