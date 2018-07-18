<?php
/**
 * This class is used to prepare all data related to the login component such
 * that the data can easily be displayed in the view of the component.
 */
class LoginModel
{
    /* Private Properties *****************************************************/

    private $db;
    private $db_fields;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $sql = "SELECT st.title, st.content FROM sections_translation AS st
            LEFT JOIN languages AS l ON l.id = st.id_languages
            LEFT JOIN pages_sections AS ps ON ps.id_sections = st.id
            LEFT JOIN pages AS p ON p.id = ps.id_pages
            WHERE l.locale = :locale AND p.keyword = 'login'";
        $fields = $db->query_db($sql,
            array(":locale" => $_SESSION['language']));
        foreach($fields as $field)
        $this->db_fields[$field['title']] = $field['content'];
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
        return array_key_exists($key, $this->db_fields) ? $this->db_fields[$key] : "";
    }
}
?>
