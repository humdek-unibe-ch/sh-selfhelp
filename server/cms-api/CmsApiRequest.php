<?php
spl_autoload_register(function ($class_name) {
    if (substr($class_name, -6) === "CmsApi") {
        $file_name = $class_name . ".php";
        $directory = __DIR__;
        $file_path = recursiveFileSearch($directory, $file_name);

        if ($file_path) {
            require_once $file_path;
        }
    }
});

function recursiveFileSearch($directory, $file_name)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $file_name) {
            return $file->getPathname();
        }
    }
    return false;
}

/**
 * Class defining the basic functionality of a Cms API request.
 */
class CmsApiRequest
{
    private $services;
    private $class_name;
    private $method_name;
    private $keyword;
    private $params;

    public function __construct($services, $class_name, $method_name = null, $keyword = '')
    {
        $this->services = $services;
        $this->class_name = $class_name;
        $this->method_name = $method_name;
        $this->keyword = $keyword;
        $this->params = $this->collectParameters();
    }

    private function collectParameters()
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
        $inputData = file_get_contents('php://input');
        parse_str($inputData, $jsonData);

        if (!$jsonData || !is_array($jsonData)) {
            $jsonData = json_decode($inputData, true);
        }

        if ($jsonData !== null && json_last_error() === JSON_ERROR_NONE && count($jsonData) > 0) {
            $params['data'] = $jsonData;
        }

        // Decode URL-encoded parameters
        foreach ($params as $key => $value) {
            if (!is_array($value)) {
                $params[$key] = urldecode($value);
            }
        }

        return $params;
    }

    public function return_response()
    {
        $success = false;
        $data = null;

        if (class_exists($this->class_name)) {
            $instance = new $this->class_name($this->services, $this->keyword);
            $response = $instance->authorizeUser();
            $instance->init_response($response);

            if (method_exists($instance, $this->method_name)) {
                $reflection = new ReflectionMethod($instance, $this->method_name);

                if ($reflection->isPublic()) {
                    $parameters = $reflection->getParameters();

                    // Prepare parameters for method call
                    $methodParameters = [];
                    foreach ($parameters as $param) {
                        $paramName = $param->name;
                        $methodParameters[] = $this->params[$paramName] ?? ($param->isOptional() ? $param->getDefaultValue() : null);
                    }

                    // Execute the method
                    $data = call_user_func_array([$instance, $this->method_name], $methodParameters);
                    $success = ($data !== null);
                } else {
                    $data = "Request '{$this->class_name}' method '{$this->method_name}' is not public";
                }
            } else {
                $data = "Request '{$this->class_name}' has no method '{$this->method_name}'";
            }
        } else {
            $data = "Unknown request class '{$this->class_name}'";
        }

        header('Content-Type: application/json');
        echo json_encode(array("success" => $success, "data" => $data));
    }
}
?>
