<?php

namespace App\DataFixtures;

use App\Entity\ApiRoute;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApiRouteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Define routes as they appeared in the SQL query
        $routesData = [
            [
                'name' => 'auth_login',
                'path' => '/auth/login',
                'controller' => 'App\\Controller\\AuthController::login',
                'methods' => 'GET',
                'requirements' => null
            ],
            [
                'name' => 'auth_refresh',
                'path' => '/auth/refresh-token',
                'controller' => 'App\\Controller\\AuthController::refreshToken',
                'methods' => 'GET',
                'requirements' => null
            ],
            [
                'name' => 'auth_logout',
                'path' => '/auth/logout',
                'controller' => 'App\\Controller\\AuthController::logout',
                'methods' => 'GET',
                'requirements' => null
            ],
            [
                'name' => 'content_pages',
                'path' => '/pages',
                'controller' => 'App\\Controller\\ContentController::getAllPages',
                'methods' => 'GET',
                'requirements' => null
            ],
            [
                'name' => 'content_page',
                'path' => '/pages/{page_keyword}',
                'controller' => 'App\\Controller\\ContentController::getPage',
                'methods' => 'GET',
                'requirements' => '{"page_keyword": "[A-Za-z0-9_-]+"}'
            ],
            [
                'name' => 'content_update_page',
                'path' => '/pages/{page_keyword}',
                'controller' => 'App\\Controller\\ContentController::updatePage',
                'methods' => 'PUT',
                'requirements' => '{"page_keyword": "[A-Za-z0-9_-]+"}'
            ],
            [
                'name' => 'admin_get_pages',
                'path' => '/admin/pages',
                'controller' => 'App\\Controller\\AdminController::getPages',
                'methods' => 'GET',
                'requirements' => null
            ],
            [
                'name' => 'admin_page_fields',
                'path' => '/admin/pages/{page_keyword}/fields',
                'controller' => 'App\\Controller\\AdminController::getPageFields',
                'methods' => 'GET',
                'requirements' => '{"page_keyword": "[A-Za-z0-9_-]+"}'
            ],
            [
                'name' => 'admin_page_sections',
                'path' => '/admin/pages/{page_keyword}/sections',
                'controller' => 'App\\Controller\\AdminController::getPageSections',
                'methods' => 'GET',
                'requirements' => '{"page_keyword": "[A-Za-z0-9_-]+"}'
            ],
        ];

        foreach ($routesData as $routeData) {
            $route = new ApiRoute();
            $route->setName($routeData['name']);
            $route->setPath($routeData['path']);
            $route->setController($routeData['controller']);
            $route->setMethods($routeData['methods']);
            
            if ($routeData['requirements']) {
                $route->setRequirements($routeData['requirements']);
            }
            
            $manager->persist($route);
        }

        $manager->flush();
    }
}