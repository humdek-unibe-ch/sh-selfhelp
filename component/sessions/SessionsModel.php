<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the sessions component such
 * that the data can easily be displayed in the view of the component.
 */
class SessionsModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all sessions related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $db_fields = $this->db->fetch_page_fields("sessions");
        $this->set_db_fields($db_fields);
    }
}
?>
