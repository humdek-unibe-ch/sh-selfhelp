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
        $locale_cond = $db->get_locale_condition();
        $sql = "SELECT f.name, pft.content
            FROM pages_fields_translation AS pft
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN pages AS p ON p.id = pft.id_pages
            WHERE p.keyword = 'login' AND $locale_cond";
        $fields = $db->query_db($sql, array());
        foreach($fields as $field)
            $this->db_fields[$field['name']] = $field['content'];
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
