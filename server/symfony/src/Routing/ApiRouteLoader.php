<?php

namespace App\Routing;

use App\Entity\Permission;
use App\Repository\ApiRouteRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Custom route loader that loads routes from database
 */
class ApiRouteLoader extends Loader
{
    protected bool $isLoaded = false;
    
    public function __construct(
        private ApiRouteRepository $apiRouteRepository,
        private CacheInterface $cache,
        protected ?string $env
    ) {
        // The parent Loader doesn't need any arguments
        parent::__construct();
    }

    public function load(mixed $resource, string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the database routes loader twice');
        }

        // Use cache in production, skip in dev for easier development
        $cacheKey = 'api_routes_collection';
        $useCache = $this->env !== 'dev';

        if ($useCache) {
            $routes = $this->cache->get($cacheKey, function (ItemInterface $item) {
                // Cache for 1 hour in production
                $item->expiresAfter(3600);
                
                return $this->buildRouteCollection();
            });
        } else {
            $routes = $this->buildRouteCollection();
        }

        $this->isLoaded = true;

        return $routes;
    }

    /**
     * Build the route collection from database
     */
    private function buildRouteCollection(): RouteCollection
    {
        $routes = new RouteCollection();
        
        // Use optimized single-query method to get all routes with permissions
        $allRoutesData = $this->apiRouteRepository->findAllRoutesWithPermissionsAsArray();
        
        foreach ($allRoutesData as $routeData) {
            $version = $routeData['version'];
            
            // Always prepend version to the path
            $path = '/' . $version . $routeData['path'];
            
            // Map controller to versioned namespace
            $controller = $this->mapControllerToVersionedNamespace($routeData['controller'], $version);
            
            $defaults = [
                '_controller' => $controller,
                '_version' => $version,
            ];
            
            // Parse methods (GET, POST, etc.)
            $methods = explode(',', $routeData['methods']);

            // Requirements and params are already arrays from the optimized query
            $requirements = $routeData['requirements'] ?? [];
            $params = $routeData['params'] ?? [];

            // Attach params as a default for controller access
            $defaults['_params'] = $params;
            
            // Permission names are already parsed from the optimized query
            $permissionNames = $routeData['permission_names'] ?? [];
            
            // Create route options with permissions
            $options = [
                'permissions' => $permissionNames
            ];
            
            // Create the route with permissions in options
            $route = new Route(
                $path,                 // path
                $defaults,             // defaults
                $requirements,         // requirements
                $options,              // options (contains permissions)
                '',                    // host
                [],                    // schemes
                $methods               // methods
            );
            $routes->add($routeData['route_name'] . '_' . $version, $route);
        }

        return $routes;
    }

    /**
     * Clear the route cache - call this when routes/permissions change
     */
    public function clearCache(): void
    {
        $this->cache->delete('api_routes_collection');
    }
    
    /**
     * Maps a controller from the database to the versioned namespace
     * 
     * @param string $controller The controller string from the database (e.g., App\Controller\AuthController::login)
     * @param string $version The API version (e.g., v1)
     * @return string The mapped controller string (e.g., App\Controller\Api\V1\Auth\AuthController::login)
     */
    private function mapControllerToVersionedNamespace(string $controller, string $version): string
    {
        // Skip if already using the versioned namespace
        if (str_contains($controller, '\\Controller\\Api\\')) {
            return $controller;
        }
        
        // Parse controller string (e.g., "App\Controller\AuthController::login")
        [$controllerClass, $method] = explode('::', $controller);
        
        // Extract controller name and domain
        $parts = explode('\\', $controllerClass);
        $controllerName = end($parts);
        
        // Determine domain from controller name
        $domain = str_replace('Controller', '', $controllerName);
        
        // Map to versioned namespace
        $versionedClass = sprintf('App\\Controller\\Api\\%s\\%s\\%sController', 
            ucfirst(strtolower($version)),
            ucfirst($domain),
            ucfirst($domain)
        );
        
        return $versionedClass . '::' . $method;
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return $type === 'api_database';
    }
}
