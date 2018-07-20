<?php
/**
 * This class is used to prepare all data related to the sessions component such
 * that the data can easily be displayed in the view of the component.
 */
class SessionsModel
{
    /* Private Properties *****************************************************/

    private $db;
    private $fields;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all sessions related fields from the database.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->fields = array();
        $db_fields = $db->fetch_page_fields("sessions");
        foreach($db_fields as $field)
            $this->fields[$field['name']] = $field['content'];
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the data filed given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @retval string
     *  The content of the filed specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key)
    {
        return array_key_exists($key, $this->fields) ? $this->fields[$key] : "";
    }
}
?>
