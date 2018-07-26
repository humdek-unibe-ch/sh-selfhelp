<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the profile component such
 * that the data can easily be displayed in the view of the component.
 */
class ProfileModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $fields = $this->db->fetch_page_fields("profile");
        $this->set_db_fields($fields);
    }
}
?>
