<?php

/**
 * Class to deal with user inputs.
 */
class UserInput
{
    /* Private Properties *****************************************************/

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * The collection of input field attributes. See UserInput::set_field_attrs.
     */
    private $field_attrs;

    /* Constructors ***********************************************************/

    /**
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->set_field_attrs();
    }

    /* Private Methods ********************************************************/

    /**
     * Fetches all user input fields from the database given certain conditions.
     *
     * @param array $conds
     *  A key => value array of db conditions where the key corresponds to the
     *  db column and the value to the db value.
     * @retval array
     *  An array of field items wher eeach item has the following keys:
     *  - 'user_hash'     A unique string that connects values to a user without
     *                    revealing the identity of the user.
     *  - 'user_gender'   The gender of the user.
     *  - 'page'          The keyword of the page where the data was entered.
     *  - 'nav'           The name of the navigation section where the data was
     *                    entered.
     *  - 'field_name'    The name of the input field.
     *  - 'field_type'    The type of the input field. This is either the name
     *                    of the form field style or if the style is 'input' the
     *                    input type.
     *  - 'value'         The value that was entered by the user.
     *  - 'timestamp'     The date and time when the value was entered.
     */
    private function fetch_input_fields($conds = array())
    {
        $sql = "SELECT ui.id_users, ui.value, ui.edit_time, ui.id_sections,
            g.name AS gender
            FROM user_input AS ui
            LEFT JOIN users AS u ON u.id = ui.id_users
            LEFT JOIN genders AS g ON g.id = u.id_genders
            WHERE 1";
        foreach($conds as $key => $value)
            $sql .= " AND " . $key . " = '" . $value . "'";
        $fields_db = $this->db->query_db($sql);

        $fields = array();
        foreach($fields_db as $field)
        {
            $id = intval($field["id_sections"]);
            $fields[] = array(
                "user_hash" => substr(base_convert(hash("sha256",
                    $field["id_users"]), 16, 36), 0, 8),
                "user_gender" => $field['gender'],
                "page" => $this->field_attrs[$id]["page"],
                "nav" => $this->field_attrs[$id]["nav"],
                "field_name" => $this->field_attrs[$id]["name"],
                "field_type" => $this->field_attrs[$id]["type"],
                "value" => $field["value"],
                "timestamp" => $field["edit_time"],
            );
        }
        return $fields;
    }

    /**
     * Fetch the page name to which the given navigation section belongs.
     *
     * @param int $id_section
     *  The id of the section
     * @return string
     *  The page name or null if the name could not be found.
     */
    private function fetch_nav_section_page($id_section)
    {
        $sql = "SELECT p.keyword FROM sections_navigation AS sn
            LEFT JOIN pages AS p ON p.id = sn.id_pages
            WHERE sn.child = :id";
        $page = $this->db->query_db_first($sql, array(":id" => $id_section));
        if($page) return $page["keyword"];
        else return null;
    }

    /**
     * Fetch the name of a section.
     *
     * @param int $id
     *  The id of the section
     * @return string
     *  The section name or null if the name could not be found.
     */
    private function fetch_section_name($id)
    {
        $sql = "SELECT name FROM sections WHERE id = :id";
        $parent = $this->db->query_db_first($sql, array(":id" => $id));
        if($parent) return $parent["name"];
        else return null;
    }

    /**
     * Fetch the page name to which the given section belongs.
     *
     * @param int $id_section
     *  The id of the section
     * @return string
     *  The page name or null if the name could not be found.
     */
    private function fetch_section_page($id_section)
    {
        $sql = "SELECT p.keyword FROM pages_sections AS ps
            LEFT JOIN pages AS p ON p.id = ps.id_pages
            WHERE ps.id_sections = :id";
        $page = $this->db->query_db_first($sql, array(":id" => $id_section));
        if($page) return $page["keyword"];
        else return null;
    }

    /**
     * Fetch the id of the parent section.
     *
     * @param int $id_child
     *  The id of the child section.
     * @retval int
     *  The id of the parent section or null if no parent could be found.
     */
    private function fetch_section_parent($id_child)
    {
        $sql = "SELECT parent FROM sections_hierarchy WHERE child = :id";
        $parent = $this->db->query_db_first($sql, array(":id" => $id_child));
        if($parent) return intval($parent["parent"]);
        else return null;
    }

    /**
     * Find the page name and navigation section name of a given child section.
     *
     * @param int $id_section
     *  The id of the child section.
     * @retval array
     *  An array with the keys "page" and "nav" where the former holds the name
     *  of the parent page and the latter the name of the parent navigation
     *  section.
     */
    private function find_section_page($id_section)
    {
        $page = null;
        $nav = null;
        $parent_it = $this->fetch_section_parent($id_section);
        $parent = $parent_it;
        while($parent_it !== null)
        {
            $parent = $parent_it;
            $parent_it = $this->fetch_section_parent($parent_it);
        }
        if($parent !== null)
        {
            $page = $this->fetch_section_page($parent);
            if($page === null)
            {
                $page = $this->fetch_nav_section_page($parent);
                $nav = $this->fetch_section_name($parent);
            }
        }
        return array("page" => $page, "nav" => $nav);
    }

    /**
     * Collect attributes for each existing user input field.
     * The following attributes are set:
     *  - 'page'  The name of the parent page of the field.
     *  - 'nav'   The name of the parent navigation section
     *  - 'name'  The name of the field
     *  - 'type'  The type of the field
     */
    private function set_field_attrs()
    {
        $this->field_attrs = array();
        $sql = "SELECT DISTINCT ui.id_sections, sft_it.content AS input_type, sft_in.content AS input_name, st.name AS form_type FROM user_input AS ui
            LEFT JOIN sections_fields_translation AS sft_it ON sft_it.id_sections = ui.id_sections AND sft_it.id_fields = 69
            LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 55
            LEFT JOIN sections AS s ON s.id = ui.id_sections
            LEFT JOIN styles AS st ON st.id = s.id_styles";
        $sections = $this->db->query_db($sql);
        foreach($sections as $section)
        {
            $id = intval($section['id_sections']);
            $type = ($section['input_type'] != null) ? $section['input_type'] : $section['form_type'];
            $page = $this->find_section_page($id);
            $this->field_attrs[$id] = array(
                "page" => $page["page"],
                "nav" => $page["nav"],
                "name" => $section['input_name'],
                "type" => $type,
            );
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get all input fields given a filter
     *
     * @param array $filter
     *  The filter array can be empty or have any of the following keys:
     *   - 'gender'       This can either be set to 'male' or 'female'.
     *   - 'field_name'   Selects all fields with the given name.
     *   - 'page'         Selects all fields on a given page.
     *   - 'nav'          Selects all fields in a given navigation sections.
     *   - 'id_section'   Selects all fields with given section id.
     *   - 'id_user'      Selects all fields from a given user id.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields($filter = array())
    {
        $db_cond = array();
        if(isset($filter["gender"]))
            $db_cond["g.name"] = $filter["gender"];
        if(isset($filter["id_section"]))
            $db_cond["ui.id_sections"] = $filter["id_section"];
        if(isset($filter["id_user"]))
            $db_cond["ui.id_users"] = $filter["id_user"];
        $fields_all = $this->fetch_input_fields($db_cond);
        $fields = array();
        foreach($fields_all as $field)
            if((!isset($filter["field_name"]) || (isset($filter["field_name"])
                        && $field['field_name'] === $filter["field_name"]))
                && (!isset($filter["page"]) || (isset($filter["page"])
                        && $field['page'] === $filter["page"]))
                && (!isset($filter["nav"]) || (isset($filter["nav"])
                        && strpos($field['nav'], $filter["nav"]) !== false))
            )
                $fields[] = $field;
        return $fields;
    }

    /**
     * Get all input fields submitted by male users.
     *
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_gender_male()
    {
        return $this->get_input_fields(array("gender" => "male"));
    }

    /**
     * Get all input fields submitted by female users.
     *
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_gender_female()
    {
        return $this->get_input_fields(array("gender" => "female"));
    }

    /**
     * Get all input fields that match a field section id.
     *
     * @param int $field_id
     *  The field_id to match.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_field_id($field_id)
    {
        return $this->get_input_fields(array("id_sections" => $field_id));
    }

    /**
     * Get all input fields that match a field name.
     *
     * @param string $field_name
     *  The field_name to match.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_field_name($field_name)
    {
        return $this->get_input_fields(array("field_name" => $field_name));
    }

    /**
     * Get all input fields that are placed on a given page.
     *
     * @param string $keyword
     *  The page keyword to match.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_page($keyword)
    {
        return $this->get_input_fields(array("page" => $keyword));
    }

    /**
     * Get all input fields that are placed on a given navigation section.
     *
     * @param string $name
     *  The navigation section name to match. All navigation sections containing
     *  the given name are considered.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_nav($name)
    {
        return $this->get_input_fields(array("nav" => $name));
    }
}
?>
