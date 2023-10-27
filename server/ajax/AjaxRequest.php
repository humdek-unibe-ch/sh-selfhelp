<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
spl_autoload_register(function ($class_name) {
    if(strpos($class_name, "Ajax") === 0)
        require_once __DIR__ . '/' . $class_name . ".php";
});

/**
 * The class to define the basic functionality of an ajax request.
 */
class AjaxRequest
{
    /* Private Properties *****************************************************/

    /**
     * The service handler instance which holds all services
     */
    private $services;

    /**
     * The name of the request class.
     */
    private $class_name = null;

    /**
     * The name of the request method.
     */
    private $method_name = null;

    /**
     * Keyword of the request url.
     */
    private $keyword = null;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param string $keyword
     *  The keyword, it will be used to find the url request permissions
     * @param string $class_name
     *  The name of the calss to be instantiated.
     * @param string $method_name
     *  The name of the method to be called on the instance of
     *  AjaxRequest::class_name.
     */
    public function __construct($services, $class_name, $method_name=null, $keyword='')
    {
        $this->services = $services;
        $this->class_name = $class_name;
        $this->method_name = $method_name;
        $this->keyword = $keyword;
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Prints the request response as a json string.
     */
    public function print_json()
    {
        $success = false;
        if(class_exists($this->class_name))
        {
            $instance = new $this->class_name($this->services);
            if(!$instance->has_access($this->keyword))
                $data = "Access denied'";
            else if(!method_exists($instance, $this->method_name))
                $data = "Request '$this->class_name' has no method '$this->method_name'";
            else
            {
                $data = call_user_func_array(array($instance, $this->method_name),
                    array($_POST));
                $success = ($data !== null);
            }
        }
        else
            $data = "Unknown request class '".$this->class_name."'";
        echo json_encode(array(
            "success" => $success,
            "data" => $data
        ));
    }
}
?>
