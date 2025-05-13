<?php

namespace App\StyleComponent\Component;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base model class
 * 
 * Base class for all component models
 */
abstract class BaseModel
{
    /**
     * @var ContainerInterface Service container
     */
    protected ContainerInterface $container;
    
    /**
     * Constructor
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }
}