<?php
/**
 * The class to define the basic functionality of a model.
 */
abstract class BaseModel
{
    /* Private Properties *****************************************************/

    protected $router;
    protected $db;
    protected $nav;
    protected $login;
    protected $acl;
    protected $db_fields;
    protected $services;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $this->services = $services;
        $this->router = $services['router'];
        $this->db = $services['db'];
        $this->acl = $services['acl'];
        $this->login = $services['login'];
        $this->nav = $services['nav'];
        $this->parsedown = $services['parsedown'];
        $this->db_fields = array();
    }

    /* Protected Methods ******************************************************/

    /**
     * Returns an url given a router keyword. The keyword :back will generate
     * the url of the last visited page or the home page if the last visited
     * page is the current page or unknown.
     *
     * @retval string
     *  The generated url.
     */
    protected function get_url($url)
    {
        if($url == "#back")
        {
            if(isset($_SERVER['HTTP_REFERER'])
                    && ($_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']))
            {
                return htmlspecialchars($_SERVER['HTTP_REFERER']);
            }
            return $this->router->generate("home");
        }
        else if($url[0] == "#")
        {
            return $this->router->generate(substr($url, 1));
        }
        else
        {
            return $url;
        }
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
            if($field['name'] == "url")
                $field['content'] = $this->get_url($field['content']);
            else if($field['type'] == "markdown")
                $field['content'] = $this->parsedown->text($field['content']);
            else if($field['type'] == "markdown-inline")
                $field['content'] = $this->parsedown->line($field['content']);
            $this->db_fields[$field['name']] = array(
                "content" => $field['content'],
                "type" => $field['type']
            );
        }
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
    protected function set_db_fields_full($fields)
    {
        $this->db_fields = $fields;
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the content of a data field given a specific key. If the key does
     * not exist an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The content of the field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key)
    {
        $field = $this->get_db_field_full($key);
        if($field == "") return "";
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
     * Generates the url of a link, given a router keyword.
     *
     * @param string $key
     *  A router key.
     *
     * @retval string
     *  The generated link url.
     */
    public function get_link_url($key, $params=array())
    {
        return $this->router->generate($key, $params);
    }

    /**
     * Checks whether a link, defined by a router key, is currently active.
     *
     * @param string $key
     *  A router key.
     *
     * @retval bool
     *  True if the link specified bt the router key is active, false otherwise.
     */
    public function is_link_active($key)
    {
        $sql = "SELECT pj.keyword FROM pages AS p
            LEFT JOIN pages AS pj ON p.id = pj.parent
            WHERE p.keyword = :keyword AND pj.keyword IS NOT NULL";
        $matches = $this->db->query_db($sql, array(":keyword" => $key));
        foreach($matches as $match)
            if($this->router->is_active($match['keyword'])) return true;
        return $this->router->is_active($key);
    }

    /**
     * Get the model services.
     *
     * @retval array
     *  An associative array with the available services.
     */
    public function get_services()
    {
        return $this->services;
    }

    public function get_next_nav_id()
    {
        if($this->nav != null)
            return $this->nav->get_next_id();
        return 0;
    }

    public function get_previous_nav_id()
    {
        if($this->nav != null)
            return $this->nav->get_previous_id();
        return 0;
    }

    public function get_count()
    {
        if($this->nav != null)
            return $this->nav->get_count();
        return 0;
    }

    public function has_navigation()
    {
        return ($this->nav != null);
    }

    /**
     * Gets a navigation itme prefix if available. The prefix corresponds to
     * the title field of the navigation section.
     *
     * @return string
     *  The navigation item prefix.
     */
    public function get_item_prefix()
    {
        if($this->nav == null) return "";
        $db_fields = $this->db->fetch_section_fields($this->nav->get_root_id());
        foreach($db_fields as $field)
            if($field['name'] == "title")
                return $field['content'];
        return "";
    }

    /**
     * Gets the hierarchical assembled navigation items.
     *
     * @return array
     *  A hierarchical array. See NavSectionModel::fetch_children($id_section).
     */
    public function get_navigation_items()
    {

        if($this->nav != null)
            return $this->nav->get_navigation_items();
        return array();
    }
}
?>
