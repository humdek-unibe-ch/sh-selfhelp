<?php

namespace App\Routing;

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
        
        // Load routes from database
        $dbRoutes = $this->apiRouteRepository->findAllRoutes();
        
        foreach ($dbRoutes as $dbRoute) {
            $path = $dbRoute->getPath();
            $defaults = [
                '_controller' => $dbRoute->getController(),
            ];
            
            // Parse methods (GET, POST, etc.)
            $methods = explode(',', $dbRoute->getMethods());
            
            // Parse requirements if any
            $requirements = $dbRoute->getRequirementsArray();
            
            // Create the route
            $route = new Route($path, $defaults, $requirements ?? [], [], '', [], $methods);
            $routes->add($dbRoute->getName(), $route);
        }

        $this->isLoaded = true;

        return $routes;
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return $type === 'api_database';
    }
}
