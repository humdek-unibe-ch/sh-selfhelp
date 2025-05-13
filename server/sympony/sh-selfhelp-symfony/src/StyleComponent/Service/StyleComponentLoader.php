<?php

namespace App\StyleComponent\Service;

use App\StyleComponent\Component\BaseComponent;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Style component loader service
 * 
 * Loads style components dynamically
 */
class StyleComponentLoader
{
    /**
     * @var array Loaded components cache
     */
    private array $components = [];
    
    /**
     * Constructor
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ContainerInterface $container,
        private readonly string $componentDir
    ) {
    }
    
    /**
     * Load a component by name
     * 
     * @param string $name The component name
     * @return BaseComponent|null The loaded component or null if not found
     */
    public function loadComponent(string $name): ?BaseComponent
    {
        // Check cache first
        if (isset($this->components[$name])) {
            return $this->components[$name];
        }
        
        // Get component info from database
        $sql = "SELECT s.id, s.name, s.id_type FROM styles s WHERE s.name = :name";
        $componentInfo = $this->connection->fetchAssociative($sql, ['name' => $name]);
        
        if (!$componentInfo) {
            return null;
        }
        
        // Determine component class name
        $className = 'App\\StyleComponent\\Component\\' . ucfirst($name) . '\\' . ucfirst($name) . 'Component';
        
        // Check if class exists
        if (!class_exists($className)) {
            return null;
        }
        
        // Create component instance
        $component = new $className(
            $this->container->get('service_container'),
            $componentInfo['id'],
            $componentInfo['name'],
            $componentInfo['id_type']
        );
        
        // Cache component
        $this->components[$name] = $component;
        
        return $component;
    }
}