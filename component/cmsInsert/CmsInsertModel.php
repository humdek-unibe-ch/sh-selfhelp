<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cms component such
 * that the data can easily be displayed in the view of the component.
 */
class CmsInsertModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    public function create_new_page($keyword, $url, $protocol, $type)
    {
        $nav_id = null;
        if($type == 4)
        {
            $type = 3;
            $nav_id = $this->create_new_navigation_section($keyword);
        }
        $pid = $this->db->insert("pages", array(
            "keyword" => $keyword,
            "url" => $url,
            "protocol" => $protocol,
            "id_navigation_section" => $nav_id,
            "id_actions" => $type,
            "id_type" => EXPERIMENT_PAGE_ID,
        ));
        $this->set_page_acl($pid);
        return $pid;
    }
}
?>
