<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";
require_once $_SERVER['DOCUMENT_ROOT'] . SERVICE_PATH . "/Navigation.php";
/**
 * This class is used to prepare all data related to the accordion list
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class AccordionListModel extends StyleModel
{
    /* Private Properties *****************************************************/

    private $id_active;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $items = $this->get_db_field("items");
        if(isset($items['nav_page']))
        {
            $sql = "SELECT id_navigation_section AS id_nav FROM pages
                WHERE keyword = :key";
            $id_nav = $this->db->query_db_first($sql,
                array(":key" => $items['nav_page']));
            if($id_nav)
            {
                $nav = new Navigation($this->router, $this->db,
                    $items['nav_page'], $id_nav["id_nav"]);
                $this->set_db_field("items", $nav->get_navigation_items());
            }
        }
        else
            $this->set_db_field("items", array());
    }
}
?>
