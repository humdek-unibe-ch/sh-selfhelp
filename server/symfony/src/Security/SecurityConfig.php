<?php

namespace App\Security;

use App\Security\JWTAuthenticator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;


class SecurityConfig
{
    public function configure(ContainerConfigurator $container): void
    {
        // This is typically configured in config/packages/security.yaml
        // But this class can be used as a reference for the configuration
    }
}