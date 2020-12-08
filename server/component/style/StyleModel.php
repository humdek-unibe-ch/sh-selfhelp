<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/StyleComponent.php";
require_once __DIR__ . "/BaseStyleComponent.php";
require_once __DIR__ . "/IStyleModel.php";
/**
 * This class is used to prepare all data related to the style component such
 * that the data can easily be displayed in the view of the component.
 */
class StyleModel extends BaseModel implements IStyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The name of the section.
     */
    private $section_name;

    /**
     * The name of the style associated to the section.
     */
    private $style_name;

    /**
     * The type of the style associated to the section.
     */
    private $style_type;

    /**
     * The collection of fields that are attributed to this style component.
     */
    private $db_fields;

    /** 
     * An array of get parameters.
     */
    private $params;


    /* Constructors ***********************************************************/

    /**
     * The constructor fetches a section item from the database and assignes
     * the fetched content to private class properties.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     */
    public function __construct($services, $id, $params=array(), $id_page=-1)
    {
        parent::__construct($services);
        if($this->is_cms_page())
        {
            if($_SESSION['cms_gender'] !== "both")
                $_SESSION['gender'] = $_SESSION['cms_gender'];
            if($_SESSION['cms_language'] !== "all")
                $_SESSION['language'] = $_SESSION['cms_language'];
        }
        else
        {
            $_SESSION['gender'] = $_SESSION['user_gender'];
            $_SESSION['language'] = $_SESSION['user_language'];
        }
        $this->db_fields['id'] = array(
            "content" => $id,
            "type" => "internal",
        );

        $sql = "SELECT s.id, sec.name, s.name AS style, t.name AS type
            FROM styles AS s
            LEFT JOIN styleType AS t ON t.id = s.id_type
            LEFT JOIN sections AS sec ON sec.id_styles = s.id
            WHERE sec.id = :id";
        $style = $this->db->query_db_first($sql, array(":id" => $id));
        if(!$style) return;
        $this->style_name = $style['style'];
        $this->style_type = $style['type'];
        $this->section_name = $style['name'];

        $fields = $this->db->fetch_page_fields($this->get_style_name());
        $this->set_db_fields($fields);

        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields);

        $db_children = $this->db->fetch_section_children($id);
        foreach($db_children as $child)
        {
            $this->children[$child['name']] = new StyleComponent(
                $services, intval($child['id']), $params, $id_page);
        }
        $this->params = $params;
    }

    /* Private Methods ********************************************************/

    /**
     * Fetch the data from the database base on the JSON configuration
     * @param array $data_config
     * Json configuration
     * @retval array
     * array with the retrieved fields and their values
     */
    private function fetch_data($data_config)
    {
        $result = array();
        foreach ($data_config as $key => $config) {
            // loop configs; DB requests
            $table_id = $config['type'] === 'static' ? $this->get_static_table_id($config['table']) : $this->get_dynamic_table_id($config['table']);
            $data = null;
            if ($table_id) {
                if ($config['type'] === 'static') {
                    $filter = "ORDER BY row_id ASC";
                    if ($config['retrieve'] === 'last') {
                        $filter = "ORDER BY row_id DESC";
                    }
                } else {
                    $filter = "ORDER BY edit_time ASC";
                    if ($config['retrieve'] === 'last') {
                        $filter = "ORDER BY edit_time DESC";
                    }
                }
                $data = $config['type'] === 'static' ? $this->get_static_data($table_id, $filter) : $this->get_dynamic_data($table_id, $filter);
                foreach ($config['fields'] as $key => $field) {
                    // loop fields
                    $field_value =  isset($data[0][$field['field_name']]) ? $data[0][$field['field_name']] : $field['not_found_text']; // get the first value
                    if ($config['retrieve'] === 'all') {
                        // get the other values too
                        $field_value = '"' . $field_value . '"'; // add quotes to the first entry in the array
                        foreach ($data as $key => $row) {
                            if ($key > 0) {
                                // we already got the first row
                                if (isset($row[$field['field_name']])) {
                                    $field_value = $field_value . ',"' . $row[$field['field_name']] . '"';
                                }
                            }
                        }
                        $field_value = "[" . $field_value . "]"; // add array bracket around the whole result
                    }
                    $result[$field['field_holder']] = $field_value;
                }
            }
        }
        return $result;
    }

    /**
     * Get staic data from the database
     * @param int $table_id
     * id of the table that we want to retrieve
     * @param string $filter
     * filter used to sort the data
     * @retval array
     * the results rows in array
     */
    private function get_static_data($table_id, $filter)
    {
        $sql = 'CALL get_uploadTable_with_filter(:table_id, :filter)';
        return $this->db->query_db($sql, array(
            ":table_id" => $table_id,
            ":filter" => $filter
        ));
    }

    /**
     * Get dynamic data from the database
     * @param int $table_id
     * id of the table that we want to retrieve
     * @param string $filter
     * filter used to sort the data
     * @retval array
     * the results rows in array
     */
    private function get_dynamic_data($table_id, $filter)
    {
        $sql = 'CALL get_form_data_for_user_with_filter(:table_id, :user_id, :filter)';
        return $this->db->query_db($sql, array(
            ":table_id" => $table_id,
            ":user_id" => $_SESSION['id_user'],
            ":filter" => $filter
        ));
    }

    /**
     * Get the static table id based on name
     * @param string $table_name
     * table name
     * @retval int table id
     */
    private function get_static_table_id($table_name)
    {
        $sql = 'SELECT * 
                FROM view_data_tables 
                WHERE orig_name = :name';
        return $this->db->query_db_first($sql, array(
            ":name" => $table_name
        ))['id'];
    }

    /**
     * Get the dynamic table id based on name
     * @param string $table_name
     * table name
     * @retval int table id
     */
    private function get_dynamic_table_id($table_name)
    {
        $sql = 'SELECT * 
                FROM view_user_input 
                WHERE form_name = :name';
        return $this->db->query_db_first($sql, array(
            ":name" => $table_name
        ))['form_id'];
    }

    /**
     * Parse the page params and if they are needed in the config they are replaced with their values
     * @param array $data_config
     * The json config
     * @retval array the json parsed and parameters assigned
     */
    private function parse_params($data_config)
    {
        $str_data = json_encode($data_config);
        preg_match_all('~#\w+\b~', $str_data, $m);
        foreach ($m as $key => $value) {
            if ($value) {
                $param_name = str_replace('#', '', $value[0]);
                if (isset($this->params[$param_name])) {
                    $ser_data = str_replace($value[0], $this->params[$param_name], $str_data);
                }
            }
        }
        return isset($ser_data) ? json_decode($ser_data, true) : $data_config;
    }

    /* Protected Methods ******************************************************/

    /**
     * Returns an url given a router keyword. The keyword \#back will generate
     * the url of the last visited page or the home page if the last visited
     * page is the current page or unknown. The keyword \#self points to the
     * current page.
     *
     * @retval string
     *  The generated url.
     */
    protected function get_url($url)
    {
        return $this->router->get_url($url);
    }

    /**
     * Set the content of a db_field attribute of the model.
     *
     * @param string $key
     *  The name of the db field.
     * @param mixed $content
     *  The content of the db field.
     */
    protected function set_db_field($key, $content)
    {
        if($this->get_db_field_full($key) == "") return;
        $this->db_fields[$key]['content'] = $content;
    }

    /**
     * Set the db_fields attribute of the model. Each field is assigned as an
     * key => value element where the key is the field name and the value the
     * field content.
     *
     * @param array $fields
     *  An array of field items where one item is an associative array of the
     *  form:
     *   "name" => name of the db field
     *   "content" => the content of the db field
     */
    protected function set_db_fields($fields)
    {
        foreach($fields as $field)
        {
            $default = $field["default_value"] ?? "";
            if($field['name'] == "url")
                $field['content'] = $this->get_url($field['content']);
            else if($field['type'] == "markdown")
                $field['content'] = $this->parsedown->text($field['content']);
            else if($field['type'] == "markdown-inline")
                $field['content'] = $this->parsedown->line($field['content']);
            else if($field['type'] == "json")
            {
                $field['content'] = json_decode($field['content'], true);
                /* $field['content'] = $this->json_style_parse($field['content']); */
            }
            $this->db_fields[$field['name']] = array(
                "content" => $field['content'],
                "type" => $field['type'],
                "id" => $field['id'],
                "default" => $default,
            );
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Checks whether the current page is a CMS page.
     *
     * @retval bool
     *  true if the current page is a CMS page, false otherwise.
     */
    public function is_cms_page()
    {
        return ($this->is_link_active("cms")
            || $this->is_link_active("cmsSelect")
            || $this->is_link_active("cmsUpdate")
            || $this->is_link_active("cmsInsert")
            || $this->is_link_active("cmsDelete")
        );

    }

    /**
     * Returns the content of a data field given a specific key. If the key does
     * not exist an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     * @param mixed $default
     *  The default field value to be returned if the field is not set.
     *
     * @retval string
     *  The content of the field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key, $default="")
    {
        $field = $this->get_db_field_full($key);
        if($field == "")
        {
            if(isset($field['default']) && $field['default'] != "")
                return $field['default'];
            else
                return $default;
        }
        return $field['content'];
    }

    /**
     * Returns the data field given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field_full($key)
    {
        if(array_key_exists($key, $this->db_fields))
            return $this->db_fields[$key];
        else
            return "";
    }

    /**
     * Returns the db field array where each field item is stores as a key,
     * value pair. The key corresponds to the name of the field and the value to
     * the content of the field.
     *
     * @retval array
     *  The key, value pairs describing data fields.
     */
    public function get_db_fields()
    {
        return $this->db_fields;
    }

    /**
     * Returns the style name. This will be used to load the corresponding
     * template.
     *
     * @retval string
     *  The style name.
     */
    public function get_style_name()
    {
        return $this->style_name;
    }

    /**
     * Returns the style type.
     *
     * @retval string
     *  The style type.
     */
    public function get_style_type()
    {
        return $this->style_type;
    }

    /**
     * Returns the section name.
     *
     * @retval string
     *  The section name.
     */
    public function get_section_name()
    {
        return $this->section_name;
    }

    /**
     * Search for a child section of a specific name.
     *
     * @param string $name
     *  The name of the section to be seacrhed
     * @retval reference
     *  Reference to the section instance.
     */
    public function &get_child_section_by_name($name)
    {
        if(array_key_exists($name, $this->children))
            return $this->children[$name];
        foreach($this->children as $child)
        {
            $section = $child->get_child_section_by_name($name);
            if($section !== null)
                return $section;
        }
        return null;
    }

    /**
     * This function is called whenever the style component is updated via the
     * CMS. Redefine within the style.
     */
    public function cms_update_callback($model) { }

     /**
     * Retrieve the data based on the JSON configuration
     * @param array $data_config
     * JSON structure
     * @retval array with the retrieved fields and their values, empty string if fails.
     */
    public function retrieve_data($data_config)
    {
        $parsed_data = $this->parse_params($data_config);
        if (!$parsed_data) {
            return '';
        } else {
            return $this->fetch_data($parsed_data);
        }
    }

}
?>
