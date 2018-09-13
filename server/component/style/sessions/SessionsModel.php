<?php
require_once __DIR__ . "/../StyleModel.php";
require_once SERVICE_PATH . "/Navigation.php";
/**
 * This class is used to prepare all data related to the sessions component such
 * that the data can easily be displayed in the view of the component.
 */
class SessionsModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all sessions related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $db_fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($db_fields);
        $this->nav = new Navigation($this->router, $this->db, "session", 25);
    }

    /**
     * Gets a navigation itme prefix if available. The prefix corresponds to
     * the title field of the navigation section.
     *
     * @return string
     *  The navigation item prefix.
     */
    public function get_item_prefix()
    {
        if($this->nav == null) return "";
        $db_fields = $this->db->fetch_section_fields($this->nav->get_root_id());
        foreach($db_fields as $field)
            if($field['name'] == "title")
                return $field['content'];
        return "";
    }
}
?>
