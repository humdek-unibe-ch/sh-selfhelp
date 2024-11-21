<?php
require_once __DIR__ . "/CmsApiResponse.php";
require_once __DIR__ . "/../../service/PerformanceLogger.php";
require_once __DIR__ . "/content/ContentCmsApi.php";
require_once __DIR__ . "/admin/AdminCmsApi.php";

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

    /** @var array Collection of request parameters */
    private $params;

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
        $this->params = $this->collectParameters();
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
     * @brief Collects and processes request parameters from various sources
     * 
     * Gathers parameters from:
     * - Route parameters
     * - POST data
     * - GET data
     * - JSON or URL-encoded request body
     * 
     * @return array Processed request parameters
     */
    private function collectParameters(): array
    {
        // Collect route parameters
        $params = [...$this->services->get_router()->route['params']];
        unset($params['class'], $params['method']);

        // Add POST and GET parameters
        if (!empty($_POST)) {
            $params['data'] = $_POST;
        }
        if (!empty($_GET)) {
            $params = array_merge($_GET, $params);
        }

        // Parse JSON or URL-encoded input body
        $inputData = file_get_contents(filename: 'php://input');
        parse_str(string: $inputData, result: $jsonData);

        if (!$jsonData || !is_array(value: $jsonData)) {
            $jsonData = json_decode(json: $inputData, associative: true);
        }

        if ($jsonData !== null && json_last_error() === JSON_ERROR_NONE && count(value: $jsonData) > 0) {
            $params['data'] = $jsonData;
        }

        // Decode URL-encoded parameters
        foreach ($params as $key => $value) {
            if (!is_array(value: $value)) {
                $params[$key] = urldecode(string: $value);
            }
        }

        return $params;
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
                    $reflection = new ReflectionMethod(objectOrMethod: $instance, method: $this->method_name);
                    if (!$reflection->isPublic()) {
                        $response = new CmsApiResponse(
                            403,
                            null,
                            "Request '{$this->class_name}' method '{$this->method_name}' is not public"
                        );
                    } else {
                        // Prepare parameters for method call
                        $methodParameters = $this->prepareMethodParameters(reflection: $reflection);

                        // Execute the method
                        $result = call_user_func_array([$instance, $this->method_name], $methodParameters);

                        if ($result === null) {
                            $response = new CmsApiResponse(
                                400,
                                null,
                                'Method execution failed'
                            );
                        } else {
                            $response = new CmsApiResponse(200, $result);
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
                        table: 'log_performance',
                        entries: array(
                            "log" => json_encode($styleMetrics),
                            "id_user_activity" => $user_activity_id
                        )
                    );
                }
            });

            $response->send();
        } catch (Exception $e) {
            $response = new CmsApiResponse(500, null, $e->getMessage());
            $response->addAfterSendCallback(callback: function () use ($router, $debug_start_time): void {
                $router->log_user_activity($debug_start_time, true);
            });
            $response->send();
        }
    }

    /**
     * @brief Prepares parameters for method execution
     * 
     * Maps request parameters to method parameters based on reflection data,
     * handling optional parameters and default values.
     * 
     * @param ReflectionMethod $reflection Method reflection instance
     * @return array Array of prepared parameters for method execution
     */
    private function prepareMethodParameters(ReflectionMethod $reflection): array
    {
        $methodParameters = [];
        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $methodParameters[] = $this->params[$paramName] ??
                ($param->isOptional() ? $param->getDefaultValue() : null);
        }
        return $methodParameters;
    }
}
