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
    protected $children;

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
        $this->children = array();
        $this->services = $services;
        $this->router = $services['router'];
        $this->db = $services['db'];
        $this->acl = $services['acl'];
        $this->login = $services['login'];
        $this->nav = $services['nav'];
        $this->parsedown = $services['parsedown'];
        $this->db_fields = array();
    }

    /* Public Methods *********************************************************/

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
     * Gets the child components.
     *
     * @retval array
     *  An array of style components.
     */
    public function get_children()
    {
        return $this->children;
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

    /**
     * Return the id of the next navigation section if a navigation exists.
     *
     * @retval int
     *  The id of the next navigation section or 0 if no navigation is
     *  avaliable.
     */
    public function get_next_nav_id()
    {
        if($this->nav != null)
            return $this->nav->get_next_id();
        return 0;
    }

    /**
     * Return the id of the previous navigation section if a navigation exists.
     *
     * @retval int
     *  The id of the previous navigation section or 0 if no navigation is
     *  avaliable.
     */
    public function get_previous_nav_id()
    {
        if($this->nav != null)
            return $this->nav->get_previous_id();
        return 0;
    }

    /**
     * Gets the number of navigation items.
     *
     * @retval int
     *  The number of navigation items.
     */
    public function get_count()
    {
        if($this->nav != null)
            return $this->nav->get_count();
        return 0;
    }

    /**
     * Checks whether a navigation is available.
     *
     * @retval bool
     *  True if a navigation is available, false otherwise.
     */
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
