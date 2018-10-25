<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";

/**
 * This class is used to prepare all data related to the form style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class FormBaseModel extends StyleModel
{
    /* Private Properties *****************************************************/

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
    }

    /**
     * Check whether user has already submitted data to this form field.
     *
     * @param int $id
     *  The section id of the field to check for.
     * @retval bool
     *  True if data exists, false otherwise.
     */
    public function has_field_data($id)
    {
        $sql = "SELECT * FROM user_input
            WHERE id_sections = :id AND id_section_form = :fid
            AND id_users = :uid";
        $res = $this->db->query_db($sql, array(
            ":id" => $id,
            ":fid" => $this->get_db_field("id"),
            ":uid" => $_SESSION['id_user'],
        ));
        if($res) return true;
        else return false;
    }

    /**
     * Check whether user has already submitted data to this form.
     *
     * @retval bool
     *  True if data exists, false otherwise.
     */
    public function has_form_data()
    {
        $sql = "SELECT * FROM user_input
            WHERE id_section_form = :fid AND id_users = :uid";
        $res = $this->db->query_db($sql, array(
            ":fid" => $this->get_db_field("id"),
            ":uid" => $_SESSION['id_user'],
        ));
        if($res) return true;
        else return false;
    }

    /**
     * Fetch the label of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the label will be fetched.
     * @retval string
     *  The label of the form field or the empty string if the label is not
     *  available.
     */
    public function get_field_label($id_section)
    {
        $sql = "SELECT sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            WHERE f.name = 'label' AND sft.id_sections = :id";
        $label = $this->db->query_db_first($sql,
            array(":id" => $id_section));
        if($label) return $label["content"];
        return "";
    }

    /**
     * Fetch the style of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the style will be fetched.
     * @retval string
     *  The style of the form field or the empty string if the style is not
     *  available.
     */
    public function get_field_style($id_section)
    {
        $sql = "SELECT st.name FROM styles AS st
            LEFT JOIN sections AS s ON s.id_styles = st.id
            WHERE s.id = :id";
        $style = $this->db->query_db_first($sql,
            array(":id" => $id_section));
        if($style) return $style["name"];
        return "";
    }

    /**
     * Fetch the type of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the type will be fetched.
     * @retval string
     *  The type of the form field or the empty string if the type is not
     *  available.
     */
    public function get_field_type($id_section)
    {
        $sql = "SELECT sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            WHERE f.name = 'type_input' AND sft.id_sections = :id";
        $type = $this->db->query_db_first($sql,
            array(":id" => $id_section));
        if($type) return $type["content"];
        return "";
    }

    /**
     * Save the user input to the database.
     *
     * @param array $user_input
     *  The array of input key => value pairs where the key is the name of the
     *  input field.
     * @param bool $log
     *  If set to true, each data set is saved as a timestamped new entry.
     *  If set to false, existing data is updated.
     * @retval int
     *  The number of affected rows in the database or false if an error
     *  ocurred.
     */
    public function save_user_input($user_input, $log = false)
    {
        $count = 0;
        foreach($user_input as $id => $value)
        {
            if($log || !$this->has_field_data($id))
                $res = $this->insert_new_entry($id, $value);
            else
                $res = $this->update_entry($id, $value);

            if($res === false)
                return false;
            else
                $count += $res;
        }
        return $count;
    }

    /**
     * Insert a new form field entry to the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function insert_new_entry($id, $value)
    {
        return $this->db->insert("user_input", array(
            "id_users" => intval($_SESSION['id_user']),
            "id_sections" => $id,
            "id_section_form" => $this->get_db_field("id"),
            "value" => $value,
        ));
    }

    /**
     * Update a form field entry in the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function update_entry($id, $value)
    {
        return $this->db->update_by_ids("user_input",
            array(
                "value" => $value,
            ),
            array(
                "id_users" => intval($_SESSION['id_user']),
                "id_sections" => $id,
                "id_section_form" => $this->get_db_field("id"),
            )
        );
    }
}
?>
