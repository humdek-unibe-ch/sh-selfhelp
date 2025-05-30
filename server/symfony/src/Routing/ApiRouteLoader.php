<?php

namespace App\Routing;

use App\Entity\Permission;
use App\Repository\ApiRouteRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Custom route loader that loads routes from database
 */
class ApiRouteLoader extends Loader
{
    protected bool $isLoaded = false;
    
    public function __construct(
        private ApiRouteRepository $apiRouteRepository,
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

        $routes = new RouteCollection();
        
        // Get all available versions
        $versions = $this->apiRouteRepository->findAllVersions();
        
        foreach ($versions as $version) {
            // Load routes for this version
            $dbRoutes = $this->apiRouteRepository->findAllRoutesByVersion($version);
            
            foreach ($dbRoutes as $dbRoute) {
                // Always prepend version to the path
                $path = '/' . $version . $dbRoute->getPath();
                
                // Map controller to versioned namespace
                $controller = $this->mapControllerToVersionedNamespace($dbRoute->getController(), $version);
                
                $defaults = [
                    '_controller' => $controller,
                    '_version' => $version,
                ];
                
                // Parse methods (GET, POST, etc.)
                $methods = explode(',', $dbRoute->getMethods());

                // Requirements and params are now arrays
                $requirements = $dbRoute->getRequirements() ?? [];
                $params = $dbRoute->getParams() ?? [];

                // Attach params as a default for controller access
                $defaults['_params'] = $params;
                
                // Fetch permissions for this route
                $permissions = $this->apiRouteRepository->findPermissionsForRoute($dbRoute->getId());
                
                // Extract permission names for easier access in security voter
                $permissionNames = array_map(function(Permission $permission) {
                    return $permission->getName();
                }, $permissions);
                
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
                $routes->add($dbRoute->getRouteName() . '_' . $version, $route);
            }
        }

        $this->isLoaded = true;

        return $routes;
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
