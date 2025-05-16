<?php

namespace App\Service;

use App\Repository\ApiRouteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ACLService;
use App\Service\UserContextService;

/**
 * Service for dynamically handling API route requests
 */
class DynamicControllerService extends AbstractController
{
    private array $routeCache = [];

    public function __construct(
        private ApiRouteRepository $apiRouteRepository,
        protected ContainerInterface $container,
        private UserContextService $userContextService,
        private ACLService $aclService
    ) {
    }

    /**
     * Dynamically call a controller based on route name
     */
    public function handle(string $routeName, Request $request, array $attributes = []): JsonResponse
    {
        // Get route info
        $route = $this->getRouteInfo($routeName);
        
        if (!$route) {
            return $this->createApiResponse(
                null,
                Response::HTTP_NOT_FOUND,
                sprintf('Route "%s" not found', $routeName)
            );
        }

        // --- ACL INTEGRATION START ---
        // Example: If the route requires page access, check ACL
        $pageId = $request->attributes->get('page_id') ?? ($attributes['page_id'] ?? null);
        $accessMode = $request->attributes->get('access_mode') ?? ($attributes['access_mode'] ?? 'select');
        $isGroup = $request->attributes->get('is_group') ?? ($attributes['is_group'] ?? false);
        // Only perform ACL check if pageId is present (customize as needed per your route requirements)
        if ($pageId !== null) {
            $userId = $this->userContextService->getCurrentUser()->getId();
            if (!$this->aclService->hasAccess($userId, $pageId, $accessMode)) {
                return $this->createApiResponse(
                    null,
                    Response::HTTP_FORBIDDEN,
                    'Access denied by ACL'
                );
            }
        }
        // --- ACL INTEGRATION END ---
        
        // Parse controller string (e.g., "App\Controller\AuthController::login")
        [$controllerClass, $method] = explode('::', $route['controller']);
        
        // Check if controller exists
        if (!class_exists($controllerClass)) {
            return $this->createApiResponse(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                sprintf('Controller "%s" not found', $controllerClass)
            );
        }
        
        // Get controller service if it's registered
        $controller = $this->container->has($controllerClass) 
            ? $this->container->get($controllerClass)
            : new $controllerClass();
            
        // Check if method exists
        if (!method_exists($controller, $method)) {
            return $this->createApiResponse(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                sprintf('Method "%s" not found in controller "%s"', $method, $controllerClass)
            );
        }
        
        // Call the controller method
        try {
            return $controller->$method($request, ...$attributes);
        } catch (\Exception $e) {
            return $this->createApiResponse(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }
    
    /**
     * Get cached route info
     */
    private function getRouteInfo(string $routeName): ?array
    {
        if (!isset($this->routeCache[$routeName])) {
            $route = $this->apiRouteRepository->findOneBy(['name' => $routeName]);
            
            if (!$route) {
                return null;
            }
            
            $this->routeCache[$routeName] = [
                'path' => $route->getPath(),
                'controller' => $route->getController(),
                'methods' => $route->getMethods(),
                'requirements' => $route->getRequirementsArray()
            ];
        }
        
        return $this->routeCache[$routeName];
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
            'logged_in' => false, // We don't have user info here
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTime())->format('c'),
                'dynamic' => true
            ],
            'data' => $data
        ];

        return new JsonResponse($response, $status);
    }
}
