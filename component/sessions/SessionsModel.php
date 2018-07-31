<?php
require_once __DIR__ . "/../BaseModel.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/service/Navigation.php";
/**
 * This class is used to prepare all data related to the sessions component such
 * that the data can easily be displayed in the view of the component.
 */
class SessionsModel extends BaseModel
{
    private $id_nav;

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
        $sections = $this->db->fetch_page_sections("sessions");
        $this->id_nav = 0;
        foreach($sections as $section)
            if(intval($section['id_styles']) == NAVIGATION_STYLE_ID)
            {
                $this->id_nav = intval($section['id']);
                break;
            }
        $this->nav = new Navigation($this->router, $this->db, "session",
            $this->id_nav);
    }
}
?>
