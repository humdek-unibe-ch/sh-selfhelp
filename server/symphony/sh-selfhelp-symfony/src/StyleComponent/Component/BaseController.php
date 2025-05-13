<?php

namespace App\StyleComponent\Component;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base controller class
 * 
 * Base class for all component controllers
 */
abstract class BaseController
{
    /**
     * @var ContainerInterface Service container
     */
    protected ContainerInterface $container;
    
    /**
     * @var BaseModel Component model
     */
    protected BaseModel $model;
    
    /**
     * Constructor
     */
    public function __construct(
        ContainerInterface $container,
        BaseModel $model
    ) {
        $this->container = $container;
        $this->model = $model;
    }
}