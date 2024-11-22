<?php
require_once __DIR__ . "/CmsApiResponse.php";
require_once __DIR__ . "/../../service/PerformanceLogger.php";
require_once __DIR__ . "/content/ContentCmsApi.php";
require_once __DIR__ . "/admin/AdminCmsApi.php";
require_once __DIR__ . "/auth/AuthCmsApi.php";

/**
 * @brief Class defining the basic functionality of a CMS API request.
 * 
 * This class handles the routing and execution of CMS API requests, including
 * parameter collection, method validation, and response handling.
 */
class CmsApiRequest
{
    /** @var object Services container instance */
    private $services;

    /** @var string Name of the API class to be called */
    private $class_name;

    /** @var string Name of the method to be executed */
    private $method_name;

    /** @var string Optional keyword parameter */
    private $keyword;

    /** @var string Client type (web/app) making the request */
    private $client_type;

    /**
     * @brief Constructs a new CMS API request instance
     * 
     * @param object $services    Services container instance
     * @param string $class_name  Name of the API class to be called
     * @param string $method_name Name of the method to be executed (optional)
     * @param string $keyword     Optional keyword parameter (default: '')
     */
    public function __construct($services, $class_name, $method_name = null, $keyword = '')
    {
        $this->services = $services;
        $this->class_name = $class_name;
        $this->method_name = $method_name;
        $this->keyword = $keyword;
        $this->client_type = $this->determineClientType();
    }

    /**
     * @brief Determines the type of client making the request
     * 
     * @return string 'app' for mobile app requests, 'web' for web frontend requests
     */
    private function determineClientType(): string
    {
        // Check for custom header first
        $clientHeader = $_SERVER['HTTP_X_CLIENT_TYPE'] ?? '';
        if ($clientHeader) {
            return strtolower($clientHeader) === pageAccessTypes_mobile ? pageAccessTypes_mobile : pageAccessTypes_web;
        }

        // Fallback to User-Agent check
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return (strpos($userAgent, 'SelfHelpApp/') !== false) ? pageAccessTypes_mobile : pageAccessTypes_web;
    }

    /**
     * @brief Get the client type making the request
     * 
     * @return string 'app' or 'web'
     */
    public function getClientType(): string
    {
        return $this->client_type;
    }

    /**
     * Prepare and validate method parameters
     * 
     * @param ReflectionMethod $reflection Method reflection
     * @return array Method parameters
     */
    private function prepareMethodParameters(\ReflectionMethod $reflection): array 
    {
        // Get all available parameters from different sources
        $params = [...$this->services->get_router()->route['params']];
        unset($params['class'], $params['method']); // Remove routing parameters

        // Collect POST data
        if (!empty($_POST)) {
            $params = array_merge($params, $_POST);
        }

        // Collect GET data
        if (!empty($_GET)) {
            $params = array_merge($params, $_GET);
        }

        // Collect JSON/Raw input data
        $inputData = file_get_contents('php://input');
        if (!empty($inputData)) {
            // Try parsing as URL-encoded data first
            parse_str($inputData, $parsedData);
            
            // If not URL-encoded, try JSON
            if (empty($parsedData)) {
                $parsedData = json_decode($inputData, true);
            }

            if (!empty($parsedData) && is_array($parsedData)) {
                $params = array_merge($params, $parsedData);
            }
        }

        // URL decode non-array values
        array_walk($params, function(&$value) {
            if (!is_array($value)) {
                $value = urldecode($value);
            }
        });

        // Get method's required parameters
        $methodParams = $reflection->getParameters();
        $requiredParams = [];
        $optionalParams = [];
        
        // Separate required and optional parameters
        foreach ($methodParams as $param) {
            if ($param->isOptional()) {
                $optionalParams[$param->getName()] = $param->getDefaultValue();
            } else {
                $requiredParams[] = $param->getName();
            }
        }

        // Check for missing required parameters
        $missingParams = array_diff($requiredParams, array_keys($params));
        if (!empty($missingParams)) {
            throw new \InvalidArgumentException(
                "Missing required parameters: " . implode(', ', $missingParams)
            );
        }

        // Build final parameter array in correct order
        $finalParams = [];
        foreach ($methodParams as $param) {
            $paramName = $param->getName();
            if (isset($params[$paramName])) {
                $finalParams[] = $params[$paramName];
            } elseif ($param->isOptional()) {
                $finalParams[] = $param->getDefaultValue();
            }
        }

        return $finalParams;
    }

    /**
     * @brief Executes the API request and sends the response
     * 
     * Validates the requested class and method, authorizes the user,
     * executes the method with appropriate parameters, and sends the response.
     * 
     * @throws Exception When method execution fails
     * @return void
     */
    public function return_response(): void
    {
        $debug_start_time = microtime(true);
        $router = $this->services->get_router();

        try {
            if (!class_exists($this->class_name)) {
                $response = new CmsApiResponse(
                    404,
                    null,
                    "Unknown request class '{$this->class_name}'"
                );
            } else {
                $instance = new $this->class_name($this->services, $this->keyword, $this->client_type);
                $instance->authorizeUser();
                // $instance->init_response($response);

                if (!method_exists(object_or_class: $instance, method: $this->method_name)) {
                    $response = new CmsApiResponse(
                        404,
                        null,
                        "Request '{$this->class_name}' has no method '{$this->method_name}'"
                    );
                } else {
                    // Get reflection of the target class
                    $reflection = new \ReflectionMethod(
                        $this->class_name,
                        $this->method_name
                    );

                    if (!$reflection->isPublic()) {
                        $response = new CmsApiResponse(
                            400,
                            null,
                            "Request '{$this->class_name}' method '{$this->method_name}' is not public"
                        );
                    } else {
                        // Get method parameters and execute
                        $methodParameters = $this->prepareMethodParameters($reflection);
                        $result = call_user_func_array(
                            [$instance, $this->method_name],
                            $methodParameters
                        );

                        if ($result === null) {
                            // If no response set, get it from the instance
                            $response = $instance->get_response();
                        } else {
                            // If method returned a response, use it
                            $response = new CmsApiResponse(status: 200, data: $result);
                        }
                    }
                }
            }

            // Add the logging callback with performance metrics
            $response->addAfterSendCallback(function () use ($router, $debug_start_time): void {

                $user_activity_id = $router->log_user_activity($debug_start_time, true);

                if (DEBUG) {
                    // Collect all style metrics                
                    $styleMetrics = PerformanceLogger::getAllStyleMetrics();
                    $this->services->get_db()->insert(
                        table: 'logPerformance',
                        entries: array(
                            "log" => json_encode($styleMetrics),
                            "id_user_activity" => $user_activity_id
                        )
                    );
                }
            });

            if ($response instanceof CmsApiResponse) {
                $response->send();
            } else {
                (new CmsApiResponse(200, $response))->send();
            }
        } catch (Exception $e) {
            $response = new CmsApiResponse(500, null, $e->getMessage());
            $response->addAfterSendCallback(callback: function () use ($router, $debug_start_time): void {
                $router->log_user_activity($debug_start_time, true);
            });
            $response->send();
        }
    }
}
