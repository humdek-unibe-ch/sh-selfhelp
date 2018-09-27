<?php

/**
 * The class to define the basic functionality of an ajax request.
 */
class AjaxRequest
{
    /* Private Properties *****************************************************/

    /**
     * The instance of the request class.
     */
    private $request;

    /**
     * The name of the request class.
     */
    private $request_name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $request
     *  The name of a class to be instantiated.
     */
    public function __construct($db, $request)
    {
        $this->request = null;
        $this->request_name = $request;
        if(class_exists($request))
            $this->request = new $request($db);
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Prints the request response as a json string.
     */
    public function print_json()
    {
        $success = false;
        if(!$this->request)
            $data = "Unknown request '$this->request_name'";
        else if(!method_exists($this->request, "get_data"))
            $data = "Request '$this->request_name' has no method 'get_data()'";
        else
        {
            $data = $this->request->get_data();
            $success = ($data !== null);
        }
        echo json_encode(array(
            "success" => $success,
            "data" => $data,
        ));
    }
}
?>
