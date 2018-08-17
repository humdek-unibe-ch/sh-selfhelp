<?php
require_once __DIR__ . "/SearchSection.php";

/**
 * The class to define the basic functionality of an ajax request.
 */
class AjaxRequest
{
    /* Private Properties *****************************************************/

    private $request;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $request
     */
    public function __construct($db, $request)
    {
        $this->request = new $request($db);
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    public function print_json()
    {
        $data = $this->request->get_data();
        echo json_encode(array(
            "success" => ($data !== null),
            "data" => $data
        ));
    }
}
?>
