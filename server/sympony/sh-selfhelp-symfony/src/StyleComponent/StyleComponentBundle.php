<?php

namespace App\StyleComponent;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\StyleComponent\DependencyInjection\StyleComponentExtension;

/**
 * StyleComponentBundle
 * 
 * Integrates the style component system with Symfony
 */
class StyleComponentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new StyleComponentExtension();
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        // Register component compiler passes
        $container->addCompilerPass(new StyleComponentCompilerPass());
    }
}