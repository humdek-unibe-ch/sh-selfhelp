<?php

namespace App\EventListener;

use App\Service\Core\ApiRequestLoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

/**
 * ApiRequestLoggerListener
 * 
 * Event listener for automatically logging API requests and responses
 */
class ApiRequestLoggerListener implements EventSubscriberInterface
{
    /**
     * @var ApiRequestLoggerService
     */
    private $loggerService;

    /**
     * Constructor
     * 
     * @param ApiRequestLoggerService $loggerService
     */
    public function __construct(ApiRequestLoggerService $loggerService)
    {
        $this->loggerService = $loggerService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
            KernelEvents::RESPONSE => ['onKernelResponse', -10],  // Low priority to run after response is fully prepared
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    /**
     * Handle kernel request event
     * 
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Only process master requests (not sub-requests)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Only log API requests
        if (!$this->isApiRequest($request)) {
            return;
        }
        
        // Start timing the request
        $requestHash = $this->loggerService->startRequest($request);
        
        // Store request hash in request attributes for later retrieval
        $request->attributes->set('_api_request_hash', $requestHash);
    }

    /**
     * Handle kernel response event
     * 
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        // Only process master requests
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Only log API requests
        if (!$this->isApiRequest($request)) {
            return;
        }
        
        // Get request hash from request attributes
        $requestHash = $request->attributes->get('_api_request_hash');
        if (!$requestHash) {
            return;
        }
        
        // Log the request and response
        $this->loggerService->logRequest(
            $request,
            $event->getResponse(),
            $requestHash
        );
    }

    /**
     * Handle kernel exception event
     * 
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        
        // Only log API requests
        if (!$this->isApiRequest($request)) {
            return;
        }
        
        // Get request hash from request attributes
        $requestHash = $request->attributes->get('_api_request_hash');
        if (!$requestHash) {
            return;
        }
        
        // Get exception details
        $exception = $event->getThrowable();
        $errorMessage = $exception->getMessage();
        
        // If there's a response, log it with the error
        if ($event->hasResponse()) {
            $this->loggerService->logRequest(
                $request,
                $event->getResponse(),
                $requestHash,
                $errorMessage
            );
        }
    }

    /**
     * Check if a request is an API request
     * 
     * @param Request $request
     * @return bool
     */
    private function isApiRequest(\Symfony\Component\HttpFoundation\Request $request): bool
    {
        // Check path for API prefix - adjust pattern as needed
        $path = $request->getPathInfo();
        return strpos($path, '/cms-api/') === 0;
    }
}
