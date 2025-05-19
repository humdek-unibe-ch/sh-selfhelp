<?php

namespace App\EventListener;

use App\Controller\Api\ApiVersionResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * API Version Listener
 * 
 * Listens to kernel.request events and resolves the API version
 */
class ApiVersionListener implements EventSubscriberInterface
{
    private ApiVersionResolver $versionResolver;
    
    public function __construct(ApiVersionResolver $versionResolver)
    {
        $this->versionResolver = $versionResolver;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 30], // Run before the router
        ];
    }
    
    /**
     * Handle kernel.request event
     * 
     * @param RequestEvent $event The event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Only process API requests
        if (!str_starts_with($request->getPathInfo(), '/cms-api')) {
            return;
        }
        
        // Resolve API version
        $version = $this->versionResolver->getVersion($request);
        
        // Store version in request attributes for later use
        $request->attributes->set('_api_version', $version);
    }
}
