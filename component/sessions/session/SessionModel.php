<?php
require_once __DIR__ . "/../../BaseModel.php";
/**
 * This class is used to prepare all data related to the session component such
 * that the data can easily be displayed in the view of the component.
 */
class SessionModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The section id of this session.
     */
    public function __construct($services, $id)
    {
        $router = $services['router'];
        $db = $services['db'];
        parent::__construct($router, $db);
        $this->section_fields = array();
        $this->page_fields = array();
        $db_fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($db_fields);

        $db_fields = $this->db->fetch_page_fields("session");
        $this->set_db_fields($db_fields);

        $this->db_fields["content"] = array();
        $children = $this->db->fetch_section_children($id);
        foreach($children as $child)
            array_push($this->db_fields["content"],
               new StyleComponent($services, intval($child['id'])));
    }

    /* Public Methods *********************************************************/

    /**
     * Get the title of the session.
     *
     * @retval string
     *  The title of the session.
     */
    public function get_title()
    {
        return $this->get_db_field("title");
    }

    /**
     * Get the label of the back button.
     *
     * @retval string
     *  The label of the back button.
     */
    public function get_back_label()
    {
        return $this->get_db_field("back");
    }

    /**
     * Get the title of the next button.
     *
     * @retval string
     *  The label of the next button.
     */
    public function get_next_label()
    {
        return $this->get_db_field("next");
    }
}
?>
