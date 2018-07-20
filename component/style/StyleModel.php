<?php
/**
 * This class is used to prepare all data related to the style component such
 * that the data can easily be displayed in the view of the component.
 */
class StyleModel
{
    /* Private Properties *****************************************************/

    private $section;
    private $fields;
    private $router;
    private $db;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches a section item from the database and assignes
     * the fetched content to private class properties.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The id of the database section item to be rendered.
     */
    public function __construct($router, $db, $id)
    {
        $this->router = $router;
        $this->db = $db;
        $this->section = $db->select_by_uid_join("sections", $id);

        $this->fields = $this->fetch_section_content($id);
    }
    /* Private Methods ********************************************************/

    /**
     * Fetch the content of the section fields from the database given a section
     * id.
     *
     * @param int $id
     *  The id of the section.
     * @retval array
     *  An array prepared by StyleModel::prepare_section.
     */
    private function fetch_section_content($id)
    {
        $db_fields = $this->db->fetch_section_fields($id);
        return $this->prepare_section($id, $db_fields);
    }

    /**
     * Returns an url given a router keyword. The keyword :back will generate
     * the url of the last visited page or the home page if the last visited
     * page is the current page or unknown.
     *
     * @retval string
     *  The generated url.
     */
    private function get_url($url)
    {
        if($url == ":back")
        {
            if(isset($_SERVER['HTTP_REFERER'])
                    && ($_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']))
            {
                return htmlspecialchars($_SERVER['HTTP_REFERER']);
            }
            return $this->router->generate("home");
        }
        else
        {
            return $this->router->generate($url);
        }
    }

    /**
     * Prepare the fields array of section fields.
     *
     * @param int $id
     *  The id of the section.
     * @param array $db_fields
     *  An associative array returned by a db querry.
     * @retval array
     *  An array of the from <field_name> => <field_content>.
     */
    private function prepare_section($id, $db_fields)
    {
        $fields = array();
        foreach($db_fields as $field)
        {
            if($field['name'] == "url")
                $field['content'] = $this->get_url($field['content']);
            $fields[$field['name']] = $field['content'];
        }
        return $fields;
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the data filed given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @param string $key
     *  The field name.
     * @retval string
     *  The content of the filed specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key)
    {
        if(array_key_exists($key, $this->fields))
            return $this->fields[$key];
        else
            return "";
    }

    /**
     * Returns the style name. This will be used to load the corresponding
     * template.
     *
     * @retval string
     *  The style name.
     */
    public function get_tpl_name()
    {
        return $this->section['name_styles'];
    }

    /**
     * Returns the children section ids of a saection fomr the database.
     *
     * @param int $id
     *  The id of the parent section.
     * @retval array
     *  An array containing the children section ids stored as 'id' => <id>.
     */
    public function fetch_section_children($id)
    {
        $sql = "SELECT child AS id FROM sections_hierarchy
            WHERE parent = :id
            ORDER BY position";

        return $this->db->query_db($sql, array(":id" => $id));
    }
}
?>
