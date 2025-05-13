<?php

namespace App\StyleComponent\Component;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

/**
 * Base view class
 * 
 * Base class for all component views
 */
abstract class BaseView
{
    /**
     * @var ContainerInterface Service container
     */
    protected ContainerInterface $container;
    
    /**
     * @var Environment Twig environment
     */
    protected Environment $twig;
    
    /**
     * @var string Template path
     */
    protected string $template;
    
    /**
     * Constructor
     */
    public function __construct(
        ContainerInterface $container,
        string $template
    ) {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->template = $template;
    }
    
    /**
     * Render the view
     * 
     * @param array $data The data to pass to the template
     * @return string The rendered view
     */
    public function render(array $data = []): string
    {
        return $this->twig->render($this->template, $data);
    }
}