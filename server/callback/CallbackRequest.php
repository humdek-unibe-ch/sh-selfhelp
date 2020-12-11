<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, "Callback") === 0)
        require_once __DIR__ . '/' . $class_name . ".php";
});

/**
 * The class to define the basic functionality of an ajax request.
 */
class CallbackRequest
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

    /* Constructors ***********************************************************/

    /**
     * The constructor. Only public methods are accessible
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param string $class_name
     *  The name of the calss to be instantiated.
     * @param string $method_name
     *  The name of the method to be called on the instcane of
     *  AjaxRequest::class_name.
     */
    public function __construct($services, $class_name, $method_name = null)
    {
        $this->services = $services;
        $this->class_name = $class_name;
        $this->method_name = $method_name;
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Prints the request response as a json string.
     */
    public function print_json()
    {
        // only public methods are accessible for a call
        $success = false;
        if (class_exists($this->class_name)) {
            $instance = new $this->class_name($this->services);
            if (!method_exists($instance, $this->method_name)) {
                $data = "Request '$this->class_name' has no method '$this->method_name'";
            } else {
                $reflection = new ReflectionMethod($instance, $this->method_name);
                if ($reflection->isPublic()) {
                    $data = call_user_func_array(
                        array($instance, $this->method_name),
                        array($_POST)
                    );
                    $success = ($data !== null);
                } else {
                    $data = "Request '$this->class_name' method '$this->method_name'" . " is not public";                    
                }
            }
        } else {
            $data = "Unknown request class '" . $this->class_name . "'";
        }
    }
}
?>
