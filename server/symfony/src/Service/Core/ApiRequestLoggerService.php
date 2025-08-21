<?php

namespace App\Service\Core;

use App\Entity\ApiRequestLog;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\Cache\Core\CacheService;

/**
 * ApiRequestLoggerService
 * 
 * Service for logging API requests and responses
 */
class ApiRequestLoggerService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array Request start times indexed by request hash
     */
    private $requestStartTimes = [];

    /**
     * Constructor
     * 
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        private readonly CacheService $cache
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Start timing an API request
     * 
     * @param Request $request HTTP request
     * @return string Request hash for identifying this request later
     */
    public function startRequest(Request $request): string
    {
        // Generate a unique hash for this request
        $requestHash = spl_object_hash($request);
        
        // Record start microtime
        $this->requestStartTimes[$requestHash] = microtime(true);
        
        return $requestHash;
    }

    /**
     * Log an API request and response
     * 
     * @param Request $request HTTP request
     * @param Response $response HTTP response
     * @param string $requestHash Request hash from startRequest()
     * @param string|null $errorMessage Optional error message if request failed
     * @return ApiRequestLog The created log entity
     */
    public function logRequest(
        Request $request, 
        Response $response, 
        string $requestHash,
        ?string $errorMessage = null
    ): ApiRequestLog {
        // Get request start time and calculate duration
        $startTime = $this->requestStartTimes[$requestHash] ?? microtime(true);
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000); // Duration in milliseconds
        
        // Clean up the tracked request
        unset($this->requestStartTimes[$requestHash]);
        
        // Get authenticated user ID if available
        $userId = null;
        $token = $this->tokenStorage->getToken();
        if ($token !== null) {
            $user = $token->getUser();
            if ($user instanceof UserInterface && method_exists($user, 'getId')) {
                $userId = $user->getId();
            }
        }
        
        // Create start and end DateTime objects
        $requestDateTime = new DateTime();
        $requestDateTime->setTimestamp((int)$startTime);
        
        $responseDateTime = new DateTime();
        $responseDateTime->setTimestamp((int)$endTime);
        
        // Prepare request parameters (safely handling file uploads)
        $requestParams = $this->sanitizeRequestParams($request);
        
        // Prepare response data (truncate if too large)
        $responseContent = $response->getContent();
        if (strlen($responseContent) > 10000) {
            $responseContent = substr($responseContent, 0, 10000) . '... [truncated]';
        }
        
        // Create log entity
        $log = new ApiRequestLog();
        $log->setRouteName($request->attributes->get('_route'))
            ->setPath($request->getPathInfo())
            ->setMethod($request->getMethod())
            ->setStatusCode($response->getStatusCode())
            ->setUserId($userId)
            ->setIpAddress($request->getClientIp())
            ->setRequestTime($requestDateTime)
            ->setResponseTime($responseDateTime)
            ->setDurationMs($duration)
            ->setRequestParams(json_encode($requestParams))
            ->setRequestHeaders(json_encode($this->getHeadersArray($request)))
            ->setResponseData($responseContent)
            ->setErrorMessage($errorMessage);
        
        // Persist the log
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        
        return $log;
    }
    
    /**
     * Get request headers as array
     * 
     * @param Request $request
     * @return array
     */
    private function getHeadersArray(Request $request): array
    {
        $headers = [];
        foreach ($request->headers->all() as $key => $value) {
            // Filter out sensitive headers
            if (!in_array(strtolower($key), ['authorization', 'cookie', 'x-api-key'])) {
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Sanitize request parameters, handling files and sensitive data
     * 
     * @param Request $request
     * @return array
     */
    private function sanitizeRequestParams(Request $request): array
    {
        $params = [];
        
        // Get query parameters
        if ($request->query->count() > 0) {
            $params['query'] = $this->sanitizeData($request->query->all());
        }
        
        // Get request body parameters
        if ($request->request->count() > 0) {
            $params['request'] = $this->sanitizeData($request->request->all());
        }
        
        // Handle JSON content
        $content = $request->getContent();
        if (!empty($content) && $this->isJsonContent($request)) {
            try {
                $jsonParams = json_decode($content, true);
                if (is_array($jsonParams)) {
                    $params['json'] = $this->sanitizeData($jsonParams);
                }
            } catch (\Exception $e) {
                // Ignore JSON parsing errors
            }
        }
        
        // Handle file uploads (just log file info, not content)
        if ($request->files->count() > 0) {
            $params['files'] = [];
            foreach ($request->files->all() as $key => $file) {
                if (is_array($file)) {
                    $params['files'][$key] = '[multiple files]';
                } else {
                    $params['files'][$key] = [
                        'name' => $file->getClientOriginalName(),
                    ];
                }
            }
        }
        
        return $params;
    }
    
    /**
     * Check if the request content type is JSON
     * 
     * @param Request $request
     * @return bool
     */
    private function isJsonContent(Request $request): bool
    {
        $contentType = $request->headers->get('Content-Type', '');
        return strpos($contentType, 'json') !== false;
    }
    
    /**
     * Sanitize data, masking sensitive fields
     * 
     * @param array $data
     * @return array
     */
    private function sanitizeData(array $data): array
    {
        $sensitiveFields = [
            'password', 'token', 'secret', 'key', 'apikey', 'api_key',
            'credit_card', 'creditcard', 'card_number', 'cardnumber'
        ];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
            } elseif (is_string($key) && in_array(strtolower($key), $sensitiveFields)) {
                $data[$key] = '********';
            }
        }
        
        return $data;
    }
}
