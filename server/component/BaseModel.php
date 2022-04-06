<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * The class to define the basic functionality of a model.
 */
abstract class BaseModel
{
    /* Private Properties *****************************************************/

    /**
     *  The router instance is used to generate valid links.
     */
    protected $router;

    /**
     *  The db instance which grants access to the DB.
     */
    protected $db;

    /**
     * The instance to the navigation service which allows to switch between
     * sections, associated to a specific page.
     */
    protected $nav;

    /**
     * The login instance that allows to check user credentials.
     */
    protected $login;

    /**
     * The instnce of the access control layer (ACL) which allows to decide
     * which links to display.
     */
    protected $acl;

    /**
     * The parsedown instance that allows to parse markdown content.
     */
    protected $parsedown;

    /**
     * The instance instance that is used to log transactions in the database.
     */
    protected $transaction;

    /**
     * User input handler.
     */
    protected $user_input;

    /**
     * Mail handler.
     */
    protected $mail;

    /**
     * JobScheduler handler.
     */
    protected $job_scheduler;

    /**
     * An associative array holding the different available services. See the
     * class definition basepage for a list of all services.
     */
    protected $services;

    /**
     * The collection of child components that are assigend to this component.
     */
    protected $children;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        $this->children = array();
        $this->services = $services;
        $this->router = $services->get_router();
        $this->db = $services->get_db();
        $this->acl = $services->get_acl();
        $this->login = $services->get_login();
        $this->transaction = $services->get_transaction();
        $this->nav = $services->get_nav();
        $this->parsedown = $services->get_parsedown();
        $this->user_input = $services->get_user_input();
        $this->job_scheduler = $services->get_job_scheduler();
    }

    /** Private Methods *******************************************************/

    /**
     * Get the url of a navigation item, given an id.
     *
     * @param int $id
     *  The id of the navigation item to generate the url.
     * @retval string
     *  The generated url or the empty string if the url could not be generated.
     */
    private function get_nav_item_url($id)
    {
        if($this->nav == null) return "";
        if($id == 0) return "";
        return $this->get_link_url($this->nav->get_page_keyword(),
            array("nav" => $id));
    }

    /* Protected Methods *********************************************************/
    

    /* Public Methods *********************************************************/

    /**
     * Generates the url of a link, given a router keyword.
     *
     * @param string $key
     *  A router key.
     * @param array $params
     *  The url parameters used to generate the url.
     *
     * @retval string
     *  The generated link url.
     */
    public function get_link_url($key, $params=array())
    {
        if($this->router->has_route($key))
            return $this->router->generate($key, $params);
        else
            return "";
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
     * Set the child components.
     *
     * @param array $children
     *  An array of style components.
     */
    public function set_children($children)
    {
        $this->children = $children;
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
     * Return the url of the next navigation section if a navigation exists.
     *
     * @retval string
     *  The url of the next navigation section or the empty string if no
     *  navigation is avaliable.
     */
    public function get_next_nav_url()
    {
        return $this->get_nav_item_url($this->nav->get_next_id());
    }

    /**
     * Return the url of the previous navigation section if a navigation exists.
     *
     * @retval string
     *  The url of the previous navigation section or the empty string if no
     *  navigation is avaliable.
     */
    public function get_previous_nav_url()
    {
        return $this->get_nav_item_url($this->nav->get_previous_id());
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

    /**
     * Get a list of languages and prepares the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':      The id of the language.
     *   'locale':   
     *   'language':   
     *   'csv_separator':
     */
    public function get_languages()
    {
        return $this->db->get_languages();
    }

    /**
     * get user groups from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_groups()
    {
        $groups = array();
        foreach ($this->db->select_table("groups") as $group) {
            array_push($groups, array("value" => intval($group['id']), "text" => $group['name']));
        }
        return $groups;
    }
    
}
?>
