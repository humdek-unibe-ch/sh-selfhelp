<?php

namespace App\StyleComponent\Component;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base component class
 * 
 * Base class for all style components
 */
abstract class BaseComponent
{
    /**
     * @var ContainerInterface Service container
     */
    protected ContainerInterface $container;
    
    /**
     * @var int Component ID
     */
    protected int $id;
    
    /**
     * @var string Component name
     */
    protected string $name;
    
    /**
     * @var int Component type ID
     */
    protected int $typeId;
    
    /**
     * @var BaseController Component controller
     */
    protected BaseController $controller;
    
    /**
     * @var BaseModel Component model
     */
    protected BaseModel $model;
    
    /**
     * @var BaseView Component view
     */
    protected BaseView $view;
    
    /**
     * Constructor
     */
    public function __construct(
        ContainerInterface $container,
        int $id,
        string $name,
        int $typeId
    ) {
        $this->container = $container;
        $this->id = $id;
        $this->name = $name;
        $this->typeId = $typeId;
        
        $this->initComponent();
    }
    
    /**
     * Initialize component
     * 
     * @return void
     */
    abstract protected function initComponent(): void;
    
    /**
     * Get component ID
     * 
     * @return int The component ID
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * Get component name
     * 
     * @return string The component name
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Get component type ID
     * 
     * @return int The component type ID
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }
    
    /**
     * Get component controller
     * 
     * @return BaseController The component controller
     */
    public function getController(): BaseController
    {
        return $this->controller;
    }
    
    /**
     * Get component model
     * 
     * @return BaseModel The component model
     */
    public function getModel(): BaseModel
    {
        return $this->model;
    }
    
    /**
     * Get component view
     * 
     * @return BaseView The component view
     */
    public function getView(): BaseView
    {
        return $this->view;
    }
}