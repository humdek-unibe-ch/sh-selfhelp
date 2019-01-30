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
     * The DB handler
     */
    private $db;

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
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $class_name
     *  The name of the calss to be instantiated.
     * @param string $method_name
     *  The name of the method to be called on the instcane of
     *  AjaxRequest::class_name.
     */
    public function __construct($db, $class_name, $method_name=null)
    {
        $this->db = $db;
        $this->class_name = $class_name;
        $this->method_name = $method_name;
        $login = new Login($db);
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
            $instance = new $this->class_name($this->db);
            if(!method_exists($instance, $this->method_name))
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
            "data" => $data,
        ));
    }
}
?>
