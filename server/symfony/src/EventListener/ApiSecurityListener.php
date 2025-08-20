<?php

namespace App\EventListener;

use App\Service\Auth\UserContextService;
use App\Service\CMS\UserPermissionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listener that checks if the user has the required permissions for the API route
 * 
 * This listener runs on the kernel.controller event, which occurs after the route has been
 * matched and the controller has been resolved, but before the controller is executed.
 * At this point, the authentication process has been completed, so we can reliably
 * check if the user has the required permissions.
 */
class ApiSecurityListener implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private UserContextService $userContextService,
        private UserPermissionService $permissionService,
        private LoggerInterface $logger
    ) {}
    
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Use kernel.controller event which runs after authentication
            KernelEvents::CONTROLLER => ['onKernelController', 10], // Priority 10
        ];
    }
    
    /**
     * Checks if the user has the required permissions for the API route
     * This runs after authentication has been processed
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        
        // Only check API routes
        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/cms-api/')) {
            return;
        }
        
        // Skip OPTIONS requests (CORS preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return;
        }
        
        try {
            // Get the current route name
            $routeName = $request->attributes->get('_route');
            if (!$routeName) {
                // No route matched, skip permission check
                return;
            }
            
            // Get the required permissions using optimized cache service
            $requiredPermissions = $this->permissionService->getRoutePermissions($routeName);
            
            if (empty($requiredPermissions)) {
                return;
            }
            
            // Get the current user using UserContextService
            // At this point authentication has been completed
            $user = $this->userContextService->getCurrentUser();
            if (!$user) {
                throw new AccessDeniedException('User not authenticated.');
            }
            
            // Get the user's permissions using optimized cache service
            $userPermissions = $this->permissionService->getUserPermissions($user);
            
            $this->logger->debug('Checking permissions for route', [
                'route' => $routeName,
                'requiredPermissions' => $requiredPermissions,
                'userPermissions' => $userPermissions
            ]);
            
            // Check if the user has at least one of the required permissions
            $hasPermission = false;
            foreach ($requiredPermissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            // If the user doesn't have any of the required permissions, deny access
            if (!$hasPermission) {
                $this->logger->warning('Access denied to API route', [
                    'route' => $routeName,
                    'path' => $path,
                    'requiredPermissions' => $requiredPermissions,
                    'userId' => $user->getId()
                ]);
                
                throw new AccessDeniedException('You do not have permission to access this API endpoint.');
            }
        } catch (AccessDeniedException $e) {
            // Let the ApiExceptionListener handle this exception
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error in API security check', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Let the ApiExceptionListener handle this exception
            throw new AccessDeniedException('An error occurred while checking permissions.', $e);
        }
    }
}
