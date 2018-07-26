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
        $this->router = $services['router'];
        $this->db = $services['db'];
        $this->acl = $services['acl'];
        $this->login = $services['login'];
        $this->nav = $services['nav'];
        $this->db_fields = array();
    }

    /* Protected Methods ******************************************************/

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
            $this->db_fields[$field['name']] = $field['content'];
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the data filed given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The content of the filed specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key)
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
        return $this->router->is_active($key);
    }
}
?>
