<?php
/**
 * This class is used to prepare all data related to the session component such
 * that the data can easily be displayed in the view of the component.
 */
class SessionModel
{
    /* Private Properties *****************************************************/

    private $db;
    private $id;
    private $section_fields;
    private $page_fields;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db, $id)
    {
        $this->db = $db;
        $this->fields = array();
        $db_fields = $this->db->fetch_section_fields($id);
        foreach($db_fields as $field)
            $this->section_fields[$field['name']] = $field['content'];

        $db_fields = $this->db->fetch_page_fields("session");
        foreach($db_fields as $field)
            $this->page_fields[$field['name']] = $field['content'];
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
        return $this->section_fields["title"];
    }

    /**
     * Get the label of the back button.
     *
     * @retval string
     *  The label of the back button.
     */
    public function get_back_label()
    {
        return $this->page_fields["back"];
    }

    /**
     * Get the title of the next button.
     *
     * @retval string
     *  The label of the next button.
     */
    public function get_next_label()
    {
        return $this->page_fields["next"];
    }
}
?>
