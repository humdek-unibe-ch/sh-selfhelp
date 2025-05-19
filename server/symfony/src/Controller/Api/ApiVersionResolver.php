<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API Version Resolver
 * 
 * Resolves API versions and routes requests to the appropriate controller
 */
class ApiVersionResolver
{
    /**
     * Available API versions
     */
    private const AVAILABLE_VERSIONS = ['v1'];
    
    /**
     * Default API version
     */
    private const DEFAULT_VERSION = 'v1';
    
    /**
     * Get the API version from the request
     * 
     * @param Request $request The request
     * @return string The API version
     */
    public function getVersion(Request $request): string
    {
        // Check if version is specified in the URL
        $pathInfo = $request->getPathInfo();
        $matches = [];
        if (preg_match('#^/cms-api/(v\d+)/#', $pathInfo, $matches)) {
            $version = $matches[1];
            if (in_array($version, self::AVAILABLE_VERSIONS)) {
                return $version;
            }
        }
        
        // Check if version is specified in the Accept header
        $accept = $request->headers->get('Accept');
        if ($accept && preg_match('#application/vnd\.selfhelp\.(\w+)\+json#', $accept, $matches)) {
            $version = $matches[1];
            if (in_array($version, self::AVAILABLE_VERSIONS)) {
                return $version;
            }
        }
        
        // Use default version
        return self::DEFAULT_VERSION;
    }
    
    /**
     * Get the controller class for the given version and controller name
     * 
     * @param string $version The API version
     * @param string $controllerName The controller name
     * @return string The controller class
     * @throws NotFoundHttpException If the controller is not found
     */
    public function getControllerClass(string $version, string $controllerName): string
    {
        $className = sprintf('App\\Controller\\Api\\%s\\%s\\%sController', 
            ucfirst($version), 
            ucfirst($controllerName),
            ucfirst($controllerName)
        );
        
        if (!class_exists($className)) {
            throw new NotFoundHttpException(sprintf('Controller %s not found for version %s', $controllerName, $version));
        }
        
        return $className;
    }
}
