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
        $sql = "SELECT f.name, sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            LEFT JOIN languages AS l ON l.id = sft.id_languages
            LEFT JOIN sections AS s ON s.id = sft.id_sections
            WHERE s.name = 'login' AND l.locale = :locale";
        $fields = $db->query_db($sql,
            array(":locale" => $_SESSION['language']));
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
